    {{-- ============================================================
         MODAL TAMBAH / EDIT PAKET (popup, tanpa iframe)
         ============================================================ --}}
    <div class="modal fade" id="modal-paket" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header py-2"
                     style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h5 class="modal-title mb-0" id="modal-paket-title">
                        <i class="fa fa-cube mr-2"></i>Tambah Paket
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-paket-id">
                    <input type="hidden" id="modal-paket-sample-type-id">

                    {{-- Loading --}}
                    <div id="modal-paket-loading" class="text-center py-4" style="display:none;">
                        <i class="fa fa-spinner fa-spin fa-2x text-success"></i>
                        <p class="mt-2 text-muted small">Memuat data...</p>
                    </div>

                    {{-- Form --}}
                    <div id="modal-paket-form">
                        {{-- Alert --}}
                        <div id="modal-paket-alert" class="alert py-2" style="display:none; font-size:13px;"></div>

                        {{-- Nama Paket --}}
                        <div class="form-group">
                            <label><i class="fa fa-tag mr-1"></i>Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal-paket-name"
                                   placeholder="Contoh: Kimia Air Lengkap">
                        </div>

                        {{-- Parameter --}}
                        <div class="form-group">
                            <label><i class="fa fa-microscope mr-1"></i>Parameter Pengujian <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Centang parameter yang akan masuk dalam paket ini.</p>

                            {{-- Selected preview --}}
                            <div id="modal-paket-selected-preview"
                                 style="min-height:38px; border:1px solid #e2e8f0; border-radius:6px; padding:6px 10px; margin-bottom:8px; background:#f8f9fa; font-size:12px;">
                                <span class="text-muted" id="modal-paket-no-param-msg">Belum ada parameter dipilih</span>
                            </div>

                            {{-- Search --}}
                            <input type="text" class="form-control mb-2" id="modal-paket-method-search"
                                   placeholder="🔍 Cari parameter..."
                                   oninput="paketMethodFilter(this.value)"
                                   onkeyup="paketMethodFilter(this.value)">

                            {{-- List --}}
                            <div id="modal-paket-method-list"
                                 style="max-height:240px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; padding:4px;">
                                <p class="text-muted text-center p-3 small">Buka tab ini untuk memuat daftar parameter.</p>
                            </div>
                        </div>

                        {{-- Harga --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:13px;">Harga Bahan (Rp)</label>
                                    <input type="number" class="form-control modal-paket-price-input"
                                           id="modal-paket-bahan" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:13px;">Harga Sarana (Rp)</label>
                                    <input type="number" class="form-control modal-paket-price-input"
                                           id="modal-paket-sarana" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:13px;">Harga Jasa (Rp)</label>
                                    <input type="number" class="form-control modal-paket-price-input"
                                           id="modal-paket-jasa" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success py-2 mb-0" style="font-size:13px;">
                            <i class="fa fa-calculator mr-1"></i>
                            Total: <strong id="modal-paket-total-display">Rp 0</strong>
                            <input type="hidden" id="modal-paket-total" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-modal-paket-save">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    /* Filter method list di modal paket */
    function paketMethodFilter(val) {
        var q    = (val || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#modal-paket-method-list .paket-method-item');
        for (var i = 0; i < rows.length; i++) {
            var text = rows[i].textContent.toLowerCase();
            rows[i].style.display = (q === '' || text.indexOf(q) !== -1) ? '' : 'none';
        }
    }
    </script>

    {{-- ============================================================
         MODAL EDIT METHOD/PARAMETER (popup, tanpa iframe)
         ============================================================ --}}
    <div class="modal fade" id="modal-edit-param-method" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document" style="max-width: min(95vw, 1100px);">
            <div class="modal-content">
                <div class="modal-header py-2"
                     style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                    <h5 class="modal-title mb-0" id="mepm-title">
                        <i class="fa fa-pencil-alt mr-2"></i>Edit Parameter
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 78vh; overflow-y: auto;">
                    <input type="hidden" id="mepm-method-id">

                    {{-- Loading --}}
                    <div id="mepm-loading" class="text-center py-5">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Memuat data parameter...</p>
                    </div>

                    {{-- Alert --}}
                    <div id="mepm-alert" class="alert" style="display:none;"></div>

                    {{-- Form body --}}
                    <div id="mepm-body-wrap" style="display:none;">
                        <div class="row">
                            {{-- Kolom kiri --}}
                            <div class="col-md-7">

                                <div class="form-group">
                                    <label>Nama Parameter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mepm-params-method"
                                           placeholder="Nama parameter" required>
                                </div>

                                <div class="form-group">
                                    <label>Metode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mepm-name-method"
                                           placeholder="Metode" required>
                                </div>

                                <div class="form-group">
                                    <label>Apakah bagian PDAM?</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check mr-3">
                                            <input class="form-check-input" type="radio" name="mepm_id_pdam_method"
                                                   id="mepm-pdam-ya" value="1">
                                            <label class="form-check-label" for="mepm-pdam-ya">Ya</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="mepm_id_pdam_method"
                                                   id="mepm-pdam-tidak" value="0">
                                            <label class="form-check-label" for="mepm-pdam-tidak">Tidak</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Berhubungan dengan Kesehatan</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_berhubungan_kesehatan" id="mepm-kes-ya" value="1">
                                        <label class="form-check-label" for="mepm-kes-ya">Berhubungan dengan Kesehatan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_berhubungan_kesehatan" id="mepm-kes-tidak" value="0">
                                        <label class="form-check-label" for="mepm-kes-tidak">Tidak Berhubungan dengan Kesehatan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_berhubungan_kesehatan" id="mepm-kes-mikro" value="">
                                        <label class="form-check-label" for="mepm-kes-mikro">Mikrobiologi</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Jenis Parameter</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-org" value="kimia organik">
                                        <label class="form-check-label" for="mepm-jenis-org">Kimia an organik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-kimiawi" value="kimiawi">
                                        <label class="form-check-label" for="mepm-jenis-kimiawi">Kimiawi</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-fisika" value="fisika">
                                        <label class="form-check-label" for="mepm-jenis-fisika">Parameter Fisik</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_jenis_parameter_kimia" id="mepm-jenis-mikro" value="">
                                        <label class="form-check-label" for="mepm-jenis-mikro">Mikrobiologi</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Alat dan Reagen</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_is_ready" id="mepm-ready-ya" value="1">
                                        <label class="form-check-label" for="mepm-ready-ya">Tersedia</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="mepm_is_ready" id="mepm-ready-tidak" value="0">
                                        <label class="form-check-label" for="mepm-ready-tidak">Belum Tersedia</label>
                                    </div>
                                </div>

                                {{-- Opsi Hasil --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fa fa-check-square mr-1"></i>Opsi Hasil (Opsional)</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="mepm-is-option" value="1">
                                            <label class="form-check-label" for="mepm-is-option">
                                                <strong>Hasil Opsional</strong> – Pakai opsi pilihan (contoh: Positif/Negatif)
                                            </label>
                                        </div>
                                        <div id="mepm-option-wrap" style="display:none;">
                                            <div id="mepm-option-rows"></div>
                                            <input type="hidden" id="mepm-option-hidden">
                                            <small class="text-muted">
                                                <i class="fa fa-info-circle"></i>
                                                Klik <span class="badge badge-success"><i class="fa fa-plus"></i></span> untuk menambah opsi
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Harga --}}
                                <div class="form-group">
                                    <label>Harga Bahan</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-bahan"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Sarana</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-sarana"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Jasa</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-jasa"
                                               min="0" placeholder="0">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Total</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp.</span>
                                        </div>
                                        <input type="number" class="form-control" id="mepm-price-total"
                                               min="0" placeholder="0" readonly>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom kanan --}}
                            <div class="col-md-5">

                                {{-- Laboratorium --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0"><i class="fa fa-flask mr-1"></i>Laboratorium</h6>
                                    </div>
                                    <div class="card-body py-2" style="max-height:160px; overflow-y:auto;">
                                        <div id="mepm-lab-list">
                                            @foreach ($data_methods as $lm)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           value="{{ $lm->id_lab }}"
                                                           id="mepm-lab-{{ $lm->id_lab }}">
                                                    <label class="form-check-label" for="mepm-lab-{{ $lm->id_lab }}">
                                                        {{ $lm->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Harga per Jenis Sampel --}}
                                <div class="card border-info">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 text-info">
                                            <i class="fa fa-tags mr-1"></i>Harga per Jenis Sampel
                                        </h6>
                                        <small class="text-muted">
                                            Kosong = pakai Harga Total. Untuk permohonan Kesmas/non-klinik.
                                        </small>
                                    </div>
                                    <div class="card-body p-0">
                                        {{-- Filter bar: muncul saat ada konteks jenis sampel --}}
                                        <div id="mepm-stp-filter-bar"
                                             class="d-none align-items-center justify-content-between px-3 py-2"
                                             style="background:#e8f5e9; border-bottom:1px solid #c3e6cb;">
                                            <div style="font-size:12px; color:#155724;">
                                                <i class="fa fa-filter mr-1"></i>
                                                Menampilkan harga untuk:
                                                <strong id="mepm-stp-filter-label">—</strong>
                                            </div>
                                            <button type="button" id="mepm-stp-toggle-all"
                                                    class="btn btn-link btn-sm p-0"
                                                    style="font-size:12px; color:#0056b3;">
                                                Tampilkan semua jenis
                                            </button>
                                        </div>
                                        <div class="table-responsive" style="max-height:300px; overflow-y:auto;">
                                            <table class="table table-sm table-bordered mb-0" id="mepm-stp-table">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width:60%">Jenis Sampel</th>
                                                        <th>Harga (Rp)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sampletypes as $st)
                                                        <tr data-st-id="{{ $st->id_sample_type }}">
                                                            <td><small>{{ $st->name_sample_type }}</small></td>
                                                            <td>
                                                                <input type="number" min="0" step="1"
                                                                       class="form-control form-control-sm"
                                                                       placeholder="— default —">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /mepm-body-wrap --}}
                </div>{{-- /modal-body --}}
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-mepm-save" style="display:none;">
                        <i class="fa fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL TAMBAH PARAMETER (2-Step)
         ============================================================ --}}
    <div class="modal fade" id="modal-tambah-param" tabindex="-1" role="dialog" aria-labelledby="modal-tambah-param-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title" id="modal-tambah-param-title">
                        <i class="fa fa-plus-circle mr-2"></i>Tambah Parameter Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    {{-- Step Indicator --}}
                    <div class="d-flex" style="border-bottom: 1px solid #e2e8f0; background: #f8f9fa;">
                        <div id="modal-param-step-indicator-1" class="flex-1 text-center py-3 active"
                            style="flex:1; border-right:1px solid #e2e8f0; font-size:13px;">
                            <span class="step-num" style="display:inline-block; width:24px; height:24px; border-radius:50%; background:#667eea; color:white; line-height:24px; font-weight:700; margin-right:6px;">1</span>
                            <strong>Detail Parameter</strong>
                        </div>
                        <div id="modal-param-step-indicator-2" class="flex-1 text-center py-3 text-muted"
                            style="flex:1; font-size:13px;">
                            <span class="step-num" style="display:inline-block; width:24px; height:24px; border-radius:50%; background:#cbd5e0; color:white; line-height:24px; font-weight:700; margin-right:6px;">2</span>
                            <strong>Baku Mutu</strong>
                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Info Lab --}}
                        <div class="alert alert-light border mb-3 py-2" style="font-size:13px;">
                            <i class="fa fa-flask mr-1"></i> Laboratorium:
                            <span class="badge ml-1" id="modal-param-lab-badge"></span>
                        </div>

                        {{-- STEP 0: Pilih parameter yang sudah ada --}}
                        <div id="modal-param-step0" style="display:none;">
                            <p class="mb-2" style="font-size:13px;">
                                <i class="fa fa-info-circle mr-1 text-info"></i>
                                Parameter berikut <strong>belum memiliki baku mutu</strong> untuk jenis sampel ini.
                                Pilih salah satu untuk menambahkan baku mutunya.
                                <span class="badge badge-info ml-1" id="mpicker-count">0</span>
                            </p>
                            <input type="text" id="mpicker-search" class="form-control mb-3"
                                   placeholder="🔍 Cari parameter..." autocomplete="off"
                                   oninput="mpickerFilter(this.value)"
                                   onkeyup="mpickerFilter(this.value)">
                            <div id="mpicker-list"
                                 style="max-height: 340px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;">
                            </div>
                        </div>

                        {{-- STEP 1 --}}
                        <div id="modal-param-step1">
                            <form id="form-step1-param">
                                <input type="hidden" id="modal-param-lab-id" name="modal_lab_id">
                                <input type="hidden" id="modal-param-result-method-id">
                                <input type="hidden" id="modal-bm-store-url">

                                <div class="form-group">
                                    <label><i class="fa fa-tag mr-1"></i>Nama Parameter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="modal-param-params-method" placeholder="Contoh: BOD5, TSS, Coliform">
                                </div>
                                <div class="form-group">
                                    <label><i class="fa fa-flask mr-1"></i>Metode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="modal-param-name-method" placeholder="Contoh: SNI 6989.72:2009">
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Harga Bahan (Rp)</label>
                                            <input type="number" class="form-control" id="modal-param-price-bahan" value="0" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Harga Sarana (Rp)</label>
                                            <input type="number" class="form-control" id="modal-param-price-sarana" value="0" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Harga Jasa (Rp)</label>
                                            <input type="number" class="form-control" id="modal-param-price-jasa" value="0" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Harga Total (Rp)</label>
                                    <input type="number" class="form-control" id="modal-param-price-total" value="0" readonly style="background:#f8f9fa;">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hubungan Kesehatan</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_berhubungan_kesehatan" value="1" id="modal-kes-ya">
                                                    <label class="form-check-label" for="modal-kes-ya">Ya (Kimia)</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_berhubungan_kesehatan" value="0" id="modal-kes-tidak" checked>
                                                    <label class="form-check-label" for="modal-kes-tidak">Tidak</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_berhubungan_kesehatan" value="" id="modal-kes-mikro">
                                                    <label class="form-check-label" for="modal-kes-mikro">Mikro</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Alat & Reagen</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_is_ready" value="1" id="modal-ready-ya" checked>
                                                    <label class="form-check-label" for="modal-ready-ya">Tersedia</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="modal_is_ready" value="0" id="modal-ready-tidak">
                                                    <label class="form-check-label" for="modal-ready-tidak">Belum Tersedia</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- STEP 2 --}}
                        <div id="modal-param-step2" style="display:none;">
                            <div class="alert alert-success py-2 mb-3" style="font-size:13px;">
                                <i class="fa fa-check-circle mr-1"></i> Parameter <strong id="modal-step2-param-name"></strong>
                                berhasil disimpan di lab <strong id="modal-step2-lab-name"></strong>.
                            </div>

                            <div class="card mb-3">
                                <div class="card-header py-2" style="background:#f8f9fa;">
                                    <strong id="modal-bm-form-title"><i class="fa fa-balance-scale mr-1"></i> Baku Mutu</strong>
                                    <small class="text-muted ml-2">(Opsional — bisa diisi nanti)</small>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" id="modal-bm-method-id">
                                    <input type="hidden" id="modal-bm-sampletype-id">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Satuan</label>
                                                <input type="hidden" id="modal-bm-unit-id">
                                                <div class="sdd-wrap" id="sdd-unit">
                                                    <div class="sdd-display sdd-placeholder" tabindex="0">— Pilih Satuan —</div>
                                                    <div class="sdd-panel">
                                                        <input type="text" class="sdd-search" placeholder="Cari satuan...">
                                                        <ul class="sdd-list">
                                                            <li data-value="">— Pilih Satuan —</li>
                                                            @foreach ($units as $unit)
                                                                <li data-value="{{ $unit->id_unit }}">{!! $unit->shortname_unit !!}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Acuan Baku Mutu</label>
                                                <input type="hidden" id="modal-bm-library-id">
                                                <div class="sdd-wrap" id="sdd-library">
                                                    <div class="sdd-display sdd-placeholder" tabindex="0">— Pilih Acuan —</div>
                                                    <div class="sdd-panel">
                                                        <input type="text" class="sdd-search" placeholder="Cari acuan...">
                                                        <ul class="sdd-list">
                                                            <li data-value="">— Pilih Acuan —</li>
                                                            @foreach ($libraries as $lib)
                                                                <li data-value="{{ $lib->id_library }}">{{ $lib->title_library }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section khusus Makanan / Minuman / Lainnya --}}
                                    <div id="modal-bm-mml-section" style="display:none;">
                                        <hr style="border-top:1px dashed #e2e8f0; margin:8px 0 14px;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Tipe Nilai Baku Mutu <span class="text-danger">*</span></label>
                                                    <div class="mt-1">
                                                        <div class="form-check mb-1">
                                                            <input class="form-check-input" type="radio" name="modal_bm_tipe_nilai" value="kuantitatif" id="modal-bm-tipe-kuantitatif" checked>
                                                            <label class="form-check-label" for="modal-bm-tipe-kuantitatif">
                                                                <strong>Kuantitatif</strong>
                                                                <small class="text-muted d-block" style="font-size:11px;">Nilai berupa angka, mis: ≤ 5 mg/kg, 0–100 CFU/g</small>
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="modal_bm_tipe_nilai" value="kualitatif" id="modal-bm-tipe-kualitatif">
                                                            <label class="form-check-label" for="modal-bm-tipe-kualitatif">
                                                                <strong>Kualitatif</strong>
                                                                <small class="text-muted d-block" style="font-size:11px;">Nilai berupa kategori, mis: Negatif, Positif, MS / TMS</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Jenis Makanan <small class="text-muted">(opsional)</small></label>
                                                    <input type="hidden" id="modal-bm-jenis-makanan-id">
                                                    <div class="sdd-wrap" id="sdd-jenis-makanan">
                                                        <div class="sdd-display sdd-placeholder" tabindex="0">— Pilih Jenis Makanan —</div>
                                                        <div class="sdd-panel">
                                                            <input type="text" class="sdd-search" placeholder="Cari jenis makanan...">
                                                            <ul class="sdd-list">
                                                                <li data-value="">— Pilih Jenis Makanan —</li>
                                                                @foreach ($all_jenis_makanan as $jm)
                                                                    <li data-value="{{ $jm->id_jenis_makanan }}">{{ $jm->name_jenis_makanan }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Punya Sub Baku Mutu?</label>
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="modal_bm_is_sub" value="false" id="modal-bm-nosub" checked>
                                                <label class="form-check-label" for="modal-bm-nosub">Tidak</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="modal_bm_is_sub" value="true" id="modal-bm-issub">
                                                <label class="form-check-label" for="modal-bm-issub">Ya</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="modal-bm-no-sub">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Min</label>
                                                    <input type="text" class="form-control" id="modal-bm-min" placeholder="Contoh: 4.0">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Max</label>
                                                    <input type="text" class="form-control" id="modal-bm-max" placeholder="Contoh: 6.5">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Nilai Sama Dengan & Nilai Laporan: TinyMCE inline di form (tanpa popup) --}}
                                        <div class="form-group">
                                            <label>
                                                Nilai Sama Dengan <small class="text-muted">(non-range)</small>
                                            </label>
                                            <textarea class="form-control" id="modal-bm-equal" rows="2"
                                                placeholder="Contoh: Negatif"></textarea>
                                            <small class="form-text text-muted">Toolbar di atas kolom; simbol ≤, ≥ lewat ikon karakter (Ω).</small>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Nilai Baku Mutu di Laporan</label>
                                            <textarea class="form-control" id="modal-bm-nilai" rows="2"
                                                placeholder="Contoh: ≤ 6.5"></textarea>
                                            <small class="form-text text-muted">Teks yang ditampilkan di laporan.</small>
                                        </div>
                                    </div>

                                    <div id="modal-bm-sub-container" style="display:none;">
                                        <div class="alert alert-info py-2" style="font-size:12px;">
                                            <i class="fa fa-info-circle"></i> Sub baku mutu dengan detail lebih kompleks dapat diisi lengkap di halaman
                                            <a href="{{ route('elits-baku-mutu-kimia.index') }}" target="_blank">Baku Mutu Kimia</a> /
                                            <a href="{{ route('elits-baku-mutu-mikro.index') }}" target="_blank">Baku Mutu Mikro</a>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    {{-- Footer Step 0: Pilih Parameter --}}
                    <div id="modal-footer-step0" style="display:none;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i> Batal
                        </button>
                        <small class="text-muted ml-2">Klik <strong>Pilih</strong> pada parameter di atas</small>
                    </div>
                    {{-- Footer Step 1 --}}
                    <div id="modal-footer-step1" style="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-1"></i> Batal
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-modal-param-next">
                            Lanjut ke Baku Mutu <i class="fa fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                    {{-- Footer Step 2 (hidden initially via JS) --}}
                    <div id="modal-footer-step2" style="display:none;">
                        <button type="button" class="btn btn-outline-secondary" id="btn-modal-bm-skip">
                            <i class="fa fa-forward mr-1"></i> Lewati
                        </button>
                        <button type="button" class="btn btn-success" id="btn-modal-bm-save">
                            <i class="fa fa-check mr-1"></i> Simpan Baku Mutu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mpickerFilter(val) {
            var q = (val || '').toLowerCase().trim();
            var rows = document.querySelectorAll('#mpicker-list .mpicker-row');
            for (var i = 0; i < rows.length; i++) {
                var strong = rows[i].querySelector('strong');
                var name = strong ? strong.textContent.toLowerCase() : '';
                rows[i].style.display = (q === '' || name.indexOf(q) !== -1) ? 'flex' : 'none';
            }
        }
    </script>

