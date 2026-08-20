@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                    Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Bukti Pendaftaran</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row align-items-center mb-4">
        <div class="col">
          <h3 class="page-title">
            Bukti Pendaftaran
          </h3>
        </div>
        <!-- Page title actions -->
        <div class="col-auto ms-auto d-print-none">
          <div class="d-flex">
            <div class="template-demo">

              <a href="/elits-permohonan-uji-klinik">
                <button type="button" class="btn btn-primary btn-icon-text">
                  <i class="fa fa-check btn-icon-prepend" aria-hidden="true"></i>
                  Selesai
                </button>
              </a>
            </div>
          </div>
        </div>
      </div>


      <div class="table-responsive">
        <table class="table" style="width: 100%">
          <tr>
            <td width="30%">
              No. Register
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Tanggal Register
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik) ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y') : '-' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Nama Pasien
            </td>
            <td width="1%">
              :
            </td>
            <td>
              <strong>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</strong>
            </td>
          </tr>

          <tr>
            <td width="30%">
              Jenis Kelamin
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Tanggal Lahir
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien) ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat('D MMMM Y') : '' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Usia
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
              {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
              {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari
            </td>
          </tr>

          <tr>
            <td width="30%">
              No. Rekam Medis
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item_permohonan_uji_klinik->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              No. Telepon
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->pasien->phone_pasien ?? '-' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Alamat Pasien
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              No. KTP
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Pengirim
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik ?? '-' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Dokter Perujuk
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $item_permohonan_uji_klinik->dokter_permohonan_uji_klinik ?? '-' }}
            </td>
          </tr>
        </table>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="d-flex justify-content-center">
            <h3>Scan Disini</h3>
          </div>
        </div>

        <div class="col-md-12">
          <div class="d-flex justify-content-center">

            <img
              src="{{ route('qrcode-permohonan-uji-klinik', [$item_permohonan_uji_klinik->id_permohonan_uji_klinik, 250, 0]) }}" />
            <br>

          </div>
        </div>

        <div class="col-md-12">
          <div class="d-flex justify-content-center">
            <h3>Tracking Hasil</h3>
          </div>
        </div>
      </div>

      <a href="{{ route('elits-permohonan-uji-klinik.print-permohonan-uji-klinik-qrcode', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}"
        class="btn btn-light" target="__blank"><i class="fas fa-print"></i> Print</a>
    </div>
  </div>
@endsection
