{{--
    Component : Modal Buat Acuan Baku Mutu Baru
    Dipakai di: baku-mutu/add.blade.php, baku-mutu/edit.blade.php
    Endpoint  : POST route('elits-libraries.store') via AJAX
    Fields    : title_library (wajib), link_library (opsional)
--}}

<div class="modal fade" id="modalCreateLibrary" tabindex="-1" role="dialog"
    aria-labelledby="modalCreateLibraryLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCreateLibraryLabel">
                    <i class="fa fa-book mr-2"></i>Buat Acuan Baku Mutu Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                <form id="form-create-library" novalidate>
                    @csrf

                    <div class="form-group">
                        <label for="modal_title_library">
                            Judul <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_title_library"
                            name="title_library" placeholder="Isikan Judul" required>
                        <div class="invalid-feedback">Judul wajib diisi.</div>
                    </div>

                    <div class="form-group">
                        <label for="modal_link_library">Link</label>
                        <input type="text" class="form-control" id="modal_link_library"
                            name="link_library" placeholder="Isikan Link (opsional)">
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn-simpan-library">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        var storeUrl = "{{ route('elits-libraries.store') }}";

        // Reset form saat modal ditutup
        $('#modalCreateLibrary').on('hidden.bs.modal', function () {
            var form = document.getElementById('form-create-library');
            form.reset();
            form.classList.remove('was-validated');
        });

        // Submit via AJAX
        $('#btn-simpan-library').on('click', function () {
            var form = document.getElementById('form-create-library');

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url    : storeUrl,
                method : 'POST',
                data   : $('#form-create-library').serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (response) {
                    if (response.status) {
                        var libraryTargets = [
                            '#library_id',
                            '#eg-library',
                            '#es-library',
                            '#modal-library-id',
                            '#mepm-override-library-id',
                            '#mepm-umum-library-id'
                        ];
                        libraryTargets.forEach(function(sel) {
                            var $el = $(sel);
                            if (!$el.length) {
                                return;
                            }
                            // Hindari duplikat opsi jika select sudah punya value yang sama
                            if ($el.find('option[value="' + response.id_library + '"]').length === 0) {
                                $el.append(new Option(response.title_library, response.id_library, false, false));
                            }
                            $el.val(response.id_library).trigger('change');
                        });

                        $('#modalCreateLibrary').modal('hide');

                        swal('Berhasil!', response.pesan, 'success');
                    } else {
                        var pesanText = (typeof response.pesan === 'object')
                            ? Object.values(response.pesan).join('\n')
                            : response.pesan;
                        swal('Gagal!', pesanText, 'warning');
                    }
                },
                error: function () {
                    swal('Error!', 'Terjadi kesalahan saat menyimpan acuan baku mutu.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i>Simpan');
                }
            });
        });

    })();
</script>
