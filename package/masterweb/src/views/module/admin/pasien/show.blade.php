@extends('masterweb::template.admin.layout')
@section('title')
  Pasien Management - Show
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-pasien') }}">Pasien Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>show</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="card">
    <div class="card-body">

      <div class="row">
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table" collspan="5" cellpadding="1">
              <tr>
                <th style="width: 20%">NIK Pasien</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ $item->nik_pasien ?? '-' }}</td>
              </tr>

              <tr>
                <th style="width: 20%">Nomor Rekam Medis</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ $item->no_rekammedis_pasien ?? '-' }}</td>
              </tr>

              <tr>
                <th style="width: 20%">Nama Pasien</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ mb_strtoupper((string) ($item->nama_pasien ?? '-'), 'UTF-8') }}</td>
              </tr>

              <tr>
                <th style="width: 20%">Divisi/Instansi</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ $item->divisi_instansi_pasien ?? '-' }}</td>
              </tr>

              <tr>
                <th style="width: 20%">Jenis Kelamin Pasien</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ $item->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
              </tr>

              <tr>
                <th style="width: 20%">Tanggal Lahir Pasien</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">
                  {{ \Carbon\Carbon::createFromFormat('Y-m-d', $item->tgllahir_pasien)->isoFormat('dddd, D MMMM Y') ?? '-' }}
                </td>
              </tr>

              <tr>
                <th style="width: 20%">Nomor Telepon</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ $item->phone_pasien ?? '-' }}</td>
              </tr>

              <tr>
                <th style="width: 20%">Alamat Pasien</th>
                <th style="width: 2%">:</th>
                <td style="width: 78%">{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item) }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <br>

      <button type="button" onclick="document.location='{{ url('/elits-pasien') }}'"
        class="btn btn-light">Kembali</button>
    </div>
  </div>
@endsection
