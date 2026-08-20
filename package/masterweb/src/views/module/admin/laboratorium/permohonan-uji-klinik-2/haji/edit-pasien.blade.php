@extends('masterweb::template.admin.layout')

@section('title')
  Edit Pasien Haji
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}">Permohonan Uji Klinik Haji</a></li>
                <li class="breadcrumb-item"><a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}">Daftar Pasien</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Edit Pasien</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
          <h4 class="card-title mb-1">Edit Pasien — {{ $haji->nama_haji }}</h4>
          <p class="text-muted mb-0">
            No. Lab / Spesimen:
            <strong>{{ $permohonan->noregister_permohonan_uji_klinik ?? '-' }}</strong>
          </p>
        </div>
        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-light">
          <i class="fa fa-arrow-left"></i> Kembali
        </a>
      </div>

      <form method="POST" action="{{ route('elits-permohonan-uji-klinik-2.haji.update-pasien', [$haji->id_permohonan_uji_klinik_haji, $permohonan->id_permohonan_uji_klinik]) }}">
        @csrf
        @method('PUT')

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="nik_pasien">NIK</label>
              <input type="text" class="form-control" name="nik_pasien" id="nik_pasien" maxlength="16"
                value="{{ old('nik_pasien', $pasien->nik_pasien) }}" placeholder="16 digit">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="nama_pasien">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase" name="nama_pasien" id="nama_pasien" required
                value="{{ old('nama_pasien', $pasien->nama_pasien) }}" placeholder="Nama sesuai KTP"
                style="text-transform: uppercase;">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="gender_pasien">Jenis Kelamin <span class="text-danger">*</span></label>
              <select class="form-control" name="gender_pasien" id="gender_pasien" required>
                <option value="">-- Pilih --</option>
                <option value="L" {{ old('gender_pasien', $pasien->gender_pasien) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('gender_pasien', $pasien->gender_pasien) === 'P' ? 'selected' : '' }}>Perempuan</option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="tgllahir_pasien">Tanggal Lahir <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="tgllahir_pasien" id="tgllahir_pasien" required
                value="{{ old('tgllahir_pasien', $pasien->tgllahir_pasien ? \Carbon\Carbon::parse($pasien->tgllahir_pasien)->format('Y-m-d') : '') }}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="tmpt_lahir">Tempat Lahir</label>
              <input type="text" class="form-control" name="tmpt_lahir" id="tmpt_lahir"
                value="{{ old('tmpt_lahir', $pasien->tmpt_lahir) }}">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="pekerjaan">Pekerjaan</label>
              <input type="text" class="form-control" name="pekerjaan" id="pekerjaan"
                value="{{ old('pekerjaan', $pasien->pekerjaan) }}">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="phone_pasien">No. Telepon</label>
              <input type="text" class="form-control" name="phone_pasien" id="phone_pasien"
                value="{{ old('phone_pasien', $pasien->phone_pasien) }}">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="alamat_pasien">Alamat</label>
          <textarea class="form-control" name="alamat_pasien" id="alamat_pasien" rows="3"
            placeholder="Alamat lengkap pasien">{{ old('alamat_pasien', \Smt\Masterweb\Helpers\Smt::sanitizeAlamatPasien($pasien->alamat_pasien ?? '', $pasien->tgllahir_pasien ?? null)) }}</textarea>
          <small class="form-text text-muted">Isi alamat pasien (bukan tanggal lahir).</small>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Simpan
          </button>
          <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-light">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection
