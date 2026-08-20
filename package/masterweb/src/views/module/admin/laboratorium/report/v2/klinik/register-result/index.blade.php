@extends('masterweb::template.admin.layout')

@section('title')
  Register Hasil Klinis
@endsection

@section('content')
  @php
    $columnGroups = $columnGroups ?? [];
    $columnTotal = $columnTotal ?? 0;
    $colspanEmpty = 7 + max($columnTotal, 1);
  @endphp

  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item active"><a href="#">Register Hasil Klinis</a></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="form-group row">
            <div class="col-md-2">
              <label for="month" class="bold">Bulan</label>
              <select class="smt-select2 form-control w-100" id="month">
                @for ($i = 1; $i <= 12; $i++)
                  <option value="{{ sprintf('%02d', $i) }}" {{ isSelected($i, (int)$month) }}>{{ fbulan(sprintf('%02d', $i)) }}
                  </option>
                @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label for="year" class="bold">Tahun</label>
              <select class="smt-select2 form-control w-100" id="year">
                @for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                  <option value="{{ $i }}" {{ isSelected($i, (int)$year) }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="button" class="btn btn-primary" id="btn-load-data">
                <i class="fas fa-search"></i> Tampilkan
              </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <a href="#" class="btn btn-success" id="btn-export-excel">
                <i class="fas fa-file-excel"></i> Export Excel
              </a>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="button" class="btn btn-outline-primary" id="btn-kolom-settings" data-toggle="modal" data-target="#modal-kolom-register">
                <i class="fas fa-cog"></i> Pengaturan Kolom
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <h4 class="text-center">REGISTER HASIL KLINIS</h4>
          <h5 class="text-center">Bulan {{ fbulan($month) }} Tahun {{ $year }}</h5>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-bordered table-sm" id="register-table" style="font-size: 10px; border-top: 1px solid #000;">
              <thead>
                <tr>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; width: 30px;">No</th>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; width: 80px;">Tanggal</th>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; width: 100px;">No Spesimen</th>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; width: 80px;">No RM</th>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; min-width: 150px;">Nama Pasien</th>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; width: 50px;">Umur</th>
                  <th rowspan="3" style="vertical-align: middle; text-align: center; min-width: 200px;">Alamat</th>
                  <th colspan="{{ max($columnTotal, 1) }}" style="text-align: center;">Hasil Pemeriksaan</th>
                </tr>
                <tr>
                  @forelse ($columnGroups as $group)
                    <th colspan="{{ count($group['columns']) }}" style="text-align: center; background-color: #e9ecef;">{{ $group['label'] }}</th>
                  @empty
                    <th style="text-align: center; background-color: #e9ecef;">-</th>
                  @endforelse
                </tr>
                <tr>
                  @forelse ($columnGroups as $group)
                    @foreach ($group['columns'] as $col)
                      <th>{{ $col['label'] }}</th>
                    @endforeach
                  @empty
                    <th>-</th>
                  @endforelse
                </tr>
              </thead>
              <tbody>
                @if(isset($data) && count($data) > 0)
                  @foreach ($data as $row)
                    <tr>
                      <td style="text-align: center;">{{ $row['no'] }}</td>
                      <td style="text-align: center;">{{ $row['tanggal'] }}</td>
                      <td style="text-align: center;">{{ $row['no_spesimen'] }}</td>
                      <td style="text-align: center;">{{ $row['no_rm'] }}</td>
                      <td>{{ $row['nama_pasien'] }}</td>
                      <td style="text-align: center;">{{ $row['umur'] }}</td>
                      <td>{{ $row['alamat'] }}</td>
                      @foreach ($columnGroups as $group)
                        @foreach ($group['columns'] as $col)
                          <td style="text-align: center;">{{ $row['results'][$group['key']][$col['kode']] ?? '' }}</td>
                        @endforeach
                      @endforeach
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="{{ $colspanEmpty }}" class="text-center">Tidak ada data untuk bulan dan tahun yang dipilih</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Pengaturan Kolom --}}
  <div class="modal fade" id="modal-kolom-register" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 96vw; width: 96vw; margin: 1rem auto;">
      <div class="modal-content" style="max-height: 92vh; display: flex; flex-direction: column;">
        <div class="modal-header py-2 flex-shrink-0">
          <h5 class="modal-title"><i class="fas fa-cog mr-2"></i>Pengaturan Kolom Register Hasil Klinis</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body d-flex flex-column" style="overflow: hidden; flex: 1 1 auto; min-height: 0;">
          <p class="text-muted mb-2 flex-shrink-0" style="font-size: 13px;">
            Setiap kolom (mis. <strong>GDN</strong>) dihubungkan ke <strong>parameter satuan</strong> (mis. Gula Darah Puasa).
            Nama kolom dipakai sebagai header di tabel. Bisa menambah atau menghapus kolom.
          </p>
          <div class="d-flex mb-2 flex-shrink-0" style="gap: 8px;">
            <input type="text" class="form-control" id="kolom-settings-search" placeholder="Cari nama / grup / satuan...">
            <button type="button" class="btn btn-outline-success text-nowrap" id="btn-add-kolom">
              <i class="fas fa-plus"></i> Tambah Kolom
            </button>
          </div>
          <div id="kolom-settings-scroll" class="table-responsive flex-grow-1" style="min-height: 0; max-height: calc(92vh - 210px); overflow: auto; border: 1px solid #dee2e6;">
            <table class="table table-sm table-bordered table-hover mb-0" id="table-kolom-settings" style="font-size: 12px; min-width: 980px;">
              <thead class="thead-light" style="position: sticky; top: 0; z-index: 2; background: #e9ecef;">
                <tr>
                  <th style="width: 60px; text-align: center; background: #e9ecef;">Tampil</th>
                  <th style="width: 160px; background: #e9ecef;">Nama Kolom</th>
                  <th style="width: 150px; background: #e9ecef;">Grup</th>
                  <th style="width: 80px; background: #e9ecef;">Urutan</th>
                  <th style="min-width: 420px; background: #e9ecef;">Parameter Satuan</th>
                  <th style="width: 60px; text-align: center; background: #e9ecef;">Hapus</th>
                </tr>
              </thead>
              <tbody id="kolom-settings-body">
                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer py-2 flex-shrink-0">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-primary" id="btn-save-kolom-settings">
            <i class="fas fa-save"></i> Simpan Pengaturan
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      var grupOptions = {};
      var satuanOptions = [];
      var newRowCounter = 0;
      var urlGet = "{{ route('register-result-clinic.kolom-settings') }}";
      var urlSave = "{{ route('register-result-clinic.kolom-settings.save') }}";

      function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : text).html();
      }

      function buildGrupOptionsHtml(selected) {
        var html = '';
        Object.keys(grupOptions).forEach(function(key) {
          html += '<option value="' + escapeHtml(key) + '"' + (selected === key ? ' selected' : '') + '>' + escapeHtml(grupOptions[key]) + '</option>';
        });
        return html;
      }

      function buildSatuanOptionsHtml(selectedIds) {
        selectedIds = selectedIds || [];
        var html = '';
        satuanOptions.forEach(function(s) {
          var selected = selectedIds.indexOf(s.id) !== -1 ? ' selected' : '';
          html += '<option value="' + escapeHtml(s.id) + '"' + selected + '>' + escapeHtml(s.nama) + '</option>';
        });
        return html;
      }

      function destroySatuanSelects($scope) {
        ($scope || $('#kolom-settings-body')).find('.kolom-satuan').each(function() {
          if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
          }
        });
      }

      function initSatuanSelects($scope) {
        ($scope || $('#kolom-settings-body')).find('.kolom-satuan').each(function() {
          var $el = $(this);
          if ($el.hasClass('select2-hidden-accessible')) {
            return;
          }
          $el.select2({
            width: '100%',
            placeholder: 'Pilih parameter satuan...',
            allowClear: true,
            dropdownParent: $('#modal-kolom-register')
          });
        });
      }

      function nextSortValue() {
        var max = 0;
        $('#kolom-settings-body .kolom-sort').each(function() {
          var v = parseInt($(this).val(), 10) || 0;
          if (v > max) max = v;
        });
        return max + 1;
      }

      function buildRowHtml(row) {
        var nama = row.nama || row.label || row.kode || '';
        var satuanNames = (row.satuan || []).map(function(s) { return s.nama; }).join(' ');
        var search = (nama + ' ' + (row.grup_label || '') + ' ' + satuanNames).toLowerCase();
        var html = '';
        html += '<tr class="kolom-settings-row" data-id="' + escapeHtml(row.id || '') + '" data-search="' + escapeHtml(search) + '">';
        html += '<td class="text-center align-middle"><input type="checkbox" class="kolom-tampil"' + (row.tampil !== false && row.tampil !== 0 ? ' checked' : '') + '></td>';
        html += '<td><input type="text" class="form-control form-control-sm kolom-nama" value="' + escapeHtml(nama) + '" placeholder="Contoh: GDN"></td>';
        html += '<td><select class="form-control form-control-sm kolom-grup">' + buildGrupOptionsHtml(row.grup || 'other') + '</select></td>';
        html += '<td><input type="number" class="form-control form-control-sm kolom-sort" value="' + (row.sort || 0) + '" min="0"></td>';
        html += '<td><select class="form-control form-control-sm kolom-satuan" multiple="multiple">' + buildSatuanOptionsHtml(row.satuan_ids || []) + '</select></td>';
        html += '<td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-kolom" title="Hapus"><i class="fas fa-trash"></i></button></td>';
        html += '</tr>';
        return html;
      }

      function renderKolomRows(rows) {
        destroySatuanSelects();
        if (!rows || !rows.length) {
          $('#kolom-settings-body').html('<tr class="kolom-empty-row"><td colspan="6" class="text-center text-muted">Belum ada kolom. Klik Tambah Kolom.</td></tr>');
          return;
        }

        var html = '';
        rows.forEach(function(row) {
          html += buildRowHtml(row);
        });
        $('#kolom-settings-body').html(html);
        initSatuanSelects();
      }

      function loadKolomSettings() {
        destroySatuanSelects();
        $('#kolom-settings-body').html('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>');
        $.getJSON(urlGet)
          .done(function(res) {
            if (!res || !res.status) {
              $('#kolom-settings-body').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>');
              return;
            }
            grupOptions = res.grup_options || {};
            satuanOptions = res.satuan_options || [];
            renderKolomRows(res.data || []);
          })
          .fail(function() {
            $('#kolom-settings-body').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>');
          });
      }

      $('#modal-kolom-register').on('show.bs.modal', function() {
        $('#kolom-settings-search').val('');
        newRowCounter = 0;
        loadKolomSettings();
      });

      $('#modal-kolom-register').on('hidden.bs.modal', function() {
        destroySatuanSelects();
      });

      $('#btn-add-kolom').on('click', function() {
        $('#kolom-settings-body .kolom-empty-row').remove();
        newRowCounter++;
        var row = {
          id: 'new-' + newRowCounter,
          nama: '',
          grup: 'other',
          sort: nextSortValue(),
          tampil: true,
          satuan_ids: []
        };
        var $tr = $(buildRowHtml(row));
        $('#kolom-settings-body').append($tr);
        initSatuanSelects($tr);
        $tr.find('.kolom-nama').focus();
      });

      $('#kolom-settings-body').on('click', '.btn-hapus-kolom', function() {
        var $tr = $(this).closest('tr');
        var id = String($tr.data('id') || '');
        if (id.indexOf('new-') === 0 || id === '') {
          destroySatuanSelects($tr);
          $tr.remove();
          return;
        }
        if (!confirm('Hapus kolom ini? Perubahan tersimpan setelah klik Simpan Pengaturan.')) {
          return;
        }
        $tr.addClass('table-danger kolom-will-delete');
        $tr.find('.kolom-nama, .kolom-grup, .kolom-sort, .kolom-satuan, .kolom-tampil').prop('disabled', true);
        $(this).prop('disabled', true);
      });

      $('#kolom-settings-search').on('keyup', function() {
        var q = ($(this).val() || '').toLowerCase();
        $('.kolom-settings-row').each(function() {
          var hay = $(this).attr('data-search') || '';
          var nama = ($(this).find('.kolom-nama').val() || '').toLowerCase();
          $(this).toggle(!q || hay.indexOf(q) !== -1 || nama.indexOf(q) !== -1);
        });
      });

      $('#btn-save-kolom-settings').on('click', function() {
        var $btn = $(this);
        var items = [];
        var invalid = false;

        $('#kolom-settings-body tr.kolom-settings-row').each(function() {
          var $tr = $(this);
          var nama = $.trim($tr.find('.kolom-nama').val() || '');
          var willDelete = $tr.hasClass('kolom-will-delete');
          if (!willDelete && nama === '') {
            invalid = true;
            $tr.find('.kolom-nama').focus();
            return false;
          }
          items.push({
            id: $tr.data('id'),
            nama: nama,
            tampil: $tr.find('.kolom-tampil').is(':checked') ? 1 : 0,
            grup: $tr.find('.kolom-grup').val(),
            sort: $tr.find('.kolom-sort').val(),
            satuan_ids: $tr.find('.kolom-satuan').val() || [],
            hapus: willDelete ? 1 : 0
          });
        });

        if (invalid) {
          alert('Nama kolom tidak boleh kosong.');
          return;
        }
        if (!items.length) {
          alert('Tidak ada data kolom.');
          return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        $.ajax({
          url: urlSave,
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            items: items
          },
          success: function(res) {
            if (res && res.status) {
              alert(res.pesan || 'Berhasil disimpan');
              $('#modal-kolom-register').modal('hide');
              window.location.reload();
            } else {
              alert((res && res.pesan) ? res.pesan : 'Gagal menyimpan');
            }
          },
          error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal menyimpan';
            alert(msg);
          },
          complete: function() {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Pengaturan');
          }
        });
      });

      $('#btn-load-data').click(function() {
        var month = $('#month').val();
        var year = $('#year').val();
        window.location.href = "{{ route('register-result-clinic.index') }}?month=" + month + "&year=" + year;
      });

      $('#btn-export-excel').click(function(e) {
        e.preventDefault();
        var month = $('#month').val();
        var year = $('#year').val();
        var url = "{{ route('register-result-clinic.export') }}?month=" + month + "&year=" + year;
        window.location.href = url;
      });
    });
  </script>
@endsection
