@extends('masterweb::template.admin.layout')

@section('title')
  {{ ($tipe ?? 'biasa') === 'haji' ? 'Laporan Klinik Haji' : 'Laporan Klinik' }}
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
                <li class="breadcrumb-item active"><a href="#">{{ ($tipe ?? 'biasa') === 'haji' ? 'Laporan Klinik Haji' : 'Laporan Klinik' }}</a></li>
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
              <button type="button" class="btn btn-primary" id="btn-load-data" hidden>
                <i class="fas fa-search"></i> Tampilkan
              </button>
            </div>
            <div class="col-md-3">
              <label for="export_type" class="bold">Export</label>
              <select class="smt-select2 form-control w-100" id="export_type">
                <option value="month">Per Bulan</option>
                <option value="year">Per Tahun</option>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="button" class="btn btn-success" id="btn-export-excel">
                <i class="fas fa-file-excel"></i> Export Excel
              </button>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="button" class="btn btn-outline-primary" id="btn-paket-settings" data-toggle="modal" data-target="#modal-paket-laporan">
                <i class="fas fa-cog"></i> Pengaturan Paket Laporan
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <h4 class="text-center">{{ $reportTitle ?? 'Catatan Harian Pemeriksaan Unit Klinik' }}</h4>
          <h5 class="text-center">Bulan : {{ fbulan($month) }} {{ $year }}</h5>
        </div>
      </div>

      @php
        // Use daysInMonth from controller, or calculate if not available
        if (!isset($daysInMonth)) {
          if (isset($data) && isset($data['jumlah_pasien'])) {
            $daysInMonth = count($data['jumlah_pasien']);
          } else {
            // Calculate from month and year if data not available
            $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
          }
        }
      @endphp

      <div class="row">
        <div class="col-12">
          <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-bordered table-sm" id="report-table" style="font-size: 12px;">
              <thead style="border-top: 1px solid #000;">
                <tr>
                  <th rowspan="2" style="vertical-align: middle; text-align: center; width: 40px; border-top: 2px solid #000;">No</th>
                  <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 200px; border-top: 2px solid #000;">Uraian</th>
                  <th colspan="{{ $daysInMonth }}" style="text-align: center; border-top: 2px solid #000;">Tanggal</th>
                  <th rowspan="2" style="vertical-align: middle; text-align: center; width: 80px; border-top: 2px solid #000;">Jumlah</th>
                  <th rowspan="2" style="vertical-align: middle; text-align: center; width: 100px; border-top: 2px solid #000;">Ket</th>
                </tr>
                <tr>
                  @for ($i = 1; $i <= $daysInMonth; $i++)
                    <th style="text-align: center; width: 40px;">{{ $i }}</th>
                  @endfor
                </tr>
              </thead>
              <tbody id="report-tbody">
                @if(isset($data))
                  @php
                    $rowNum = 1;
                    $kimiaParams = $kimiaParams ?? ['GDN', 'GD 2 Jam PP', 'GDS', 'HbA1c', 'Cholesterol', 'LDL', 'HDL', 'Trigliserid', 'Asam Urat', 'Ureum', 'Creatinin', 'SGOT', 'SGPT'];
                    $otherParams = $otherParams ?? ['Darah rutin', 'Hemoglobin', 'LED', 'Widal', 'Golongan darah', 'HBsAg', 'Urin rutin', 'Tes Kehamilan', 'Tes Narkoba', 'NS1', 'Dengue IgG/IgM', 'Typhi IgG/IgM', 'Croschek TB', 'Feses'];
                  @endphp
                  
                  {{-- Row 1: Jumlah pasien --}}
                  @php
                    $pasienTotal = array_sum($data['jumlah_pasien'] ?? []);
                  @endphp
                  <tr>
                    <td style="text-align: center;">{{ $rowNum }}</td>
                    <td><strong>Jumlah pasien</strong></td>
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                      <td style="text-align: center;">{{ ($data['jumlah_pasien'][$day] ?? 0) > 0 ? $data['jumlah_pasien'][$day] : '' }}</td>
                    @endfor
                    <td style="text-align: center;"><strong>{{ $pasienTotal }}</strong></td>
                    <td></td>
                  </tr>
                  @php $rowNum++; @endphp

                  {{-- Kimia klinik section: header = pasien unik per hari (bukan jumlah semua tes) --}}
                  @php
                    $kimiaDaily = $data['kimia_klinik'] ?? array_fill(1, $daysInMonth, 0);
                    $kimiaTotal = array_sum($kimiaDaily);
                  @endphp
                  <tr>
                    <td style="text-align: center; vertical-align: top;" rowspan="{{ count($kimiaParams) + 1 }}">{{ $rowNum }}</td>
                    <td><strong>Kimia klinik</strong></td>
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                      <td style="text-align: center;">{{ ($kimiaDaily[$day] ?? 0) > 0 ? $kimiaDaily[$day] : '' }}</td>
                    @endfor
                    <td style="text-align: center;"><strong>{{ $kimiaTotal > 0 ? $kimiaTotal : '' }}</strong></td>
                    <td></td>
                  </tr>
                  @foreach ($kimiaParams as $param)
                    @php
                      $paramData = $data['parameters'][$param] ?? array_fill(1, $daysInMonth, 0);
                      $paramTotal = array_sum($paramData);
                    @endphp
                    <tr>
                      <td>{{ $param }}</td>
                      @for ($day = 1; $day <= $daysInMonth; $day++)
                        <td style="text-align: center;">{{ ($paramData[$day] ?? 0) > 0 ? $paramData[$day] : '' }}</td>
                      @endfor
                      <td style="text-align: center;">{{ $paramTotal > 0 ? $paramTotal : '' }}</td>
                      <td></td>
                    </tr>
                  @endforeach
                  @php $rowNum++; @endphp

                  {{-- Darah rutin & Hemoglobin dipisah (ada di otherParams) --}}
                  @foreach ($otherParams as $param)
                    @php
                      $paramData = $data['parameters'][$param] ?? array_fill(1, $daysInMonth, 0);
                      $paramTotal = array_sum($paramData);
                    @endphp
                    <tr>
                      <td style="text-align: center;">{{ $rowNum }}</td>
                      <td>{{ $param }}</td>
                      @for ($day = 1; $day <= $daysInMonth; $day++)
                        <td style="text-align: center;">{{ ($paramData[$day] ?? 0) > 0 ? $paramData[$day] : '' }}</td>
                      @endfor
                      <td style="text-align: center;">{{ $paramTotal > 0 ? $paramTotal : '' }}</td>
                      <td></td>
                    </tr>
                    @php $rowNum++; @endphp
                  @endforeach
                @else
                  <tr>
                    <td colspan="{{ $daysInMonth + 4 }}" class="text-center">Pilih bulan dan tahun untuk menampilkan data</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal pengaturan paket laporan --}}
  <div class="modal fade" id="modal-paket-laporan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-cog mr-2"></i>Pengaturan Paket Laporan Klinik</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-3" style="font-size: 13px;">
            Atur singkatan, kategori, paket gabungan, dan apakah paket ditampilkan di laporan.
            Berlaku untuk <strong>Laporan Klinik</strong> dan <strong>Laporan Klinik Haji</strong>.
          </p>
          <div class="form-group mb-3">
            <input type="text" class="form-control" id="paket-settings-search" placeholder="Cari nama paket / singkatan...">
          </div>
          <div class="table-responsive" style="max-height: 55vh; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover mb-0" id="table-paket-settings" style="font-size: 12px;">
              <thead class="thead-light" style="position: sticky; top: 0; z-index: 2;">
                <tr>
                  <th style="width: 36px; text-align: center;">Tampil</th>
                  <th>Nama Paket</th>
                  <th style="min-width: 120px;">Singkatan</th>
                  <th style="min-width: 110px;">Kategori</th>
                  <th style="width: 90px; text-align: center;">Gabungan</th>
                </tr>
              </thead>
              <tbody id="paket-settings-body">
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="btn-save-paket-settings">
            <i class="fas fa-save mr-1"></i> Simpan Pengaturan
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      var tipe = @json($tipe ?? 'biasa');
      var indexUrl = tipe === 'haji'
        ? "{{ route('report-annual-clinic-haji.index') }}"
        : "{{ route('report-annual-clinic.index') }}";
      var exportUrl = tipe === 'haji'
        ? "{{ route('report-annual-clinic-haji.export') }}"
        : "{{ route('report-annual-clinic.export') }}";
      var settingsGetUrl = tipe === 'haji'
        ? "{{ route('report-annual-clinic-haji.paket-settings') }}"
        : "{{ route('report-annual-clinic.paket-settings') }}";
      var settingsSaveUrl = tipe === 'haji'
        ? "{{ route('report-annual-clinic-haji.paket-settings.save') }}"
        : "{{ route('report-annual-clinic.paket-settings.save') }}";
      var csrfToken = "{{ csrf_token() }}";

      $('#btn-load-data').click(function() {
        var month = $('#month').val();
        var year = $('#year').val();
        window.location.href = indexUrl + "?month=" + month + "&year=" + year;
      });

      $('#month, #year').on('change', function() {
        $('#btn-load-data').click();
      });

      $('#btn-export-excel').click(function() {
        var month = $('#month').val();
        var year = $('#year').val();
        var exportType = $('#export_type').val();
        var url = exportUrl + "?month=" + month + "&year=" + year + "&export_type=" + exportType;
        window.location.href = url;
      });

      function escapeHtml(str) {
        return String(str == null ? '' : str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function renderPaketSettingsRows(rows) {
        if (!rows || !rows.length) {
          $('#paket-settings-body').html('<tr><td colspan="5" class="text-center text-muted">Tidak ada data paket</td></tr>');
          return;
        }

        var html = '';
        rows.forEach(function(row, idx) {
          html += '<tr class="paket-settings-row" data-search="' + escapeHtml((row.nama + ' ' + row.singkatan).toLowerCase()) + '">';
          html += '<td class="text-center align-middle">';
          html += '<input type="checkbox" class="paket-tampil" data-id="' + escapeHtml(row.id) + '"' + (row.tampil ? ' checked' : '') + '>';
          html += '</td>';
          html += '<td class="align-middle">' + escapeHtml(row.nama) + '</td>';
          html += '<td><input type="text" class="form-control form-control-sm paket-singkatan" data-id="' + escapeHtml(row.id) + '" value="' + escapeHtml(row.singkatan) + '" placeholder="Contoh: HDL"></td>';
          html += '<td><select class="form-control form-control-sm paket-kategori" data-id="' + escapeHtml(row.id) + '">';
          html += '<option value=""' + (!row.kategori ? ' selected' : '') + '>Otomatis</option>';
          html += '<option value="kimia"' + (row.kategori === 'kimia' ? ' selected' : '') + '>Kimia klinik</option>';
          html += '<option value="lain"' + (row.kategori === 'lain' ? ' selected' : '') + '>Lainnya</option>';
          html += '</select></td>';
          html += '<td class="text-center align-middle">';
          html += '<input type="checkbox" class="paket-agregat" data-id="' + escapeHtml(row.id) + '"' + (row.is_agregat ? ' checked' : '') + ' title="Paket gabungan">';
          html += '</td>';
          html += '</tr>';
        });
        $('#paket-settings-body').html(html);
      }

      function loadPaketSettings() {
        $('#paket-settings-body').html('<tr><td colspan="5" class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>');
        $.getJSON(settingsGetUrl)
          .done(function(res) {
            if (res.status) {
              renderPaketSettingsRows(res.data || []);
            } else {
              $('#paket-settings-body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>');
            }
          })
          .fail(function() {
            $('#paket-settings-body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>');
          });
      }

      $('#modal-paket-laporan').on('show.bs.modal', function() {
        loadPaketSettings();
        $('#paket-settings-search').val('');
      });

      $('#paket-settings-search').on('keyup', function() {
        var q = ($(this).val() || '').toLowerCase().trim();
        $('.paket-settings-row').each(function() {
          var hay = $(this).attr('data-search') || '';
          $(this).toggle(!q || hay.indexOf(q) !== -1);
        });
      });

      $('#btn-save-paket-settings').on('click', function() {
        var $btn = $(this);
        var items = [];
        $('#paket-settings-body tr.paket-settings-row').each(function() {
          var $tr = $(this);
          var id = $tr.find('.paket-tampil').data('id');
          if (!id) return;
          items.push({
            id: id,
            tampil: $tr.find('.paket-tampil').is(':checked') ? 1 : 0,
            singkatan: $tr.find('.paket-singkatan').val() || '',
            kategori: $tr.find('.paket-kategori').val() || '',
            is_agregat: $tr.find('.paket-agregat').is(':checked') ? 1 : 0
          });
        });

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...');
        $.ajax({
          url: settingsSaveUrl,
          type: 'POST',
          data: {
            _token: csrfToken,
            items: items
          },
          dataType: 'JSON'
        }).done(function(res) {
          if (res.status) {
            $('#modal-paket-laporan').modal('hide');
            if (typeof swal === 'function') {
              swal({
                icon: 'success',
                title: 'Berhasil',
                text: res.pesan || 'Pengaturan disimpan',
              }).then(function() {
                window.location.reload();
              });
            } else {
              alert(res.pesan || 'Pengaturan disimpan');
              window.location.reload();
            }
          } else {
            alert(res.pesan || 'Gagal menyimpan');
          }
        }).fail(function(xhr) {
          var msg = (xhr.responseJSON && xhr.responseJSON.pesan) ? xhr.responseJSON.pesan : 'Gagal menyimpan pengaturan';
          alert(msg);
        }).always(function() {
          $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Pengaturan');
        });
      });
    });
  </script>
@endsection

