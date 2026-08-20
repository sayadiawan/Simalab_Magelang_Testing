@extends('masterweb::template.admin.layout')

@section('title')
  Monitoring Sampling dan Penerima
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
                <li class="breadcrumb-item active"><a href="#">Monitoring Sampling dan Penerima</a></li>
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
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <h4 class="text-center">MONITORING SAMPLING DAN PENERIMA</h4>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-bordered table-sm" id="monitoring-table" style="font-size: 10px; border-top: 1px solid #000;">
              <thead>
                <tr>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No</th>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Tanggal</th>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No. RM</th>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No. Spesimen</th>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Nama Pasien</th>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jenis Pemeriksaan</th>
                  <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jenis Sampel</th>
                  <th rowspan="2" colspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Sampling</th>
                  <th colspan="11" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Penerimaan Sampel</th>
                </tr>
                <tr>
                  <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Darah</th>
                  <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Serum</th>
                  <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Urine</th>
                  <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Feses</th>
                  <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Petugas</th>
                  <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jam</th>
                  <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Keterangan</th>
                </tr>
                <tr>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Berhasil/Gagal</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Paraf Petugas</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jam</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
                  <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
                </tr>
              </thead>
              <tbody>
                @if(isset($data) && count($data) > 0)
                  @foreach ($data as $row)
                    <tr>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['no'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['tanggal'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['no_rm'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['no_spesimen'] }}</td>
                      <td style="border: 1px solid #000;">{{ $row['nama_pasien'] }}</td>
                      <td style="border: 1px solid #000;">{{ $row['jenis_pemeriksaan'] }}</td>
                      <td style="border: 1px solid #000;">{{ $row['jenis_sampel'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['status_sampling'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['petugas_sampling'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['jam_sampling'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['darah_volume'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['darah_kualitas'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['serum_volume'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['serum_kualitas'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['urine_volume'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['urine_kualitas'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['feses_volume'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['feses_kualitas'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['petugas_penerimaan'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['jam_penerimaan'] }}</td>
                      <td style="text-align: center; border: 1px solid #000;">{{ $row['keterangan'] }}</td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="19" class="text-center" style="border: 1px solid #000;">Tidak ada data untuk bulan dan tahun yang dipilih</td>
                  </tr>
                @endif
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
      $('#btn-load-data').click(function() {
        var month = $('#month').val();
        var year = $('#year').val();
        window.location.href = "{{ route('monitoring-sampling-penerima.index') }}?month=" + month + "&year=" + year;
      });

      $('#month, #year').on('change', function() {
        // Optional: auto-reload on change
        // $('#btn-load-data').click();
      });

      $('#btn-export-excel').click(function(e) {
        e.preventDefault();
        var month = $('#month').val();
        var year = $('#year').val();
        var url = "{{ route('monitoring-sampling-penerima.export') }}?month=" + month + "&year=" + year;
        window.location.href = url;
      });
    });
  </script>
@endsection

