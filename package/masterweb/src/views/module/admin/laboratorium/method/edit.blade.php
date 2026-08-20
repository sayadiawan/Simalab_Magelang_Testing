@extends('masterweb::template.admin.layout')
@section('title')
    Method Management
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-methods') }}">Method Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>edit</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Method Management</h4>

            <form enctype="multipart/form-data" class="forms-sample" action="{{ route('elits-methods.update', [$id]) }}"
                method="POST">
                @csrf
                <input type="hidden" value="PUT" name="_method">
                @if (!empty($returnAfterPath))
                    <input type="hidden" name="return_after_path" value="{{ $returnAfterPath }}">
                @endif

                <div class="form-group">
                    <label for="params_method">Nama Parameter</label>
                    <input type="text" class="form-control" id="params_method" name="params_method"
                        value="{{ $method->params_method }}" placeholder="Parameter" required>
                </div>


                {{-- <div class="form-group">
                <label for="name_report_method">Nama Parameter di Laporan</label>
                <input type="text" class="form-control" id="name_report_method" name="name_report_method" value="{{(isset($method->name_report_method))?$method->name_report_method:''}}"  placeholder="Masukkan Nama Parameter di Laporan (jika berbeda)">
            </div> --}}

                @php
                    $metodeList = isset($method->name_method) && $method->name_method !== ''
                        ? array_values(array_filter(array_map('trim', explode('/', $method->name_method)), function ($v) {
                            return $v !== '';
                        }))
                        : [];
                    if (count($metodeList) === 0) {
                        $metodeList = [''];
                    }
                @endphp
                <div class="form-group">
                    <label class="mb-2">
                        <i class="fa fa-flask mr-1"></i>Metode
                        <span class="badge badge-danger ml-1">Wajib</span>
                    </label>
                    <div id="metode-rows">
                        @foreach ($metodeList as $idx => $metodeItem)
                            <div class="input-group mb-2 metode-row">
                                <input type="text" class="form-control metode-input"
                                    placeholder="Masukkan metode" value="{{ $metodeItem }}">
                                <div class="input-group-append">
                                    @if ($idx === 0)
                                        <button type="button" class="btn btn-success btn-add-metode"
                                            title="Tambah metode">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-danger btn-remove-metode"
                                            title="Hapus metode">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="name_method" name="name_method"
                        value="{{ $method->name_method }}" required>
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> Bisa lebih dari satu. Klik
                        <span class="badge badge-success"><i class="fa fa-plus"></i></span>
                        untuk menambah. Di Baca Hasil akan tampil sebagai dropdown jika lebih dari satu.
                    </small>
                </div>

                <div class="form-group">
                    <label for="keterangan_default">Keterangan Default</label>
                    <textarea class="form-control" id="keterangan_default" name="keterangan_default" rows="3"
                        placeholder="Contoh: PERMENKES RI No. 2 Tahun 2023">{{ $method->keterangan_default ?? '' }}</textarea>
                    <small class="text-muted">Nilai ini akan otomatis muncul di kolom Keterangan pada halaman Baca Hasil dan Verifikasi Hasil.</small>
                </div>

                <div class="form-group">
                    <label for="id_pdam_method">Apakah merupakan bagian PDAM?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="1" name="id_pdam_method"
                            id="id_pdam_method" {{ $method->id_pdam_method == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="flexRadioDefault1">
                            Ya
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="0" name="id_pdam_method"
                            id="id_pdam_method" {{ $method->id_pdam_method == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="id_pdam_method">
                            Tidak
                        </label>
                    </div>
                </div>

                <!-- <div class="form-group">
                                      <label for="name_method">Satuan</label>
                                      <select id="unitAttributes" name="unit" class="js-example-basic-multiple" required>

                                          @foreach ($units as $unit)
    <option value="{{ $unit->id_unit }}" {{ $method->unit_method == $unit->id_unit ? 'selected' : '' }}>{{ $unit->shortname_unit }}</option>
    @endforeach
                                      </select>
                                  </div> -->

                <!-- <div class="form-group">
                                      <label for="kadar_diperbolehkan_method">Kadar yang diperbolehkan</label>
                                      <input type="text" class="form-control" id="kadar_diperbolehkan_method" name="kadar_diperbolehkan_method" value="{{ $method->kadar_diperbolehkan_method }}" placeholder="Kadar yang diperbolehkan" required >
                                  </div> -->

                <div class="form-group">
                    <label for="name_method">Berhubungan dengan Kesehatan (Jika Kimia, Jika Tidak Abaikan)</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="1"
                            {{ $method->berhubungan_kesehatan == '1' ? 'checked' : '' }} name="berhubungan_kesehatan"
                            id="berhubungan_kesehatan">
                        <label class="form-check-label" for="flexRadioDefault1">
                            Berhubungan dengan Kesehatan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="0"
                            {{ $method->berhubungan_kesehatan == '0' ? 'checked' : '' }} name="berhubungan_kesehatan"
                            id="berhubungan_kesehatan">
                        <label class="form-check-label" for="berhubungan_kesehatan">
                            Tidak Berhubungan dengan Kesehatan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value=""
                            {{ $method->berhubungan_kesehatan == '' ? 'checked' : '' }} name="berhubungan_kesehatan"
                            id="berhubungan_kesehatan">
                        <label class="form-check-label" for="berhubungan_kesehatan">
                            Mikrobiologi
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name_method">Jenis Parameter</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="kimia organik"
                            {{ $method->jenis_parameter_kimia == 'kimia organik' ? 'checked' : '' }}
                            name="jenis_parameter_kimia" id="jenis_parameter_kimia">
                        <label class="form-check-label" for="jenis_parameter_kimia">
                            Kimia an organik
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="kimiawi"
                            {{ $method->jenis_parameter_kimia == 'kimiawi' ? 'checked' : '' }}
                            name="jenis_parameter_kimia" id="jenis_parameter_kimia">
                        <label class="form-check-label" for="jenis_parameter_kimia">
                            Kimiawi
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="fisika"
                            {{ $method->jenis_parameter_kimia == 'fisika' ? 'checked' : '' }} name="jenis_parameter_kimia"
                            id="jenis_parameter_kimia">
                        <label class="form-check-label" for="jenis_parameter_kimia">
                            Parameter Fisik
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value=""
                            {{ $method->jenis_parameter_kimia == '' ? 'checked' : '' }} name="jenis_parameter_kimia"
                            id="jenis_parameter_kimia">
                        <label class="form-check-label" for="jenis_parameter_kimia">
                            Mikrobiologi
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alat dan Reagen</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="1"
                            {{ $method->is_ready == '1' ? 'checked' : '' }} name="is_ready" id="is_ready_1">
                        <label class="form-check-label" for="is_ready_1">
                            Tersedia
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value="0"
                            {{ $method->is_ready == '0' ? 'checked' : '' }} name="is_ready" id="is_ready_0">
                        <label class="form-check-label" for="is_ready_0">
                            Belum Tersedia
                        </label>
                    </div>
                </div>

                <!-- Opsi Hasil -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-check-square mr-2"></i>Opsi Hasil (Opsional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_option" name="is_option"
                                    value="1" {{ isset($method->is_option) && $method->is_option ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_option">
                                    <strong>Hasil Opsional</strong> - Gunakan opsi pilihan untuk hasil (contoh:
                                    Positif/Negatif)
                                </label>
                            </div>
                        </div>

                        @php
                            $show_option =
                                isset($method->is_option) && $method->is_option ? '' : 'style="display: none"';
                            $options = isset($method->option) && $method->option ? explode(',', $method->option) : [];
                            $options = array_map('trim', $options);
                            $options = array_filter($options);
                        @endphp

                        <div class="form-group display-option-field" {!! $show_option !!}>
                            <label class="mb-2">
                                <i class="fa fa-list-ul mr-1"></i>Daftar Opsi Hasil
                                <span class="badge badge-danger ml-1">Wajib</span>
                            </label>
                            <div id="option-rows">
                                @if (count($options) > 0)
                                    @foreach ($options as $idx => $opt)
                                        <div class="input-group mb-2 option-row">
                                            <input type="text" class="form-control option-input"
                                                placeholder="Masukkan opsi" value="{{ $opt }}">
                                            <div class="input-group-append">
                                                @if ($idx === 0 && count($options) === 1)
                                                    <button type="button" class="btn btn-success btn-add-option"
                                                        title="Tambah opsi">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-danger btn-remove-option"
                                                        title="Hapus opsi">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 option-row">
                                        <input type="text" class="form-control option-input"
                                            placeholder="Masukkan opsi (contoh: Positif)">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success btn-add-option"
                                                title="Tambah opsi">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" id="option" name="option" value="{{ $method->option ?? '' }}">
                            <small class="text-muted mt-1">
                                <i class="fa fa-info-circle"></i> Klik tombol <span class="badge badge-success"><i
                                        class="fa fa-plus"></i></span> untuk menambah opsi
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="code_sampletype">Laboratorium</label>
                    <select id="laboratoriumAttributes" name="laboratoriumAttributes[]"
                        class="js-example-basic-multiple form-control" style="display:none; width: 100%"
                        multiple="multiple" required>

                        @php
                            $ketemu = false;
                        @endphp
                        @foreach ($all_laboratorium as $laboratorium)
                            @php
                                $ketemu = false;
                            @endphp
                            @if (isset($method_laboratorium_details))
                                @foreach ($method_laboratorium_details as $method_laboratorium_detail)
                                    @if ($laboratorium->id_laboratorium == $method_laboratorium_detail->laboratorium_id)
                                        @php
                                            $ketemu = true;
                                        @endphp
                                    @endif
                                @endforeach
                            @endif
                            <option value="{{ $laboratorium->id_laboratorium }}" {{ $ketemu ? 'selected' : '' }}>
                                {{ $laboratorium->nama_laboratorium }}</option>
                        @endforeach

                    </select>
                </div>




                <div class="form-group">
                    <label for="cost_samples">Harga Bahan</label>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                Rp.
                            </span>
                        </div>
                        <input type="number" class="form-control" id="price_bahan" name="price_bahan"
                            value="{{ isset($method->price_bahan) ? $method->price_bahan : '0' }}" type="number"
                            placeholder="Isikan Harga" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost_samples">Harga Sarana</label>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                Rp.
                            </span>
                        </div>
                        <input type="number" class="form-control" id="price_sarana" name="price_sarana"
                            value="{{ isset($method->price_sarana) ? $method->price_sarana : '0' }}" type="number"
                            placeholder="Isikan Harga" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost_samples">Harga Jasa</label>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                Rp.
                            </span>
                        </div>
                        <input type="number" class="form-control" id="price_jasa" name="price_jasa"
                            value="{{ isset($method->price_jasa) ? $method->price_jasa : '0' }}" type="number"
                            placeholder="Isikan Harga" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost_samples">Harga Total</label>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                Rp.
                            </span>
                        </div>
                        <input type="number" class="form-control" id="price_total_method" readonly
                            name="price_total_method"
                            value="{{ isset($method->price_total_method) ? $method->price_total_method : '0' }}"
                            type="number" placeholder="Isikan Harga" required>
                    </div>
                </div>

                @include('masterweb::module.admin.laboratorium.method._sample_type_prices')

                {{-- <div class="form-group">
                <label for="module_method">Module</label>
                <input type="text" class="form-control" id="module_method" name="module_method" value="{{$method->module_method}}" placeholder="Module" required >
            </div>

            <div class="form-group">
                <label for="model_method">Model</label>
                <input type="text" class="form-control" id="model_method" name="model_method" value="{{$method->model_method}}" placeholder="Model" required >
            </div> --}}


                <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                <button onclick="goBack()" class="btn btn-light">Kembali</button>
            </form>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
        $(document).ready(function() {
            $.fn.select2.defaults.set("theme", "classic");
            $('#laboratoriumAttributes').select2({
                placeholder: "Pilih Laboratorium"
            });
            $('#unitAttributes').select2({
                placeholder: "Pilih Unit"
            });
        })

        $('#price_bahan').keyup(function() {
            // console.log($(this).val())
            pricetotal()
        })
        $('#price_jasa').keyup(function() {
            pricetotal()
        })
        $('#price_sarana').keyup(function() {
            pricetotal()
        })

        function pricetotal() {
            var total = parseInt($('#price_bahan').val()) + parseInt($('#price_jasa').val()) + parseInt($('#price_sarana')
                .val())
            $('#price_total_method').val(total)
        }

        function updateMetodeHiddenField() {
            var metode = [];
            $('.metode-input').each(function() {
                var val = $(this).val().trim();
                if (val) {
                    metode.push(val);
                }
            });
            $('#name_method').val(metode.join(' / '));
        }

        function addMetodeRow(value = '') {
            var row = $('<div class="input-group mb-2 metode-row">' +
                '<input type="text" class="form-control metode-input" placeholder="Masukkan metode" value="' +
                value + '">' +
                '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger btn-remove-metode" title="Hapus metode">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>');

            if ($('#metode-rows .metode-row').length === 0) {
                row.find('.input-group-append').html(
                    '<button type="button" class="btn btn-success btn-add-metode" title="Tambah metode">' +
                    '<i class="fa fa-plus"></i>' +
                    '</button>'
                );
            }

            $('#metode-rows').append(row);
            updateMetodeHiddenField();
        }

        $(document).on('input', '.metode-input', function() {
            updateMetodeHiddenField();
        });

        $(document).on('click', '.btn-add-metode', function() {
            addMetodeRow();
        });

        $(document).on('click', '.btn-remove-metode', function() {
            var rows = $('#metode-rows .metode-row');
            if (rows.length > 1) {
                $(this).closest('.metode-row').remove();
                updateMetodeHiddenField();

                if ($('#metode-rows .metode-row').length === 1) {
                    $('#metode-rows .metode-row .btn-remove-metode')
                        .removeClass('btn-danger btn-remove-metode')
                        .addClass('btn-success btn-add-metode')
                        .html('<i class="fa fa-plus"></i>')
                        .attr('title', 'Tambah metode');
                }
            }
        });

        $('form.forms-sample').on('submit', function(e) {
            updateMetodeHiddenField();
            if (!$('#name_method').val().trim()) {
                e.preventDefault();
                alert('Metode wajib diisi minimal satu.');
                $('.metode-input').first().focus();
                return false;
            }
        });

        updateMetodeHiddenField();

        // Toggle option field
        function updateOptionHiddenField() {
            var options = [];
            $('.option-input').each(function() {
                var val = $(this).val().trim();
                if (val) {
                    options.push(val);
                }
            });
            $('#option').val(options.join(', '));
        }

        function addOptionRow(value = '') {
            var row = $('<div class="input-group mb-2 option-row">' +
                '<input type="text" class="form-control option-input" placeholder="Masukkan opsi" value="' + value +
                '">' +
                '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger btn-remove-option" title="Hapus opsi">' +
                '<i class="fa fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>');

            if ($('#option-rows .option-row').length === 0) {
                row.find('.input-group-append').html(
                    '<button type="button" class="btn btn-success btn-add-option" title="Tambah opsi">' +
                    '<i class="fa fa-plus"></i>' +
                    '</button>'
                );
            }

            $('#option-rows').append(row);
        }

        $(document).on('input', '.option-input', function() {
            updateOptionHiddenField();
        });

        $(document).on('click', '.btn-add-option', function() {
            addOptionRow();
        });

        $(document).on('click', '.btn-remove-option', function() {
            var rows = $('#option-rows .option-row');
            if (rows.length > 1) {
                $(this).closest('.option-row').remove();
                updateOptionHiddenField();

                // Jika hanya tersisa 1 baris, ubah tombol menjadi add
                if ($('#option-rows .option-row').length === 1) {
                    $('#option-rows .option-row .btn-remove-option')
                        .removeClass('btn-danger btn-remove-option')
                        .addClass('btn-success btn-add-option')
                        .html('<i class="fa fa-plus"></i>')
                        .attr('title', 'Tambah opsi');
                }
            }
        });

        $('#is_option').change(function(e) {
            e.preventDefault();

            if ($('#is_option').is(':checked')) {
                $('.display-option-field').show();
                if ($('#option-rows .option-row').length === 0) {
                    addOptionRow();
                }
                $('#option').prop('required', true);
            } else {
                $('.display-option-field').hide();
                $('#option').prop('required', false);
                $('#option').val('');
                $('#option-rows').empty();
            }
        });

        // Inisialisasi saat page load jika is_option sudah dicentang
        if ($('#is_option').is(':checked')) {
            updateOptionHiddenField();
        }
    </script>
@endsection
