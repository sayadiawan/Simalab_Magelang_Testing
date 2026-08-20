@extends('masterweb::template.admin.layout')

@section('title')
    Baku Mutu Manager
@endsection

@section('content')





    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-baku-mutu-' . $lab_link) }}"> Baku Mutu
                                        Lab.{{ $lab }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>update</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form enctype="multipart/form-data" class="forms-sample"
                action="{{ route('elits-baku-mutu-' . $lab_link . '.update', [$id]) }}" method="POST" id="form">
                @csrf

                <input type="hidden" value="PUT" name="_method">
                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

                <!-- Informasi Dasar Parameter -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Informasi Dasar Parameter</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="sampletype_id">
                                <i class="fa fa-flask mr-1"></i>Jenis Sampel
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select name="sampletype_id" class="form-control" id="sampletype_id" style="width: 100%">
                                        <option value="" {{ $baku_mutu->sampletype_id == '' ? 'selected' : '' }} disabled>
                                            Pilih Jenis Sampel
                                        </option>
                                        @foreach ($sample_types as $sample_type)
                                            <option value="{{ $sample_type->id_sample_type }}"
                                                {{ $baku_mutu->sampletype_id == $sample_type->id_sample_type ? 'selected' : '' }}>
                                                {{ $sample_type->name_sample_type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-info flex-shrink-0"
                                    data-toggle="modal" data-target="#modalCreateSampleType"
                                    title="Buat jenis sampel baru jika tidak tersedia di daftar">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                Tidak menemukan jenis sampel? Klik <strong><i class="fa fa-plus"></i></strong> untuk membuat baru.
                            </small>
                        </div>

                        @php
                            // Cek apakah jenis sample adalah "Makanan/Minuman/Lainnya"
                            $sampleType = \Smt\Masterweb\Models\SampleType::find($baku_mutu->sampletype_id);
                            $isMakananMinuman =
                                $sampleType && (str_contains($sampleType->name_sample_type, 'Makanan') || str_contains($sampleType->name_sample_type, 'Minuman') || str_contains($sampleType->name_sample_type, 'Lainnya'));
                            // Untuk lab kimia: tampilkan jenis makanan tapi opsional
                            // Untuk lab non-kimia: tampilkan jenis makanan dan wajib
                            $shouldShowJenisMakanan = $isMakananMinuman;
                            $isRequired = $isMakananMinuman && $lab_link !== "kimia";
                        @endphp
                        <div class="form-group jenis_makanan"
                            style="display: {{ $shouldShowJenisMakanan ? 'block' : 'none' }};">
                            <label for="jenis_makanan_id">
                                <i class="fa fa-utensils mr-1"></i>Jenis Makanan
                                <span class="badge badge-danger ml-1 jenis-makanan-required" style="display: {{ $isRequired ? 'inline' : 'none' }};">Wajib</span>
                                <span class="badge badge-info ml-1 jenis-makanan-optional" style="display: {{ !$isRequired && $shouldShowJenisMakanan ? 'inline' : 'none' }};">Opsional</span>
                            </label>
                            <select id="jenis_makanan_id" name="jenis_makanan_id"
                                class="js-customer-basic-multiple js-states form-control" style="width: 100%">
                                <option value="" {{ !$baku_mutu->jenis_makanan_id ? 'selected' : '' }} {{ $lab_link === 'kimia' ? '' : 'disabled' }}>Pilih
                                    Jenis Makanan{{ $lab_link === 'kimia' ? ' (Opsional)' : '' }}</option>
                                @foreach ($all_jenis_makanan as $jenis_makanan)
                                    <option value="{{ $jenis_makanan->id_jenis_makanan }}"
                                        {{ $baku_mutu->jenis_makanan_id == $jenis_makanan->id_jenis_makanan ? 'selected' : '' }}>
                                        {{ $jenis_makanan->name_jenis_makanan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group tipe_nilai_baku_mutu"
                            style="display: {{ $shouldShowJenisMakanan ? 'block' : 'none' }};">
                            <label for="tipe_nilai_baku_mutu">
                                <i class="fa fa-balance-scale mr-1"></i>Tipe Nilai Baku Mutu
                                <span class="badge badge-danger ml-1">Wajib</span>
                            </label>
                            <select id="tipe_nilai_baku_mutu" name="tipe_nilai_baku_mutu" class="form-control"
                                style="width: 100%" {{ $shouldShowJenisMakanan ? 'required' : '' }}>
                                <option value="" {{ empty($baku_mutu->tipe_nilai_baku_mutu) ? 'selected' : '' }} disabled>
                                    Pilih Tipe Nilai Baku Mutu
                                </option>
                                <option value="kuantitatif"
                                    {{ isset($baku_mutu->tipe_nilai_baku_mutu) && $baku_mutu->tipe_nilai_baku_mutu === 'kuantitatif' ? 'selected' : '' }}>
                                    Kuantitatif
                                </option>
                                <option value="kualitatif"
                                    {{ isset($baku_mutu->tipe_nilai_baku_mutu) && $baku_mutu->tipe_nilai_baku_mutu === 'kualitatif' ? 'selected' : '' }}>
                                    Kualitatif
                                </option>
                            </select>
                            <small class="form-text text-muted">Muncul untuk jenis sampel Makanan, Minuman, atau Lainnya</small>
                        </div>

                        <div class="form-group">
                            <label for="method_id">
                                <i class="fa fa-list mr-1"></i>Parameter
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select name="method_id" class="form-control" id="method_id" style="width: 100%">
                                        <option value="{{ $baku_mutu->method_id == '' ? 'selected' : '' }}" disabled>
                                            Pilih Parameter
                                        </option>
                                        @foreach ($methods as $method)
                                            <option value="{{ $method->id_method }}"
                                                {{ $baku_mutu->method_id == $method->id_method ? 'selected' : '' }}>
                                                {{ $method->params_method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-success flex-shrink-0"
                                    data-toggle="modal" data-target="#modalCreateParameter"
                                    title="Buat parameter baru jika tidak tersedia di daftar">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                Tidak menemukan parameter? Klik <strong><i class="fa fa-plus"></i></strong> untuk membuat baru.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="name_report">
                                <i class="fa fa-file-alt mr-1"></i>Nama Parameter di Laporan
                                <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                    title="Contoh: NO&lt;sub&gt;3&lt;/sub&gt; untuk menulis NO₃"></i>
                            </label>
                            <textarea class="form-control" id="name_report" name="name_report" placeholder="Nama Parameter di Laporan"
                                style="display: none;">{{ isset($baku_mutu->name_report) ? rubahNilaikeForm($baku_mutu->name_report) : '' }}</textarea>
                            <button type="button" class="btn btn-sm btn-primary open-editor-name-report"
                                data-target="name_report">
                                <i class="fa fa-file-text mr-1"></i>
                                Edit Nama Parameter di Laporan
                            </button>
                            <div class="mt-2 p-3 border rounded" style="background-color: #fff; min-height: 50px;">
                                <small class="text-muted"><i
                                        class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                <div id="preview_name_report" style="margin-top: 5px;">
                                    {!! isset($baku_mutu->name_report) && $baku_mutu->name_report
                                        ? rubahNilaikeHtml(rubahNilaikeForm($baku_mutu->name_report))
                                        : '-' !!}
                                </div>
                            </div>
                            <small class="form-text text-muted">Nama parameter yang akan muncul di laporan hasil</small>
                        </div>

                        <div class="form-group">
                            <label for="library_id">
                                <i class="fa fa-book mr-1"></i>Acuan Baku Mutu
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select name="library_id" class="form-control" id="library_id" style="width: 100%">
                                        <option value="" {{ $baku_mutu->library_id == '' ? 'selected' : '' }} disabled>Pilih
                                            Acuan
                                            Baku Mutu
                                        </option>
                                        @foreach ($libraries as $library)
                                            <option value="{{ $library->id_library }}"
                                                {{ $baku_mutu->library_id == $library->id_library ? 'selected' : '' }}>
                                                {{ $library->title_library }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary flex-shrink-0"
                                    data-toggle="modal" data-target="#modalCreateLibrary"
                                    title="Buat acuan baku mutu baru jika tidak tersedia di daftar">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                Tidak menemukan acuan? Klik <strong><i class="fa fa-plus"></i></strong> untuk membuat baru.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="unitAttributes">
                                <i class="fa fa-ruler mr-1"></i>Satuan
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select id="unitAttributes" name="unit_id" class="form-control" style="width: 100%">
                                        <option value="" {{ $baku_mutu->unit_id == '' ? 'selected' : '' }} disabled>
                                            Pilih Satuan
                                        </option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id_unit }}"
                                                {{ $baku_mutu->unit_id == $unit->id_unit ? 'selected' : '' }}>
                                                {!! $unit->shortname_unit !!}</option>
                                        @endforeach
                                        <option value="-" {{ $baku_mutu->unit_id == '-' ? 'selected' : '' }}>-
                                        </option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-warning flex-shrink-0"
                                    data-toggle="modal" data-target="#modalCreateUnit"
                                    title="Buat satuan baru jika tidak tersedia di daftar">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                Tidak menemukan satuan? Klik <strong><i class="fa fa-plus"></i></strong> untuk membuat baru.
                            </small>
                        </div>

                        @php
                            $lokasiData = null;
                            $useLokasi = false;
                            if (isset($baku_mutu->lokasi_data) && !empty($baku_mutu->lokasi_data)) {
                                $lokasiData = json_decode($baku_mutu->lokasi_data, true);
                                $useLokasi = !empty($lokasiData);
                            }
                            // Cek apakah jenis sampel adalah Kualitas Udara
                            $isKualitasUdara = false;
                            if (isset($baku_mutu->sampletype) && $baku_mutu->sampletype) {
                                $sampleTypeName = strtolower($baku_mutu->sampletype->name_sample_type ?? '');
                                $isKualitasUdara = strpos($sampleTypeName, 'udara') !== false;
                            }
                        @endphp

                        {{-- Opsi Lokasi khusus untuk Kualitas Udara --}}
                        <div class="form-group" id="lokasi_option_container" style="display: {{ $isKualitasUdara ? 'block' : 'none' }};">
                            <label>
                                <input type="checkbox" id="use_lokasi" name="use_lokasi" value="1" {{ $useLokasi ? 'checked' : '' }}>
                                <i class="fa fa-map-marker-alt mr-1"></i>Gunakan Lokasi / Ruangan (untuk baku mutu berbeda per ruangan)
                            </label>
                            <small class="form-text text-muted">Centang jika ingin menambahkan baku mutu untuk lokasi/ruangan tertentu. Jika tidak dicentang, baku mutu akan berlaku untuk semua lokasi.</small>
                        </div>

                        <div id="lokasi_container" style="display: {{ $useLokasi ? 'block' : 'none' }};">
                            <div class="form-group">
                                <label><i class="fa fa-map-marker-alt mr-1"></i>Lokasi / Ruangan dengan Baku Mutu</label>
                                <div id="lokasi_list">
                                    @if($useLokasi && !empty($lokasiData))
                                        @foreach($lokasiData as $index => $lokasi)
                                            <div class="lokasi-item card mb-3" data-index="{{ $index }}">
                                                <div class="card-body">
                                                    <button type="button" class="close remove-lokasi" aria-label="Close" {{ count($lokasiData) > 1 ? '' : 'style="display: none;"' }}>
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    <h6 class="card-title">Lokasi {{ $index + 1 }}</h6>
                                                    <div class="form-group">
                                                        <label>Nama Lokasi / Ruangan</label>
                                                        <input type="text" class="form-control lokasi-nama" name="lokasi[{{ $index }}][nama]" value="{{ $lokasi['nama'] ?? '' }}" placeholder="Contoh: Ruang A, Lab 1, dll">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                                        <input type="text" class="form-control" name="lokasi[{{ $index }}][min]" value="{{ $lokasi['min'] ?? '' }}" placeholder="Kadar Min Baku Mutu">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                                        <input type="text" class="form-control" name="lokasi[{{ $index }}][max]" value="{{ $lokasi['max'] ?? '' }}" placeholder="Kadar Max Baku Mutu">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b></label>
                                                        <input type="text" class="form-control" name="lokasi[{{ $index }}][equal]" value="{{ isset($lokasi['equal']) ? rubahNilaikeForm($lokasi['equal']) : '' }}" placeholder="Nilai Harus Sama Dengan">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Nilai Baku Mutu di Laporan</label>
                                                        <textarea class="form-control" name="lokasi[{{ $index }}][nilai_baku_mutu]" placeholder="Nilai Baku Mutu">{{ isset($lokasi['nilai_baku_mutu']) ? rubahNilaikeForm($lokasi['nilai_baku_mutu']) : '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="lokasi-item card mb-3" data-index="0">
                                            <div class="card-body">
                                                <button type="button" class="close remove-lokasi" aria-label="Close" style="display: none;">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <h6 class="card-title">Lokasi 1</h6>
                                                <div class="form-group">
                                                    <label>Nama Lokasi / Ruangan</label>
                                                    <input type="text" class="form-control lokasi-nama" name="lokasi[0][nama]" placeholder="Contoh: Ruang A, Lab 1, dll">
                                                </div>
                                                <div class="form-group">
                                                    <label>Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                                    <input type="text" class="form-control" name="lokasi[0][min]" placeholder="Kadar Min Baku Mutu">
                                                </div>
                                                <div class="form-group">
                                                    <label>Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                                    <input type="text" class="form-control" name="lokasi[0][max]" placeholder="Kadar Max Baku Mutu">
                                                </div>
                                                <div class="form-group">
                                                    <label>Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b></label>
                                                    <input type="text" class="form-control" name="lokasi[0][equal]" placeholder="Nilai Harus Sama Dengan">
                                                </div>
                                                <div class="form-group">
                                                    <label>Nilai Baku Mutu di Laporan</label>
                                                    <textarea class="form-control" name="lokasi[0][nilai_baku_mutu]" placeholder="Nilai Baku Mutu"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="tambah_lokasi">
                                    <i class="fas fa-plus"></i> Tambah Lokasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name_method">Punya Sub Baku Mutu :</label>
                    <input type="radio" id="html" name="is_sub" value="true"
                        {{ $baku_mutu->is_sub == '1' ? 'checked' : '' }}>
                    Ya<br>
                    <input type="radio" id="css" name="is_sub" value="false"
                        {{ $baku_mutu->is_sub == '0' ? 'checked' : '' }}>
                    Tidak<br>
                </div>

                <!-- Konfigurasi Baku Mutu -->
                <div class="no_sub" id="no_sub_container" style="{{ $baku_mutu->is_sub == ' 0' ? 'display: block;' : 'display: none;' }}">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fa fa-sliders mr-2"></i>Konfigurasi Baku Mutu</h5>
                        </div>
                        <div class="card-body">
                            <!-- Nilai Baku Mutu -->
                            <div class="border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                                <h6 class="mb-3"><i class="fa fa-chart-line mr-2"></i>Nilai Baku Mutu</h6>

                                <div class="form-group">
                                    <label for="min">
                                        <i class="fa fa-arrow-down mr-1"></i>Min (Minimum)
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Gunakan titik (.) untuk desimal. Contoh: 4.0 atau kosongkan jika tidak ada"></i>
                                    </label>
                                    <input class="form-control" id="min" type="number" step="0.01"
                                        name="min_no_sub" value="{{ $baku_mutu->min }}" placeholder="Contoh: 4.0">
                                </div>

                                <div class="form-group">
                                    <label for="max">
                                        <i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Gunakan titik (.) untuk desimal. Contoh: 6.5 atau kosongkan jika tidak ada"></i>
                                    </label>
                                    <input class="form-control" id="max" type="number" step="0.01"
                                        name="max_no_sub" value="{{ $baku_mutu->max }}" placeholder="Contoh: 6.5">
                                </div>

                                <div class="form-group">
                                    <label for="equal">
                                        <i class="fa fa-equals mr-1"></i>Nilai Sama Dengan
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Untuk nilai non-range seperti Positif/Negatif. Kosongkan jika menggunakan range Min-Max"></i>
                                    </label>
                                    @php
                                        // Cek apakah Method memiliki is_option = 1
                                        $methodIsOption = false;
                                        $methodOptionValue = '';
                                        $methodOptions = [];
                                        if ($baku_mutu->method && $baku_mutu->method->is_option == 1) {
                                            $methodIsOption = true;
                                            $methodOptionValue = $baku_mutu->method->option ?? '';
                                            if (!empty($methodOptionValue)) {
                                                $methodOptions = array_map('trim', explode(',', $methodOptionValue));
                                                $methodOptions = array_filter($methodOptions);
                                            }
                                        }
                                        $currentEqualValue = rubahNilaikeForm($baku_mutu->equal);
                                    @endphp
                                    <input type="text" class="form-control equal-input" id="equal_no_sub"
                                        name="equal_no_sub" value="{{ $currentEqualValue }}"
                                        placeholder="Contoh: Positif" style="display: none;">
                                    @if ($methodIsOption && count($methodOptions) > 0)
                                        <!-- Dropdown untuk is_option = 1 -->
                                        <select class="form-control equal-dropdown" id="equal_dropdown_no_sub"
                                            name="equal_dropdown_no_sub">
                                            <option value="">- Kosongkan -</option>
                                            @foreach ($methodOptions as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ $currentEqualValue == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <!-- TinyMCE Editor untuk is_option = 0 -->
                                        <button type="button"
                                            class="btn btn-sm btn-success open-editor-equal equal-editor-btn"
                                            data-target="equal_no_sub">
                                            <i class="fa fa-equals mr-1"></i>
                                            Edit Nilai Sama Dengan
                                        </button>
                                    @endif
                                    <div class="mt-2 p-3 border rounded" style="background-color: #fff; min-height: 50px;"
                                        id="preview_equal_no_sub_container">
                                        <small class="text-muted"><i
                                                class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                        <div id="preview_equal_no_sub" style="margin-top: 5px;">
                                            {!! rubahNilaikeForm($baku_mutu->equal) ? rubahNilaikeHtml(rubahNilaikeForm($baku_mutu->equal)) : '-' !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nilai_baku_mutu">
                                        <i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan
                                    </label>
                                    <textarea class="form-control" id="nilai_baku_mutu_no_sub" name="nilai_baku_mutu_no_sub"
                                        placeholder="Nilai Baku Mutu" style="display: none;">{{ rubahNilaikeForm($baku_mutu->nilai_baku_mutu) }}</textarea>
                                    <button type="button" class="btn btn-sm btn-primary open-editor-nilai"
                                        data-target="nilai_baku_mutu_no_sub">
                                        <i class="fa fa-file-text mr-1"></i>
                                        Edit Nilai Baku Mutu
                                    </button>
                                    <div class="mt-2 p-3 border rounded"
                                        style="background-color: #fff; min-height: 50px;">
                                        <small class="text-muted"><i
                                                class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                        <div id="preview_nilai_baku_mutu_no_sub" style="margin-top: 5px;">
                                            {!! rubahNilaikeForm($baku_mutu->nilai_baku_mutu)
                                                ? rubahNilaikeHtml(rubahNilaikeForm($baku_mutu->nilai_baku_mutu))
                                                : '-' !!}</div>
                                    </div>
                                    <small class="form-text text-muted">Teks yang akan muncul di laporan hasil</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sub" style="{{ $baku_mutu->is_sub == ' 1' ? 'display: block;' : 'display: none;' }}">
                    @if (count($bakuMutudetailparameternonkliniks) > 0)
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($bakuMutudetailparameternonkliniks as $bakuMutudetailparameternonklinik)
                            <div class="form-group sub_baku_mutu" id="sub_baku_mutu_{{ $no }}">
                                <div class="card">
                                    <div class="card-body">
                                        <button type="button" class="close" onclick="minus({{ $no }})"
                                            id="minus_{{ $no }}" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h5 class="card-title">
                                            <center>Sub Baku Mutu {{ $no }}</center>
                                        </h5>
                                        <div class="form-group">
                                            <label for="min">Nama Sub Baku</label>
                                            <input type="text" class="form-control" id="name_subbakumutu"
                                                value="{{ $bakuMutudetailparameternonklinik->name_baku_mutu_detail_parameter_non_klinik }}"
                                                name="name_subbakumutu[0]" placeholder="Nama Sub Baku Mutu">
                                        </div>
                                        <div class="form-group">
                                            <label for="min">Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan
                                                    apabila terdapan
                                                    koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                            <input type="text" class="form-control" id="min"
                                                name="min[{{ $no - 1 }}]"
                                                value="{{ $bakuMutudetailparameternonklinik->min_baku_mutu_detail_parameter_non_klinik }}"
                                                placeholder="Kadar Min Baku Mutu">
                                        </div>

                                        <div class="form-group">
                                            <label for="max">Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan
                                                    apabila terdapat
                                                    koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                            <input type="text" class="form-control" id="max"
                                                value="{{ $bakuMutudetailparameternonklinik->max_baku_mutu_detail_parameter_non_klinik }}"
                                                name="max[{{ $no - 1 }}]" placeholder="Kadar Max Baku Mutu">
                                        </div>

                                        <div class="form-group">
                                            <label for="equal">Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu
                                                    bukan berupa
                                                    range minimal maksimal misal (Negatif atau Positif) maka isi disini,
                                                    apabila
                                                    tidak maka kosongi)</b></label>
                                            <input type="text" class="form-control"
                                                value="{{ $bakuMutudetailparameternonklinik->equal_baku_mutu_detail_parameter_non_klinik }}"
                                                id="equal" name="equal[{{ $no - 1 }}]"
                                                placeholder="Nilai Harus Sama Dengan">
                                        </div>



                                        <div class="form-group">
                                            <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan</label>
                                            <input type="text" class="form-control" id="nilai_baku_mutu"
                                                value="{{ $bakuMutudetailparameternonklinik->nilai_baku_mutu_detail_parameter_non_klinik }}"
                                                name="nilai_baku_mutu[{{ $no - 1 }}]"
                                                placeholder="Nilai Baku Mutu">
                                        </div>
                                        @if ($no == count($bakuMutudetailparameternonkliniks))
                                            <button type="button" id="tambah"
                                                class="tambah btn btn-primary btn-lg btn-block"><i
                                                    class="fas fa-plus"></i> Sub Baku Mutu</button>
                                        @endif

                                    </div>

                                </div>
                            </div>
                            @php
                                $no++;
                            @endphp
                        @endforeach
                    @else
                        <div class="form-group sub_baku_mutu" id="sub_baku_mutu_1">
                            <div class="card">
                                <div class="card-body">
                                    <button type="button" class="close" onclick="minus(1)" id="minus_1"
                                        data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h5 class="card-title">
                                        <center>Sub Baku Mutu 1</center>
                                    </h5>
                                    <div class="form-group">
                                        <label for="min">Nama Sub Baku</label>
                                        <input type="text" class="form-control" id="name_subbakumutu"
                                            name="name_subbakumutu[0]" placeholder="Nama Sub Baku Mutu">
                                    </div>
                                    <div class="form-group">
                                        <label for="min">Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila
                                                terdapan
                                                koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                        <input type="text" class="form-control" id="min" name="min[0]"
                                            placeholder="Kadar Min Baku Mutu">
                                    </div>

                                    <div class="form-group">
                                        <label for="max">Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila
                                                terdapat
                                                koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                        <input type="text" class="form-control" id="max" name="max[0]"
                                            placeholder="Kadar Max Baku Mutu">
                                    </div>

                                    <div class="form-group">
                                        <label for="equal">Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan
                                                berupa
                                                range minimal maksimal misal (Negatif atau Positif) maka isi disini, apabila
                                                tidak maka kosongi)</b></label>
                                        <input type="text" class="form-control" id="equal" name="equal[0]"
                                            placeholder="Nilai Harus Sama Dengan">
                                    </div>

                                    <div class="form-group">
                                        <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan</label>
                                        <input type="text" class="form-control" id="nilai_baku_mutu"
                                            name="nilai_baku_mutu[0]" placeholder="Nilai Baku Mutu">
                                    </div>
                                    <button type="button" id="tambah"
                                        class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i> Sub
                                        Baku Mutu</button>

                                </div>

                            </div>
                        </div>
                    @endif
                </div>

            </form>
            {{-- Modal: Buat Parameter Baru --}}
            @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_parameter')

            {{-- Modal: Buat Acuan Baku Mutu Baru --}}
            @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_library')

            {{-- Modal: Buat Satuan Baru --}}
            @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_unit')

            {{-- Modal: Buat Jenis Sampel Baru --}}
            @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_sample_type')

            <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
            <button onclick="goBack()" class="btn btn-light" type="button">Kembali</button>
        </div>
    </div>

    <!-- TinyMCE Editor Modal untuk Nilai Harus Sama Dengan -->
    <div class="modal fade" id="editorModalEqual" tabindex="-1" role="dialog" aria-labelledby="editorModalEqualLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="editorModalEqualLabel">
                        <i class="fa fa-equals mr-2"></i>
                        Editor: Nilai Harus Sama Dengan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <strong><i class="fa fa-info-circle"></i> Nilai Harus Sama Dengan:</strong>
                        <p class="mb-2">Gunakan field ini untuk nilai yang HARUS SAMA (bukan range). Contoh:
                            <b>Negatif</b>, <b>Positif</b>, <b>Tidak Berbau</b>
                        </p>
                        <hr>
                        <strong>Tips Format:</strong>
                        <ul class="mb-0">
                            <li>Superscript: ketik <code>^(2)</code> → x²</li>
                            <li>Subscript: ketik <code>_(2)</code> → H₂O</li>
                            <li>Simbol: gunakan toolbar <b>Special Characters (Ω)</b> untuk ≤, ≥, ±</li>
                        </ul>
                    </div>
                    <textarea id="tinyMCEEditorEqual"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success" id="saveEditorEqual">
                        <i class="fa fa-check mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TinyMCE Editor Modal untuk Nilai Baku Mutu di Laporan -->
    <div class="modal fade" id="editorModalNilai" tabindex="-1" role="dialog" aria-labelledby="editorModalNilaiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editorModalNilaiLabel">
                        <i class="fa fa-file-text mr-2"></i>
                        Editor: Nilai Baku Mutu di Laporan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><i class="fa fa-info-circle"></i> Nilai Baku Mutu di Laporan:</strong>
                        <p class="mb-2">Nilai yang akan DITAMPILKAN di laporan. Bisa berupa range atau nilai spesifik.
                        </p>
                        <hr>
                        <strong>Tips Format:</strong>
                        <ul class="mb-0">
                            <li>Superscript: ketik <code>^(2)</code> → x²</li>
                            <li>Subscript: ketik <code>_(2)</code> → H₂O</li>
                            <li>Simbol: gunakan toolbar <b>Special Characters (Ω)</b> untuk ≤, ≥, ±</li>
                        </ul>
                    </div>
                    <textarea id="tinyMCEEditorNilai"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEditorNilai">
                        <i class="fa fa-check mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TinyMCE Editor Modal untuk Nama Parameter di Laporan -->
    <div class="modal fade" id="editorModalNameReport" tabindex="-1" role="dialog"
        aria-labelledby="editorModalNameReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editorModalNameReportLabel">
                        <i class="fa fa-file-text mr-2"></i>
                        Editor: Nama Parameter di Laporan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><i class="fa fa-info-circle"></i> Nama Parameter di Laporan:</strong>
                        <p class="mb-2">Nama parameter yang akan DITAMPILKAN di laporan.</p>
                        <hr>
                        <strong>Tips Format:</strong>
                        <ul class="mb-0">
                            <li>Subscript: ketik <code>_(2)</code> → H₂O</li>
                            <li>Superscript: ketik <code>^(2)</code> → x²</li>
                            <li>Contoh: NO<sub>3</sub> → tulis NO&lt;sub&gt;3&lt;/sub&gt;</li>
                        </ul>
                    </div>
                    <textarea id="tinyMCEEditorNameReport"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEditorNameReport">
                        <i class="fa fa-check mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">
@endsection

@section('scripts')
    <!-- TinyMCE CDN from jsDelivr (Free, no API key required) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.7/tinymce.min.js"></script>

    @php
        // Prepare methods data for JavaScript
        $methodsDataForJS = [];
        foreach ($methods as $method) {
            $methodsDataForJS[] = [
                'id' => $method->id_method,
                'is_option' => $method->is_option ?? 0,
                'option' => $method->option ?? '',
            ];
        }
    @endphp

    <script>
        $(document).ready(function() {
            // Inisialisasi tooltip
            $(function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $.fn.select2.defaults.set("theme", "classic");


            $('#sampletype_id').select2({
                    placeholder: "Pilih Jenis Sampel",
                    allowClear: true
                })
                .on('change', function(e) {
                    var getID = $(this).select2('data');
                    var labLink = "{{ $lab_link }}";
                    var selectedSampleText = (getID && getID[0] && getID[0].text) ? getID[0].text : '';
                    var isMakananMinumanLainnya = selectedSampleText.includes("Makanan") ||
                        selectedSampleText.includes("Minuman") ||
                        selectedSampleText.includes("Lainnya");

                    // Show/hide jenis makanan berdasarkan jenis sample dan lab
                    // Untuk lab kimia: jika jenis sampel makanan/minuman/lainnya, tampilkan jenis makanan tapi opsional (tidak wajib)
                    // Untuk lab non-kimia: jika jenis sampel makanan/minuman/lainnya, tampilkan jenis makanan dan wajib
                    if (isMakananMinumanLainnya) {
                        $(".jenis_makanan").css("display", "block");
                        $(".tipe_nilai_baku_mutu").css("display", "block");
                        $('#tipe_nilai_baku_mutu').prop('required', true);
                        if (labLink === "kimia") {
                            // Lab kimia: jenis makanan opsional (tidak wajib)
                            $('#jenis_makanan_id').prop('required', false);
                            $('#jenis_makanan_id').removeAttr('required');
                            $('.jenis-makanan-required').hide();
                            $('.jenis-makanan-optional').show();
                            // Pastikan option kosong bisa dipilih
                            $('#jenis_makanan_id option[value=""]').prop('disabled', false);
                        } else {
                            // Lab non-kimia: jenis makanan wajib
                            $('#jenis_makanan_id').prop('required', true);
                            $('.jenis-makanan-required').show();
                            $('.jenis-makanan-optional').hide();
                            // Option kosong disabled untuk lab non-kimia
                            $('#jenis_makanan_id option[value=""]').prop('disabled', true);
                        }
                    } else {
                        $(".jenis_makanan").css("display", "none");
                        $(".tipe_nilai_baku_mutu").css("display", "none");
                        $('#jenis_makanan_id').prop('required', false);
                        $('#jenis_makanan_id').removeAttr('required');
                        $('#jenis_makanan_id').val(null).trigger('change'); // Clear selection
                        $('#tipe_nilai_baku_mutu').prop('required', false);
                        $('#tipe_nilai_baku_mutu').removeAttr('required');
                        $('#tipe_nilai_baku_mutu').val('');
                        $('.jenis-makanan-required').hide();
                        $('.jenis-makanan-optional').hide();
                    }

                    // Show/hide lokasi option khusus untuk Kualitas Udara
                    if (getID && getID.length > 0 && getID[0].text && getID[0].text.toLowerCase().includes('udara')) {
                        $('#lokasi_option_container').show();
                    } else {
                        $('#lokasi_option_container').hide();
                        $('#use_lokasi').prop('checked', false);
                        $('#lokasi_container').hide();
                        $('#no_sub_container').show();
                    }
                });

            // Inisialisasi Select2 untuk jenis_makanan_id
            var labLink = "{{ $lab_link }}";
            $('#jenis_makanan_id').select2({
                placeholder: labLink === "kimia" ? "Pilih Jenis Makanan (Opsional)" : "Pilih Jenis Makanan",
                allowClear: true
            });

            // Check on page load jika jenis sample sudah "Makanan/Minuman/Lainnya"
            var selectedText = $('#sampletype_id option:selected').text();
            var labLink = "{{ $lab_link }}";
            if (selectedText && (selectedText.includes("Makanan") || selectedText.includes("Minuman") || selectedText.includes("Lainnya"))) {
                $(".jenis_makanan").css("display", "block");
                $(".tipe_nilai_baku_mutu").css("display", "block");
                $('#tipe_nilai_baku_mutu').prop('required', true);
                if (labLink === "kimia") {
                    // Lab kimia: jenis makanan opsional (tidak wajib)
                    $('#jenis_makanan_id').prop('required', false);
                    $('#jenis_makanan_id').removeAttr('required');
                    $('.jenis-makanan-required').hide();
                    $('.jenis-makanan-optional').show();
                    // Pastikan option kosong bisa dipilih
                    $('#jenis_makanan_id option[value=""]').prop('disabled', false);
                } else {
                    // Lab non-kimia: jenis makanan wajib
                    $('#jenis_makanan_id').prop('required', true);
                    $('.jenis-makanan-required').show();
                    $('.jenis-makanan-optional').hide();
                    // Option kosong disabled untuk lab non-kimia
                    $('#jenis_makanan_id option[value=""]').prop('disabled', true);
                }
            } else {
                $(".tipe_nilai_baku_mutu").css("display", "none");
                $('#tipe_nilai_baku_mutu').prop('required', false);
                $('#tipe_nilai_baku_mutu').removeAttr('required');
            }

            // Check on page load jika jenis sample adalah Kualitas Udara
            if (selectedText && selectedText.toLowerCase().includes('udara')) {
                $('#lokasi_option_container').show();
            }

            $('#unitAttributes').select2({
                placeholder: "Pilih Satuan",
                width: '100%',
                dropdownParent: $('body')
            });

            $('#method_id').select2({
                placeholder: "Pilih Parameter",
                width: '100%',
                dropdownParent: $('body')
            });
            $('#sampletype_id').select2();
            $('#library_id').select2({
                placeholder: "Pilih Acuan Baku Mutu",
                width: '100%',
                dropdownParent: $('body')
            });

            $('.btn-simpan').on('click', function() {
                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    var link = "{{ $lab_link }}";
                                    // console.log(link);
                                    if (link == "kimia") {
                                        document.location =
                                            "{{ route('elits-baku-mutu-kimia.index') }}";
                                    } else {
                                        document.location =
                                            "{{ route('elits-baku-mutu-mikro.index') }}";
                                    }
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    pesan += value + '. <br>';
                                    wrapper.innerHTML = pesan;
                                });

                                swal({
                                    title: "Error!",
                                    content: wrapper,
                                    icon: "warning"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: response.pesan,
                                    icon: "warning"
                                });
                            }
                        }
                    },
                    error: function() {
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                })
            })
        })

        $('#method_id').change(function() {
            var data = $("#method_id option:selected").text();
            $('#name_report').val(data);
            updatePreview('name_report', data);
        });

        $(':radio[name="is_sub"]').change(function() {
            var is_sub = $(this).filter(':checked').val();

            if (is_sub == "true") {
                $(".no_sub").css("display", "none")
                $(".sub").css("display", "block")
            } else {
                $(".no_sub").css("display", "block")
                $(".sub").css("display", "none")
            }
            console.log(is_sub)
        });

        var default_is_sub = "{{ $baku_mutu->is_sub }}"

        if (default_is_sub == "1") {
            $(':radio[name="is_sub"]').filter('[value="true"]').attr('checked', true);
            $(':radio[name="is_sub"]').filter('[value="false"]').attr('checked', false);
        } else {
            $(':radio[name="is_sub"]').filter('[value="true"]').attr('checked', false);
            $(':radio[name="is_sub"]').filter('[value="false"]').attr('checked', true);
        }

        var no = parseInt("{{ count($bakuMutudetailparameternonkliniks) }}");

        function minus(no) {
            var count = $(".sub .sub_baku_mutu").children().length;
            // console.log(count)
            if (count > 1) {
                $('#sub_baku_mutu_' + no).remove();
                sorting()
                if (no == count) {
                    $('#sub_baku_mutu_' + (count - 1) + ' .card-body').append(
                        '<button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i> Sub Baku Mutu</button>'
                    )
                    $("#tambah").click(function() {
                        tambah(no + 1)
                        sorting()
                    });
                }
            }


        }

        function tambah(no) {
            $('#tambah').remove();

            var new_field = $(`<div class="form-group sub_baku_mutu" id="sub_baku_mutu_` + no + `">
                    <div class="card">
                        <div class="card-body">
                            <button type="button" onclick="minus(` + no + `)" class="close" id="minus_` + no + `" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h5 class="card-title"><center>Sub Baku Mutu ` + no +
                `</center></h5>
                            <div class="form-group">
                                <label for="min">Nama Sub Baku</label>
                                <input type="text" class="form-control" id="name_subbakumutu" name="name_subbakumutu[` +
                (no - 1) + `]" placeholder="Nama Sub Baku Mutu"  >
                            </div>
                            <div class="form-group">
                                <label for="min">Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapan koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                <input type="text" class="form-control" id="min" name="min[` + (no - 1) + `]" placeholder="Kadar Min Baku Mutu"  >
                            </div>

                            <div class="form-group">
                                <label for="max">Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                                <input type="text" class="form-control" id="max" name="max[` + (no - 1) + `]" placeholder="Kadar Max Baku Mutu"  >
                            </div>

                            <div class="form-group">
                                <label for="equal">Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b></label>
                                <input type="text" class="form-control" id="equal" name="equal[` + (no - 1) + `]" placeholder="Nilai Harus Sama Dengan"  >
                            </div>

                            <div class="form-group">
                                <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan</label>
                                <input type="text" class="form-control" id="nilai_baku_mutu" name="nilai_baku_mutu[` +
                (no - 1) + `]" placeholder="Nilai Baku Mutu"  >
                            </div><button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i> Sub Baku Mutu</button>
                            </div>
                            </div>
                            </div>`);

            $(".sub").append(new_field);
            // $("#minus_"+(no+1)).click(function() {
            //     minus(no+1)
            //     sorting()
            // });

            $("#tambah").click(function() {
                tambah(no + 1)
                sorting()
            });
            sorting()

        }

        $("#minus_" + no).click(function() {
            sorting()
        });

        function sorting() {

            $(".sub .sub_baku_mutu").each(function(i, element) {
                // $(element).find('.card-title');
                // console.log( $(element).find('.card-title'))
                $(element).find('.card-title').html("<center>Sub Baku " + (i + 1) + "</center>");
                $(element).find('.close').prop("id", "minus_" + (i + 1));
                $(element).find('.close').attr("onclick", "minus(" + (i + 1) + ")");
                $(element).prop("id", "sub_baku_mutu_" + (i + 1));
                $(element).find('#min').prop("name", "min[" + (i) + "]");
                $(element).find('#max').prop("name", "max[" + (i) + "]");
                $(element).find('#equal').prop("name", "equal[" + (i) + "]");
                $(element).find('#nilai_baku_mutu').prop("name", "nilai_baku_mutu[" + (i) + "]");
                // $("#minus_"+(i+1)).click(function() {
                //     minus(i+1)
                //     sorting()
                // });


                // $('.card-title').text();
            });
        }

        $("#tambah").click(function() {
            tambah(no + 1)
        });

        // Handle checkbox use_lokasi (khusus untuk Kualitas Udara)
        $('#use_lokasi').change(function() {
            if ($(this).is(':checked')) {
                $('#lokasi_container').show();
                $('#no_sub_container').hide();
            } else {
                $('#lokasi_container').hide();
                $('#no_sub_container').show();
            }
        });

        // Handle tambah lokasi
        var lokasiIndex = {{ $useLokasi && !empty($lokasiData) ? count($lokasiData) : 1 }};
        $('#tambah_lokasi').click(function() {
            var newLokasi = $(`
                <div class="lokasi-item card mb-3" data-index="${lokasiIndex}">
                    <div class="card-body">
                        <button type="button" class="close remove-lokasi" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h6 class="card-title">Lokasi ${lokasiIndex + 1}</h6>
                        <div class="form-group">
                            <label>Nama Lokasi / Ruangan</label>
                            <input type="text" class="form-control lokasi-nama" name="lokasi[${lokasiIndex}][nama]" placeholder="Contoh: Ruang A, Lab 1, dll">
                        </div>
                        <div class="form-group">
                            <label>Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                            <input type="text" class="form-control" name="lokasi[${lokasiIndex}][min]" placeholder="Kadar Min Baku Mutu">
                        </div>
                        <div class="form-group">
                            <label>Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>
                            <input type="text" class="form-control" name="lokasi[${lokasiIndex}][max]" placeholder="Kadar Max Baku Mutu">
                        </div>
                        <div class="form-group">
                            <label>Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b></label>
                            <input type="text" class="form-control" name="lokasi[${lokasiIndex}][equal]" placeholder="Nilai Harus Sama Dengan">
                        </div>
                        <div class="form-group">
                            <label>Nilai Baku Mutu di Laporan</label>
                            <textarea class="form-control" name="lokasi[${lokasiIndex}][nilai_baku_mutu]" placeholder="Nilai Baku Mutu"></textarea>
                        </div>
                    </div>
                </div>
            `);
            $('#lokasi_list').append(newLokasi);
            lokasiIndex++;
            updateLokasiNumbers();
        });

        // Handle remove lokasi
        $(document).on('click', '.remove-lokasi', function() {
            if ($('.lokasi-item').length > 1) {
                $(this).closest('.lokasi-item').remove();
                updateLokasiNumbers();
                reindexLokasi();
            }
        });

        // Update lokasi numbers
        function updateLokasiNumbers() {
            $('.lokasi-item').each(function(index) {
                $(this).find('.card-title').text('Lokasi ' + (index + 1));
                if ($('.lokasi-item').length > 1) {
                    $(this).find('.remove-lokasi').show();
                } else {
                    $(this).find('.remove-lokasi').hide();
                }
            });
        }

        // Reindex lokasi inputs
        function reindexLokasi() {
            $('.lokasi-item').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('input[name*="[nama]"]').attr('name', 'lokasi[' + index + '][nama]');
                $(this).find('input[name*="[min]"]').attr('name', 'lokasi[' + index + '][min]');
                $(this).find('input[name*="[max]"]').attr('name', 'lokasi[' + index + '][max]');
                $(this).find('input[name*="[equal]"]').attr('name', 'lokasi[' + index + '][equal]');
                $(this).find('textarea[name*="[nilai_baku_mutu]"]').attr('name', 'lokasi[' + index + '][nilai_baku_mutu]');
            });
            lokasiIndex = $('.lokasi-item').length;
        }

        // Initialize
        updateLokasiNumbers();

        function goBack() {
            window.history.back();
        }

        // ============================================
        // TinyMCE Integration for Baku Mutu Fields - 2 Modal Terpisah
        // ============================================

        let currentTargetFieldEqual = null;
        let currentTargetFieldNilai = null;
        let currentTargetFieldNameReport = null;
        let pendingEqualValue = null;
        let pendingNilaiValue = null;
        let pendingNameReportValue = null;

        // Conversion functions
        function convertToTinyMCE(value) {
            if (!value) return '';
            value = value.replace(/≤/g, '&le;');
            value = value.replace(/≥/g, '&ge;');
            value = value.replace(/±/g, '&plusmn;');
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            return value;
        }

        function convertFromTinyMCE(value) {
            if (!value) return '';
            value = value.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
            value = value.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
            value = value.replace(/<[^>]*>/g, ''); // Strip remaining HTML tags
            value = value.replace(/&le;/gi, '≤');
            value = value.replace(/&ge;/gi, '≥');
            value = value.replace(/&lt;/g, '<');
            value = value.replace(/&gt;/g, '>');
            value = value.replace(/&plusmn;/g, '±');
            value = value.replace(/&nbsp;/g, ' ');
            return value;
        }

        // Convert to HTML for preview display
        function convertToHTMLPreview(value) {
            if (!value) return '-';

            // Auto-close parentheses
            var openSupCount = (value.match(/\^\(/g) || []).length;
            var openSubCount = (value.match(/\_\(/g) || []).length;
            var closeCount = (value.match(/\)/g) || []).length;
            var totalOpen = openSupCount + openSubCount;
            if (totalOpen > closeCount) {
                for (var i = 0; i < (totalOpen - closeCount); i++) {
                    value += ')';
                }
            }

            // Convert operators
            value = value.replace(/<=|≤/g, '&#8804;');
            value = value.replace(/>=|≥/g, '&#8805;');
            value = value.replace(/\+\-|±/g, '&plusmn;');

            // Convert superscript and subscript
            value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');

            return value;
        }

        // Update preview box
        function updatePreview(targetId, value) {
            const previewId = 'preview_' + targetId;
            const htmlValue = convertToHTMLPreview(value);
            $('#' + previewId).html(htmlValue);
        }

        // ============================================
        // MODAL 1: Nilai Harus Sama Dengan (Equal)
        // ============================================

        // Open editor modal Equal
        $(document).on('click', '.open-editor-equal', function() {
            const targetId = $(this).data('target');
            currentTargetFieldEqual = $('#' + targetId);

            // Get current value and convert to TinyMCE format
            const currentValue = currentTargetFieldEqual.val();
            pendingEqualValue = convertToTinyMCE(currentValue);

            console.log('Opening Equal editor, value:', currentValue, 'converted:', pendingEqualValue);
            // Show modal
            $('#editorModalEqual').modal('show');
        });

        // Initialize TinyMCE for Equal when modal is shown
        $('#editorModalEqual').on('shown.bs.modal', function() {
            if (tinymce.get('tinyMCEEditorEqual')) {
                tinymce.get('tinyMCEEditorEqual').remove();
            }

            tinymce.init({
                selector: '#tinyMCEEditorEqual',
                height: 300,
                menubar: false,
                base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                suffix: '.min',
                plugins: ['advlist autolink lists link charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic | superscript subscript | charmap | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist | removeformat | help',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
                    + ' table { border-collapse:collapse; width:100%; table-layout:auto; }'
                    + ' table td, table th { border:1px dashed #ccc; padding:2px 4px; vertical-align:top; }'
                    + ' table tr td:nth-child(1), table tr th:nth-child(1) { width:1%; white-space:nowrap; text-align:left; padding-right:4px; }'
                    + ' table tr td:nth-child(2), table tr th:nth-child(2) { width:1%; white-space:nowrap; text-align:center; padding:0 4px; }'
                    + ' table tr td:nth-child(3), table tr th:nth-child(3) { text-align:left; }',
                setup: function(editor) {
                    editor.on('init', function() {
                        console.log('TinyMCE Equal initialized, setting content:',
                            pendingEqualValue);
                        if (pendingEqualValue) {
                            editor.setContent(pendingEqualValue);
                        }
                    });
                }
            });
        });

        // Destroy TinyMCE Equal when modal is hidden
        $('#editorModalEqual').on('hidden.bs.modal', function() {
            if (tinymce.get('tinyMCEEditorEqual')) {
                tinymce.get('tinyMCEEditorEqual').remove();
            }
            currentTargetFieldEqual = null;
            pendingEqualValue = null;
        });

        // Save editor Equal content
        $('#saveEditorEqual').on('click', function() {
            if (currentTargetFieldEqual && tinymce.get('tinyMCEEditorEqual')) {
                const editorContent = tinymce.get('tinyMCEEditorEqual').getContent();
                const convertedContent = convertFromTinyMCE(editorContent);

                currentTargetFieldEqual.val(convertedContent);
                const targetId = currentTargetFieldEqual.attr('id');
                updatePreview(targetId, convertedContent);

                console.log('Saved Equal:', convertedContent);
                $('#editorModalEqual').modal('hide');
            }
        });

        // ============================================
        // MODAL 2: Nilai Baku Mutu di Laporan (Nilai)
        // ============================================

        // Open editor modal Nilai
        $(document).on('click', '.open-editor-nilai', function() {
            const targetId = $(this).data('target');
            currentTargetFieldNilai = $('#' + targetId);

            const currentValue = currentTargetFieldNilai.val();
            pendingNilaiValue = convertToTinyMCE(currentValue);

            console.log('Opening Nilai editor, value:', currentValue, 'converted:', pendingNilaiValue);
            $('#editorModalNilai').modal('show');
        });

        // Initialize TinyMCE for Nilai when modal is shown
        $('#editorModalNilai').on('shown.bs.modal', function() {
            if (tinymce.get('tinyMCEEditorNilai')) {
                tinymce.get('tinyMCEEditorNilai').remove();
            }

            tinymce.init({
                selector: '#tinyMCEEditorNilai',
                height: 300,
                menubar: false,
                base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                suffix: '.min',
                plugins: ['advlist autolink lists link charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic | superscript subscript | charmap | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist | removeformat | help',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
                    + ' table { border-collapse:collapse; width:100%; table-layout:auto; }'
                    + ' table td, table th { border:1px dashed #ccc; padding:2px 4px; vertical-align:top; }'
                    + ' table tr td:nth-child(1), table tr th:nth-child(1) { width:1%; white-space:nowrap; text-align:left; padding-right:4px; }'
                    + ' table tr td:nth-child(2), table tr th:nth-child(2) { width:1%; white-space:nowrap; text-align:center; padding:0 4px; }'
                    + ' table tr td:nth-child(3), table tr th:nth-child(3) { text-align:left; }',
                setup: function(editor) {
                    editor.on('init', function() {
                        console.log('TinyMCE Nilai initialized, setting content:',
                            pendingNilaiValue);
                        if (pendingNilaiValue) {
                            editor.setContent(pendingNilaiValue);
                        }
                    });
                }
            });
        });

        // Destroy TinyMCE Nilai when modal is hidden
        $('#editorModalNilai').on('hidden.bs.modal', function() {
            if (tinymce.get('tinyMCEEditorNilai')) {
                tinymce.get('tinyMCEEditorNilai').remove();
            }
            currentTargetFieldNilai = null;
            pendingNilaiValue = null;
        });

        // Save editor Nilai content
        $('#saveEditorNilai').on('click', function() {
            if (currentTargetFieldNilai && tinymce.get('tinyMCEEditorNilai')) {
                const editorContent = tinymce.get('tinyMCEEditorNilai').getContent();
                const convertedContent = convertFromTinyMCE(editorContent);

                currentTargetFieldNilai.val(convertedContent);
                const targetId = currentTargetFieldNilai.attr('id');
                updatePreview(targetId, convertedContent);

                console.log('Saved Nilai:', convertedContent);
                $('#editorModalNilai').modal('hide');
            }
        });

        // ============================================
        // MODAL 3: Nama Parameter di Laporan (Name Report)
        // ============================================

        // Open editor modal Name Report
        $(document).on('click', '.open-editor-name-report', function() {
            const targetId = $(this).data('target');
            currentTargetFieldNameReport = $('#' + targetId);

            const currentValue = currentTargetFieldNameReport.val();
            pendingNameReportValue = convertToTinyMCE(currentValue);

            console.log('Opening Name Report editor, value:', currentValue, 'converted:', pendingNameReportValue);
            $('#editorModalNameReport').modal('show');
        });

        // Initialize TinyMCE for Name Report when modal is shown
        $('#editorModalNameReport').on('shown.bs.modal', function() {
            if (tinymce.get('tinyMCEEditorNameReport')) {
                tinymce.get('tinyMCEEditorNameReport').remove();
            }

            tinymce.init({
                selector: '#tinyMCEEditorNameReport',
                height: 300,
                menubar: false,
                base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                suffix: '.min',
                plugins: ['advlist autolink lists link charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic | superscript subscript | charmap | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist | removeformat | help',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
                    + ' table { border-collapse:collapse; width:100%; table-layout:auto; }'
                    + ' table td, table th { border:1px dashed #ccc; padding:2px 4px; vertical-align:top; }'
                    + ' table tr td:nth-child(1), table tr th:nth-child(1) { width:1%; white-space:nowrap; text-align:left; padding-right:4px; }'
                    + ' table tr td:nth-child(2), table tr th:nth-child(2) { width:1%; white-space:nowrap; text-align:center; padding:0 4px; }'
                    + ' table tr td:nth-child(3), table tr th:nth-child(3) { text-align:left; }',
                setup: function(editor) {
                    editor.on('init', function() {
                        console.log('TinyMCE Name Report initialized, setting content:',
                            pendingNameReportValue);
                        if (pendingNameReportValue) {
                            editor.setContent(pendingNameReportValue);
                        }
                    });
                }
            });
        });

        // Destroy TinyMCE Name Report when modal is hidden
        $('#editorModalNameReport').on('hidden.bs.modal', function() {
            if (tinymce.get('tinyMCEEditorNameReport')) {
                tinymce.get('tinyMCEEditorNameReport').remove();
            }
            currentTargetFieldNameReport = null;
            pendingNameReportValue = null;
        });

        // Save editor Name Report content
        $('#saveEditorNameReport').on('click', function() {
            if (currentTargetFieldNameReport && tinymce.get('tinyMCEEditorNameReport')) {
                const editorContent = tinymce.get('tinyMCEEditorNameReport').getContent();
                const convertedContent = convertFromTinyMCE(editorContent);

                currentTargetFieldNameReport.val(convertedContent);
                const targetId = currentTargetFieldNameReport.attr('id');
                updatePreview(targetId, convertedContent);

                console.log('Saved Name Report:', convertedContent);
                $('#editorModalNameReport').modal('hide');
            }
        });


        // Inisialisasi preview semua field TinyMCE jika ada nilai
        var nameReportValue = $('#name_report').val();
        if (nameReportValue) {
            updatePreview('name_report', nameReportValue);
        }

        var equalValue = $('#equal_no_sub').val();
        if (equalValue) {
            updatePreview('equal_no_sub', equalValue);
        }

        var nilaiBakuMutuValue = $('#nilai_baku_mutu_no_sub').val();
        if (nilaiBakuMutuValue) {
            updatePreview('nilai_baku_mutu_no_sub', nilaiBakuMutuValue);
        }

        // ============================================
        // Handle Dropdown untuk Method.is_option = 1
        // ============================================

        // Store methods data for quick access
        var methodsData = @json($methodsDataForJS);

        // Function to update equal field based on method selection
        function updateEqualFieldFromMethod(methodId) {
            var method = methodsData.find(m => m.id === methodId);
            var $equalContainer = $('.form-group').has('#equal_no_sub').parent();
            var $equalInput = $('#equal_no_sub');
            var $equalDropdown = $('#equal_dropdown_no_sub');
            var $equalEditorBtn = $('.equal-editor-btn');
            var currentEqualValue = $equalInput.val();

            if (method && method.is_option == 1 && method.option) {
                var options = method.option.split(',').map(opt => opt.trim()).filter(opt => opt);

                if (options.length > 0) {
                    // Remove existing dropdown or editor button
                    $equalDropdown.remove();
                    $equalEditorBtn.remove();

                    // Create dropdown
                    var dropdownHtml =
                        '<select class="form-control equal-dropdown" id="equal_dropdown_no_sub" name="equal_dropdown_no_sub">';
                    dropdownHtml += '<option value="">- Kosongkan -</option>';
                    options.forEach(function(opt) {
                        var selected = currentEqualValue == opt ? 'selected' : '';
                        dropdownHtml += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                    dropdownHtml += '</select>';

                    // Insert dropdown after hidden input
                    $equalInput.after(dropdownHtml);

                    // Set initial value
                    if (currentEqualValue) {
                        $('#equal_dropdown_no_sub').val(currentEqualValue);
                    }
                } else {
                    // No options, show editor button
                    $equalDropdown.remove();
                    if ($equalEditorBtn.length === 0) {
                        var editorBtnHtml =
                            '<button type="button" class="btn btn-sm btn-success open-editor-equal equal-editor-btn" data-target="equal_no_sub">' +
                            '<i class="fa fa-equals mr-1"></i>Edit Nilai Sama Dengan</button>';
                        $equalInput.after(editorBtnHtml);
                    }
                }
            } else {
                // Method doesn't have is_option = 1, show editor button
                $equalDropdown.remove();
                if ($equalEditorBtn.length === 0) {
                    var editorBtnHtml =
                        '<button type="button" class="btn btn-sm btn-success open-editor-equal equal-editor-btn" data-target="equal_no_sub">' +
                        '<i class="fa fa-equals mr-1"></i>Edit Nilai Sama Dengan</button>';
                    $equalInput.after(editorBtnHtml);
                }
            }
        }

        // Handle method_id change
        $('#method_id').on('change', function() {
            var selectedMethodId = $(this).val();
            if (selectedMethodId) {
                updateEqualFieldFromMethod(selectedMethodId);
            }
        });

        // Handle dropdown change for equal field
        $(document).on('change', '#equal_dropdown_no_sub', function() {
            var selectedValue = $(this).val();
            $('#equal_no_sub').val(selectedValue);
            updatePreview('equal_no_sub', selectedValue);
        });

        // Sync dropdown to hidden input before form submit
        $('.btn-simpan').on('click', function(e) {
            var $equalDropdown = $('#equal_dropdown_no_sub');
            if ($equalDropdown.length) {
                $('#equal_no_sub').val($equalDropdown.val());
            }
        });

        // Initialize on page load - only update if dropdown doesn't exist (method changed scenario)
        // If dropdown already exists from PHP (method.is_option = 1), keep it as is
        $(document).ready(function() {
            // Only update if dropdown doesn't exist but method is selected
            // This handles the case where method_id changes but dropdown needs to be created
            var initialMethodId = $('#method_id').val();
            var $existingDropdown = $('#equal_dropdown_no_sub');
            if (initialMethodId && $existingDropdown.length === 0) {
                // No dropdown exists, check if we need to create one
                var method = methodsData.find(m => m.id === initialMethodId);
                if (method && method.is_option == 1 && method.option) {
                    updateEqualFieldFromMethod(initialMethodId);
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
@endsection
