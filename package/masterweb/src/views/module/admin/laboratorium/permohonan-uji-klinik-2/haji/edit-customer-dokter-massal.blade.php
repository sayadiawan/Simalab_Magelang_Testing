@extends('masterweb::template.admin.layout')

@section('title')
  Edit Customer & Dokter Pengirim Haji
@endsection

@section('content')
  <style>
    .info-card {
      background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
      border-radius: 12px;
      padding: 22px;
      color: white;
      margin-bottom: 22px;
    }
    .info-card h4 { color: white; margin-bottom: 8px; font-weight: 600; }
    .form-section {
      background: #fff;
      border-radius: 12px;
      padding: 22px;
      margin-bottom: 18px;
      border: 1px solid #e9ecef;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .form-section h5 {
      color: #495057;
      font-weight: 600;
      margin-bottom: 18px;
      padding-bottom: 10px;
      border-bottom: 2px solid #3b82f6;
    }
  </style>

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
                <li class="breadcrumb-item active" aria-current="page"><span>Edit Customer & Dokter</span></li>
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
      <ul class="mb-0 pl-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="info-card">
    <h4><i class="fa fa-edit mr-2"></i>Edit Massal — {{ $haji->nama_haji }}</h4>
    <p class="mb-0" style="opacity:.92;">
      Perubahan nama customer dan dokter pengirim akan diterapkan ke
      <strong>{{ $jumlahPasien }} pasien</strong> dalam rombongan ini.
      @if (!empty($haji->tgl_haji))
        Tanggal pendaftaran: {{ \Carbon\Carbon::parse($haji->tgl_haji)->isoFormat('D MMMM Y') }}.
      @endif
    </p>
  </div>

  <form
    action="{{ route('elits-permohonan-uji-klinik-2.haji.update-customer-dokter', $haji->id_permohonan_uji_klinik_haji) }}"
    method="POST"
    id="form-edit-customer-dokter">
    @csrf
    @method('PUT')

    <div class="form-section">
      <h5><i class="fa fa-building mr-2"></i>Nama Customer / Puskesmas</h5>

      <div class="form-group">
        <label for="customer_id">Pilih customer master (opsional)</label>
        <select name="customer_id" id="customer_id" class="form-control">
          <option value="">— Tetap pakai customer saat ini / rename teks di bawah —</option>
          @foreach ($customers as $c)
            <option
              value="{{ $c->id_customer }}"
              data-name="{{ $c->name_customer }}"
              {{ ($customer && $customer->id_customer === $c->id_customer) ? 'selected' : '' }}>
              {{ $c->name_customer }}@if (!empty($c->address_customer)) — {{ $c->address_customer }}@endif
            </option>
          @endforeach
        </select>
        <small class="text-muted">Jika dipilih, nama di bawah bisa disesuaikan dari customer tersebut.</small>
      </div>

      <div class="form-group">
        <label for="nama_customer">Nama customer yang tampil di rombongan <span class="text-danger">*</span></label>
        <input
          type="text"
          class="form-control"
          id="nama_customer"
          name="nama_customer"
          value="{{ old('nama_customer', $haji->nama_haji) }}"
          required
          maxlength="255"
          placeholder="Contoh: Puskesmas Secang 1">
      </div>

      <div class="form-group mb-0">
        <div class="custom-control custom-checkbox">
          <input type="hidden" name="update_customer_master" value="0">
          <input
            type="checkbox"
            class="custom-control-input"
            id="update_customer_master"
            name="update_customer_master"
            value="1"
            {{ old('update_customer_master', '1') == '1' ? 'checked' : '' }}>
          <label class="custom-control-label" for="update_customer_master">
            Sekalian ubah nama di master customer
            @if ($customer)
              (<strong>{{ $customer->name_customer }}</strong>)
            @endif
          </label>
        </div>
        <small class="text-muted d-block mt-1">
          Dicentang: nama di <code>ms_customer</code> ikut berubah (berpengaruh ke nota/konsolidasi yang memakai customer yang sama).
        </small>
      </div>
    </div>

    <div class="form-section">
      <h5><i class="fa fa-user-md mr-2"></i>Dokter Pengirim</h5>

      <div class="alert alert-info py-2 mb-3">
        <div>
          <strong>Dokter pengirim mayoritas saat ini:</strong>
          @if ($dokterUtama !== '')
            <span class="text-dark">{{ $dokterUtama }}</span>
          @else
            <em class="text-muted">(kosong / belum diisi)</em>
          @endif
          <span class="text-muted">
            — {{ (int) ($dokterUtamaTotal ?? 0) }} dari {{ (int) $jumlahPasien }} pasien
          </span>
        </div>
        @if ($dokterUtama === '' && !empty($dokterNonKosongNama))
          <div class="mt-1">
            Dokter terisi terbanyak:
            <strong>{{ $dokterNonKosongNama }}</strong>
            <span class="text-muted">({{ (int) $dokterNonKosongTotal }} pasien)</span>
          </div>
        @endif
      </div>

      @if ($dokterGroups->count() > 0)
        <div class="mb-3">
          <small class="text-muted d-block mb-1">Sebaran dokter pengirim di rombongan ini:</small>
          <ul class="mb-0 pl-3">
            @foreach ($dokterGroups as $g)
              <li>
                {{ trim((string) ($g->nama_dokter_pengirim_permohonan_uji_klinik ?? '')) !== ''
                  ? $g->nama_dokter_pengirim_permohonan_uji_klinik
                  : '(kosong)' }}
                — {{ $g->total }} pasien
                @if ($loop->first)
                  <span class="badge badge-primary badge-pill">mayoritas</span>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="form-group mb-0">
        <label for="nama_dokter_pengirim">Nama dokter pengirim baru (diterapkan ke semua pasien)</label>
        <input
          type="text"
          class="form-control"
          id="nama_dokter_pengirim"
          name="nama_dokter_pengirim"
          value="{{ old('nama_dokter_pengirim', $dokterUtama) }}"
          maxlength="255"
          placeholder="Contoh: dr. Nama Dokter">
        <small class="text-muted">
          Prefill dari dokter mayoritas saat ini
          @if ($dokterUtama !== '')
            (<strong>{{ $dokterUtama }}</strong>).
          @else
            (saat ini mayoritas masih kosong).
          @endif
          Kosongkan field ini jika tidak ingin mengubah dokter pengirim pasien.
        </small>
      </div>
    </div>

    <div class="d-flex flex-wrap" style="gap: 8px;">
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-save"></i> Simpan Perubahan Massal
      </button>
      <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji->id_permohonan_uji_klinik_haji) }}" class="btn btn-outline-secondary">
        <i class="fa fa-arrow-left"></i> Kembali ke Daftar Pasien
      </a>
      <a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}" class="btn btn-outline-secondary">
        Daftar Haji
      </a>
    </div>
  </form>

  <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.5.1.slim.min.js') }}"></script>
  <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
  <script>
    $(function () {
      $('#customer_id').on('change', function () {
        var name = $(this).find('option:selected').data('name');
        if (name) {
          $('#nama_customer').val(name);
        }
      });

      $('#form-edit-customer-dokter').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var nama = ($('#nama_customer').val() || '').trim();
        var dokter = ($('#nama_dokter_pengirim').val() || '').trim();
        var jumlah = {{ (int) $jumlahPasien }};

        if (!nama) {
          swal('Perhatian', 'Nama customer wajib diisi.', 'warning');
          return;
        }

        var text = 'Nama customer rombongan akan diubah menjadi "' + nama + '".';
        if (dokter) {
          text += '\nDokter pengirim "' + dokter + '" akan diterapkan ke ' + jumlah + ' pasien.';
        } else {
          text += '\nDokter pengirim pasien tidak diubah (field kosong).';
        }

        swal({
          title: 'Simpan perubahan massal?',
          text: text,
          icon: 'warning',
          buttons: {
            cancel: 'Batal',
            confirm: { text: 'Ya, simpan', value: true, className: 'btn btn-primary' }
          },
          dangerMode: true
        }).then(function (ok) {
          if (ok) form.submit();
        });
      });
    });
  </script>
@endsection
