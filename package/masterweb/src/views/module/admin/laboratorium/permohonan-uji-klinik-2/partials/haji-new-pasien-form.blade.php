{{-- Template row pasien baru — placeholder __IDX__ dan __NO_RM__ diganti via JS --}}
<div class="pasien-row card mb-3" data-index="__IDX__">
  <div class="card-header bg-light d-flex justify-content-between align-items-center">
    <strong>Pasien #__IDX__</strong>
    <button type="button" class="btn btn-danger btn-sm btn-remove-pasien" data-index="__IDX__">
      <i class="fa fa-trash"></i> Hapus
    </button>
  </div>
  <div class="card-body">
    <div class="card border-0 shadow-sm mb-0"
      style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); overflow: visible !important;">
      <div class="card-body" style="overflow: visible !important;">
        <h5 class="font-weight-bold mb-4"
          style="color: #0b3a5c; border-bottom: 3px solid #0b3a5c; padding-bottom: 10px;">
          <i class="fa fa-user-circle mr-2"></i>INFORMASI PASIEN
        </h5>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold" style="color: #495057;">
                <i class="fa fa-id-card mr-2" style="color: #0b3a5c;"></i>NIK PASIEN
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                    <i class="fa fa-id-card"></i>
                  </span>
                </div>
                <input type="text" class="form-control js-nik-pasien" name="pasien[__IDX__][nik_pasien]"
                  placeholder="Masukkan NIK 16 Digit" maxlength="16"
                  style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Sesuai Kartu Tanda Penduduk (16 digit)
              </small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold" style="color: #495057;">
                <i class="fa fa-user mr-2" style="color: #0b3a5c;"></i>NAMA LENGKAP <span style="color: red">*</span>
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                    <i class="fa fa-user"></i>
                  </span>
                </div>
                <input type="text" class="form-control js-nama-pasien" name="pasien[__IDX__][nama_pasien]" required
                  placeholder="Nama Sesuai KTP"
                  style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; text-transform: uppercase;">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Sesuai Kartu Tanda Penduduk
              </small>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold" style="color: #495057;">
                <i class="fa fa-map-marker-alt mr-2" style="color: #0b3a5c;"></i>TEMPAT LAHIR
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                    <i class="fa fa-map-marker-alt"></i>
                  </span>
                </div>
                <input type="text" class="form-control" name="pasien[__IDX__][tmpt_lahir]" placeholder="Contoh: Jakarta"
                  style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; text-transform: uppercase;">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Kota/Kabupaten tempat lahir
              </small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold" style="color: #495057;">
                <i class="fa fa-briefcase mr-2" style="color: #0b3a5c;"></i>PEKERJAAN
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                    <i class="fa fa-briefcase"></i>
                  </span>
                </div>
                <input type="text" class="form-control" name="pasien[__IDX__][pekerjaan]" placeholder="Contoh: Pegawai Swasta"
                  style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Pekerjaan saat ini
              </small>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="font-weight-bold" style="color: #495057;">
            <i class="fa fa-file-text mr-2" style="color: #0b3a5c;"></i>NOMOR REKAM MEDIS <span style="color: red">*</span>
          </label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"
                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                <i class="fa fa-file-text"></i>
              </span>
            </div>
            <input type="text" class="form-control js-no-rm" name="pasien[__IDX__][no_rekammedis_pasien]" readonly
              value="__NO_RM__"
              style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; background-color: #f8f9fa; font-weight: bold; color: #0b3a5c;">
          </div>
          <small class="form-text text-muted">
            <i class="fa fa-lock mr-1"></i>Nomor otomatis tergenerate
          </small>
        </div>

        @php
          $hajiNs = $klinikNumberSettings ?? \Smt\Masterweb\Models\KlinikNumberSettings::getSettings();
        @endphp
        @if(!empty($hajiNs) && ($hajiNs->is_nomor_lab_manual || $hajiNs->is_nomor_spesimen_manual))
        <div class="row">
          @if($hajiNs->is_nomor_lab_manual)
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold" style="color: #495057;">
                <i class="fa fa-flask mr-2" style="color: #0b3a5c;"></i>NOMOR LAB <span style="color: red">*</span>
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                    <i class="fa fa-flask"></i>
                  </span>
                </div>
                <input type="text" class="form-control js-nomor-lab-manual" name="pasien[__IDX__][nomor_lab_manual]"
                  placeholder="Masukkan nomor urut lab" inputmode="numeric" required
                  style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; font-weight: 600; color: #0b3a5c;">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Wajib diisi (hanya angka)
              </small>
            </div>
          </div>
          @endif
          @if($hajiNs->is_nomor_spesimen_manual)
          <div class="col-md-6">
            <div class="form-group">
              <label class="font-weight-bold" style="color: #495057;">
                <i class="fa fa-vial mr-2" style="color: #0b3a5c;"></i>NOMOR SAMPLE <span style="color: red">*</span>
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"
                    style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                    <i class="fa fa-vial"></i>
                  </span>
                </div>
                <input type="text" class="form-control js-nomor-sample-manual" name="pasien[__IDX__][nomor_spesimen_manual]"
                  placeholder="Masukkan nomor urut sample" inputmode="numeric" required
                  style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px; font-weight: 600; color: #0b3a5c;">
              </div>
              <small class="form-text text-muted">
                <i class="fa fa-info-circle mr-1"></i>Wajib diisi (hanya angka)
              </small>
            </div>
          </div>
          @endif
        </div>
        @endif

        <div class="form-group">
          <label class="font-weight-bold mb-3" style="color: #495057;">
            <i class="fa fa-venus-mars mr-2" style="color: #0b3a5c;"></i>JENIS KELAMIN <span style="color: red">*</span>
          </label>
          <div class="row">
            <div class="col-md-6">
              <div class="card border-0 shadow-sm mb-2 js-gender-card" style="cursor: pointer; transition: all 0.3s;"
                data-gender="L">
                <div class="card-body p-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                  <div class="form-check d-flex align-items-center">
                    <input type="radio" class="form-check-input js-gender-radio" name="pasien[__IDX__][gender_pasien]"
                      value="L" checked style="cursor: pointer; width: 20px; height: 20px;">
                    <label class="form-check-label ml-3 mb-0" style="cursor: pointer; font-size: 16px; font-weight: 600; color: #1976d2;">
                      <i class="fa fa-mars mr-2" style="font-size: 20px;"></i>Laki-laki
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card border-0 shadow-sm mb-2 js-gender-card" style="cursor: pointer; transition: all 0.3s;"
                data-gender="P">
                <div class="card-body p-3" style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%);">
                  <div class="form-check d-flex align-items-center">
                    <input type="radio" class="form-check-input js-gender-radio" name="pasien[__IDX__][gender_pasien]"
                      value="P" style="cursor: pointer; width: 20px; height: 20px;">
                    <label class="form-check-label ml-3 mb-0" style="cursor: pointer; font-size: 16px; font-weight: 600; color: #c2185b;">
                      <i class="fa fa-venus mr-2" style="font-size: 20px;"></i>Perempuan
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="font-weight-bold mb-3" style="color: #495057;">
            <i class="fa fa-calendar mr-2" style="color: #0b3a5c;"></i>TANGGAL LAHIR <span style="color: red">*</span>
          </label>

          <input type="hidden" class="js-tgllahir-hidden" name="pasien[__IDX__][tgllahir_pasien]">

          <div class="mb-3 d-flex justify-content-center">
            <div class="btn-group js-birth-mode-group" role="group">
              <button type="button" class="btn btn-primary js-btn-birth-dropdown"
                style="border-radius: 8px 0 0 8px; padding: 10px 20px; font-weight: 600;">
                <i class="fa fa-list mr-1"></i> Pilih Dropdown
              </button>
              <button type="button" class="btn btn-outline-primary js-btn-birth-manual"
                style="border-radius: 0 8px 8px 0; padding: 10px 20px; font-weight: 600;">
                <i class="fa fa-keyboard mr-1"></i> Input Manual
              </button>
            </div>
          </div>
          <div class="text-center mb-3">
            <small class="text-muted js-birth-mode-info">
              <i class="fa fa-info-circle mr-1"></i>Pilih mode input yang Anda inginkan (Dropdown atau Manual)
            </small>
          </div>

          <div class="js-birth-dropdown-container card border-0 shadow-sm"
            style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-md-4">
                  <label class="small font-weight-bold text-muted mb-2">
                    <i class="fa fa-calendar-day mr-1"></i>Tanggal
                  </label>
                  <select class="form-control js-birth-day"
                    style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px; font-weight: 600;">
                    <option value="">Pilih</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="small font-weight-bold text-muted mb-2">
                    <i class="fa fa-calendar-alt mr-1"></i>Bulan
                  </label>
                  <select class="form-control js-birth-month"
                    style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px; font-weight: 600;">
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
                  <select class="form-control js-birth-year"
                    style="border: 2px solid #fb8c00; border-radius: 8px; font-size: 15px; height: 45px; font-weight: 600;">
                    <option value="">Pilih</option>
                  </select>
                </div>
              </div>
              <div class="mt-3 p-2 text-center" style="background: rgba(255, 255, 255, 0.7); border-radius: 8px;">
                <small class="text-muted">Tanggal Lahir:</small>
                <div class="js-selected-birth-date" style="font-size: 18px; font-weight: bold; color: #e65100;">
                  -- Belum dipilih --
                </div>
              </div>
            </div>
          </div>

          <div class="js-birth-manual-container card border-0 shadow-sm"
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
                <input type="text" class="form-control js-birth-manual-input" placeholder="Ketik: 23021990"
                  maxlength="10" inputmode="numeric"
                  style="border: 2px solid #4caf50; border-left: none; font-size: 18px; height: 50px; font-weight: 600; letter-spacing: 2px;">
              </div>
              <small class="text-success d-block mt-2" style="font-weight: 600;">
                <i class="fa fa-magic mr-1"></i><strong>Tips:</strong> Ketik angka saja, slash otomatis muncul!
              </small>
            </div>
          </div>

          <div class="card border-0 shadow-sm mt-3 js-age-display-container"
            style="display: none; background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);">
            <div class="card-body p-3">
              <div class="text-center mb-2">
                <i class="fa fa-birthday-cake mr-2" style="color: #0277bd; font-size: 20px;"></i>
                <strong style="color: #0277bd; font-size: 16px;">UMUR PASIEN</strong>
              </div>
              <div class="row text-center">
                <div class="col-4">
                  <div class="p-2" style="background: white; border-radius: 8px;">
                    <div class="js-age-years" style="font-size: 24px; font-weight: bold; color: #0277bd;">0</div>
                    <small class="text-muted">Tahun</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-2" style="background: white; border-radius: 8px;">
                    <div class="js-age-months" style="font-size: 24px; font-weight: bold; color: #0277bd;">0</div>
                    <small class="text-muted">Bulan</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-2" style="background: white; border-radius: 8px;">
                    <div class="js-age-days" style="font-size: 24px; font-weight: bold; color: #0277bd;">0</div>
                    <small class="text-muted">Hari</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="font-weight-bold" style="color: #495057;">
            <i class="fa fa-phone mr-2" style="color: #0b3a5c;"></i>NO. TELP/HP
          </label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"
                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); border: none; color: white;">
                <i class="fa fa-phone"></i>
              </span>
            </div>
            <input type="text" class="form-control js-phone-pasien" name="pasien[__IDX__][phone_pasien]"
              placeholder="Contoh: 081234567890"
              style="border: 2px solid #e2e8f0; border-left: none; font-size: 15px; height: 45px;">
          </div>
          <small class="form-text text-muted">
            <i class="fa fa-info-circle mr-1"></i>Nomor telepon/HP yang dapat dihubungi
          </small>
        </div>
      </div>
    </div>

    <div class="form-group mt-3">
      <label class="font-weight-bold mb-3" style="color: #0b3a5c; font-size: 16px;">
        <i class="fa fa-map-marker mr-2"></i>WILAYAH DOMISILI
      </label>

      <div class="mb-3" style="position: relative; z-index: 100;">
        <div class="card border-0 shadow-sm"
          style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #0b3a5c !important;">
          <div class="card-body py-3">
            <label class="small font-weight-bold mb-2" style="color: #1976d2;">
              <i class="fa fa-search mr-1"></i> Pencarian Cepat Wilayah
            </label>
            <div style="position: relative;">
              <input type="text" class="form-control form-control-lg js-search-wilayah"
                placeholder="Ketik nama desa, kecamatan, atau kabupaten... (min 2 karakter)" autocomplete="off"
                style="border: 2px solid #1976d2; border-radius: 10px; font-size: 15px;">
              <div class="js-search-wilayah-results"
                style="position: absolute; width: 100%; z-index: 99999; display: none; top: 100%; left: 0; margin-top: 4px;">
                <div class="card border-0 shadow-lg">
                  <div class="list-group list-group-flush js-search-wilayah-results-list"
                    style="max-height: 400px; overflow-y: auto;"></div>
                </div>
              </div>
            </div>
            <div class="mt-2">
              <button type="button" class="btn btn-sm js-btn-toggle-manual-wilayah"
                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                <i class="fa fa-list mr-1"></i> Pilih Manual
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3 js-manual-wilayah-selector"
        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); display: none;">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 font-weight-bold" style="color: #0b3a5c;">
              <i class="fa fa-list-ul mr-2"></i>Pilih Wilayah Secara Bertahap
            </h6>
            <button type="button" class="btn btn-sm btn-outline-secondary js-btn-hide-manual-wilayah">
              <i class="fa fa-times"></i> Tutup
            </button>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-muted mb-2">
                <i class="fa fa-globe mr-1"></i> Provinsi
              </label>
              <select class="form-control select-wilayah js-provinsi-pasien" name="pasien[__IDX__][provinsi_pasien]">
                <option value="">-- Pilih Provinsi --</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-muted mb-2">
                <i class="fa fa-building mr-1"></i> Kabupaten/Kota
              </label>
              <select class="form-control select-wilayah js-kabupaten-pasien" name="pasien[__IDX__][kabupaten_pasien]" disabled>
                <option value="">-- Pilih Kabupaten/Kota --</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-muted mb-2">
                <i class="fa fa-map-signs mr-1"></i> Kecamatan
              </label>
              <select class="form-control select-wilayah js-kecamatan-pasien" name="pasien[__IDX__][kecamatan_pasien]" disabled>
                <option value="">-- Pilih Kecamatan --</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="small font-weight-bold text-muted mb-2">
                <i class="fa fa-home mr-1"></i> Desa/Kelurahan
              </label>
              <select class="form-control select-wilayah js-desa-pasien" name="pasien[__IDX__][desa_pasien]" disabled>
                <option value="">-- Pilih Desa/Kelurahan --</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="font-weight-bold" style="color: #0b3a5c;">
        <i class="fa fa-map-marker mr-2"></i>ALAMAT
      </label>
      <textarea class="form-control js-alamat-pasien" name="pasien[__IDX__][alamat_pasien]" rows="3"
        placeholder="Alamat terisi otomatis sesuai wilayah yang dipilih (desa/kecamatan/kabupaten)"
        style="border: 2px solid #e2e8f0; border-radius: 8px; resize: vertical;"></textarea>
      <small class="form-text text-muted">
        <i class="fa fa-info-circle mr-1"></i>Alamat mengikuti wilayah domisili yang dipilih, hingga tingkat desa/kelurahan.
      </small>
    </div>
  </div>
</div>
