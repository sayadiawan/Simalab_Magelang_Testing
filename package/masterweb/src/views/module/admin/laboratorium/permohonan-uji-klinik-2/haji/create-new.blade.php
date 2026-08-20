@extends('masterweb::template.admin.layout')

@section('title')
  Tambah Data Haji Baru
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
                @if (!empty($haji_id) && (int) ($step ?? 0) === 3)
                  <li class="breadcrumb-item">
                    <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji_id) }}">Daftar Pasien</a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page"><span>Tambah Pasien</span></li>
                @else
                  <li class="breadcrumb-item active" aria-current="page"><span>Tambah Data Haji Baru</span></li>
                @endif
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
      <div class="col-12">
          <div class="alert alert-success">
              {{ session('success') }}
          </div>
      </div>
  @endif
  @if (session('error'))
      <div class="col-12">
          <div class="alert alert-danger">
              {{ session('error') }}
          </div>
      </div>
  @endif

  <div class="card">
    <div class="card-body">
      <h4 class="card-title mb-4">Tambah Data Haji Baru</h4>

      <!-- Progress Steps -->
      <div class="steps-progress mb-4">
        <div class="step-item {{ $step >= 1 ? 'active' : '' }} {{ $step > 1 ? 'completed' : '' }}">
          <div class="step-number">1</div>
          <div class="step-label">Customer</div>
        </div>
        <div class="step-item {{ $step >= 2 ? 'active' : '' }} {{ $step > 2 ? 'completed' : '' }}">
          <div class="step-number">2</div>
          <div class="step-label">Item Pemeriksaan</div>
        </div>
        <div class="step-item {{ $step >= 3 ? 'active' : '' }}">
          <div class="step-number">3</div>
          <div class="step-label">Pasien</div>
        </div>
      </div>

      <!-- Step 1: Customer -->
      @if($step == 1)
      <div class="step-content">
        <h5 class="mb-3">Step 1: Pilih atau Tambah Customer</h5>
        <form action="{{ route('elits-permohonan-uji-klinik-2.haji.store-customer') }}" method="POST" id="form-customer">
          @csrf

          <!-- Pilihan Mode -->
          <div class="form-group">
            <label class="mb-3" style="font-size: 16px; font-weight: bold;">Pilih Mode:</label>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="mode_search" class="mode-option-card" style="cursor: pointer; display: block; margin: 0;">
                  <input class="form-check-input" type="radio" name="customer_mode" id="mode_search" value="search" checked style="display: none;">
                  <div class="card mode-card" id="card_mode_search" style="border: 2px solid #007bff; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transition: all 0.3s;">
                    <div class="card-body text-center" style="padding: 30px;">
                      <i class="fa fa-search" id="icon_mode_search" style="font-size: 48px; margin-bottom: 15px; color: white; transition: all 0.3s;"></i>
                      <h5 class="card-title mb-0" style="font-size: 20px; font-weight: bold;">Cari dari Sistem</h5>
                      <p class="card-text mt-2 mb-0" style="font-size: 14px; opacity: 0.9;">Pilih customer yang sudah terdaftar</p>
                    </div>
                  </div>
                </label>
              </div>
              <div class="col-md-6 mb-3">
                <label for="mode_new" class="mode-option-card" style="cursor: pointer; display: block; margin: 0;">
                  <input class="form-check-input" type="radio" name="customer_mode" id="mode_new" value="new" style="display: none;">
                  <div class="card mode-card" id="card_mode_new" style="border: 2px solid #e0e0e0; background: #f8f9fa; color: #333; transition: all 0.3s;">
                    <div class="card-body text-center" style="padding: 30px;">
                      <i class="fa fa-plus-circle" id="icon_mode_new" style="font-size: 48px; margin-bottom: 15px; color: #28a745; transition: all 0.3s;"></i>
                      <h5 class="card-title mb-0" style="font-size: 20px; font-weight: bold;">Buat Baru</h5>
                      <p class="card-text mt-2 mb-0" style="font-size: 14px; opacity: 0.8;">Tambah customer baru ke sistem</p>
                    </div>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <!-- Mode: Cari dari Sistem -->
          <div class="form-group" id="customer_search_group">
            <label for="customer_search">Cari Customer</label>
            <select class="form-control select2" id="customer_search" name="customer_id" style="width: 100%;">
              <option value="">-- Pilih Customer --</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->id_customer }}">{{ $customer->name_customer }} - {{ $customer->address_customer }}</option>
              @endforeach
            </select>
          </div>

          <!-- Detail Customer (akan muncul setelah dipilih) -->
          <div id="customer_detail" style="display: none;" class="mt-3 mb-3">
            <div class="card">
              <div class="card-header bg-info text-white">
                <h6 class="mb-0">Detail Customer</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <p><strong>Nama:</strong> <span id="detail_name_customer">-</span></p>
                    <p><strong>Alamat:</strong> <span id="detail_address_customer">-</span></p>
                  </div>
                  <div class="col-md-6">
                    <p><strong>Email:</strong> <span id="detail_email_customer">-</span></p>
                    <p><strong>Contact Person:</strong> <span id="detail_cp_customer">-</span></p>
                    <p><strong>Kecamatan:</strong> <span id="detail_kecamatan_customer">-</span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Mode: Buat Baru -->
          <div id="customer_new_group" class="new-customer-card" style="display: none;">
            <button type="button" class="close cancel_customer_new" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>

            <h5 style="color: #e53e3e; font-weight: 700; margin-bottom: 20px;">
              <i class="fa fa-user-plus"></i> Data Pelanggan Baru
            </h5>

            <div class="form-group">
              <label for="name_customer">
                <i class="fa fa-user"></i> Nama Pelanggan
              </label>
              <input type="text" class="form-control" id="name_customer" name="name_customer"
                placeholder="Masukkan Nama Pelanggan Baru">
            </div>

            <div class="form-group">
              <label for="address_customer">
                <i class="fa fa-map-marker-alt"></i> Alamat
              </label>
              <textarea class="form-control" id="address_customer" name="address_customer"
                placeholder="Isikan Alamat Lengkap"></textarea>
            </div>

            <div class="form-group">
              <label for="kecamatan">
                <i class="fa fa-map"></i> Kecamatan
              </label>
              <select name="kecamatan" class="form-control" id="kecamatan" onchange="CheckKecamatan(this)" style="width: 100%;">
                <option value="" selected disabled>Pilih Kecamatan</option>
                <option value="Bandongan">Bandongan</option>
                <option value="Borobudur">Borobudur</option>
                <option value="Candimulyo">Candimulyo</option>
                <option value="Dukun">Dukun</option>
                <option value="Grabag">Grabag</option>
                <option value="Kajoran">Kajoran</option>
                <option value="Kaliangkrik">Kaliangkrik</option>
                <option value="Mertoyudan">Mertoyudan</option>
                <option value="Mungkid">Mungkid</option>
                <option value="Muntilan">Muntilan</option>
                <option value="Ngablak">Ngablak</option>
                <option value="Ngluwar">Ngluwar</option>
                <option value="Pakis">Pakis</option>
                <option value="Salam">Salam</option>
                <option value="Salaman">Salaman</option>
                <option value="Sawangan">Sawangan</option>
                <option value="Secang">Secang</option>
                <option value="Srumbung">Srumbung</option>
                <option value="Tegalrejo">Tegalrejo</option>
                <option value="Tempuran">Tempuran</option>
                <option value="Windusari">Windusari</option>
                <option value="0">Lainnya</option>
              </select>
            </div>

            <div class="form-group">
              <input type="text" class="form-control" id="kecamatan_other" name="kecamatan_other"
                placeholder="Isikan Kecamatan Lain" style="display:none;">
            </div>

            <div class="form-group">
              <label for="email_customer">
                <i class="fa fa-envelope"></i> Email
              </label>
              <input type="email" class="form-control" id="email_customer" name="email_customer"
                placeholder="Isikan Email">
            </div>

            <div class="form-group">
              <label for="cp_customer">
                <i class="fa fa-phone"></i> Contact Person
              </label>
              <textarea class="form-control" id="cp_customer" name="cp_customer" placeholder="Isikan Contact Person"></textarea>
            </div>
          </div>
          <!-- Input Dokter Pengirim -->
          <div class="form-group mt-3">
            <label for="nama_dokter_pengirim_permohonan_uji_klinik">Dokter Pengirim</label>
            <input type="text" class="form-control" id="nama_dokter_pengirim_permohonan_uji_klinik" name="nama_dokter_pengirim_permohonan_uji_klinik" placeholder="Masukkan nama dokter pengirim">
          </div>

          <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Lanjut ke Item Pemeriksaan</button>
            <a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}" class="btn btn-light">Kembali</a>
          </div>
        </form>
      </div>
      @endif

      <!-- Step 2: Item Pemeriksaan -->
      @if($step == 2)
      <div class="step-content">
        <h5 class="mb-3">Step 2: Pilih Item Pemeriksaan</h5>
        <p class="text-muted mb-3" style="font-size: 14px;">
          Pilih parameter pemeriksaan individu (sama seperti pemeriksaan umum). Minimal satu parameter wajib dicentang.
          Jika sudah ada pasien haji sebelumnya, paket mayoritas otomatis tercentang — bisa diubah sebelum lanjut.
        </p>
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('info'))
          <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        <form action="{{ route('elits-permohonan-uji-klinik-2.haji.store-parameter') }}" method="POST" id="form-parameter">
          @csrf
          <input type="hidden" name="customer_id" value="{{ $customer_id }}">
          <input type="hidden" name="haji_id" value="{{ $haji_id }}">

          <div class="paper-container">
            <div class="info-section">
              <div class="info-row">
                <div class="info-label">Customer:</div>
                <div class="info-value">{{ $customer_name }}</div>
              </div>
            </div>

            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.parameter-category-layout', [
              'categoryLayouts' => $categoryLayouts ?? collect(),
              'parameter_jenis_klinik' => $parameter_jenis_klinik,
              'parameter_paket_extra' => $parameter_paket_extra,
              'paket' => $paket ?? [],
              'paket_extra' => $paket_extra ?? [],
            ])
          </div>

          <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Lanjut ke Tambah Pasien</button>
            <a href="{{ route('elits-permohonan-uji-klinik-2.haji.create-new') }}" class="btn btn-light">Kembali</a>
          </div>
        </form>
      </div>
      @endif

      <!-- Step 3: Pasien -->
      @if($step == 3)
      <div class="step-content step3-pasien">
        <div class="step3-header mb-4">
          <h5 class="mb-1">Step 3: Daftarkan Pasien</h5>
          <p class="text-muted mb-0" style="font-size: 14px;">
            Pilih petugas, lalu masukkan pasien lewat pencarian, buat baru, atau import Excel. Setelah daftar siap, klik <strong>Simpan Pasien</strong>.
          </p>
        </div>

        <div class="step3-section mb-4">
          <label for="petugas_penerima" class="step3-section-label">1. Petugas Registrasi</label>
          <div class="input-group" style="max-width: 480px;">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fa fa-user"></i></span>
            </div>
            <select class="form-control" name="petugas_penerima" id="petugas_penerima" form="form-pasien">
              <option value="">Pilih Petugas Registrasi</option>
              @foreach (($petugasPenerima ?? []) as $petugas)
                <option value="{{ $petugas }}" {{ old('petugas_penerima') == $petugas ? 'selected' : '' }}>{{ $petugas }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="step3-section mb-3">
          <label class="step3-section-label d-block">2. Cara menambah pasien</label>
          <div class="pasien-mode-tabs" role="tablist">
            <label for="mode_pasien_search" class="pasien-mode-tab is-active" id="tab_mode_pasien_search">
              <input class="d-none" type="radio" name="pasien_mode" id="mode_pasien_search" value="search" checked form="form-pasien">
              <i class="fa fa-search"></i>
              <span>Cari dari sistem</span>
            </label>
            <label for="mode_pasien_new" class="pasien-mode-tab" id="tab_mode_pasien_new">
              <input class="d-none" type="radio" name="pasien_mode" id="mode_pasien_new" value="new" form="form-pasien">
              <i class="fa fa-user-plus"></i>
              <span>Buat pasien baru</span>
            </label>
            @if(isset($haji_id) && $haji_id)
            <button type="button" class="pasien-mode-tab pasien-mode-tab--action" id="btn-toggle-import-excel">
              <i class="fa fa-file-excel-o"></i>
              <span>Import Excel</span>
            </button>
            @endif
          </div>
        </div>

        @if(isset($haji_id) && $haji_id)
        <div id="import_excel_panel" class="step3-import-panel mb-4" style="display: none;">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <strong><i class="fa fa-file-excel-o text-success mr-1"></i> Import massal via Excel</strong>
              <p class="text-muted mb-0" style="font-size: 13px;">
                @php
                  $__labManual = !empty($klinikNumberSettings) && $klinikNumberSettings->is_nomor_lab_manual;
                  $__sampleManual = !empty($klinikNumberSettings) && $klinikNumberSettings->is_nomor_spesimen_manual;
                  if ($__labManual && $__sampleManual) {
                    $__importHint = 'termasuk nomor lab & nomor sample';
                  } elseif ($__labManual) {
                    $__importHint = 'termasuk nomor lab (nomor sample otomatis)';
                  } elseif ($__sampleManual) {
                    $__importHint = 'termasuk nomor sample (nomor lab otomatis)';
                  } else {
                    $__importHint = 'nomor lab & sample diisi otomatis';
                  }
                @endphp
                Unduh format → isi data ({{ $__importHint }}) → upload. Pasien masuk ke daftar di bawah, lalu klik Simpan.
              </p>
            </div>
            <button type="button" class="btn btn-sm btn-light" id="btn-close-import-excel" title="Tutup">
              <i class="fa fa-times"></i>
            </button>
          </div>
          <div class="row align-items-end">
            <div class="col-md-2 mb-2">
              <label for="jumlah_baris_excel" class="mb-1" style="font-size: 12px;">Jumlah baris</label>
              <input type="number"
                     id="jumlah_baris_excel"
                     class="form-control"
                     value="10"
                     min="1"
                     max="1000">
            </div>
            <div class="col-md-3 mb-2">
              <a href="#"
                 id="btn-download-excel"
                 onclick="downloadExcelFormat(); return false;"
                 class="btn btn-outline-success btn-block">
                <i class="fa fa-download"></i> Download format
              </a>
            </div>
            <div class="col-md-7 mb-2">
              <form id="form-upload-excel" action="{{ route('elits-permohonan-uji-klinik-2.import-haji', $haji_id) }}"
                    method="POST" enctype="multipart/form-data" class="mb-0">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                <div class="input-group">
                  <input type="file" name="file" id="file-excel" class="form-control" accept=".xlsx,.xls" required>
                  <div class="input-group-append">
                    <button class="btn btn-primary" type="submit" id="btn-upload-excel">
                      <i class="fa fa-upload"></i> Upload
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        @endif

        <form action="{{ route('elits-permohonan-uji-klinik-2.haji.store-pasien') }}" method="POST" id="form-pasien">
          @csrf
          <input type="hidden" name="customer_id" value="{{ $customer_id }}">
          <input type="hidden" name="haji_id" value="{{ $haji_id }}">

          <!-- Mode: Cari dari Sistem -->
          <div class="form-group" id="pasien_search_group">
            <div class="pasien-search-panel">
              <div class="pasien-search-panel__header">
                <div>
                  <h6 class="mb-1" id="pasien_search_label"><i class="fa fa-search mr-1"></i> Cari pasien terdaftar</h6>
                  <p class="mb-0 text-muted" style="font-size: 13px;" id="pasien_search_help">
                    Ketik minimal 2 huruf (nama / NIK / no. rekam medis), lalu klik <strong>Tambah</strong> pada hasil.
                  </p>
                </div>
              </div>

              <div class="pasien-search-input-wrap">
                <span class="pasien-search-input-wrap__icon"><i class="fa fa-search"></i></span>
                <input type="text"
                       id="pasien_search_input"
                       class="form-control pasien-search-input"
                       placeholder="Contoh: Budi / 3201... / 1007"
                       autocomplete="off">
                <button type="button" class="btn btn-light pasien-search-clear" id="pasien_search_clear" title="Hapus kata kunci" style="display: none;">
                  <i class="fa fa-times"></i>
                </button>
              </div>

              <div id="pasien_search_status" class="pasien-search-status text-muted" style="display: none;"></div>
              <div id="pasien_search_results" class="pasien-search-results" style="display: none;"></div>

              <select id="pasien_search" name="pasien_search[]" multiple="multiple" class="d-none" aria-hidden="true"></select>
            </div>
          </div>

          <!-- Detail Pasien -->
          <div id="pasien_detail_list" class="mt-3 mb-3" style="display: none;">
            <div class="pasien-selected-panel">
              <div class="pasien-selected-panel__header">
                <h6 class="mb-0">
                  <i class="fa fa-users mr-1"></i>
                  Pasien yang akan didaftarkan
                  <span id="pasien_selected_count" class="badge badge-primary ml-1">0</span>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-clear-all-pasien" style="display: none;">
                  <i class="fa fa-trash"></i> Kosongkan
                </button>
              </div>
              <div id="pasien_detail_cards" class="pasien-selected-list"></div>
            </div>
          </div>

          <!-- Mode: Buat Baru -->
          <div class="form-group" id="pasien_new_group" style="display: none;">
            <div class="pasien-new-panel">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-1"><i class="fa fa-user-plus mr-1"></i> Isi data pasien baru</h6>
                  <p class="text-muted mb-0" style="font-size: 13px;">Bisa menambah beberapa pasien sekaligus.</p>
                </div>
                <button type="button" class="btn btn-success btn-sm" id="btn-tambah-pasien">
                  <i class="fa fa-plus"></i> Tambah baris
                </button>
              </div>
              <div id="pasien-list"></div>
            </div>
          </div>

          <div class="step3-section mb-4 mt-4">
            <label class="step3-section-label d-block">3. Mode pengambilan sampel <span class="text-danger">*</span></label>
            <p class="text-muted mb-3" style="font-size: 13px;">
              Wajib dipilih sebelum simpan. Jika <strong>Dibawa Pelanggan Sendiri</strong>, pengambilan sampel dilewati dan pasien langsung ke penerimaan sampel. Jika <strong>Diambil Di Lokasi/Rumah</strong>, isi biaya pengambilan sampel per pasien; total akan masuk ke nota.
            </p>
            <div class="form-group mb-3" style="max-width: 520px;">
              <label for="mode_pengambilan_sampel">MODE PENGAMBILAN SAMPEL</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fa fa-vial"></i></span>
                </div>
                <select class="form-control" name="mode_pengambilan_sampel" id="mode_pengambilan_sampel" required>
                  <option value="">Pilih mode pengambilan sampel</option>
                  <option value="diambil_lab" {{ old('mode_pengambilan_sampel') == 'diambil_lab' ? 'selected' : '' }}>Diambil di Lab</option>
                  <option value="dibawa_pelanggan" {{ old('mode_pengambilan_sampel') == 'dibawa_pelanggan' ? 'selected' : '' }}>Dibawa Pelanggan Sendiri</option>
                  <option value="diambil_lokasi_rumah" {{ old('mode_pengambilan_sampel') == 'diambil_lokasi_rumah' ? 'selected' : '' }}>Diambil Di Lokasi/Rumah</option>
                </select>
              </div>
            </div>
            <div class="form-group mb-0" id="biaya_pengambilan_container" style="display: none; max-width: 520px;">
              <label for="biaya_pengambilan_sampel">BIAYA</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fa fa-money-bill-wave"></i></span>
                </div>
                <input type="text" class="form-control" name="biaya_pengambilan_sampel"
                  id="biaya_pengambilan_sampel" placeholder="Masukkan biaya pengambilan sampel"
                  value="{{ old('biaya_pengambilan_sampel') }}" inputmode="numeric" autocomplete="off"
                  pattern="[0-9]*">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Isi biaya pengambilan sampel per pasien
              </small>
            </div>
          </div>

          <div class="step3-actions mt-4 pt-3">
            <button type="submit" class="btn btn-primary btn-lg" id="btn-submit-pasien">
              <i class="fa fa-save mr-1"></i> Simpan Pasien
            </button>
            @if (!empty($haji_id))
              <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $haji_id) }}"
                 class="btn btn-outline-danger btn-lg"
                 title="Batalkan tambah pasien dan kembali ke daftar pasien">
                <i class="fa fa-times mr-1"></i> Batal
              </a>
              <a href="{{ route('elits-permohonan-uji-klinik-2.haji.create-new') }}?step=2&customer_id={{ $customer_id }}&haji_id={{ $haji_id }}"
                 class="btn btn-light btn-lg">
                Kembali ke Parameter
              </a>
            @else
              <a href="{{ route('elits-permohonan-uji-klinik-2.haji.create-new') }}?step=2&customer_id={{ $customer_id }}"
                 class="btn btn-light btn-lg">
                Kembali
              </a>
            @endif
          </div>
        </form>
      </div>
      @endif
    </div>
  </div>

  <style>
    .steps-progress {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
      position: relative;
    }

    .steps-progress::before {
      content: '';
      position: absolute;
      top: 20px;
      left: 0;
      right: 0;
      height: 2px;
      background: #e0e0e0;
      z-index: 0;
    }

    .step-item {
      flex: 1;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #e0e0e0;
      color: #999;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
      font-weight: bold;
      transition: all 0.3s;
    }

    .step-item.active .step-number {
      background: #667eea;
      color: white;
    }

    .step-item.completed .step-number {
      background: #4caf50;
      color: white;
    }

    .step-item.completed .step-number::after {
      content: '✓';
    }

    .step-label {
      font-size: 14px;
      color: #666;
    }

    .step-item.active .step-label {
      color: #667eea;
      font-weight: bold;
    }

    .paper-container {
      background-color: #f5f5dc;
      border: 3px solid #4caf50;
      border-radius: 8px;
      padding: 30px;
      margin: 20px 0;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      position: relative;
    }

    .paper-container::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background-color: #4caf50;
    }

    .paper-container::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background-color: #4caf50;
    }

    .category-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 10px 15px;
      font-weight: bold;
      font-size: 16px;
      letter-spacing: 1px;
      margin: 20px 0 15px 0;
      border-radius: 5px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .parameter-list {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px 15px;
      margin-bottom: 25px;
      align-items: start;
    }

    @media (max-width: 1200px) {
      .parameter-list {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .parameter-list {
        grid-template-columns: 1fr;
      }
    }

    .parameter-item {
      display: flex;
      align-items: flex-start;
      padding: 10px 12px;
      background: white;
      border-radius: 4px;
      border: 1px solid #e0e0e0;
      transition: all 0.3s ease;
      min-height: 44px;
      box-sizing: border-box;
    }

    .parameter-item.parameter-empty {
      background: transparent !important;
      border: none !important;
      padding: 10px 12px !important;
      min-height: 44px !important;
      visibility: hidden !important;
      content: '' !important;
    }

    .parameter-item.parameter-empty:hover {
      background: transparent !important;
      border: none !important;
      transform: none !important;
    }

    .parameter-item:hover {
      background: #f0f0f0;
      border-color: #4caf50;
      transform: translateY(-2px);
    }

    /* Mode Card Styling */
    .mode-card {
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      height: 100%;
    }

    .mode-card:hover {
      transform: translateY(-5px) !important;
    }

    .mode-option-card input[type="radio"]:checked + .mode-card {
      border-width: 3px !important;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
    }

    .mode-option-card:hover .mode-card {
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .new-customer-card {
      background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
      border-radius: 12px;
      padding: 25px;
      margin-top: 20px;
      border: 2px solid #fc8181;
      position: relative;
    }

    .new-customer-card .close {
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 28px;
      color: #e53e3e;
      opacity: 1;
      transition: all 0.3s;
    }

    .new-customer-card .close:hover {
      transform: rotate(90deg);
      color: #c53030;
    }

    .new-customer-card .form-group label {
      font-size: 13px;
      font-weight: 600;
      color: #4a5568;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
    }

    .new-customer-card .form-group label i {
      color: #667eea;
      margin-right: 6px;
    }

    .new-customer-card .form-control {
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      padding: 12px 15px;
      font-size: 15px;
      transition: all 0.3s;
    }

    .new-customer-card .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .pasien-row .js-search-wilayah-results .list-group-item {
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .pasien-row .js-search-wilayah-results .list-group-item:hover {
      background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);
    }

    .pasien-row .select-wilayah.loading {
      background-repeat: no-repeat !important;
      background-position: calc(100% - 12px) center !important;
      background-size: 20px 20px !important;
    }

    .parameter-item input[type="checkbox"] {
      width: 20px;
      height: 20px;
      margin-right: 12px;
      margin-top: 2px;
      cursor: pointer;
      flex-shrink: 0;
    }

    .parameter-item label {
      margin: 0;
      margin-left: 20pt;
      cursor: pointer;
      flex: 1;
      font-size: 14px;
      color: #333;
      line-height: 1.5;
      word-wrap: break-word;
      overflow-wrap: break-word;
      white-space: normal;
    }

    .info-section {
      background: white;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      border: 1px solid #e0e0e0;
    }

    .info-row {
      display: flex;
      margin-bottom: 8px;
    }

    .info-label {
      font-weight: bold;
      width: 180px;
      color: #555;
    }

    .info-value {
      color: #333;
    }

    .step3-section-label {
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .pasien-mode-tabs {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .pasien-mode-tab {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin: 0;
      padding: 10px 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: #fff;
      color: #374151;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.15s ease;
      user-select: none;
    }

    .pasien-mode-tab i {
      color: #6b7280;
    }

    .pasien-mode-tab:hover {
      border-color: #9ca3af;
      background: #f9fafb;
    }

    .pasien-mode-tab.is-active {
      border-color: #4f46e5;
      background: #eef2ff;
      color: #3730a3;
      box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
    }

    .pasien-mode-tab.is-active i {
      color: #4f46e5;
    }

    .pasien-mode-tab--action {
      border-style: dashed;
      color: #047857;
    }

    .pasien-mode-tab--action i {
      color: #059669;
    }

    .pasien-mode-tab--action.is-open {
      border-color: #059669;
      background: #ecfdf5;
      border-style: solid;
    }

    .step3-import-panel {
      border: 1px solid #a7f3d0;
      background: #f0fdf4;
      border-radius: 10px;
      padding: 14px 16px;
    }

    .pasien-new-panel {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      background: #fafbfc;
      padding: 16px;
    }

    .step3-actions {
      border-top: 1px solid #e5e7eb;
    }

    .pasien-search-panel {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      background: #fafbfc;
      padding: 16px;
    }

    .pasien-search-panel__header {
      margin-bottom: 12px;
    }

    .pasien-search-panel__header h6 {
      font-weight: 700;
      color: #1f2937;
    }

    .pasien-search-input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }

    .pasien-search-input-wrap__icon {
      position: absolute;
      left: 14px;
      color: #6b7280;
      z-index: 2;
      pointer-events: none;
    }

    .pasien-search-input {
      height: 48px;
      padding-left: 40px !important;
      padding-right: 44px !important;
      border: 2px solid #d1d5db !important;
      border-radius: 8px !important;
      font-size: 15px;
      background: #fff !important;
    }

    .pasien-search-input:focus {
      border-color: #4f46e5 !important;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
    }

    .pasien-search-clear {
      position: absolute;
      right: 6px;
      z-index: 2;
      width: 36px;
      height: 36px;
      padding: 0;
      border-radius: 6px;
    }

    .pasien-search-status {
      margin-top: 10px;
      font-size: 13px;
    }

    .pasien-search-results {
      margin-top: 10px;
      max-height: 320px;
      overflow-y: auto;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #fff;
    }

    .pasien-search-result-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 14px;
      border-bottom: 1px solid #f3f4f6;
      transition: background 0.15s ease;
    }

    .pasien-search-result-item:last-child {
      border-bottom: none;
    }

    .pasien-search-result-item:hover {
      background: #f5f7ff;
    }

    .pasien-search-result-item.is-added {
      background: #f0fdf4;
      opacity: 0.85;
    }

    .pasien-search-result-meta {
      min-width: 0;
      flex: 1;
    }

    .pasien-search-result-meta .nama {
      font-weight: 700;
      color: #111827;
      margin-bottom: 4px;
      text-transform: uppercase;
    }

    .pasien-search-result-meta .detail {
      font-size: 12px;
      color: #6b7280;
      line-height: 1.4;
    }

    .pasien-search-result-meta .detail span {
      display: inline-block;
      margin-right: 10px;
    }

    .pasien-selected-panel {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      background: #fff;
      overflow: hidden;
    }

    .pasien-selected-panel__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 14px;
      background: #f8fafc;
      border-bottom: 1px solid #e5e7eb;
    }

    .pasien-selected-panel__header h6 {
      font-weight: 700;
      color: #1f2937;
      font-size: 14px;
    }

    .pasien-selected-list {
      max-height: 560px;
      overflow-y: auto;
    }

    .pasien-selected-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 14px;
      border-bottom: 1px solid #f3f4f6;
      transition: background 0.12s ease;
    }

    .pasien-selected-item:last-child {
      border-bottom: none;
    }

    .pasien-selected-item:hover {
      background: #f9fafb;
    }

    .pasien-selected-item__index {
      flex-shrink: 0;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: #eef2ff;
      color: #4338ca;
      font-size: 12px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-top: 2px;
    }

    .pasien-selected-item__body {
      flex: 1;
      min-width: 0;
    }

    .pasien-selected-item__name {
      font-weight: 700;
      color: #111827;
      text-transform: uppercase;
      font-size: 14px;
      margin-bottom: 6px;
      line-height: 1.3;
    }

    .pasien-selected-item__badge {
      display: inline-block;
      margin-left: 8px;
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 10px;
      font-weight: 700;
      text-transform: none;
      letter-spacing: 0.02em;
      vertical-align: middle;
      background: #dbeafe;
      color: #1d4ed8;
    }

    .pasien-selected-item__meta {
      display: flex;
      flex-wrap: wrap;
      gap: 6px 14px;
      font-size: 12px;
      color: #4b5563;
      line-height: 1.4;
    }

    .pasien-selected-item__meta span {
      white-space: nowrap;
    }

    .pasien-selected-item__meta .meta-label {
      color: #9ca3af;
      font-weight: 600;
      margin-right: 4px;
    }

    .pasien-selected-item__numbers {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .pasien-selected-item__numbers .form-group {
      margin-bottom: 0;
      min-width: 140px;
      flex: 1 1 140px;
      max-width: 220px;
    }

    .pasien-selected-item__numbers label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: #374151;
      margin-bottom: 4px;
    }

    .pasien-selected-item__numbers .form-control {
      height: 36px;
      font-weight: 600;
      color: #4338ca;
      border: 1px solid #c7d2fe;
      background: #eef2ff;
    }

    .pasien-selected-item__remove {
      flex-shrink: 0;
      width: 32px;
      height: 32px;
      padding: 0;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      background: #fff;
      color: #6b7280;
      line-height: 1;
    }

    .pasien-selected-item__remove:hover {
      background: #fef2f2;
      border-color: #fecaca;
      color: #dc2626;
    }

    @media (max-width: 576px) {
      .pasien-selected-item__meta span {
        white-space: normal;
        width: 100%;
      }
    }
  </style>

  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
  <script>
    $(document).ready(function() {
      // Initialize customer search with select2
      $('#customer_search').select2({
        placeholder: 'Pilih atau cari customer',
        allowClear: true
      });

      // Update card styling based on selection
      function updateModeCards() {
        var selectedMode = $('input[name="customer_mode"]:checked').val();

        if (selectedMode === 'search') {
          // Active state for search
          $('#card_mode_search').css({
            'border': '3px solid #007bff',
            'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'color': 'white',
            'box-shadow': '0 8px 16px rgba(102, 126, 234, 0.4)',
            'transform': 'scale(1.02)'
          });
          $('#icon_mode_search').css('color', 'white');
          // Inactive state for new
          $('#card_mode_new').css({
            'border': '2px solid #e0e0e0',
            'background': '#f8f9fa',
            'color': '#333',
            'box-shadow': 'none',
            'transform': 'scale(1)'
          });
          $('#icon_mode_new').css('color', '#28a745');
        } else {
          // Active state for new
          $('#card_mode_new').css({
            'border': '3px solid #28a745',
            'background': 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
            'color': 'white',
            'box-shadow': '0 8px 16px rgba(40, 167, 69, 0.4)',
            'transform': 'scale(1.02)'
          });
          $('#icon_mode_new').css('color', 'white');
          // Inactive state for search
          $('#card_mode_search').css({
            'border': '2px solid #e0e0e0',
            'background': '#f8f9fa',
            'color': '#333',
            'box-shadow': 'none',
            'transform': 'scale(1)'
          });
          $('#icon_mode_search').css('color', '#667eea');
        }
      }

      // Initialize card styling
      updateModeCards();

      // Tombol close pada form pelanggan baru: kembali ke mode cari dari sistem
      $('.cancel_customer_new').on('click', function() {
        $('#mode_search').prop('checked', true).trigger('change');
      });

      // Toggle between search and new mode
      $('input[name="customer_mode"]').on('change', function() {
        var mode = $(this).val();

        // Update card styling
        updateModeCards();

        if (mode === 'search') {
          $('#customer_search_group').show();
          $('#customer_new_group').hide();
          $('#customer_detail').hide();
          // Clear new customer form
          $('#name_customer, #address_customer, #email_customer, #cp_customer, #kecamatan_other').val('');
          $('#kecamatan').val(null).trigger('change');
          $('#kecamatan_other').hide();
        } else {
          $('#customer_search_group').hide();
          $('#customer_new_group').show();
          $('#customer_detail').hide();
          // Clear customer search
          $('#customer_search').val(null).trigger('change');
        }
      });

      // Add hover effect
      $('.mode-card').hover(
        function() {
          if (!$(this).closest('label').find('input[type="radio"]').is(':checked')) {
            $(this).css({
              'box-shadow': '0 4px 8px rgba(0,0,0,0.1)',
              'transform': 'translateY(-2px)'
            });
          }
        },
        function() {
          if (!$(this).closest('label').find('input[type="radio"]').is(':checked')) {
            $(this).css({
              'box-shadow': 'none',
              'transform': 'translateY(0)'
            });
          }
        }
      );

      // When customer is selected, fetch and display details
      $('#customer_search').on('select2:select', function(e) {
        var customerId = e.params.data.id;

        if (customerId) {
          var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content') || $('#form-customer input[name="_token"]').val() || '{{ csrf_token() }}';

          // Fetch customer details via AJAX
          $.ajax({
            url: "{{ route('elits-permohonan-uji-klinik-2.haji.get-customer-detail') }}",
            type: "POST",
            data: {
              _token: CSRF_TOKEN,
              customer_id: customerId
            },
            dataType: 'json',
            success: function(response) {
              if (response.status) {
                var customer = response.data;

                // Display customer details
                $('#detail_name_customer').text(customer.name_customer || '-');
                $('#detail_address_customer').text(customer.address_customer || '-');
                $('#detail_email_customer').text(customer.email_customer || '-');
                $('#detail_cp_customer').text(customer.cp_customer || '-');
                $('#detail_kecamatan_customer').text(customer.kecamatan_customer || '-');

                // Show customer detail card
                $('#customer_detail').slideDown();
              }
            },
            error: function() {
              swal({
                title: "Error!",
                text: "Gagal mengambil data customer!",
                icon: "error"
              });
            }
          });
        }
      });

      // When customer is cleared, hide detail
      $('#customer_search').on('select2:clear', function(e) {
        $('#customer_detail').slideUp();
      });

      $('#kecamatan').select2({
        allowClear: true,
        placeholder: 'Pilih Kecamatan'
      });

      function CheckKecamatan(val) {
        var element = document.getElementById('kecamatan_other');
        if (val.value == '0')
          element.style.display = 'block';
        else
          element.style.display = 'none';
      }

      window.CheckKecamatan = CheckKecamatan;

      // Initialize pasien (only for step 3)
      @if($step == 3)
      var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content') || $('#form-pasien input[name="_token"]').val() || '{{ csrf_token() }}';
      var prefilledPasienIds = @json($prefilled_pasien_ids ?? []);
      var prefilledPasien = @json($prefilled_pasien ?? []);
      var hajiNumberSettings = {
        isNomorLabManual: {{ !empty($klinikNumberSettings) && $klinikNumberSettings->is_nomor_lab_manual ? 'true' : 'false' }},
        isNomorSampleManual: {{ !empty($klinikNumberSettings) && $klinikNumberSettings->is_nomor_spesimen_manual ? 'true' : 'false' }},
        nextLabNumber: {{ (int) ($nextLabNumber ?? 1) }}
      };
      var hajiNextLabCursor = parseInt(hajiNumberSettings.nextLabNumber, 10) || 1;

      function cleanHajiNomorUrut(value) {
        var digits = String(value || '').replace(/\D/g, '');
        // Angka 0 dianggap kosong (bukan nomor lab valid)
        if (!digits || parseInt(digits, 10) < 1) {
          return '';
        }
        return String(parseInt(digits, 10));
      }

      function takeNextHajiLabNumber() {
        if (!hajiNextLabCursor || hajiNextLabCursor < 1) {
          hajiNextLabCursor = 1;
        }
        var n = hajiNextLabCursor;
        hajiNextLabCursor += 1;
        return n;
      }

      /**
       * Pastikan nomor lab (dan sample manual) unik antar kartu/baris di form.
       * Duplikat otomatis digeser ke angka berikutnya.
       */
      function dedupeHajiFormManualNumbers($scope) {
        var usedLab = {};
        var usedSample = {};
        var changed = false;

        $scope.each(function() {
          var $lab = $(this).find('.js-pasien-nomor-lab, input[name*="[nomor_lab_manual]"]').first();
          var $sample = $(this).find('.js-pasien-nomor-sample, input[name*="[nomor_spesimen_manual]"]').first();

          if ($lab.length) {
            var labVal = cleanHajiNomorUrut($lab.val());
            var labInt = parseInt(labVal, 10) || 0;
            if (!labInt) {
              labInt = takeNextHajiLabNumber();
              changed = true;
            }
            while (usedLab[labInt]) {
              labInt += 1;
              changed = true;
            }
            usedLab[labInt] = true;
            if (labInt >= hajiNextLabCursor) {
              hajiNextLabCursor = labInt + 1;
            }
            $lab.val(String(labInt));
          }

          if ($sample.length && hajiNumberSettings.isNomorSampleManual) {
            var sampleVal = cleanHajiNomorUrut($sample.val());
            var sampleInt = parseInt(sampleVal, 10) || 0;
            if (sampleInt > 0) {
              while (usedSample[sampleInt]) {
                sampleInt += 1;
                changed = true;
              }
              usedSample[sampleInt] = true;
              $sample.val(String(sampleInt));
            }
          }
        });

        return changed;
      }

      function buildPasienNumberFieldsHtml(pasienId, nomorLab, nomorSample) {
        var html = '<div class="pasien-selected-item__numbers">';
        var labVal = cleanHajiNomorUrut(nomorLab);
        if (!labVal) {
          labVal = String(takeNextHajiLabNumber());
        } else {
          var labInt = parseInt(labVal, 10) || 0;
          if (labInt >= hajiNextLabCursor) {
            hajiNextLabCursor = labInt + 1;
          }
        }

        if (hajiNumberSettings.isNomorLabManual) {
          html +=
            '<div class="form-group">' +
              '<label>Nomor Lab <span class="text-danger">*</span></label>' +
              '<input type="text" class="form-control js-pasien-nomor-lab" ' +
                'name="pasien_manual_numbers[' + pasienId + '][nomor_lab_manual]" ' +
                'value="' + $('<div>').text(labVal).html() + '" ' +
                'placeholder="No. urut" inputmode="numeric" required>' +
            '</div>';
        } else {
          html +=
            '<div class="form-group">' +
              '<label>Nomor Lab <small class="text-muted">(otomatis kesmas + klinik)</small></label>' +
              '<input type="text" class="form-control js-pasien-nomor-lab" ' +
                'name="pasien_manual_numbers[' + pasienId + '][nomor_lab_manual]" ' +
                'value="' + $('<div>').text(labVal).html() + '" ' +
                'readonly>' +
            '</div>';
        }
        if (hajiNumberSettings.isNomorSampleManual) {
          html +=
            '<div class="form-group">' +
              '<label>Nomor Sample <span class="text-danger">*</span></label>' +
              '<input type="text" class="form-control js-pasien-nomor-sample" ' +
                'name="pasien_manual_numbers[' + pasienId + '][nomor_spesimen_manual]" ' +
                'value="' + $('<div>').text(cleanHajiNomorUrut(nomorSample)).html() + '" ' +
                'placeholder="No. urut" inputmode="numeric" required>' +
            '</div>';
        }
        html += '</div>';
        return html;
      }

      @if(session('haji_import_status') === 'success')
      swal({
        title: 'Import Berhasil',
        text: @json(session('success')),
        icon: 'success',
        button: 'OK'
      });
      @elseif(session('haji_import_status') === 'warning')
      swal({
        title: 'Import Selesai',
        text: @json(session('success')),
        icon: 'warning',
        button: 'OK'
      });
      @elseif(session('haji_import_status') === 'error')
      swal({
        title: 'Import Gagal',
        text: '{{ session('error') ?? 'Terjadi kesalahan saat import Excel.' }}',
        icon: 'error',
        button: 'OK'
      });
      @endif

      // Update pasien mode tab styling
      function updatePasienModeCards() {
        var selectedMode = $('input[name="pasien_mode"]:checked').val();
        $('#tab_mode_pasien_search').toggleClass('is-active', selectedMode === 'search');
        $('#tab_mode_pasien_new').toggleClass('is-active', selectedMode === 'new');
      }

      // Initialize card styling
      updatePasienModeCards();

      // Toggle between search and new mode for pasien
      $('input[name="pasien_mode"]').on('change', function() {
        var mode = $(this).val();

        updatePasienModeCards();
        $('#import_excel_panel').slideUp(150);
        $('#btn-toggle-import-excel').removeClass('is-open');

        if (mode === 'search') {
          $('#pasien_search_group').show();
          $('#pasien_new_group').hide();
          if ($('#pasien_detail_cards .pasien-selected-item').length > 0) {
            $('#pasien_detail_list').show();
          } else {
            $('#pasien_detail_list').hide();
          }
          $('#pasien-list').empty();
          if (typeof window.resetPasienRows === 'function') {
            window.resetPasienRows();
          }
        } else {
          $('#pasien_search_group').hide();
          $('#pasien_new_group').show();
          $('#pasien_detail_list').hide();
          clearPasienSearchUi(true);
          if ($('#pasien-list .pasien-row').length === 0) {
            addPasienRow();
          }
        }
      });

      $('#btn-toggle-import-excel').on('click', function() {
        var $panel = $('#import_excel_panel');
        if ($panel.is(':visible')) {
          $panel.slideUp(150);
          $(this).removeClass('is-open');
        } else {
          // Pastikan mode cari aktif agar hasil import masuk daftar
          $('#mode_pasien_search').prop('checked', true).trigger('change');
          $panel.slideDown(150);
          $(this).addClass('is-open');
        }
      });

      $('#btn-close-import-excel').on('click', function() {
        $('#import_excel_panel').slideUp(150);
        $('#btn-toggle-import-excel').removeClass('is-open');
      });

      // Auto-buka panel import jika baru saja berhasil import
      @if(in_array(session('haji_import_status'), ['success', 'warning']))
      $('#mode_pasien_search').prop('checked', true);
      updatePasienModeCards();
      @endif

      // Store pasien details cache
      var pasienDetailsCache = {};
      var pasienSearchTimer = null;
      var pasienSearchXhr = null;

      function formatTanggalLahirForLabel(tanggalLahir) {
        if (!tanggalLahir) {
          return '-';
        }

        var dt = new Date(tanggalLahir);
        if (isNaN(dt.getTime())) {
          return tanggalLahir;
        }

        return dt.toLocaleDateString('id-ID');
      }

      function formatPasienOptionText(pasien) {
        var nama = (pasien.nama_pasien || '-').toUpperCase();
        var nik = pasien.nik_pasien || '-';
        var noRekamMedis = pasien.no_rekammedis_pasien || '-';
        var tanggalLahir = formatTanggalLahirForLabel(pasien.tgllahir_pasien);
        return nama + ' - ' + nik + ' - ' + noRekamMedis + ' - (' + tanggalLahir + ')';
      }

      function getSelectedPasienIds() {
        return ($('#pasien_search').val() || []).map(String);
      }

      function setSelectedPasienIds(ids) {
        var $select = $('#pasien_search');
        $select.empty();
        (ids || []).forEach(function(id) {
          if (!id) return;
          id = id.toString();
          var label = pasienDetailsCache[id]
            ? formatPasienOptionText(pasienDetailsCache[id])
            : id;
          $select.append(new Option(label, id, true, true));
        });
        $select.val(ids);
      }

      function addSelectedPasienId(pasienId) {
        pasienId = pasienId.toString();
        var ids = getSelectedPasienIds();
        if (ids.indexOf(pasienId) === -1) {
          ids.push(pasienId);
          setSelectedPasienIds(ids);
        }
      }

      function removeSelectedPasienId(pasienId) {
        pasienId = pasienId.toString();
        var ids = getSelectedPasienIds().filter(function(id) {
          return id !== pasienId;
        });
        setSelectedPasienIds(ids);
      }

      function updatePasienSelectedCount() {
        var $items = $('#pasien_detail_cards .pasien-selected-item');
        var count = $items.length;
        $('#pasien_selected_count').text(count);
        $items.each(function(i) {
          $(this).find('.pasien-selected-item__index').text(i + 1);
        });
        if (count > 0) {
          $('#pasien_detail_list').show();
          $('#btn-clear-all-pasien').show();
          $('#pasien_search_label').html('<i class="fa fa-search mr-1"></i> Tambah pasien lain');
          $('#pasien_search_help').html('Pasien terpilih ada di daftar bawah. Cari lagi untuk menambahkan.');
        } else {
          $('#btn-clear-all-pasien').hide();
          $('#pasien_search_label').html('<i class="fa fa-search mr-1"></i> Cari pasien terdaftar');
          $('#pasien_search_help').html('Ketik minimal 2 huruf (nama / NIK / no. rekam medis), lalu klik <strong>Tambah</strong> pada hasil.');
        }
      }

      function clearPasienSearchUi(clearSelected) {
        $('#pasien_search_input').val('');
        $('#pasien_search_clear').hide();
        $('#pasien_search_results').hide().empty();
        $('#pasien_search_status').hide().text('');
        if (pasienSearchXhr && pasienSearchXhr.readyState !== 4) {
          pasienSearchXhr.abort();
        }
        if (clearSelected) {
          $('#pasien_search').empty().val(null);
          $('#pasien_detail_cards').empty();
          pasienDetailsCache = {};
          $('#pasien_detail_list').hide();
          updatePasienSelectedCount();
        }
      }

      function renderPasienSearchResults(items) {
        var $box = $('#pasien_search_results');
        var selected = getSelectedPasienIds();
        $box.empty();

        if (!items || items.length === 0) {
          $box.hide();
          $('#pasien_search_status').show().text('Tidak ada pasien yang cocok. Coba kata kunci lain.');
          return;
        }

        $('#pasien_search_status').hide().text('');
        items.forEach(function(item) {
          var id = (item.id || '').toString();
          var already = selected.indexOf(id) !== -1;
          var nama = (item.nama || item.text || '-').toUpperCase();
          var nik = item.nik || '-';
          var noRekam = item.no_rekam || '-';
          var tgl = item.tgllahir || '-';
          if (tgl && tgl !== '-') {
            tgl = formatTanggalLahirForLabel(tgl);
          }

          var btnHtml = already
            ? '<button type="button" class="btn btn-sm btn-success" disabled><i class="fa fa-check"></i> Sudah ditambah</button>'
            : '<button type="button" class="btn btn-sm btn-primary btn-add-pasien-result" data-pasien-id="' + id + '"><i class="fa fa-plus"></i> Tambah</button>';

          $box.append(
            '<div class="pasien-search-result-item' + (already ? ' is-added' : '') + '" data-pasien-id="' + id + '">' +
              '<div class="pasien-search-result-meta">' +
                '<div class="nama">' + $('<div>').text(nama).html() + '</div>' +
                '<div class="detail">' +
                  '<span><i class="fa fa-id-card"></i> NIK: ' + $('<div>').text(nik).html() + '</span>' +
                  '<span><i class="fa fa-folder-open"></i> RM: ' + $('<div>').text(noRekam).html() + '</span>' +
                  '<span><i class="fa fa-calendar"></i> ' + $('<div>').text(tgl).html() + '</span>' +
                '</div>' +
              '</div>' +
              btnHtml +
            '</div>'
          );
        });
        $box.show();
      }

      function searchPasien(keyword) {
        keyword = (keyword || '').trim();
        if (keyword.length < 2) {
          $('#pasien_search_results').hide().empty();
          $('#pasien_search_status').show().text('Ketik minimal 2 karakter untuk mencari...');
          return;
        }

        if (pasienSearchXhr && pasienSearchXhr.readyState !== 4) {
          pasienSearchXhr.abort();
        }

        $('#pasien_search_status').show().html('<i class="fa fa-spinner fa-spin"></i> Mencari pasien...');
        $('#pasien_search_results').hide().empty();

        pasienSearchXhr = $.ajax({
          url: "{{ route('get-pasien-by-select') }}",
          type: 'POST',
          dataType: 'json',
          data: {
            _token: CSRF_TOKEN,
            search: keyword
          },
          success: function(response) {
            renderPasienSearchResults(Array.isArray(response) ? response : []);
          },
          error: function(xhr) {
            if (xhr.statusText === 'abort') return;
            $('#pasien_search_status').show().text('Gagal mencari pasien. Coba lagi.');
            $('#pasien_search_results').hide().empty();
          }
        });
      }

      $('#pasien_search_input').on('input', function() {
        var val = $(this).val();
        $('#pasien_search_clear').toggle(!!val);
        clearTimeout(pasienSearchTimer);
        pasienSearchTimer = setTimeout(function() {
          searchPasien(val);
        }, 300);
      });

      $('#pasien_search_input').on('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(pasienSearchTimer);
          searchPasien($(this).val());
        }
      });

      $('#pasien_search_clear').on('click', function() {
        clearPasienSearchUi(false);
        $('#pasien_search_input').focus();
      });

      $(document).on('click', '.btn-add-pasien-result', function() {
        var pasienId = $(this).data('pasien-id');
        if (!pasienId) return;
        fetchPasienDetail(pasienId.toString());
      });

      $('#btn-clear-all-pasien').on('click', function() {
        swal({
          title: 'Kosongkan daftar?',
          text: 'Semua pasien yang dipilih akan dihapus dari daftar.',
          icon: 'warning',
          buttons: ['Batal', 'Ya, kosongkan'],
          dangerMode: true
        }).then(function(ok) {
          if (ok) {
            clearPasienSearchUi(true);
          }
        });
      });

      function syncPasienSelectOption(pasienId, optionText) {
        // Compatibility helper: pastikan ID ada di hidden select
        addSelectedPasienId(pasienId);
        var $opt = $('#pasien_search option[value="' + pasienId + '"]');
        if ($opt.length) {
          $opt.text(optionText || pasienId);
        }
      }

      function prefillImportedPasien() {
        var list = Array.isArray(prefilledPasien) ? prefilledPasien : [];
        if (list.length === 0 && Array.isArray(prefilledPasienIds) && prefilledPasienIds.length > 0) {
          prefilledPasienIds.forEach(function(pasienId) {
            if (!pasienId) return;
            fetchPasienDetail(pasienId.toString());
          });
          return;
        }

        if (list.length === 0) {
          return;
        }

        list.forEach(function(pasien) {
          if (!pasien || !pasien.id_pasien) {
            return;
          }
          var pasienId = pasien.id_pasien.toString();
          pasien.id_pasien = pasienId;
          pasienDetailsCache[pasienId] = pasien;
          addSelectedPasienId(pasienId);
          displayPasienDetail(pasien);
        });
        updatePasienSelectedCount();
      }

      // Function to fetch and display pasien detail
      function fetchPasienDetail(pasienId) {
        pasienId = pasienId.toString();

        // Check cache first
        if (pasienDetailsCache[pasienId]) {
          addSelectedPasienId(pasienId);
          displayPasienDetail(pasienDetailsCache[pasienId]);
          updatePasienSelectedCount();
          renderPasienSearchResultsRefresh();
          return;
        }

        // Fetch pasien details via AJAX
        $.ajax({
          url: "{{ route('elits-permohonan-uji-klinik-2.haji.get-pasien-detail') }}",
          type: "POST",
          data: {
            _token: CSRF_TOKEN,
            pasien_id: pasienId
          },
          dataType: 'json',
          success: function(response) {
            if (response.status) {
              var pasien = response.data;
              pasien.id_pasien = pasienId;
              pasienDetailsCache[pasienId] = pasien;

              addSelectedPasienId(pasienId);
              displayPasienDetail(pasien);
              updatePasienSelectedCount();
              renderPasienSearchResultsRefresh();
            } else {
              swal({
                title: "Error!",
                text: response.pesan || "Data pasien tidak ditemukan!",
                icon: "error"
              });
            }
          },
          error: function() {
            swal({
              title: "Error!",
              text: "Gagal mengambil data pasien!",
              icon: "error"
            });
          }
        });
      }

      function renderPasienSearchResultsRefresh() {
        // Refresh tombol "Sudah ditambah" bila hasil pencarian masih terbuka
        var $items = $('#pasien_search_results .pasien-search-result-item');
        if ($items.length === 0) return;
        var selected = getSelectedPasienIds();
        $items.each(function() {
          var id = ($(this).data('pasien-id') || '').toString();
          var already = selected.indexOf(id) !== -1;
          $(this).toggleClass('is-added', already);
          var $btn = $(this).find('button').first();
          if (already) {
            $btn.replaceWith('<button type="button" class="btn btn-sm btn-success" disabled><i class="fa fa-check"></i> Sudah ditambah</button>');
          } else if ($btn.prop('disabled')) {
            $btn.replaceWith('<button type="button" class="btn btn-sm btn-primary btn-add-pasien-result" data-pasien-id="' + id + '"><i class="fa fa-plus"></i> Tambah</button>');
          }
        });
      }

      // Function to display pasien detail card
      function displayPasienDetail(pasien) {
        var cardId = 'pasien_card_' + pasien.id_pasien;

        // Check if card already exists
        if ($('#' + cardId).length > 0) {
          return;
        }

        var nama = $('<div>').text((pasien.nama_pasien || '-').toUpperCase()).html();
        var tglLahir = pasien.tgllahir_pasien ? new Date(pasien.tgllahir_pasien).toLocaleDateString('id-ID') : '-';
        var gender = pasien.gender_pasien == 'L' ? 'Laki-laki' : (pasien.gender_pasien == 'P' ? 'Perempuan' : '-');
        var alamat = $('<div>').text(pasien.alamat_pasien || '-').html();
        var noRm = $('<div>').text(pasien.no_rekammedis_pasien || '-').html();
        var index = $('#pasien_detail_cards .pasien-selected-item').length + 1;
        var numberFields = buildPasienNumberFieldsHtml(
          pasien.id_pasien,
          pasien.nomor_lab_manual || '',
          pasien.nomor_spesimen_manual || ''
        );
        var existingBadge = pasien.is_existing_system
          ? '<span class="pasien-selected-item__badge">Sudah tersimpan di sistem</span>'
          : '';

        var cardHtml =
          '<div class="pasien-selected-item" id="' + cardId + '" data-pasien-id="' + pasien.id_pasien + '">' +
            '<div class="pasien-selected-item__index">' + index + '</div>' +
            '<div class="pasien-selected-item__body">' +
              '<div class="pasien-selected-item__name">' + nama + existingBadge + '</div>' +
              '<div class="pasien-selected-item__meta">' +
                '<span><span class="meta-label">Lahir</span>' + $('<div>').text(tglLahir).html() + '</span>' +
                '<span><span class="meta-label">JK</span>' + $('<div>').text(gender).html() + '</span>' +
                '<span><span class="meta-label">RM</span>' + noRm + '</span>' +
                '<span><span class="meta-label">Alamat</span>' + alamat + '</span>' +
              '</div>' +
              numberFields +
            '</div>' +
            '<button type="button" class="pasien-selected-item__remove btn-remove-pasien-card" data-pasien-id="' + pasien.id_pasien + '" title="Hapus dari daftar">' +
              '<i class="fa fa-times"></i>' +
            '</button>' +
          '</div>';

        $('#pasien_detail_cards').append(cardHtml);
        $('#pasien_detail_list').slideDown();
        updatePasienSelectedCount();
      }

      $(document).on('input', '.js-pasien-nomor-lab, .js-pasien-nomor-sample', function() {
        $(this).val(cleanHajiNomorUrut($(this).val()));
      });

      // Remove pasien card manually
      $(document).on('click', '.btn-remove-pasien-card', function() {
        var pasienId = ($(this).data('pasien-id') || '').toString();
        removeSelectedPasienId(pasienId);
        $('#pasien_card_' + pasienId).remove();

        if ($('#pasien_detail_cards .pasien-selected-item').length === 0) {
          $('#pasien_detail_list').slideUp();
        }
        updatePasienSelectedCount();
        renderPasienSearchResultsRefresh();
      });

      prefillImportedPasien();

      function toggleModePengambilanFieldsHaji() {
        var modePengambilan = $('#mode_pengambilan_sampel').val();
        if (modePengambilan === 'diambil_lokasi_rumah') {
          $('#biaya_pengambilan_container').slideDown();
        } else {
          $('#biaya_pengambilan_container').slideUp();
        }
      }

      $('#mode_pengambilan_sampel').on('change', toggleModePengambilanFieldsHaji);
      toggleModePengambilanFieldsHaji();

      $('#biaya_pengambilan_sampel').on('input', function() {
        this.value = String(this.value || '').replace(/\D/g, '');
      });

      // Form pasien validation
      $('#form-pasien').on('submit', function(e) {
        var modePengambilan = ($('#mode_pengambilan_sampel').val() || '').trim();
        if (!modePengambilan) {
          e.preventDefault();
          swal({
            title: "Error!",
            text: "Silakan pilih Mode Pengambilan Sampel terlebih dahulu!",
            icon: "warning"
          });
          return false;
        }

        if (modePengambilan === 'diambil_lokasi_rumah') {
          var biayaRaw = ($('#biaya_pengambilan_sampel').val() || '').toString().trim();
          var biaya = parseInt(biayaRaw, 10);
          if (biayaRaw === '' || isNaN(biaya) || biaya < 0) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Silakan isi biaya pengambilan sampel!",
              icon: "warning"
            });
            return false;
          }
        }

        var mode = $('input[name="pasien_mode"]:checked').val();

        if (mode === 'search') {
          // Mode: Cari dari Sistem
          var selectedPasien = $('#pasien_search').val();
          if (!selectedPasien || selectedPasien.length === 0) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Silakan pilih minimal satu pasien dari sistem!",
              icon: "warning"
            });
            return false;
          }

          var missingNumber = false;
          dedupeHajiFormManualNumbers($('#pasien_detail_cards .pasien-selected-item'));
          $('#pasien_detail_cards .pasien-selected-item').each(function() {
            if (hajiNumberSettings.isNomorLabManual) {
              var labVal = cleanHajiNomorUrut($(this).find('.js-pasien-nomor-lab').val());
              $(this).find('.js-pasien-nomor-lab').val(labVal);
              if (!labVal) missingNumber = true;
            }
            if (hajiNumberSettings.isNomorSampleManual) {
              var sampleVal = cleanHajiNomorUrut($(this).find('.js-pasien-nomor-sample').val());
              $(this).find('.js-pasien-nomor-sample').val(sampleVal);
              if (!sampleVal) missingNumber = true;
            }
          });
          if (missingNumber) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Nomor sample dan nomor lab wajib diisi untuk setiap pasien!",
              icon: "warning"
            });
            return false;
          }
        } else {
          // Mode: Buat Baru
          var rows = $('#pasien-list .pasien-row');
          if (rows.length === 0) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Silakan tambah minimal satu pasien!",
              icon: "warning"
            });
            return false;
          }

          // Validate each row
          var isValid = true;
          dedupeHajiFormManualNumbers(rows);
          rows.each(function() {
            var nama = ($(this).find('input[name*="[nama_pasien]"]').val() || '').trim();
            var tglLahir = ($(this).find('input[name*="[tgllahir_pasien]"]').val() || '').trim();
            var gender = $(this).find('input[name*="[gender_pasien]"]:checked').val();
            var nomorLab = cleanHajiNomorUrut($(this).find('input[name*="[nomor_lab_manual]"]').val());
            var nomorSample = cleanHajiNomorUrut($(this).find('input[name*="[nomor_spesimen_manual]"]').val());
            $(this).find('input[name*="[nomor_lab_manual]"]').val(nomorLab);
            $(this).find('input[name*="[nomor_spesimen_manual]"]').val(nomorSample);

            if (!nama || !tglLahir || !gender) {
              isValid = false;
            }
            if (hajiNumberSettings.isNomorLabManual && !nomorLab) {
              isValid = false;
            }
            if (hajiNumberSettings.isNomorSampleManual && !nomorSample) {
              isValid = false;
            }
          });

          if (!isValid) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Silakan lengkapi semua field yang wajib diisi (termasuk nomor sample dan nomor lab) untuk setiap pasien!",
              icon: "warning"
            });
            return false;
          }
        }
      });
      @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.haji-pasien-form-scripts')
      @endif

      // Form customer validation
      $('#form-customer').on('submit', function(e) {
        var mode = $('input[name="customer_mode"]:checked').val();
        var customerId = $('#customer_search').val();
        var nameCustomer = $('#name_customer').val();

        if (mode === 'search') {
          // Mode: Cari dari Sistem
          if (!customerId) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Silakan pilih customer dari sistem!",
              icon: "warning"
            });
            return false;
          }
          // Clear new customer form fields before submit
          $('#name_customer, #address_customer, #email_customer, #cp_customer, #kecamatan_other').val('');
          $('#kecamatan').val(null).trigger('change');
        } else {
          // Mode: Buat Baru
          if (!nameCustomer) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Nama customer wajib diisi!",
              icon: "warning"
            });
            return false;
          }

          if (!$('#address_customer').val()) {
            e.preventDefault();
            swal({
              title: "Error!",
              text: "Alamat customer wajib diisi!",
              icon: "warning"
            });
            return false;
          }
          // Clear customer_id before submit
          $('#customer_search').val(null).trigger('change');
        }
      });

      // Function untuk download Excel dengan parameter jumlah baris
      @if(isset($haji_id) && $haji_id)
      window.downloadExcelFormat = function() {
        var jumlahBaris = $('#jumlah_baris_excel').val() || 10;
        jumlahBaris = parseInt(jumlahBaris);

        // Validasi jumlah baris
        if (isNaN(jumlahBaris) || jumlahBaris < 1) {
          jumlahBaris = 10;
        }
        if (jumlahBaris > 1000) {
          jumlahBaris = 1000;
        }

        // Build URL dengan parameter rows
        var url = '{{ route("elits-permohonan-uji-klinik-2.download-format-haji", $haji_id ?? null) }}?rows=' + jumlahBaris;

        // Download file
        window.location.href = url;
      };
      @else
      window.downloadExcelFormat = function() {
        alert('Haji ID belum tersedia. Silakan selesaikan step sebelumnya terlebih dahulu.');
      };
      @endif

      // Pastikan form upload Excel tidak terintervensi
      $('#form-upload-excel').on('submit', function(e) {
        e.stopPropagation();
        // Pastikan ini adalah form upload, bukan download
        var fileInput = $('#file-excel')[0];
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
          e.preventDefault();
          alert('Silakan pilih file Excel terlebih dahulu.');
          return false;
        }
        // Pastikan form action benar
        var formAction = $(this).attr('action');
        if (!formAction || formAction.indexOf('import-haji') === -1) {
          e.preventDefault();
          console.error('Form action salah:', formAction);
          alert('Terjadi kesalahan pada form. Silakan refresh halaman.');
          return false;
        }
        // Biarkan form submit normal
        return true;
      });

      // Pastikan button download tidak mengintervensi form upload
      $('#btn-download-excel').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        downloadExcelFormat();
        return false;
      });

      // Pastikan button upload tidak terintervensi
      $('#btn-upload-excel').on('click', function(e) {
        // Jangan prevent default, biarkan form submit normal
        // Hanya pastikan tidak ada konflik dengan download
        e.stopPropagation();
      });
    });
  </script>
@endsection

