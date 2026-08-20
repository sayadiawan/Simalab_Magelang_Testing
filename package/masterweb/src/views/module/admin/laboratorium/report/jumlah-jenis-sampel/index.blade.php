@extends('masterweb::template.admin.layout')

@section('title')
  Rekapan Jumlah per Jenis Sampel
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
                <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                <li class="breadcrumb-item active"><a href="#">Jumlah per Jenis Sampel</a></li>
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
                  <option value="{{ sprintf('%02d', $i) }}" {{ isSelected($i, (int) $month) }}>
                    {{ fbulan(sprintf('%02d', $i)) }}
                  </option>
                @endfor
              </select>
            </div>
            <div class="col-md-2">
              <label for="year" class="bold">Tahun</label>
              <select class="smt-select2 form-control w-100" id="year">
                @for ($i = date('Y') - 5; $i <= date('Y') + 1; $i++)
                  <option value="{{ $i }}" {{ isSelected($i, (int) $year) }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="show_empty"
                  {{ !empty($showEmptyDays) ? 'checked' : '' }}>
                <label class="custom-control-label" for="show_empty">Tampilkan hari kosong</label>
              </div>
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
            <div class="col-md-1 d-flex align-items-end">
              <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-12 text-center">
          <h4 class="mb-0 font-weight-bold">REKAPAN BULAN {{ strtoupper($bulanNama) }}</h4>
          <small class="text-muted">Jumlah sampel per jenis — Klinik, Haji, Mikrobiologi, dan Kimia</small>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-bordered table-sm mb-0" id="rekapan-table"
              style="font-size: 11px; min-width: 1400px;">
              <thead>
                <tr>
                  <th rowspan="2"
                    style="text-align:center;vertical-align:middle;border:1px solid #000;background:#f0f0f0;width:40px;">
                    No</th>
                  <th rowspan="2"
                    style="text-align:center;vertical-align:middle;border:1px solid #000;background:#f0f0f0;min-width:130px;">
                    Tanggal</th>
                  <th colspan="3"
                    style="text-align:center;vertical-align:middle;border:1px solid #000;background:#d4edda;">
                    Klinis</th>
                  <th colspan="3"
                    style="text-align:center;vertical-align:middle;border:1px solid #000;background:#f8d7da;">
                    Haji</th>
                  <th colspan="7"
                    style="text-align:center;vertical-align:middle;border:1px solid #000;background:#cce5ff;">
                    Mikrobiologi</th>
                  <th colspan="5"
                    style="text-align:center;vertical-align:middle;border:1px solid #000;background:#fff3cd;">
                    Kimia</th>
                </tr>
                <tr>
                  <th style="text-align:center;border:1px solid #000;background:#e8f5e9;">Darah</th>
                  <th style="text-align:center;border:1px solid #000;background:#e8f5e9;">Urine</th>
                  <th style="text-align:center;border:1px solid #000;background:#e8f5e9;">Feses</th>
                  <th style="text-align:center;border:1px solid #000;background:#fce4ec;">Darah</th>
                  <th style="text-align:center;border:1px solid #000;background:#fce4ec;">Urine</th>
                  <th style="text-align:center;border:1px solid #000;background:#fce4ec;">Feses</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">Air Bersih</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">Air Minum</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">Air Limbah</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">Kolam</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">MM</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">Usap</th>
                  <th style="text-align:center;border:1px solid #000;background:#e3f2fd;">Udara</th>
                  <th style="text-align:center;border:1px solid #000;background:#fff8e1;">Air Bersih</th>
                  <th style="text-align:center;border:1px solid #000;background:#fff8e1;">Air Minum</th>
                  <th style="text-align:center;border:1px solid #000;background:#fff8e1;">Air Limbah</th>
                  <th style="text-align:center;border:1px solid #000;background:#fff8e1;">Kolam</th>
                  <th style="text-align:center;border:1px solid #000;background:#fff8e1;">MM</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($rows as $row)
                  <tr>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['no'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['tanggal'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['klinis_darah'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['klinis_urine'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['klinis_feses'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['haji_darah'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['haji_urine'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['haji_feses'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_air_bersih'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_air_minum'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_air_limbah'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_kolam'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_mm'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_usap'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['mikro_udara'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['kimia_air_bersih'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['kimia_air_minum'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['kimia_air_limbah'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['kimia_kolam'] ?: '' }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $row['counts']['kimia_mm'] ?: '' }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="20" style="text-align:center;border:1px solid #000;padding:16px;">
                      Tidak ada data sampel pada bulan ini.
                    </td>
                  </tr>
                @endforelse
              </tbody>
              @if (count($rows) > 0)
                <tfoot>
                  <tr style="font-weight:bold;background:#f8f9fa;">
                    <td colspan="2" style="text-align:center;border:1px solid #000;">Total</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['klinis_darah'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['klinis_urine'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['klinis_feses'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['haji_darah'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['haji_urine'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['haji_feses'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_air_bersih'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_air_minum'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_air_limbah'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_kolam'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_mm'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_usap'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['mikro_udara'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['kimia_air_bersih'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['kimia_air_minum'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['kimia_air_limbah'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['kimia_kolam'] }}</td>
                    <td style="text-align:center;border:1px solid #000;">{{ $totals['kimia_mm'] }}</td>
                  </tr>
                  <tr style="font-weight:bold;background:#e9ecef;">
                    <td colspan="2" style="text-align:center;border:1px solid #000;">Komulatif</td>
                    <td colspan="18" style="text-align:center;border:1px solid #000;font-size:14px;">
                      {{ $komulatif }}
                    </td>
                  </tr>
                </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <small class="text-muted">
            <strong>Keterangan:</strong>
            Klinis = pendaftaran klinik biasa (bukan haji). Haji = pendaftaran pemeriksaan haji (rombongan haji).
            Darah mencakup Serum/Plasma.
            Air Bersih = AH/AB (Air Higiene), Air Minum = AM, Air Limbah = AL, Kolam = AKR (Air Kolam Renang).
            MM = Makanan/Minuman/Lainnya.
            Tanggal memakai tanggal register (klinik/haji) / tanggal kirim sampel (mikro &amp; kimia).
          </small>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <style>
    @media print {
      .breadcrumb,
      .btn,
      .form-group,
      .custom-control,
      nav,
      .sidebar,
      .navbar {
        display: none !important;
      }

      .card {
        border: none !important;
        box-shadow: none !important;
      }

      #rekapan-table {
        font-size: 9px !important;
      }
    }
  </style>
  <script type="text/javascript">
    $(document).ready(function() {
      function buildUrl(base) {
        var month = $('#month').val();
        var year = $('#year').val();
        var showEmpty = $('#show_empty').is(':checked') ? '1' : '0';
        return base + '?month=' + month + '&year=' + year + '&show_empty=' + showEmpty;
      }

      $('#btn-load-data').click(function() {
        window.location.href = buildUrl("{{ route('report-jumlah-jenis-sampel.index') }}");
      });

      $('#btn-export-excel').click(function(e) {
        e.preventDefault();
        window.location.href = buildUrl("{{ route('report-jumlah-jenis-sampel.export') }}");
      });
    });
  </script>
@endsection
