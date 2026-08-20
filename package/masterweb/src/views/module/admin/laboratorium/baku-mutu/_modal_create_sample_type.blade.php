{{--
    Component : Modal Buat Jenis Sampel Baru (quick create)
    Dipakai di: baku-mutu/add, baku-mutu/edit, baca-hasil Edit Baku Mutu
    Endpoint  : POST route('elits-sampletypes.store') via AJAX (quick_create=1)
    Fields    : name_sampletype (wajib), code_sample_type (opsional)
--}}

<div class="modal fade" id="modalCreateSampleType" tabindex="-1" role="dialog"
    aria-labelledby="modalCreateSampleTypeLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCreateSampleTypeLabel">
                    <i class="fa fa-flask mr-2"></i>Buat Jenis Sampel Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="form-create-sample-type" novalidate>
                    @csrf
                    <input type="hidden" name="quick_create" value="1">

                    <div class="alert alert-info py-2 mb-3" style="font-size:12px;">
                        <i class="fa fa-info-circle mr-1"></i>
                        Jenis sampel dibuat tanpa parameter. Parameter wajib bisa ditambahkan nanti di menu Jenis Sarana.
                    </div>

                    <div class="form-group">
                        <label for="modal_name_sampletype">
                            Nama Jenis Sampel <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_name_sampletype"
                            name="name_sampletype" placeholder="Contoh: Air Bersih" required>
                        <div class="invalid-feedback">Nama jenis sampel wajib diisi.</div>
                    </div>

                    <div class="form-group">
                        <label for="modal_code_sample_type">Kode (opsional)</label>
                        <input type="text" class="form-control" id="modal_code_sample_type"
                            name="code_sample_type" placeholder="Contoh: AB">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-info" id="btn-simpan-sample-type">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var storeUrl = "{{ route('elits-sampletypes.store') }}";

        $('#modalCreateSampleType').on('hidden.bs.modal', function () {
            var form = document.getElementById('form-create-sample-type');
            form.reset();
            form.classList.remove('was-validated');
            $(form).find('[name="quick_create"]').val('1');
        });

        $('#btn-simpan-sample-type').on('click', function () {
            var form = document.getElementById('form-create-sample-type');

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url    : storeUrl,
                method : 'POST',
                data   : $('#form-create-sample-type').serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.status) {
                        var targets = [
                            '#sampletype_id',
                            '#mepm-override-sampletype-id',
                            '#mepm-umum-sampletype-id',
                            '#modal-sampletype-id'
                        ];
                        var label = response.name_sample_type || response.id_sample_type;
                        targets.forEach(function (sel) {
                            var $el = $(sel);
                            if (!$el.length) {
                                return;
                            }
                            // Select biasa
                            if ($el.is('select')) {
                                if ($el.find('option[value="' + response.id_sample_type + '"]').length === 0) {
                                    $el.append(new Option(label, response.id_sample_type, false, false));
                                }
                                $el.val(response.id_sample_type).trigger('change');
                            } else {
                                // Hidden + display di modal tambah baku mutu
                                $el.val(response.id_sample_type);
                                if (sel === '#modal-sampletype-id') {
                                    $('#modal-sample-type-display').val(label);
                                }
                            }
                        });

                        $('#modalCreateSampleType').modal('hide');
                        swal('Berhasil!', response.pesan, 'success');
                    } else {
                        var pesanText = (typeof response.pesan === 'object')
                            ? Object.values(response.pesan).join('\n')
                            : response.pesan;
                        swal('Gagal!', pesanText, 'warning');
                    }
                },
                error: function () {
                    swal('Error!', 'Terjadi kesalahan saat menyimpan jenis sampel.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                }
            });
        });
    })();
</script>
