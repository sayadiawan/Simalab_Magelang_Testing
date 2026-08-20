{{--
    Modal Buat Parameter Satuan Klinik Baru — baku mutu klinik
    Target select: #parameter_satuan_klinik_id
--}}

<div class="modal fade" id="modalCreateParameterSatuanKlinik" tabindex="-1" role="dialog"
    aria-labelledby="modalCreateParameterSatuanKlinikLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCreateParameterSatuanKlinikLabel">
                    <i class="fa fa-list mr-2"></i>Buat Parameter Satuan Klinik Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-create-parameter-satuan-klinik" novalidate>
                    @csrf
                    <input type="hidden" name="parameter_jenis_klinik" id="modal_parameter_jenis_klinik" value="">
                    <input type="hidden" name="is_sub_parameter_satuan_klinik" value="0">
                    <input type="hidden" name="harga_satuan_parameter_satuan_klinik" value="0">

                    <div class="alert alert-info py-2">
                        <small>
                            <i class="fa fa-info-circle mr-1"></i>
                            Parameter satuan akan ditambahkan untuk jenis:
                            <strong id="modal-jenis-klinik-label">-</strong>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="modal_name_parameter_satuan_klinik">
                            Nama Parameter Satuan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_name_parameter_satuan_klinik"
                            name="name_parameter_satuan_klinik" placeholder="Contoh: Blood" required>
                        <div class="invalid-feedback">Nama wajib diisi.</div>
                    </div>

                    <div class="form-group">
                        <label for="modal_jenis_pemeriksaan_parameter_satuan_klinik">
                            Jenis Pemeriksaan <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" name="jenis_pemeriksaan_parameter_satuan_klinik"
                            id="modal_jenis_pemeriksaan_parameter_satuan_klinik" required>
                            <option value="">-- Pilih jenis pemeriksaan --</option>
                            @if (reference_sas('jenis_pemeriksaan_klinik'))
                                @foreach (reference_sas('jenis_pemeriksaan_klinik') as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="invalid-feedback">Jenis pemeriksaan wajib dipilih.</div>
                    </div>

                    <div class="form-group">
                        <label for="modal_jenis_sampel_klinik">Jenis Sampel <span class="text-danger">*</span></label>
                        <select class="form-control" name="jenis_sampel[]" id="modal_jenis_sampel_klinik" multiple required>
                            @foreach (\Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelect() as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Pilih satu atau lebih jenis sampel.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success" id="btn-simpan-parameter-satuan-klinik">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var storeUrl = "{{ route('elits-parameter-satuan-klinik.store') }}";

        $('#modalCreateParameterSatuanKlinik').on('show.bs.modal', function () {
            var jenisId = $('#parameter_jenis_klinik_id').val();
            var jenisText = $('#parameter_jenis_klinik_id option:selected').text() || '-';
            $('#modal_parameter_jenis_klinik').val(jenisId || '');
            $('#modal-jenis-klinik-label').text(jenisText.trim() || '-');

            if (!$('#modal_jenis_sampel_klinik').hasClass('select2-hidden-accessible')) {
                $('#modal_jenis_sampel_klinik').select2({
                    dropdownParent: $('#modalCreateParameterSatuanKlinik'),
                    placeholder: 'Pilih jenis sampel',
                    width: '100%'
                });
            }
        });

        $('#modalCreateParameterSatuanKlinik').on('hidden.bs.modal', function () {
            var form = document.getElementById('form-create-parameter-satuan-klinik');
            form.reset();
            form.classList.remove('was-validated');
            if ($('#modal_jenis_sampel_klinik').hasClass('select2-hidden-accessible')) {
                $('#modal_jenis_sampel_klinik').val(null).trigger('change');
            }
        });

        $('#btn-simpan-parameter-satuan-klinik').on('click', function () {
            var jenisId = $('#parameter_jenis_klinik_id').val();
            if (!jenisId) {
                swal('Perhatian', 'Pilih Parameter Jenis Klinik terlebih dahulu.', 'warning');
                return;
            }
            $('#modal_parameter_jenis_klinik').val(jenisId);

            var form = document.getElementById('form-create-parameter-satuan-klinik');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: $('#form-create-parameter-satuan-klinik').serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.status && response.id_parameter_satuan_klinik) {
                        var newOption = new Option(
                            response.name_parameter_satuan_klinik,
                            response.id_parameter_satuan_klinik,
                            true,
                            true
                        );
                        $('#parameter_satuan_klinik_id').append(newOption).trigger('change');

                        if (typeof updateEqualFieldFromParameterSatuanKlinik === 'function') {
                            updateEqualFieldFromParameterSatuanKlinik(response.id_parameter_satuan_klinik);
                        }

                        $('#modalCreateParameterSatuanKlinik').modal('hide');
                        swal('Berhasil!', response.pesan, 'success');
                    } else {
                        var pesanText = (typeof response.pesan === 'object')
                            ? Object.values(response.pesan).join('\n')
                            : (response.pesan || 'Gagal menyimpan');
                        swal('Gagal!', pesanText, 'warning');
                    }
                },
                error: function () {
                    swal('Error!', 'Terjadi kesalahan saat menyimpan parameter satuan.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                }
            });
        });
    })();
</script>
