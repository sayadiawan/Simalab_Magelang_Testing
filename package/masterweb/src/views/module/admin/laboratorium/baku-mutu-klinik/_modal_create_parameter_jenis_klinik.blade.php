{{--
    Modal Buat Parameter Jenis Klinik Baru — baku mutu klinik
    Target select: #parameter_jenis_klinik_id
--}}

<div class="modal fade" id="modalCreateParameterJenisKlinik" tabindex="-1" role="dialog"
    aria-labelledby="modalCreateParameterJenisKlinikLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCreateParameterJenisKlinikLabel">
                    <i class="fa fa-flask mr-2"></i>Buat Parameter Jenis Klinik Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-create-parameter-jenis-klinik" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="modal_name_parameter_jenis_klinik">
                            Nama Parameter Jenis <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_name_parameter_jenis_klinik"
                            name="name_parameter_jenis_klinik" placeholder="Contoh: Urin Rutin" required>
                        <div class="invalid-feedback">Nama wajib diisi.</div>
                    </div>
                    <div class="form-group">
                        <label for="modal_code_parameter_jenis_klinik">
                            Kode <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_code_parameter_jenis_klinik"
                            name="code_parameter_jenis_klinik" maxlength="6" placeholder="Contoh: URIN" required>
                        <small class="form-text text-muted">Maksimal 6 karakter, harus unik.</small>
                        <div class="invalid-feedback">Kode wajib diisi.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-success" id="btn-simpan-parameter-jenis-klinik">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var storeUrl = "{{ route('elits-parameter-jenis-klinik.store') }}";

        $('#modalCreateParameterJenisKlinik').on('hidden.bs.modal', function () {
            var form = document.getElementById('form-create-parameter-jenis-klinik');
            form.reset();
            form.classList.remove('was-validated');
        });

        $('#btn-simpan-parameter-jenis-klinik').on('click', function () {
            var form = document.getElementById('form-create-parameter-jenis-klinik');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: $('#form-create-parameter-jenis-klinik').serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.status && response.id_parameter_jenis_klinik) {
                        var label = response.name_parameter_jenis_klinik;
                        if (response.code_parameter_jenis_klinik) {
                            label += ' - ' + response.code_parameter_jenis_klinik;
                        }
                        var newOption = new Option(label, response.id_parameter_jenis_klinik, true, true);
                        $('#parameter_jenis_klinik_id').append(newOption).trigger('change');

                        $('#modalCreateParameterJenisKlinik').modal('hide');
                        swal('Berhasil!', response.pesan, 'success');
                    } else {
                        var pesanText = (typeof response.pesan === 'object')
                            ? Object.values(response.pesan).join('\n')
                            : (response.pesan || 'Gagal menyimpan');
                        swal('Gagal!', pesanText, 'warning');
                    }
                },
                error: function () {
                    swal('Error!', 'Terjadi kesalahan saat menyimpan parameter jenis.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                }
            });
        });
    })();
</script>
