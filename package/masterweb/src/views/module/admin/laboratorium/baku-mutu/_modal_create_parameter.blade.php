<div class="modal fade" id="modalCreateParameter" tabindex="-1" role="dialog"
    aria-labelledby="modalCreateParameterLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCreateParameterLabel">
                    <i class="fa fa-plus-circle mr-2"></i>Buat Parameter Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                <form id="form-create-parameter" novalidate>
                    @csrf

                    <div class="alert alert-info py-2 mb-3">
                        <i class="fa fa-info-circle mr-1"></i>
                        Parameter yang dibuat akan langsung tersedia di dropdown dan terhubung ke laboratorium yang dipilih.
                    </div>

                    {{-- Nama Parameter --}}
                    <div class="form-group">
                        <label for="modal_params_method">
                            Nama Parameter <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_params_method"
                            name="params_method" placeholder="Parameter" required>
                        <div class="invalid-feedback">Nama parameter wajib diisi.</div>
                    </div>

                    {{-- Metode --}}
                    <div class="form-group">
                        <label for="modal_name_method">
                            Metode <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_name_method"
                            name="name_method" placeholder="Metode" required>
                        <div class="invalid-feedback">Metode wajib diisi.</div>
                    </div>

                    {{-- Opsi Hasil --}}
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fa fa-check-square mr-2"></i>Opsi Hasil (Opsional)</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="modal_is_option"
                                        name="is_option" value="1">
                                    <label class="form-check-label" for="modal_is_option">
                                        <strong>Hasil Opsional</strong> — Gunakan opsi pilihan untuk hasil (contoh: Positif/Negatif)
                                    </label>
                                </div>
                            </div>

                            <div class="form-group modal-display-option-field" style="display: none;">
                                <label class="mb-2">
                                    <i class="fa fa-list-ul mr-1"></i>Daftar Opsi Hasil
                                    <span class="badge badge-danger ml-1">Wajib</span>
                                </label>
                                <div id="modal_option_rows">
                                    <div class="input-group mb-2 modal-option-row">
                                        <input type="text" class="form-control modal-option-input"
                                            placeholder="Contoh: Positif">
                                        <div class="input-group-append">
                                            <button type="button"
                                                class="btn btn-success modal-btn-add-option"
                                                title="Tambah opsi">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="modal_option" name="option" value="">
                                <small class="text-muted mt-1">
                                    <i class="fa fa-info-circle"></i>
                                    Klik tombol <span class="badge badge-success"><i class="fa fa-plus"></i></span>
                                    untuk menambah opsi
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Laboratorium --}}
                    <div class="form-group">
                        <label for="modal_laboratoriumAttributes">
                            Laboratorium <span class="text-danger">*</span>
                        </label>
                        <select id="modal_laboratoriumAttributes" name="laboratoriumAttributes[]"
                            class="form-control" style="width: 100%" multiple="multiple" required>
                            @foreach ($all_laboratorium as $laboratorium)
                                <option value="{{ $laboratorium->id_laboratorium }}"
                                    {{ $laboratorium->id_laboratorium === $id_lab ? 'selected' : '' }}>
                                    {{ $laboratorium->nama_laboratorium }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Pilih minimal satu laboratorium.</div>
                    </div>

                    {{-- Bagian PDAM --}}
                    <div class="form-group">
                        <label>Apakah merupakan bagian PDAM?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="1"
                                name="id_pdam_method" id="modal_pdam_ya">
                            <label class="form-check-label" for="modal_pdam_ya">Ya</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0"
                                name="id_pdam_method" id="modal_pdam_tidak" checked>
                            <label class="form-check-label" for="modal_pdam_tidak">Tidak</label>
                        </div>
                    </div>

                    {{-- Berhubungan dengan Kesehatan --}}
                    <div class="form-group">
                        <label>Berhubungan dengan Kesehatan (Jika Kimia, Jika Tidak Abaikan)</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="1"
                                name="berhubungan_kesehatan" id="modal_kes_ya">
                            <label class="form-check-label" for="modal_kes_ya">
                                Berhubungan dengan Kesehatan
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0"
                                name="berhubungan_kesehatan" id="modal_kes_tidak" checked>
                            <label class="form-check-label" for="modal_kes_tidak">
                                Tidak Berhubungan dengan Kesehatan
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value=""
                                name="berhubungan_kesehatan" id="modal_kes_mikro">
                            <label class="form-check-label" for="modal_kes_mikro">Mikrobiologi</label>
                        </div>
                    </div>

                    {{-- Jenis Parameter --}}
                    <div class="form-group">
                        <label>Jenis Parameter</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="kimia organik"
                                name="jenis_parameter_kimia" id="modal_jenis_anorganik">
                            <label class="form-check-label" for="modal_jenis_anorganik">Kimia an organik</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="kimiawi"
                                name="jenis_parameter_kimia" id="modal_jenis_kimiawi" checked>
                            <label class="form-check-label" for="modal_jenis_kimiawi">Kimiawi</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="fisika"
                                name="jenis_parameter_kimia" id="modal_jenis_fisika">
                            <label class="form-check-label" for="modal_jenis_fisika">Parameter Fisik</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value=""
                                name="jenis_parameter_kimia" id="modal_jenis_mikro">
                            <label class="form-check-label" for="modal_jenis_mikro">Mikrobiologi</label>
                        </div>
                    </div>

                    {{-- Alat dan Reagen --}}
                    <div class="form-group">
                        <label>Alat dan Reagen</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="1"
                                name="is_ready" id="modal_ready_ya" checked>
                            <label class="form-check-label" for="modal_ready_ya">Tersedia</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0"
                                name="is_ready" id="modal_ready_tidak">
                            <label class="form-check-label" for="modal_ready_tidak">Belum Tersedia</label>
                        </div>
                    </div>

                    {{-- Harga Bahan --}}
                    <div class="form-group">
                        <label for="modal_price_bahan">Harga Bahan</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="number" class="form-control modal-price-input"
                                id="modal_price_bahan" name="price_bahan" value="0"
                                placeholder="Isikan Harga" required>
                        </div>
                    </div>

                    {{-- Harga Sarana --}}
                    <div class="form-group">
                        <label for="modal_price_sarana">Harga Sarana</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="number" class="form-control modal-price-input"
                                id="modal_price_sarana" name="price_sarana" value="0"
                                placeholder="Isikan Harga" required>
                        </div>
                    </div>

                    {{-- Harga Jasa --}}
                    <div class="form-group">
                        <label for="modal_price_jasa">Harga Jasa</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="number" class="form-control modal-price-input"
                                id="modal_price_jasa" name="price_jasa" value="0"
                                placeholder="Isikan Harga" required>
                        </div>
                    </div>

                    {{-- Harga Total --}}
                    <div class="form-group">
                        <label for="modal_price_total">Harga Total</label>
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">Rp.</span>
                            </div>
                            <input type="number" class="form-control" id="modal_price_total"
                                name="price_total_method" value="0" readonly>
                        </div>
                    </div>

                    {{-- Harga per Jenis Sampel --}}
                    @include('masterweb::module.admin.laboratorium.method._sample_type_prices', [
                        'sampletypes'        => $sample_types,
                        'sample_type_prices' => [],
                    ])

                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success" id="btn-simpan-parameter">
                    <i class="fa fa-save mr-1"></i>Simpan Parameter
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var storeUrl = "{{ route('elits-methods.store') }}";

        // ── Inisialisasi Select2 Laboratorium di dalam modal ─────────────────
        $('#modalCreateParameter').on('shown.bs.modal', function () {
            if (!$('#modal_laboratoriumAttributes').data('select2')) {
                $('#modal_laboratoriumAttributes').select2({
                    placeholder : 'Pilih Laboratorium',
                    dropdownParent: $('#modalCreateParameter')
                });
            }
        });

        // ── Reset form saat modal ditutup ─────────────────────────────────────
        $('#modalCreateParameter').on('hidden.bs.modal', function () {
            var form = document.getElementById('form-create-parameter');
            form.reset();
            form.classList.remove('was-validated');
            $('#modal_price_total').val(0);
            $('#modal_option_rows').html(
                '<div class="input-group mb-2 modal-option-row">' +
                    '<input type="text" class="form-control modal-option-input" placeholder="Contoh: Positif">' +
                    '<div class="input-group-append">' +
                        '<button type="button" class="btn btn-success modal-btn-add-option" title="Tambah opsi">' +
                            '<i class="fa fa-plus"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>'
            );
            $('#modal_option').val('');
            $('.modal-display-option-field').hide();
        });

        // ── Kalkulasi harga total ─────────────────────────────────────────────
        $(document).on('input', '#form-create-parameter .modal-price-input', function () {
            var bahan  = parseInt($('#modal_price_bahan').val())  || 0;
            var sarana = parseInt($('#modal_price_sarana').val()) || 0;
            var jasa   = parseInt($('#modal_price_jasa').val())   || 0;
            $('#modal_price_total').val(bahan + sarana + jasa);
        });

        // ── Toggle & management Opsi Hasil ───────────────────────────────────

        function updateModalOptionHiddenField() {
            var options = [];
            $('.modal-option-input').each(function () {
                var val = $(this).val().trim();
                if (val) options.push(val);
            });
            $('#modal_option').val(options.join(', '));
        }

        function addModalOptionRow(value) {
            value = value || '';
            var isFirst = ($('#modal_option_rows .modal-option-row').length === 0);
            var btn = isFirst
                ? '<button type="button" class="btn btn-success modal-btn-add-option" title="Tambah opsi"><i class="fa fa-plus"></i></button>'
                : '<button type="button" class="btn btn-danger modal-btn-remove-option" title="Hapus opsi"><i class="fa fa-times"></i></button>';

            var row = $(
                '<div class="input-group mb-2 modal-option-row">' +
                    '<input type="text" class="form-control modal-option-input" placeholder="Masukkan opsi" value="' + value + '">' +
                    '<div class="input-group-append">' + btn + '</div>' +
                '</div>'
            );
            $('#modal_option_rows').append(row);
        }

        $('#modal_is_option').on('change', function () {
            if ($(this).is(':checked')) {
                $('.modal-display-option-field').show();
                if ($('#modal_option_rows .modal-option-row').length === 0) {
                    addModalOptionRow();
                }
                $('#modal_option').prop('required', true);
            } else {
                $('.modal-display-option-field').hide();
                $('#modal_option').prop('required', false).val('');
                $('#modal_option_rows').empty();
            }
        });

        $(document).on('input', '.modal-option-input', function () {
            updateModalOptionHiddenField();
        });

        $(document).on('click', '.modal-btn-add-option', function () {
            addModalOptionRow();
        });

        $(document).on('click', '.modal-btn-remove-option', function () {
            var rows = $('#modal_option_rows .modal-option-row');
            if (rows.length > 1) {
                $(this).closest('.modal-option-row').remove();
                updateModalOptionHiddenField();

                if ($('#modal_option_rows .modal-option-row').length === 1) {
                    $('#modal_option_rows .modal-option-row .modal-btn-remove-option')
                        .removeClass('btn-danger modal-btn-remove-option')
                        .addClass('btn-success modal-btn-add-option')
                        .html('<i class="fa fa-plus"></i>')
                        .attr('title', 'Tambah opsi');
                }
            }
        });

        // ── Submit via AJAX ───────────────────────────────────────────────────
        $('#btn-simpan-parameter').on('click', function () {
            var form = document.getElementById('form-create-parameter');

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url    : storeUrl,
                method : 'POST',
                data   : $('#form-create-parameter').serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.status) {
                        var newOption = new Option(response.params_method, response.method_id, true, true);
                        $('#method_id').append(newOption).trigger('change');

                        $('#modalCreateParameter').modal('hide');

                        swal('Berhasil!', response.pesan, 'success');
                    } else {
                        var pesanText = (typeof response.pesan === 'object')
                            ? Object.values(response.pesan).join('\n')
                            : response.pesan;
                        swal('Gagal!', pesanText, 'warning');
                    }
                },
                error: function () {
                    swal('Error!', 'Terjadi kesalahan saat menyimpan parameter.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan Parameter');
                }
            });
        });

    })();
</script>
