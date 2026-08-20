@extends('masterweb::template.admin.layout')

@section('title')
    Tambah Default Catatan Hasil Klinik
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
                                <li class="breadcrumb-item"><a href="{{ route('elits-default-catatan-hasil-klinik.index') }}">Default Catatan Hasil Klinik</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Tambah</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="form-default-catatan" class="forms-sample" action="{{ route('elits-default-catatan-hasil-klinik.store') }}" method="POST" novalidate>
                @csrf
                <div class="form-group">
                    <label for="parameter_satuan_klinik">Parameter Satuan Klinik <span class="text-danger">*</span></label>
                    <select name="parameter_satuan_klinik" id="parameter_satuan_klinik" class="form-control select2">
                        <option value="">-- Pilih Parameter --</option>
                        @foreach ($parameters as $param)
                            <option value="{{ $param->id_parameter_satuan_klinik }}"
                                {{ old('parameter_satuan_klinik') == $param->id_parameter_satuan_klinik ? 'selected' : '' }}>
                                {{ $param->name_parameter_satuan_klinik }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="catatan_default">Catatan Default <span class="text-danger">*</span></label>
                    {{-- required dihapus: TinyMCE menyembunyikan textarea → browser error "not focusable" --}}
                    <textarea name="catatan_default" id="catatan_default" class="form-control" rows="8">{{ old('catatan_default') }}</textarea>
                    <small class="form-text text-muted">Boleh memakai HTML sederhana (mis. &lt;br&gt;, daftar). Akan muncul di Catatan Hasil form analis.</small>
                </div>

                <div class="form-group">
                    <label for="sort_order">Urutan</label>
                    <input type="number" class="form-control" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}">
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1"
                                {{ old('is_active', 1) ? 'checked' : '' }}>
                            Aktif
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                <a href="{{ route('elits-default-catatan-hasil-klinik.index') }}" class="btn btn-light">Kembali</a>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $(function() {
        if ($.fn.select2) {
            $('#parameter_satuan_klinik').select2({ width: '100%' });
        }
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#catatan_default',
                height: 280,
                menubar: false,
                plugins: ['lists charmap paste'],
                toolbar: 'bold italic underline | superscript subscript | charmap | bullist numlist | removeformat',
                paste_as_text: true
            });
        }

        function catatanIsEmpty(html) {
            var text = $('<div>').html(html || '').text().replace(/\u00a0/g, ' ').trim();
            return text === '';
        }

        $('#form-default-catatan').on('submit', function(e) {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            if (!$('#parameter_satuan_klinik').val()) {
                e.preventDefault();
                alert('Parameter satuan klinik wajib dipilih.');
                $('#parameter_satuan_klinik').select2('open');
                return false;
            }

            var catatan = $('#catatan_default').val();
            if (catatanIsEmpty(catatan)) {
                e.preventDefault();
                alert('Catatan default wajib diisi.');
                if (typeof tinymce !== 'undefined' && tinymce.get('catatan_default')) {
                    tinymce.get('catatan_default').focus();
                }
                return false;
            }
        });
    });
</script>
@endsection
