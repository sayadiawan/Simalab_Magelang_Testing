@extends('masterweb::template.admin.layout')

@section('title')
  Pembayaran Pemeriksaan Bendahara
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home mr-1"></i> Beranda</a></li>
              <li class="breadcrumb-item active" aria-current="page">Pembayaran Pemeriksaan</li>
            </ol>
          </nav>

          <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

          <div class="row mb-3">
            <div class="col-md-3 mb-2">
              <label for="filter-source">Sumber</label>
              <select id="filter-source" class="form-control">
                <option value="all">Semua</option>
                <option value="klinik">Klinik</option>
                <option value="kesmas">Kesmas</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label for="filter-payment-status">Status Pembayaran</label>
              <select id="filter-payment-status" class="form-control">
                <option value="all">Semua</option>
                <option value="belum_lunas">Belum Lunas</option>
                <option value="lunas">Lunas</option>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label for="filter-date-start">Tanggal Mulai</label>
              <input type="date" id="filter-date-start" class="form-control">
            </div>
            <div class="col-md-3 mb-2">
              <label for="filter-date-end">Tanggal Akhir</label>
              <input type="date" id="filter-date-end" class="form-control">
            </div>
          </div>

          <div class="table-responsive">
            <table id="bendahara-pemeriksaan-table" class="table table-striped w-100">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Sumber</th>
                  <th>No Pemeriksaan</th>
                  <th>Nama</th>
                  <th>Tanggal</th>
                  <th>Nominal</th>
                  <th>Status Pembayaran</th>
                  <th>Cetak Dokumen</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade text-left" id="modal-payment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <form id="form-payment">
          <div class="modal-header">
            <h5 class="modal-title">Konfirmasi Pembayaran Klinik</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="id_permohonan_uji_klinik" name="id_permohonan_uji_klinik">
            <input type="hidden" name="nota_petugas_permohonan_uji_payment_klinik">
            <input type="hidden" name="nota_namapetugas_permohonan_uji_payment_klinik">
            <div class="row mb-3">
              <div class="col-md-6">
                <label>Nama Pasien</label>
                <input type="text" class="form-control" name="nama_pasien" readonly>
              </div>
              <div class="col-md-6">
                <label>Petugas</label>
                <input type="text" class="form-control" id="display_petugas" readonly>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <textarea class="form-control" name="alamat_pasien" rows="2" readonly></textarea>
            </div>
            <div id="payment-items-box" class="mb-3"></div>
            <div class="row">
              <div class="col-md-6">
                <label>Total Tagihan</label>
                <input type="text" class="form-control" id="display_total_tagihan" readonly>
              </div>
              <div class="col-md-6">
                <label>Sudah Dibayar</label>
                <input type="text" class="form-control" id="display_sudah_dibayar" readonly>
              </div>
            </div>
            <div class="form-group mt-3">
              <label for="terbayar_permohonan_uji_payment_klinik">Nominal Pembayaran</label>
              <input type="number" class="form-control" name="terbayar_permohonan_uji_payment_klinik" id="terbayar_permohonan_uji_payment_klinik">
            </div>
            <input type="hidden" name="total_harga_permohonan_uji_payment_klinik" id="total_harga_permohonan_uji_payment_klinik">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-payment-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detail Pembayaran Klinik</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="payment-detail-body"></div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEditNotaKesmas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Nota Kesmas</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="formEditNotaKesmas" method="POST" action="#">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="edit-nota-diterima-dari">Telah Diterima Dari</label>
              <input type="text" class="form-control" name="nota_diterima_dari" id="edit-nota-diterima-dari">
            </div>
            <div class="form-group mb-0">
              <label for="edit-nota-alamat">Alamat</label>
              <textarea class="form-control" name="nota_address_from" id="edit-nota-alamat" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script>
    $(function() {
      var CSRF_TOKEN = $('#csrf-token').val();
      var editNotaUrlBase = @json(url('elits-permohonan-uji/edit-nota'));
      var table = $('#bendahara-pemeriksaan-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: '{{ url()->current() }}',
          type: 'GET',
          data: function(d) {
            d.source_type = $('#filter-source').val();
            d.payment_status = $('#filter-payment-status').val();
            d.date_start = $('#filter-date-start').val();
            d.date_end = $('#filter-date-end').val();
          }
        },
        columns: [
          { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
          { data: 'jenis', name: 'jenis', orderable: false, searchable: false },
          { data: 'nomor', name: 'nomor' },
          { data: 'nama', name: 'nama' },
          { data: 'tanggal', name: 'tanggal' },
          { data: 'nominal', name: 'nominal' },
          { data: 'status_html', name: 'status_html', orderable: false, searchable: false },
          { data: 'dokumen_html', name: 'dokumen_html', orderable: false, searchable: false }
        ]
      });

      $('#filter-source, #filter-payment-status, #filter-date-start, #filter-date-end').on('change', function() {
        table.ajax.reload();
      });

      $('#bendahara-pemeriksaan-table').on('click', '.btn-edit-nota-kesmas', function(e) {
        e.preventDefault();
        var btn = $(this);
        $('#formEditNotaKesmas').attr('action', editNotaUrlBase + '/' + btn.data('id'));
        $('#edit-nota-diterima-dari').val(btn.data('diterima-dari') || '');
        $('#edit-nota-alamat').val(btn.data('alamat') || '');
        $('#modalEditNotaKesmas').modal('show');
      });

      $('#bendahara-pemeriksaan-table').on('click', '.btn-payment', function(e) {
        e.preventDefault();
        var permohonanId = $(this).data('id');

        $('#form-payment')[0].reset();
        $('#payment-items-box').html('<div class="text-muted"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</div>');
        $('#id_permohonan_uji_klinik').val(permohonanId);

        $.post('{{ route('permohonan-uji-klinik-get-payment2') }}', {
          _token: CSRF_TOKEN,
          permohonan_uji_klinik_id: permohonanId
        }, function(data) {
          $('[name="nota_petugas_permohonan_uji_payment_klinik"]').val(data.nota_petugas || '');
          $('[name="nota_namapetugas_permohonan_uji_payment_klinik"]').val(data.nota_namapetugas || '');
          $('[name="nama_pasien"]').val(data.nama_pasien || '');
          $('[name="alamat_pasien"]').val(data.alamat_pasien || '');
          $('#display_petugas').val(data.nota_namapetugas || '-');
          $('#display_total_tagihan').val(data.total_harga_custom || 'Rp. 0');
          $('#display_sudah_dibayar').val(data.sudah_dibayar_custom || 'Rp. 0');
          $('#total_harga_permohonan_uji_payment_klinik').val(data.total_harga || 0);

          var items = '';
          (data.items || []).forEach(function(item) {
            items += '<tr><td>' + item.type + '</td><td>' + item.name + '</td><td class="text-right">' + item.harga_custom + '</td></tr>';
          });
          if (!items) {
            items = '<tr><td colspan="3" class="text-center text-muted">Tidak ada rincian pembayaran</td></tr>';
          }
          $('#payment-items-box').html(
            '<table class="table table-sm table-bordered mb-0">' +
              '<thead><tr><th>Jenis</th><th>Item</th><th>Nominal</th></tr></thead>' +
              '<tbody>' + items + '</tbody>' +
            '</table>'
          );
          $('#modal-payment').modal('show');
        }, 'json');
      });

      $('#form-payment').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ route('permohonan-uji-klinik-store-payment2') }}', $(this).serialize(), function(resp) {
          alert(resp.pesan || 'Pembayaran berhasil disimpan.');
          $('#modal-payment').modal('hide');
          table.ajax.reload(null, false);
        }, 'json').fail(function(xhr) {
          alert((xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal menyimpan pembayaran.');
        });
      });

      $('#bendahara-pemeriksaan-table').on('click', '.btn-payment-detail', function(e) {
        e.preventDefault();
        var permohonanId = $(this).data('id');
        $('#payment-detail-body').html('<div class="text-muted"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat detail...</div>');
        $('#modal-payment-detail').modal('show');
        $.post('{{ route('permohonan-uji-klinik-payment-detail') }}', {
          _token: CSRF_TOKEN,
          permohonan_uji_klinik_id: permohonanId
        }, function(resp) {
          if (!resp.status || !resp.data) {
            $('#payment-detail-body').html('<div class="text-danger">Detail pembayaran tidak tersedia.</div>');
            return;
          }

          var rows = '';
          (resp.data.payments || []).forEach(function(item) {
            rows += '<tr>' +
              '<td>' + (item.no_nota || '-') + '</td>' +
              '<td class="text-right">' + new Intl.NumberFormat('id-ID').format(item.total_harga || 0) + '</td>' +
              '<td class="text-right">' + new Intl.NumberFormat('id-ID').format(item.terbayar || 0) + '</td>' +
              '<td>' + (item.petugas || '-') + '</td>' +
              '<td>' + (item.created_at || '-') + '</td>' +
            '</tr>';
          });

          if (!rows) {
            rows = '<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat pembayaran.</td></tr>';
          }

          var html = ''
            + '<div class="mb-3">'
            + '  <div><strong>No Register:</strong> ' + (resp.data.no_register || '-') + '</div>'
            + '  <div><strong>Nama Pasien:</strong> ' + (resp.data.nama_pasien || '-') + '</div>'
            + '  <div><strong>Total Tagihan:</strong> ' + (resp.data.total_tagihan_formatted || 'Rp. 0') + '</div>'
            + '  <div><strong>Total Terbayar:</strong> ' + (resp.data.total_terbayar_formatted || 'Rp. 0') + '</div>'
            + '</div>'
            + '<table class="table table-bordered table-sm">'
            + '  <thead><tr><th>No Nota</th><th>Total</th><th>Terbayar</th><th>Petugas</th><th>Tanggal</th></tr></thead>'
            + '  <tbody>' + rows + '</tbody>'
            + '</table>';

          $('#payment-detail-body').html(html);
        }, 'json');
      });
    });
  </script>
@endsection
