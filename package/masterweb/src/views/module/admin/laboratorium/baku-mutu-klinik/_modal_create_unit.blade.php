{{--
    Modal Buat Satuan Baru — baku mutu klinik
    Target select: #unit_id dan .unit_id
--}}

<div class="modal fade" id="modalCreateUnit" tabindex="-1" role="dialog"
    aria-labelledby="modalCreateUnitLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalCreateUnitLabel">
                    <i class="fa fa-ruler mr-2"></i>Buat Satuan Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-create-unit-klinik" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="modal_klinik_name_unit">
                            Nama Satuan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_klinik_name_unit"
                            name="name_unit" placeholder="Contoh: Miligram per Liter" required>
                        <div class="invalid-feedback">Nama satuan wajib diisi.</div>
                    </div>
                    <div class="form-group">
                        <label for="modal_klinik_shortname_unit">
                            Singkatan Satuan <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_klinik_shortname_unit"
                            name="shortname_unit" placeholder="Contoh: mg/L" required>
                        <small class="form-text text-muted">
                            Singkatan yang akan ditampilkan di daftar pilihan.
                        </small>
                        <div class="invalid-feedback">Singkatan satuan wajib diisi.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-warning" id="btn-simpan-unit-klinik">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var storeUrl = "{{ route('elits-units.store') }}";

        $('#modalCreateUnit').on('hidden.bs.modal', function () {
            var form = document.getElementById('form-create-unit-klinik');
            form.reset();
            form.classList.remove('was-validated');
        });

        $('#btn-simpan-unit-klinik').on('click', function () {
            var form = document.getElementById('form-create-unit-klinik');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: $('#form-create-unit-klinik').serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.status) {
                        var unitTargets = ['.unit_id', '#eg-unit', '#es-unit'];
                        unitTargets.forEach(function(sel) {
                            var $el = $(sel);
                            if (!$el.length) {
                                return;
                            }
                            var newOption = new Option(response.shortname_unit, response.id_unit, true, true);
                            $el.append(newOption).trigger('change');
                        });

                        $('#modalCreateUnit').modal('hide');
                        swal('Berhasil!', response.pesan, 'success');
                    } else {
                        var pesanText = (typeof response.pesan === 'object')
                            ? Object.values(response.pesan).join('\n')
                            : response.pesan;
                        swal('Gagal!', pesanText, 'warning');
                    }
                },
                error: function () {
                    swal('Error!', 'Terjadi kesalahan saat menyimpan satuan.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                }
            });
        });
    })();
</script>
