@extends('masterweb::template.admin.layout')
@section('title')
    Parameter Satuan Klinik
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-satuan-klinik') }}">Parameter
                                        Satuan
                                        Klinik</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Edit</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            @php
                $usageSamples = $usageSamples ?? collect();
                $usageCount = $usageSamples->count();
            @endphp
            <div class="alert {{ $usageCount > 0 ? 'alert-info' : 'alert-secondary' }} mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 8px;">
                    <div>
                        <strong><i class="fa fa-flask mr-1"></i> Penggunaan parameter ini</strong>
                        <div class="mt-1">
                            @if ($usageCount > 0)
                                Ada <strong>{{ number_format($usageCount, 0, ',', '.') }} sampel</strong>
                                yang sudah memakai parameter
                                <strong>{{ $item->name_parameter_satuan_klinik }}</strong>.
                            @else
                                Belum ada sampel yang memakai parameter
                                <strong>{{ $item->name_parameter_satuan_klinik }}</strong>.
                            @endif
                        </div>
                    </div>
                    @if ($usageCount > 0)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="collapse"
                            data-target="#daftar-sampel-pengguna-parameter" aria-expanded="false"
                            aria-controls="daftar-sampel-pengguna-parameter">
                            Lihat nomor sampel
                        </button>
                    @endif
                </div>

                @if ($usageCount > 0)
                    <div class="collapse mt-3" id="daftar-sampel-pengguna-parameter">
                        <div class="border rounded bg-white p-2" style="max-height: 280px; overflow:auto;">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">No</th>
                                        <th>No. Sampel / Spesimen</th>
                                        <th>No. Lab</th>
                                        <th>Tanggal</th>
                                        <th>Tipe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($usageSamples as $idx => $usage)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <a href="{{ url('/elits-permohonan-uji-klinik-2/' . $usage->id . '/edit') }}"
                                                    target="_blank" rel="noopener">
                                                    {{ $usage->nomor_sampel }}
                                                </a>
                                            </td>
                                            <td>{{ $usage->nomor_lab ?: '-' }}</td>
                                            <td>{{ $usage->tanggal ?: '-' }}</td>
                                            <td>
                                                @if ($usage->is_haji)
                                                    <span class="badge badge-warning">Haji</span>
                                                @else
                                                    <span class="badge badge-secondary">Umum</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Nomor di atas diambil dari permohonan klinik aktif yang punya baris parameter ini.
                            Klik nomor untuk membuka detail permohonan.
                        </small>
                    </div>
                @endif
            </div>

            <form enctype="multipart/form-data" class="forms-sample" id="form"
                action="{{ route('elits-parameter-satuan-klinik.update', $item->id_parameter_satuan_klinik) }}"
                method="POST">

                @csrf
                @method('PUT')

                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                <div class="form-group">
                    <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

                    <select class="form-control" name="parameter_jenis_klinik" id="parameter_jenis_klinik">
                        <option value="{{ $item->parameter_jenis_klinik }}" selected>
                            {{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name_parameter_satuan_klinik">Nama Parameter Satuan</label>

                    <input type="text" class="form-control" id="name_parameter_satuan_klinik"
                        name="name_parameter_satuan_klinik" placeholder="Nama parameter satuan klinik.."
                        value="{{ $item->name_parameter_satuan_klinik ?? old('name_parameter_satuan_klinik') }}" required>
                </div>

                @include('masterweb::module.admin.laboratorium.parameter-satuan-klinik._metode-parameter-satuan-klinik-fields', ['item' => $item])

                <div class="form-group" id="loinc-non-haji-group">
                    <label for="loinc_parameter_satuan_klinik">
                        <span id="loinc-label-default">Loinc Parameter Satuan</span>
                        <span id="loinc-label-non-haji" style="display:none;">Loinc Parameter Satuan (Non Haji)</span>
                        <span style="color: red">*</span>
                    </label>

                    <input type="text" class="form-control" id="loinc_parameter_satuan_klinik"
                        name="loinc_parameter_satuan_klinik" placeholder="Loinc parameter satuan klinik.."
                        value="{{ $item->loinc_parameter_satuan_klinik ?? old('loinc_parameter_satuan_klinik') }}" required>
                    <small class="text-muted" id="loinc-hint-non-haji" style="display:none;">
                        Dipakai untuk permohonan non-haji &amp; pengiriman SatuSehat non-haji.
                    </small>
                </div>

                <div class="form-group" id="loinc-haji-group" style="{{ ($item->is_haji ?? 0) == 1 ? '' : 'display:none;' }}">
                    <label for="loinc_parameter_satuan_klinik_haji">Loinc Parameter Satuan (Haji)</label>
                    <input type="text" class="form-control" id="loinc_parameter_satuan_klinik_haji"
                        name="loinc_parameter_satuan_klinik_haji" placeholder="Loinc parameter satuan klinik (haji).."
                        value="{{ $item->loinc_parameter_satuan_klinik_haji ?? old('loinc_parameter_satuan_klinik_haji') }}">
                    <small class="text-muted">Dipakai untuk permohonan haji &amp; pengiriman SatuSehat haji. Kosong = pakai LOINC non-haji.</small>
                </div>

                <div class="form-group">
                    <label for="jenis_pemeriksaan_parameter_satuan_klinik">Jenis Pemeriksaan</label>

                    <select class="form-control" name="jenis_pemeriksaan_parameter_satuan_klinik"
                        id="jenis_pemeriksaan_parameter_satuan_klinik">

                        @if (reference_sas('jenis_pemeriksaan_klinik'))
                            @foreach (reference_sas('jenis_pemeriksaan_klinik') as $key => $value)
                                <option value="{{ $key }}"
                                    {{ $item->jenis_pemeriksaan_parameter_satuan_klinik == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        @endif

                    </select>
                </div>

                <div class="form-group" id="jenis-sampel-non-haji-group">
                    <label for="jenis_sampel">
                        <span id="jenis-sampel-label-default">JENIS SAMPEL</span>
                        <span id="jenis-sampel-label-non-haji" style="display:none;">JENIS SAMPEL (NON HAJI)</span>
                        <span style="color: red">*</span>
                    </label>
                    <select class="form-control" name="jenis_sampel[]" id="jenis_sampel" multiple required>
                        @php
                            $jenis_sampel_selected = is_array($item->jenis_sampel) ? $item->jenis_sampel : [];
                            $jenis_sampel_options = \Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelectWithExtra($jenis_sampel_selected);
                            $jenis_sampel_haji_selected = is_array($item->jenis_sampel_haji ?? null) ? $item->jenis_sampel_haji : [];
                            $jenis_sampel_haji_options = \Smt\Masterweb\Models\JenisSampelKlinik::optionsForSelectWithExtra($jenis_sampel_haji_selected);
                        @endphp
                        @foreach ($jenis_sampel_options as $option)
                            <option value="{{ $option }}"
                                {{ in_array($option, $jenis_sampel_selected, true) ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted" id="jenis-sampel-hint-non-haji" style="display:none;">
                        Dipakai untuk permohonan non-haji.
                    </small>
                </div>

                <div class="form-group" id="jenis-sampel-haji-group" style="{{ ($item->is_haji ?? 0) == 1 ? '' : 'display:none;' }}">
                    <label for="jenis_sampel_haji">JENIS SAMPEL (HAJI) <span style="color: red">*</span></label>
                    <select class="form-control" name="jenis_sampel_haji[]" id="jenis_sampel_haji" multiple
                        {{ ($item->is_haji ?? 0) == 1 ? 'required' : '' }}>
                        @foreach ($jenis_sampel_haji_options as $option)
                            <option value="{{ $option }}"
                                {{ in_array($option, $jenis_sampel_haji_selected, true) ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Dipakai untuk permohonan haji (pengambilan sampel, informed consent, penerimaan sampel).</small>
                </div>

                <div class="form-group">
                    <label for="is_sub_parameter_satuan_klinik">Apakah memiliki sub parameter satuan?</label>
                    <div class="form-check">
                        <input class="form-check-input is_sub_parameter_satuan_klinik" type="radio" value="1"
                            name="is_sub_parameter_satuan_klinik" id="is_sub_parameter_satuan_klinik_1"
                            {!! $item->is_sub_parameter_satuan_klinik == '1' ? 'checked' : '' !!}>
                        <label class="form-check-label" for="flexRadioDefault1">
                            Ya
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input is_sub_parameter_satuan_klinik" type="radio" value="0"
                            name="is_sub_parameter_satuan_klinik" id="is_sub_parameter_satuan_klinik_2"
                            {!! $item->is_sub_parameter_satuan_klinik == '0' ? 'checked' : '' !!}>
                        <label class="form-check-label" for="is_sub_parameter_satuan_klinik">
                            Tidak
                        </label>
                    </div>
                </div>

                <div class="sub-parameter-satuan" id="sub-parameter-satuan" {!! $item->is_sub_parameter_satuan_klinik == '1' ? '' : 'style="display: none"' !!}>
                    <div class="row">
                        <div class="table-responsive">
                            <table id="table-sub-parameter-satuan" class="table">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">No</th>
                                        <th style="width: 70%">Sub Parameter</th>
                                        <th style="width: 20%">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @if (count($data_subitem) > 0)
                                        @php
                                            $no = 1;
                                        @endphp

                                        @foreach ($data_subitem as $key_ds => $item_ds)
                                            <tr id="row_{{ $no }}" class="tr_row">
                                                <td style="width: 10%">
                                                    <h5>{{ $no }}</h5>
                                                </td>

                                                <td style="width: 70%">
                                                    <input type="text"
                                                        class="form-control name_parameter_sub_satuan_klinik"
                                                        name="name_parameter_sub_satuan_klinik[{{ $no }}]"
                                                        id="name_parameter_sub_satuan_klinik_{{ $no }}"
                                                        value="{{ $item_ds->name_parameter_sub_satuan_klinik }}">
                                                </td>

                                                <td style="width: 20%">
                                                    <button type="button" class="btn btn-primary btn-add-row"
                                                        data-row="{{ $no }}"
                                                        onclick="addRow({{ $no }})">
                                                        <i class="fas fa-plus"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-danger btn-remove-row"
                                                        data-row="{{ $no }}"
                                                        onclick="removeRow({{ $no }})">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            @php
                                                $no++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr id="row_1" class="tr_row">
                                            <td style="width: 10%">
                                                <h5>1</h5>
                                            </td>

                                            <td style="width: 70%">
                                                <input type="text"
                                                    class="form-control name_parameter_sub_satuan_klinik"
                                                    name="name_parameter_sub_satuan_klinik[1]"
                                                    id="name_parameter_sub_satuan_klinik_1">
                                            </td>

                                            <td style="width: 20%">
                                                <button type="button" class="btn btn-primary btn-add-row" data-row="1"
                                                    onclick="addRow(1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>

                                                <button type="button" class="btn btn-danger btn-remove-row"
                                                    data-row="1" onclick="removeRow(1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- <div class="form-group">
                  <label for="sort_parameter_satuan_klinik">Urutan Parameter Satuan</label>

                  <input type="number" class="form-control" id="sort_parameter_satuan_klinik"
                      name="sort_parameter_satuan_klinik" placeholder="Urutan parameter satuan klinik.."
                      value="{{ $item->sort_parameter_satuan_klinik ?? old('sort_parameter_satuan_klinik') }}"
                      required>
                </div> --}}



                {{-- Urutan Parameter Satuan dihilangkan sesuai permintaan --}}


                <div class="form-group">
                    <label for="harga_satuan_parameter_satuan_klinik">Harga Parameter Satuan (Rupiah)</label>

                    <input type="number" class="form-control" id="harga_satuan_parameter_satuan_klinik"
                        name="harga_satuan_parameter_satuan_klinik" placeholder="Harga parameter satuan klinik.."
                        value="{{ $item->harga_satuan_parameter_satuan_klinik ?? old('harga_satuan_parameter_satuan_klinik') }}"
                        required>
                </div>

                <!-- Format Angka -->
                <div class="form-group">
                    <label>Format Angka <i class="fa fa-info-circle text-info ml-1" data-toggle="tooltip" 
                        title="Pilih format angka yang akan digunakan untuk input hasil dan baku mutu"></i></label>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="number_format_en" name="number_format" value="en" 
                                            class="custom-control-input" {{ ($item->number_format ?? 'en') == 'en' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="number_format_en">
                                            <strong>International (EN)</strong><br>
                                            <small class="text-muted">Desimal: titik (.) | Ribuan: koma (,)</small><br>
                                            <small class="badge badge-secondary">Contoh: 1,234.56</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="number_format_id" name="number_format" value="id" 
                                            class="custom-control-input" {{ ($item->number_format ?? 'en') == 'id' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="number_format_id">
                                            <strong>Indonesia (ID)</strong><br>
                                            <small class="text-muted">Desimal: koma (,) | Ribuan: titik (.)</small><br>
                                            <small class="badge badge-secondary">Contoh: 1.234,56</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fa fa-lightbulb mr-2"></i>
                                <strong>Catatan:</strong> Format ini akan digunakan untuk input nilai baku mutu dan hasil pemeriksaan di seluruh sistem.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parameter Haji -->
                <div class="form-group">
                    <label for="is_haji">Parameter Haji <i class="fa fa-info-circle text-info ml-1" data-toggle="tooltip" 
                        title="Centang jika parameter ini adalah parameter haji yang memerlukan baku mutu, jenis sampel, metode, dan LOINC terpisah (haji dan non-haji)"></i></label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_haji" name="is_haji" value="1"
                            {{ ($item->is_haji ?? 0) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_haji">
                            <strong>Parameter Haji</strong> - Memerlukan baku mutu, jenis sampel, metode &amp; LOINC terpisah (Haji dan Non-Haji)
                        </label>
                    </div>
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> Jika dicentang, muncul field Non Haji dan Haji untuk jenis sampel, metode, dan LOINC (juga 2 form baku mutu).
                    </small>
                </div>

                <div class="form-group">
                    <label for="ket_default_parameter_satuan_klinik">Keterangan Default Parameter Satuan</label>

                    <textarea type="text" class="form-control" id="ket_default_parameter_satuan_klinik"
                        name="ket_default_parameter_satuan_klinik" placeholder="Keterangan default parameter satuan klinik.."
                        style="height:200px" required>{!! rubahNilaikeForm(old('ket_default_parameter_satuan_klinik', $item->ket_default_parameter_satuan_klinik ?? '')) !!}</textarea>
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
                                    value="1" {{ isset($item->is_option) && $item->is_option ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_option">
                                    <strong>Hasil Opsional</strong> - Gunakan opsi pilihan untuk hasil (contoh:
                                    Positif/Negatif)
                                </label>
                            </div>
                        </div>

                        @php
                            $show_option = isset($item->is_option) && $item->is_option ? '' : 'style="display: none"';
                            $options = isset($item->option) && $item->option ? explode(',', $item->option) : [];
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
                                                placeholder="Contoh: Positif" value="{{ $opt }}">
                                            <div class="input-group-append">
                                                {{-- Tampilkan tombol plus pada baris pertama, baris lainnya tombol hapus --}}
                                                @if ($idx === 0)
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
                                            placeholder="Contoh: Positif">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success btn-add-option"
                                                title="Tambah opsi">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" id="option" name="option" value="{{ $item->option ?? '' }}">
                            <small class="text-muted mt-1 d-block">
                                <i class="fa fa-info-circle"></i> Klik tombol <span class="badge badge-success"><i
                                        class="fa fa-plus"></i></span> untuk menambah opsi
                            </small>

                            <div class="form-check mt-3 p-3 border rounded bg-light requires-nama-jenis-wrap">
                                <input type="checkbox" class="form-check-input" id="requires_nama_jenis"
                                    name="requires_nama_jenis" value="1"
                                    {{ !empty($item->requires_nama_jenis) ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_nama_jenis">
                                    <strong>Butuh Nama Jenis</strong>
                                    <span class="badge badge-info ml-1">Penanda</span>
                                </label>
                                <small class="form-text text-muted mb-0">
                                    Centang jika di form analis perlu alur <strong>Negatif/Positif</strong> lalu ketik nama jenis
                                    (contoh: Ca. Oxalate, E. coli). Berlaku juga jika opsi hasil memakai grade
                                    <strong>(+)/(++)/(+++)</strong> tanpa kata “Positif”. Tidak perlu untuk parameter
                                    seperti narkoba / antigen / nitrit yang hanya Positif–Negatif.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

            </form>
            <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
            <button type="button" onclick="document.location='{{ url('/elits-parameter-satuan-klinik') }}'"
                class="btn btn-light">Kembali</button>
        </div>
    </div>

    <script src="{{ asset('assets/admin/vendors/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        // jQuery UI for sortable (CDN)
        document.write('<scr' + 'ipt src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></scr' + 'ipt>');

        function goBack() {
            window.history.back();
        }

        function addRow(row) {
            var tableSubParameterSatuanLength = $("#table-sub-parameter-satuan tbody .tr_row").length;

            console.log('tableSubParameterSatuanLength ' + tableSubParameterSatuanLength);

            for (x = 0; x < tableSubParameterSatuanLength; x++) {
                var tr = $("#table-sub-parameter-satuan tbody tr")[x];
                var count = $(tr).attr('id');

                console.log(count);
                count = Number(count.substring(4));

                console.log(count);
            } // /for

            var count_table_tbody_tr = $("#table-sub-parameter-satuan tbody .tr_row").length;
            id_html = count + 1;

            var dom_html = `<tr id="row_${id_html}" class="tr_row">
                            <td style="width: 10%">
                                <h5>${id_html}</h5>
                            </td>

                            <td style="width: 70%">
                                <input type="text" class="form-control name_parameter_sub_satuan_klinik" name="name_parameter_sub_satuan_klinik[${id_html}]" id="name_parameter_sub_satuan_klinik_${id_html}">
                            </td>

                            <td style="width: 20%">
                                <button type="button" class="btn btn-primary btn-add-row" data-row="${id_html}" onclick="addRow(${id_html})">
                                    <i class="fas fa-plus"></i>
                                </button>

                                <button type="button" class="btn btn-danger btn-remove-row" data-row="${id_html}" onclick="removeRow(${id_html})">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </td>
                        </tr>`;

            // $(document.getElementById('main-bdy')).append(dom_html);

            if (count_table_tbody_tr >= 1) {
                $("#table-sub-parameter-satuan tbody .tr_row:last").after(dom_html);
            } else {
                $("#table-sub-parameter-satuan tbody").html(dom_html);
            }
        }

        function removeRow(row) {
            var count_table_tbody_tr = $("#table-sub-parameter-satuan tbody .tr_row").length;

            if (count_table_tbody_tr > 1) {
                $("#table-sub-parameter-satuan tbody .tr_row#row_" + row).remove();

                getSubAmount()
            }
        }
        $(document).ready(function() {
            // Halaman edit: gunakan input angka untuk urutan, tanpa drag & drop
            $(function() {
                var CSRF_TOKEN = $('#csrf-token').val();

                $("#parameter_jenis_klinik").select2({
                    ajax: {
                        url: "{{ route('getParameterJenisKlinik') }}",
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: CSRF_TOKEN,
                                search: params.term // search term
                            };
                        },
                        processResults: function(response) {
                            return {
                                results: response
                            };
                        },
                        cache: true
                    }
                });

                $("#jenis_sampel").select2({
                    placeholder: 'Pilih jenis sampel (bisa lebih dari satu)',
                    theme: 'bootstrap4',
                    allowClear: true,
                    multiple: true
                });

                $("#jenis_sampel_haji").select2({
                    placeholder: 'Pilih jenis sampel haji (bisa lebih dari satu)',
                    theme: 'bootstrap4',
                    allowClear: true,
                    multiple: true
                });

                @if (!empty($item->jenis_sampel) && is_array($item->jenis_sampel))
                    $("#jenis_sampel").val(@json($item->jenis_sampel)).trigger('change');
                @endif
                @if (!empty($item->jenis_sampel_haji) && is_array($item->jenis_sampel_haji))
                    $("#jenis_sampel_haji").val(@json($item->jenis_sampel_haji)).trigger('change');
                @endif

                function syncJenisSampelHajiUi() {
                    var isHaji = $('#is_haji').is(':checked');
                    $('#jenis-sampel-haji-group').toggle(isHaji);
                    $('#jenis-sampel-label-default').toggle(!isHaji);
                    $('#jenis-sampel-label-non-haji').toggle(isHaji);
                    $('#jenis-sampel-hint-non-haji').toggle(isHaji);
                    $('#jenis_sampel_haji').prop('required', isHaji);
                    if (!isHaji) {
                        $('#jenis_sampel_haji').removeAttr('required');
                    }

                    $('#metode-haji-group').toggle(isHaji);
                    $('#metode-label-default').toggle(!isHaji);
                    $('#metode-label-non-haji').toggle(isHaji);
                    $('#metode-hint-default').toggle(!isHaji);
                    $('#metode-hint-non-haji').toggle(isHaji);

                    $('#loinc-haji-group').toggle(isHaji);
                    $('#loinc-label-default').toggle(!isHaji);
                    $('#loinc-label-non-haji').toggle(isHaji);
                    $('#loinc-hint-non-haji').toggle(isHaji);
                }

                $('#is_haji').on('change', syncJenisSampelHajiUi);
                syncJenisSampelHajiUi();

                $('.is_sub_parameter_satuan_klinik').change(function(e) {
                    //logic jika parameter satuan memiliki sub
                    if (($(this).val() == '0') && $('#is_sub_parameter_satuan_klinik_2').is(
                            ':checked')) {
                        console.log('close');
                        $('.sub-parameter-satuan').hide();
                    }

                    if (($(this).val() == '1') && $('#is_sub_parameter_satuan_klinik_1').is(
                            ':checked')) {
                        console.log('open');
                        $('.sub-parameter-satuan').show();
                    }
                });

                // Convert from ^() and _() format to HTML <sup> and <sub> for TinyMCE
                // Keep all other HTML intact (tables, lists, etc.)
                function convertToTinyMCE(value) {
                    if (!value) return '';
                    // Handle comparison symbols first (only if not already HTML entities)
                    value = value.replace(/≤/g, '&le;');
                    value = value.replace(/≥/g, '&ge;');
                    value = value.replace(/±/g, '&plusmn;');
                    // Convert ^() to <sup> and _() to <sub>
                    // This will preserve any existing HTML tags (tables, etc.)
                    // Only convert ^() and _() that are NOT inside HTML tags
                    // Use a more careful regex that doesn't match inside HTML tags
                    value = value.replace(/\^\(([^\)]*)\)/g, function(match, content) {
                        // Check if this is inside an HTML tag
                        var beforeMatch = value.substring(0, value.indexOf(match));
                        var openTags = (beforeMatch.match(/</g) || []).length;
                        var closeTags = (beforeMatch.match(/>/g) || []).length;
                        // If we're inside a tag, don't convert
                        if (openTags > closeTags) return match;
                        return '<sup>' + content + '</sup>';
                    });
                    value = value.replace(/\_\(([^\)]*)\)/g, function(match, content) {
                        var beforeMatch = value.substring(0, value.indexOf(match));
                        var openTags = (beforeMatch.match(/</g) || []).length;
                        var closeTags = (beforeMatch.match(/>/g) || []).length;
                        if (openTags > closeTags) return match;
                        return '<sub>' + content + '</sub>';
                    });
                    return value;
                }

                // Convert from HTML <sup> and <sub> to ^() and _() format before submit
                // Keep all other HTML tags (including tables) intact
                function convertFromTinyMCE(value) {
                    if (!value) return '';
                    // Only convert superscript and subscript to ^() and _() format
                    // Keep all other HTML tags (tables, lists, etc.) as they are
                    value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
                    value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
                    // Decode HTML entities for comparison symbols only
                    // Keep other HTML entities as they are (like &nbsp; for spaces in tables)
                    value = value.replace(/&le;/gi, '≤');
                    value = value.replace(/&ge;/gi, '≥');
                    value = value.replace(/&plusmn;/g, '±');
                    return value;
                }

                // Get current value - HTML will be preserved as-is
                var currentValue = $('#ket_default_parameter_satuan_klinik').val();
                // Convert ^() and _() to HTML if any, but preserve all existing HTML
                var tinymceValue = convertToTinyMCE(currentValue);

                // Wait for TinyMCE to load before initializing
                function initTinyMCE() {
                    if (typeof tinymce === 'undefined') {
                        setTimeout(initTinyMCE, 100);
                        return;
                    }

                    // Initialize TinyMCE using local assets (theme and plugins are in local folder)
                    tinymce.init({
                        selector: '#ket_default_parameter_satuan_klinik',
                        height: 250,
                        theme: 'modern',
                        menubar: false,
                        plugins: [
                            'advlist autolink lists charmap table',
                            'searchreplace code',
                            'insertdatetime paste help wordcount'
                        ],
                        toolbar: 'undo redo | bold italic underline | ' +
                            'superscript subscript | ' +
                            'charmap | table | ' +
                            'removeformat | code | help',
                        charmap_append: [
                            [8804, 'less than or equal to'],
                            [8805, 'greater than or equal to'],
                            [177, 'plus-minus sign']
                        ],
                        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px; padding: 10px; }'
                            + ' table { border-collapse:collapse; width:100%; table-layout:auto; }'
                            + ' table td, table th { border:1px dashed #ccc; padding:2px 4px; vertical-align:top; }'
                            + ' table tr td:nth-child(1), table tr th:nth-child(1) { width:1%; white-space:nowrap; text-align:left; padding-right:4px; }'
                            + ' table tr td:nth-child(2), table tr th:nth-child(2) { width:1%; white-space:nowrap; text-align:center; padding:0 4px; }'
                            + ' table tr td:nth-child(3), table tr th:nth-child(3) { text-align:left; }',
                        // Paste configuration to clean HTML
                        paste_as_text: false, // Allow HTML paste (for tables, etc.)
                        paste_auto_cleanup_on_paste: true,
                        paste_remove_styles: false, // Keep styles for tables
                        paste_remove_spans: false, // Keep spans
                        paste_strip_class_attributes: 'none',
                        paste_preprocess: function(plugin, args) {
                            // Hapus elemen Simple Translate saat paste
                            args.content = args.content.replace(
                                /<div[^>]*id=["\']simple-translate["\'][^>]*>[\s\S]*?<\/div>/gi,
                                '');
                            args.content = args.content.replace(
                                /<div[^>]*class=["\'][^"\']*simple-translate[^"\']*["\'][^>]*>[\s\S]*?<\/div>/gi,
                                '');
                            args.content = args.content.replace(
                                /<div[^>]*simple-translate[^>]*>[\s\S]*?<\/div>/gi, '');
                        },
                        setup: function(editor) {
                            editor.on('init', function() {
                                // Set content with a small delay to ensure editor is fully ready
                                setTimeout(function() {
                                    // Use setContent with format: 'html' to preserve HTML
                                    editor.setContent(tinymceValue || '', {
                                        format: 'html'
                                    });
                                }, 100);
                            });
                            editor.on('change blur', function() {
                                tinymce.triggerSave();
                            });
                        },
                        // Ensure HTML is preserved
                        valid_elements: '*[*]',
                        extended_valid_elements: '*[*]'
                    });
                }

                // Initialize after a short delay to ensure TinyMCE is loaded
                setTimeout(initTinyMCE, 300);

                $('.btn-simpan').on('click', function() {
                    // Convert TinyMCE content to ^() format before submit
                    if (tinymce.get('ket_default_parameter_satuan_klinik')) {
                        var htmlContent = tinymce.get('ket_default_parameter_satuan_klinik')
                            .getContent();

                        // Remove simple-translate extension HTML
                        htmlContent = htmlContent.replace(
                            /<div[^>]*id="simple-translate"[^>]*>[\s\S]*?<\/div>/gi, '');
                        htmlContent = htmlContent.replace(
                            /<div[^>]*class="[^"]*simple-translate[^"]*"[^>]*>[\s\S]*?<\/div>/gi,
                            '');
                        htmlContent = htmlContent.replace(
                            /<div[^>]*simple-translate[^>]*>[\s\S]*?<\/div>/gi, '');

                        var convertedContent = convertFromTinyMCE(htmlContent);
                        $('#ket_default_parameter_satuan_klinik').val(convertedContent);
                    }

                    $('#form').ajaxSubmit({
                        success: function(response) {
                            if (response.status == true) {
                                swal({
                                        title: "Success!",
                                        text: response.pesan,
                                        icon: "success"
                                    })
                                    .then(function() {
                                        document.location =
                                            '/elits-parameter-satuan-klinik';
                                    });
                            } else {
                                var pesan = "";

                                jQuery.each(response.pesan, function(key, value) {
                                    pesan += value + '. ';
                                });

                                swal({
                                    title: "Error!",
                                    text: pesan,
                                    icon: "warning"
                                });
                            }
                        },
                        error: function() {
                            swal("Error!", "System gagal menyimpan!", "error");
                        }
                    })
                })
            })
        })

        $(document).ready(function() {
            // Saat tombol "Pindahkan Setelah" ditekan, tampilkan dropdown
            $('#btn-show-dropdown').on('click', function() {
                $('#dropdown-move-order').toggle(); // Mengubah visibilitas dropdown
            });

            // Inisialisasi Select2 pada dropdown
            $('#after_sort_parameter_satuan_klinik').select2({
                placeholder: 'Pilih urutan setelah...',
                allowClear: true
            });
        });

        $(document).ready(function() {
            $('#after_sort_parameter_satuan_klinik').on('change', function() {
                const selectedSort = parseInt($(this).val());
                $('#sort_parameter_satuan_klinik').val(selectedSort + 1);
            });

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
                    '<input type="text" class="form-control option-input" placeholder="Contoh: Positif" value="' +
                    value + '">' +
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
                    $('#requires_nama_jenis').prop('checked', false);
                }
            });
        });
    </script>
@endsection
