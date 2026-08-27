@extends('masterweb::template.admin.layout')
@section('title')
    Pasien Management - Create
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-pasien') }}">Pasien Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form enctype="multipart/form-data" class="forms-sample" id="form"
                action="{{ route('elits-pasien.store') }}" method="POST">
                @csrf

                <!-- Beautiful Patient Information Section -->
                <div class="card border-0 shadow-sm mb-4"
                    style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); overflow: visible !important;">
                    <div class="card-body" style="overflow: visible !important;">
                        <h5 class="font-weight-bold mb-4"
                            style="color: #0b3a5c; border-bottom: 3px solid #0b3a5c; padding-bottom: 10px;">
                            <i class="fa fa-user-circle mr-2"></i>INFORMASI PASIEN
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik_pasien" class="font-weight-bold" style="color: #495057;">
                                        <i class="fa fa-id-card mr-2" style="color: #0b3a5c;"></i>NIK PASIEN
                                        <span style="color: red">*</span>
                                    </label>
                                    <div class="input-group" style="position: relative;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                <i class="fa fa-id-card"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="nik_pasien" id="nik_pasien"
                                            placeholder="Masukkan NIK 16 Digit" value="{{ old('nik_pasien') }}"
                                            maxlength="16"
                                            style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Sesuai Kartu Tanda Penduduk (16 digit)
                                    </small>
                                    <div id="satu_sehat_status" class="mt-2" style="display: none;">
                                        <small class="badge badge-success" id="satu_sehat_badge">
                                            <i class="fa fa-check-circle"></i> Data ditemukan di Satu Sehat
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_pasien" class="font-weight-bold" style="color: #495057;">
                                        <i class="fa fa-user mr-2" style="color: #0b3a5c;"></i>NAMA LENGKAP
                                        <span style="color: red">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                                <i class="fa fa-user"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" name="nama_pasien" id="nama_pasien"
                                            placeholder="Nama Sesuai KTP" value="{{ mb_strtoupper((string) (old('nama_pasien') ?? ''), 'UTF-8') }}"
                                            style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; text-transform: uppercase;">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>Sesuai Kartu Tanda Penduduk
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="no_rekammedis_pasien" class="font-weight-bold" style="color: #495057;">
                                <i class="fa fa-file-text mr-2" style="color: #0b3a5c;"></i>NOMOR REKAM MEDIS
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"
                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                        <i class="fa fa-file-text"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control no_rekammedis_pasien" name="no_rekammedis_pasien"
                                    id="no_rekammedis_pasien" placeholder="Nomor rekam medis"
                                    value="{{ old('no_rekammedis_pasien') }}"
                                    style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Kosongkan untuk auto generate
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="divisi_instansi_pasien">Divisi/Instansi</label>
                            <input type="text" class="form-control divisi_instansi_pasien"
                                name="divisi_instansi_pasien" id="divisi_instansi_pasien" placeholder="Divisi/instansi"
                                value="{{ old('divisi_instansi_pasien') }}">
                        </div>

                        <div class="form-group">
                            <label for="jenis_kelamin" class="font-weight-bold mb-3" style="color: #495057;">
                                <i class="fa fa-venus-mars mr-2" style="color: #0b3a5c;"></i>JENIS KELAMIN
                                <span style="color: red">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-2"
                                        style="cursor: pointer; transition: all 0.3s;"
                                        onclick="$('#gender_pasien_male').prop('checked', true).trigger('change');">
                                        <div class="card-body p-3"
                                            style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                            <div class="form-check d-flex align-items-center">
                                                <input type="radio" class="form-check-input" name="gender_pasien"
                                                    id="gender_pasien_male" value="L" checked
                                                    style="cursor: pointer; width: 20px; height: 20px;">
                                                <label class="form-check-label ml-3 mb-0" for="gender_pasien_male"
                                                    style="cursor: pointer; font-size: 16px; font-weight: 600; color: #1976d2;">
                                                    <i class="fa fa-mars mr-2" style="font-size: 20px;"></i>Laki-laki
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-2"
                                        style="cursor: pointer; transition: all 0.3s;"
                                        onclick="$('#gender_pasien_female').prop('checked', true).trigger('change');">
                                        <div class="card-body p-3"
                                            style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);">
                                            <div class="form-check d-flex align-items-center">
                                                <input type="radio" class="form-check-input" name="gender_pasien"
                                                    id="gender_pasien_female" value="P"
                                                    style="cursor: pointer; width: 20px; height: 20px;">
                                                <label class="form-check-label ml-3 mb-0" for="gender_pasien_female"
                                                    style="cursor: pointer; font-size: 16px; font-weight: 600; color: #c2185b;">
                                                    <i class="fa fa-venus mr-2" style="font-size: 20px;"></i>Perempuan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tmpt_lahir" class="font-weight-bold" style="color: #495057;">
                                <i class="fa fa-map-pin mr-2" style="color: #0b3a5c;"></i>TEMPAT LAHIR
                            </label>
                            <div class="mb-2" style="position: relative; z-index: 200;">
                                <label class="small font-weight-bold text-muted mb-1" for="search_tmpt_lahir_input">
                                    <i class="fa fa-search mr-1"></i> Cari dari master kota/kabupaten atau kecamatan
                                </label>
                                <div style="position: relative;">
                                    <input type="text" class="form-control" id="search_tmpt_lahir_input"
                                        placeholder="Ketik nama kabupaten/kota atau kecamatan... (min 2 karakter)"
                                        autocomplete="off"
                                        style="border: 2px solid #e2e8f0; border-radius: 8px; padding-left: 38px; font-size: 15px; height: 45px;">
                                    <i class="fa fa-search position-absolute"
                                        style="left: 14px; top: 14px; color: #0b3a5c; pointer-events: none;"></i>
                                    <div id="search_tmpt_lahir_results"
                                        style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                                        <div class="card border-0 shadow-lg">
                                            <div class="list-group list-group-flush" id="search_tmpt_lahir_results_list"
                                                style="max-height: 280px; overflow-y: auto;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"
                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                        <i class="fa fa-map-pin"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" name="tmpt_lahir" id="tmpt_lahir"
                                    placeholder="Contoh: Jakarta atau Bandung"
                                    value="{{ old('tmpt_lahir') }}"
                                    style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Opsional — pilih dari master wilayah atau ketik manual
                            </small>
                        </div>

                        <!-- Tanggal Lahir Section - Similar to permohonan uji klinik -->
                        <div class="form-group">
                            <label for="tgllahir_pasien" class="font-weight-bold mb-3" style="color: #495057;">
                                <i class="fa fa-calendar mr-2" style="color: #0b3a5c;"></i>TANGGAL LAHIR
                                <span style="color: red">*</span>
                            </label>

                            <input type="hidden" class="form-control js-date datepicker" name="tgllahir_pasien"
                                id="tgllahir_pasien" placeholder="dd/mm/yyyy">

                            <!-- Toggle Mode Buttons -->
                            <div class="mb-3 d-flex justify-content-center" style="position: relative; z-index: 100;">
                                <div class="btn-group" role="group" style="position: relative; z-index: 100;">
                                    <button type="button" class="btn btn-primary" id="btn_mode_dropdown"
                                        style="border-radius: 8px 0 0 8px; padding: 10px 20px; font-weight: 600;">
                                        <i class="fa fa-list mr-1"></i> Pilih Dropdown
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="btn_mode_manual"
                                        style="border-radius: 0 8px 8px 0; padding: 10px 20px; font-weight: 600;">
                                        <i class="fa fa-keyboard mr-1"></i> Input Manual
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown Mode -->
                            <div id="birth_dropdown_container" class="card border-0 shadow-sm"
                                style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-calendar-day mr-1"></i>Tanggal
                                            </label>
                                            <select class="form-control" id="birth_day" onchange="updateBirthDate()"
                                                style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px;">
                                                <option value="">Pilih</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-calendar-alt mr-1"></i>Bulan
                                            </label>
                                            <select class="form-control" id="birth_month" onchange="updateBirthDate()"
                                                style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px;">
                                                <option value="">Pilih</option>
                                                <option value="01">Januari</option>
                                                <option value="02">Februari</option>
                                                <option value="03">Maret</option>
                                                <option value="04">April</option>
                                                <option value="05">Mei</option>
                                                <option value="06">Juni</option>
                                                <option value="07">Juli</option>
                                                <option value="08">Agustus</option>
                                                <option value="09">September</option>
                                                <option value="10">Oktober</option>
                                                <option value="11">November</option>
                                                <option value="12">Desember</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small font-weight-bold text-muted mb-2">
                                                <i class="fa fa-calendar mr-1"></i>Tahun
                                            </label>
                                            <select class="form-control" id="birth_year" onchange="updateBirthDate()"
                                                style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px;">
                                                <option value="">Pilih</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-2 text-center"
                                        style="background: rgba(255, 255, 255, 0.7); border-radius: 8px;">
                                        <small class="text-muted">Tanggal Lahir:</small>
                                        <div id="selected_birth_date"
                                            style="font-size: 18px; font-weight: bold; color: #e65100;">
                                            -- Belum dipilih --
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Input Mode -->
                            <div id="birth_manual_container" class="card border-0 shadow-sm"
                                style="display: none; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                                <div class="card-body p-3">
                                    <label class="small font-weight-bold mb-2" style="color: #2e7d32;">
                                        <i class="fa fa-keyboard mr-1"></i>Ketik Angka Tanggal Lahir Langsung
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); border: none; color: white;">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" id="birth_manual_input"
                                            placeholder="Ketik: 23021990" maxlength="10" inputmode="numeric"
                                            oninput="formatBirthDate(this)"
                                            style="border: 2px solid #4caf50; border-left: none; font-size: 18px; height: 50px; font-weight: 600; letter-spacing: 2px;">
                                    </div>
                                    <div class="mt-3 p-3 text-center" id="manual_date_preview"
                                        style="background: rgba(255, 255, 255, 0.9); border-radius: 8px; display: none; border: 2px solid #4caf50;">
                                        <small class="text-muted">✓ Tanggal Lahir Valid:</small>
                                        <div id="manual_birth_date_display"
                                            style="font-size: 20px; font-weight: bold; color: #2e7d32; margin-top: 5px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pekerjaan" class="font-weight-bold" style="color: #495057;">
                                <i class="fa fa-briefcase mr-2" style="color: #0b3a5c;"></i>PEKERJAAN
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"
                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                        <i class="fa fa-briefcase"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" name="pekerjaan" id="pekerjaan"
                                    placeholder="Contoh: Pegawai Swasta"
                                    value="{{ old('pekerjaan') }}"
                                    style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Opsional
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="phone_pasien" class="font-weight-bold" style="color: #495057;">
                                <i class="fa fa-phone mr-2" style="color: #0b3a5c;"></i>NO. TELP/HP
                                <span style="color: red">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"
                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                                        <i class="fa fa-phone"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" name="phone_pasien" id="phone_pasien"
                                    placeholder="Contoh: 081234567890" value="{{ old('phone_pasien') }}"
                                    style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
                            </div>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>Nomor telepon/HP yang dapat dihubungi
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Wilayah Section -->
                <div class="form-group">
                    <label class="font-weight-bold mb-3" style="color: #0b3a5c; font-size: 16px;">
                        <i class="fa fa-map-marker mr-2"></i>WILAYAH DOMISILI
                    </label>

                    <!-- Search Wilayah Box -->
                    <div class="mb-3" style="position: relative; z-index: 100;">
                        <div class="card border-0 shadow-sm"
                            style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #0b3a5c !important; overflow: visible !important;">
                            <div class="card-body py-3" style="overflow: visible !important;">
                                <div class="wilayah-quick-search-row">
                                    <div class="flex-grow-1 position-relative" style="z-index: 1000;">
                                        <label class="small font-weight-bold mb-2" style="color: #1976d2;">
                                            Pencarian Cepat Wilayah
                                        </label>
                                        <div class="wilayah-quick-search">
                                            <i class="fa fa-search wilayah-quick-search__icon" aria-hidden="true"></i>
                                            <input type="text" class="form-control form-control-lg wilayah-quick-search__input"
                                                id="search_wilayah_input"
                                                placeholder="Ketik nama desa, kecamatan, atau kabupaten... (min 2 karakter)"
                                                autocomplete="off"
                                                style="border: 2px solid #1976d2; border-radius: 10px; font-size: 15px;">
                                            <div id="search_wilayah_results"
                                                style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                                                <div class="card border-0 shadow-lg">
                                                    <div class="list-group list-group-flush"
                                                        id="search_wilayah_results_list"
                                                        style="max-height: 400px; overflow-y: auto;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3 text-center">
                                        <button type="button" class="btn btn-sm" id="btn_toggle_manual_select"
                                            style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                                            <i class="fa fa-list mr-1"></i> Pilih Manual
                                        </button>
                                        <div class="small text-muted mt-1">atau pilih bertahap</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cascade Dropdown -->
                    <div class="card border-0 shadow-sm mb-3" id="manual_wilayah_selector"
                        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: none;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 font-weight-bold" style="color: #0b3a5c;">
                                    <i class="fa fa-list-ul mr-2"></i>Pilih Wilayah Secara Bertahap
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="btn_hide_manual_select">
                                    <i class="fa fa-times"></i> Tutup
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="provinsi_pasien" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-globe mr-1"></i> Provinsi
                                    </label>
                                    <select class="form-control select-wilayah" id="provinsi_pasien"
                                        name="provinsi_pasien">
                                        <option value="">-- Pilih Provinsi --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kabupaten_pasien" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-building mr-1"></i> Kabupaten/Kota
                                    </label>
                                    <select class="form-control select-wilayah" id="kabupaten_pasien"
                                        name="kabupaten_pasien" disabled>
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kecamatan_pasien" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-map-signs mr-1"></i> Kecamatan
                                    </label>
                                    <select class="form-control select-wilayah" id="kecamatan_pasien"
                                        name="kecamatan_pasien" disabled>
                                        <option value="">-- Pilih Kecamatan --</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="desa_pasien" class="small font-weight-bold text-muted mb-2">
                                        <i class="fa fa-home mr-1"></i> Desa/Kelurahan
                                    </label>
                                    <select class="form-control select-wilayah" id="desa_pasien" name="desa_pasien"
                                        disabled>
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat_pasien" class="font-weight-bold" style="color: #0b3a5c;">
                        <i class="fa fa-map-marker mr-2"></i>DETAIL ALAMAT DOMISILI
                    </label>
                    <textarea class="form-control" name="alamat_pasien" id="alamat_pasien" rows="3"
                        placeholder="Contoh: Jl. Merdeka No. 123, RT 02/RW 05, dekat Masjid Al-Ikhlas"
                        style="border: 2px solid #e2e8f0; border-radius: 8px;">{{ old('alamat_pasien') }}</textarea>
                    <small class="form-text text-muted">
                        <i class="fa fa-info-circle mr-1"></i>Masukkan detail alamat lengkap seperti nama jalan, nomor
                        rumah,
                        RT/RW, dan patokan
                    </small>
                </div>

                <br>
            </form>
            <button type="submit" class="btn btn-primary mr-2 btn-simpan">
                <i class="fa fa-save mr-2"></i>Simpan
            </button>
            <button type="button" onclick="document.location='{{ url('/elits-pasien') }}'" class="btn btn-light">
                <i class="fa fa-arrow-left mr-2"></i>Kembali
            </button>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap-birthday.js') }}"></script>

    <script>
        // Global functions for birth date
        window.updateBirthDate = function() {
            const day = $('#birth_day').val();
            const month = $('#birth_month').val();
            const year = $('#birth_year').val();

            if (day && month && year) {
                const formattedDate = `${day}/${month}/${year}`;
                $('#tgllahir_pasien').val(formattedDate);

                const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ];
                const monthName = monthNames[parseInt(month)];
                $('#selected_birth_date').html(
                    `<i class="fa fa-check-circle mr-2" style="color: #4caf50;"></i>${day} ${monthName} ${year}`
                );
            }
        };

        window.formatBirthDate = function(input) {
            let value = input.value.replace(/[^\d]/g, '');
            let formatted = '';

            if (value.length > 0) formatted = value.substring(0, 2);
            if (value.length >= 2) formatted += '/';
            if (value.length >= 3) formatted += value.substring(2, 4);
            if (value.length >= 4) formatted += '/';
            if (value.length >= 5) formatted += value.substring(4, 8);

            input.value = formatted;

            if (formatted.length === 10) {
                const parts = formatted.split('/');
                const day = parseInt(parts[0]);
                const month = parseInt(parts[1]);
                const year = parseInt(parts[2]);
                const currentYear = new Date().getFullYear();

                if (day >= 1 && day <= 31 && month >= 1 && month <= 12 && year >= 1900 && year <= currentYear) {
                    const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                        'September', 'Oktober', 'November', 'Desember'
                    ];
                    $('#manual_birth_date_display').html(`${day} ${monthNames[month]} ${year}`);
                    $('#manual_date_preview').show();
                    $('#tgllahir_pasien').val(formatted);
                }
            }
        };

        $(document).ready(function() {
            // Populate days and years
            for (let i = 1; i <= 31; i++) {
                const day = i.toString().padStart(2, '0');
                $('#birth_day').append(`<option value="${day}">${day}</option>`);
            }

            const currentYear = new Date().getFullYear();
            for (let i = currentYear; i >= currentYear - 100; i--) {
                $('#birth_year').append(`<option value="${i}">${i}</option>`);
            }

            // Toggle between dropdown and manual mode
            $('#btn_mode_dropdown').on('click', function() {
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                $('#btn_mode_manual').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#birth_dropdown_container').show();
                $('#birth_manual_container').hide();
                $('#birth_manual_input').val('');
                $('#manual_date_preview').hide();
            });

            $('#btn_mode_manual').on('click', function() {
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                $('#btn_mode_dropdown').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#birth_manual_container').show();
                $('#birth_dropdown_container').hide();
                $('#birth_day, #birth_month, #birth_year').val('');
                $('#selected_birth_date').html('-- Belum dipilih --');
                setTimeout(() => $('#birth_manual_input').focus(), 100);
            });

            // NIK input validation
            $('#nik_pasien').on('input', function() {
                this.value = this.value.replace(/[^\d]/g, '');
            });

            // Name input - auto capitalize
            $('#nama_pasien').on('input', function() {
                this.value = this.value.toUpperCase();
            });

            // Phone number validation
            $('#phone_pasien').on('input', function() {
                this.value = this.value.replace(/[^\d]/g, '');
            });

            // Load Provinsi
            $.ajax({
                url: "{{ route('get-provinsi') }}",
                type: 'GET',
                success: function(response) {
                    response.forEach(function(item) {
                        $('#provinsi_pasien').append(
                            `<option value="${item.id_wilayah}" data-kode="${item.wilayah_kode}">${item.wilayah}</option>`
                        );
                    });
                }
            });

            // Cascade dropdowns
            $('#provinsi_pasien').on('change', function() {
                const provinsiId = $(this).val();
                $('#kabupaten_pasien, #kecamatan_pasien, #desa_pasien').val('').prop('disabled', true);
                if (provinsiId) {
                    $.ajax({
                        url: "{{ route('get-kabupaten') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            provinsi_kode: $(this).find('option:selected').data('kode')
                        },
                        success: function(response) {
                            $('#kabupaten_pasien').empty().append(
                                '<option value="">-- Pilih Kabupaten/Kota --</option>');
                            response.forEach(function(item) {
                                $('#kabupaten_pasien').append(
                                    `<option value="${item.id_wilayah}" data-kode="${item.wilayah_kode}">${item.wilayah}</option>`
                                );
                            });
                            $('#kabupaten_pasien').prop('disabled', false);
                        }
                    });
                }
            });

            $('#kabupaten_pasien').on('change', function() {
                const kabupatenId = $(this).val();
                $('#kecamatan_pasien, #desa_pasien').val('').prop('disabled', true);
                if (kabupatenId) {
                    $.ajax({
                        url: "{{ route('get-kecamatan') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            kabupaten_kode: $(this).find('option:selected').data('kode')
                        },
                        success: function(response) {
                            $('#kecamatan_pasien').empty().append(
                                '<option value="">-- Pilih Kecamatan --</option>');
                            response.forEach(function(item) {
                                $('#kecamatan_pasien').append(
                                    `<option value="${item.id_wilayah}" data-kode="${item.wilayah_kode}">${item.wilayah}</option>`
                                );
                            });
                            $('#kecamatan_pasien').prop('disabled', false);
                        }
                    });
                }
            });

            $('#kecamatan_pasien').on('change', function() {
                const kecamatanId = $(this).val();
                $('#desa_pasien').val('').prop('disabled', true);
                if (kecamatanId) {
                    $.ajax({
                        url: "{{ route('get-desa') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            kecamatan_kode: $(this).find('option:selected').data('kode')
                        },
                        success: function(response) {
                            $('#desa_pasien').empty().append(
                                '<option value="">-- Pilih Desa/Kelurahan --</option>');
                            response.forEach(function(item) {
                                $('#desa_pasien').append(
                                    `<option value="${item.id_wilayah}">${item.wilayah}</option>`
                                );
                            });
                            $('#desa_pasien').prop('disabled', false);
                        }
                    });
                }
            });

            // Search tempat lahir (kabupaten/kota & kecamatan)
            let searchTmptLahirTimer;
            const tmptLahirTypeLabel = { KAB: 'Kabupaten/Kota', KEC: 'Kecamatan' };

            $('#search_tmpt_lahir_input').on('input', function() {
                const keyword = $(this).val().trim();
                clearTimeout(searchTmptLahirTimer);

                if (keyword.length < 2) {
                    $('#search_tmpt_lahir_results').hide();
                    return;
                }

                searchTmptLahirTimer = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('search-wilayah') }}",
                        type: 'GET',
                        data: {
                            keyword: keyword,
                            limit: 15,
                            types: 'KAB,KEC'
                        },
                        success: function(response) {
                            const $resultsList = $('#search_tmpt_lahir_results_list');
                            $resultsList.empty();

                            if (!response.length) {
                                $resultsList.html(
                                    '<div class="p-3 text-center text-muted">Wilayah tidak ditemukan</div>'
                                );
                            } else {
                                response.forEach(function(item) {
                                    const tipeLabel = tmptLahirTypeLabel[item.tipe] || item.tipe;
                                    $resultsList.append(`
                                        <a href="javascript:void(0)" class="list-group-item list-group-item-action tmpt-lahir-result-item"
                                           data-nama="${item.nama}">
                                            <strong>${item.nama}</strong> <span class="text-muted">(${tipeLabel})</span>
                                            <br><small class="text-muted">${item.full_path || '-'}</small>
                                        </a>
                                    `);
                                });
                            }
                            $('#search_tmpt_lahir_results').show();
                        }
                    });
                }, 400);
            });

            $(document).on('click', '.tmpt-lahir-result-item', function() {
                const nama = $(this).data('nama');
                $('#tmpt_lahir').val(nama);
                $('#search_tmpt_lahir_input').val('');
                $('#search_tmpt_lahir_results').hide();
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#search_tmpt_lahir_input, #search_tmpt_lahir_results').length) {
                    $('#search_tmpt_lahir_results').hide();
                }
            });

            // Search wilayah domisili
            let searchWilayahTimer;
            $('#search_wilayah_input').on('input', function() {
                const keyword = $(this).val().trim();
                clearTimeout(searchWilayahTimer);

                if (keyword.length < 2) {
                    $('#search_wilayah_results').hide();
                    return;
                }

                searchWilayahTimer = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('search-wilayah') }}",
                        type: 'GET',
                        data: {
                            keyword: keyword,
                            limit: 10
                        },
                        success: function(response) {
                            const $resultsList = $('#search_wilayah_results_list');
                            $resultsList.empty();

                            if (response.length === 0) {
                                $resultsList.html(
                                    '<div class="p-3 text-center">Wilayah tidak ditemukan</div>'
                                    );
                            } else {
                                response.forEach(function(item) {
                                    $resultsList.append(`
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action wilayah-result-item"
                      data-id="${item.id}" data-tipe="${item.tipe}">
                      <strong>${item.nama}</strong> (${item.tipe})
                      <br><small class="text-muted">${item.full_path || '-'}</small>
                    </a>
                  `);
                                });
                            }
                            $('#search_wilayah_results').show();
                        }
                    });
                }, 500);
            });

            // Select wilayah from search
            $(document).on('click', '.wilayah-result-item', function() {
                const wilayahId = $(this).data('id');
                const wilayahTipe = $(this).data('tipe');
                $('#search_wilayah_results').hide();
                $('#search_wilayah_input').val('');

                $.ajax({
                    url: "{{ route('get-wilayah-detail') }}",
                    type: 'GET',
                    data: {
                        wilayah_id: wilayahId
                    },
                    success: function(response) {
                        const parents = response.parents;
                        if (parents.provinsi_id) {
                            $('#provinsi_pasien').val(parents.provinsi_id).trigger('change');
                            setTimeout(function() {
                                if (parents.kabupaten_id) {
                                    $('#kabupaten_pasien').val(parents.kabupaten_id)
                                        .trigger('change');
                                    setTimeout(function() {
                                        if (parents.kecamatan_id) {
                                            $('#kecamatan_pasien').val(parents
                                                .kecamatan_id).trigger(
                                                'change');
                                            setTimeout(function() {
                                                if (parents.desa_id) {
                                                    $('#desa_pasien')
                                                        .val(parents
                                                            .desa_id);
                                                }
                                            }, 500);
                                        }
                                    }, 500);
                                }
                            }, 500);
                        }
                    }
                });
            });

            // Toggle manual selector
            $('#btn_toggle_manual_select').on('click', function() {
                $('#manual_wilayah_selector').slideToggle(300);
            });

            $('#btn_hide_manual_select').on('click', function() {
                $('#manual_wilayah_selector').slideUp(300);
            });

            // Form submit
            $('.btn-simpan').on('click', function() {
                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            }).then(function() {
                                document.location = '/elits-pasien';
                            });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    pesan += value + '. <br>';
                                    wrapper.innerHTML = pesan;
                                });

                                swal({
                                    title: "Error!",
                                    content: wrapper,
                                    icon: "warning"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "warning"
                                });
                            }
                        }
                    },
                    error: function() {
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });
        });
    </script>
@endsection
