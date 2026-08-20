@extends('masterweb::template.admin.layout')
@section('title')
    Baku Mutu Lab.{{ $lab }} Management
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-baku-mutu-' . $lab_link) }}"> Baku Mutu
                                        Lab.{{ $lab }}</a></li>
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
            <form enctype="multipart/form-data" class="forms-sample" id="form"
                action="{{ route('elits-baku-mutu-klinik.update', $item->id_baku_mutu) }}" method="POST" novalidate>

                @csrf
                @method('PUT')

                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                <input type="hidden" class="form-control" name="lab_id" id="lab_id" value="{{ $item->lab_id }}"
                    readonly>

                <!-- Informasi Dasar Parameter -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Informasi Dasar Parameter</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="parameter_jenis_klinik_id">
                                <i class="fa fa-flask mr-1"></i>Parameter Jenis Klinik
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select class="form-control" name="parameter_jenis_klinik_id" id="parameter_jenis_klinik_id" style="width: 100%">
                                        <option value="{{ $item->parameter_jenis_klinik_id }}" selected>
                                            {{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-success flex-shrink-0"
                                    data-toggle="modal" data-target="#modalCreateParameterJenisKlinik"
                                    title="Buat parameter jenis baru jika tidak tersedia di daftar">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                Tidak menemukan jenis? Klik <strong><i class="fa fa-plus"></i></strong> untuk membuat baru.
                            </small>
                        </div>

                        <div class="form-group display-parameter-satuan-klinik-id">
                            <label for="parameter_satuan_klinik_id">
                                <i class="fa fa-list mr-1"></i>Parameter Satuan Klinik
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select class="form-control" name="parameter_satuan_klinik_id" id="parameter_satuan_klinik_id" style="width: 100%">
                                        <option value="{{ $item->parameter_satuan_klinik_id }}" selected>
                                            {{ $item->parametersatuanklinik->name_parameter_satuan_klinik }}</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-success flex-shrink-0"
                                    data-toggle="modal" data-target="#modalCreateParameterSatuanKlinik"
                                    title="Buat parameter satuan baru jika tidak tersedia di daftar">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fa fa-info-circle mr-1"></i>
                                Tidak menemukan satuan parameter? Klik <strong><i class="fa fa-plus"></i></strong> untuk membuat baru.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="library_id">
                                <i class="fa fa-book mr-1"></i>Acuan Baku Mutu
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select class="form-control" name="library_id" id="library_id" style="width: 100%">
                                        <option value="{{ $item->library_id }}" selected>
                                            {{ $item->library->title_library }}</option>
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
                            <label for="unit_id">
                                <i class="fa fa-ruler mr-1"></i>Satuan
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    @if ($item->unit_id)
                                        <select class="form-control unit_id" name="unit_id" id="unit_id" style="width: 100%">
                                            <option value="{{ $item->unit_id }}" selected>
                                                {{ $item->unit->shortname_unit }}</option>
                                        </select>
                                    @else
                                        <select class="form-control unit_id" name="unit_id" id="unit_id" style="width: 100%">
                                            <option value=""></option>
                                        </select>
                                    @endif
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

                        <!-- Info Format Angka -->
                        <div class="alert alert-info mb-0">
                            <i class="fa fa-info-circle mr-2"></i>
                            <strong>Format Angka:</strong>
                            @if($numberFormat === 'id')
                                <span class="badge badge-primary">Indonesia</span>
                                <small class="d-block mt-1">
                                    Pemisah desimal: <strong>koma (,)</strong> | Pemisah ribuan: <strong>titik (.)</strong>
                                    <br>Contoh: 1.234,56
                                </small>
                            @else
                                <span class="badge badge-success">Internasional</span>
                                <small class="d-block mt-1">
                                    Pemisah desimal: <strong>titik (.)</strong> | Pemisah ribuan: <strong>koma (,)</strong>
                                    <br>Contoh: 1,234.56
                                </small>
                            @endif
                            <small class="text-muted d-block mt-2">
                                <i class="fa fa-lightbulb"></i> Format ini ditentukan dari setting Parameter Satuan dan akan berlaku untuk input hasil analis juga.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Data Khusus dipindah ke dalam tab Satuan --}}

                <input type="hidden" class="form-control" name="is_sub_parameter_satuan_baku_mutu"
                    id="is_sub_parameter_satuan_baku_mutu" value="{{ $item->is_sub_parameter_satuan_baku_mutu }}" readonly>

                <!-- Konfigurasi Baku Mutu -->
                <div class="parameter-satuan-non-sub" {!! count($data_parameter_sub_satuan_baku_mutu) > 0 ? 'style="display: none"' : '' !!}>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <ul class="nav nav-tabs card-header-tabs" id="input-mode-tabs" role="tablist">
                                <li class="nav-item">
                                    <a href="#" class="nav-link active" data-mode="single">
                                        <i class="fa fa-edit mr-1"></i>Satuan
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link" data-mode="bulk">
                                        <i class="fa fa-table mr-1"></i>Massal
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            @if(($item->is_haji ?? 0) == 0)
                            <div class="form-group single-section">
                                <label for="is_khusus_baku_mutu">
                                    <i class="fa fa-filter mr-1"></i>Tipe Data
                                </label>
                                <select class="form-control" name="is_khusus_baku_mutu" id="is_khusus_baku_mutu">
                                    <option value="0" {{ $item->is_khusus_baku_mutu == '0' ? 'selected' : '' }}>
                                        <i class="fa fa-globe"></i> General
                                    </option>
                                    <option value="1" {{ $item->is_khusus_baku_mutu == '1' ? 'selected' : '' }}>
                                        <i class="fa fa-user"></i> Specific
                                    </option>
                                </select>
                                <small class="form-text text-muted">General: berlaku untuk semua kondisi | Specific:
                                    berdasarkan umur dan jenis kelamin</small>
                            </div>
                            @else
                            <!-- Untuk baku mutu haji, tidak ada opsi Specific -->
                            <input type="hidden" name="is_khusus_baku_mutu" id="is_khusus_baku_mutu" value="0">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle mr-2"></i>
                                <strong>Baku Mutu Haji:</strong> Baku mutu haji menggunakan tipe General (tidak spesifik terhadap usia dan jenis kelamin)
                            </div>
                            @endif

                            <!-- Tipe Baku Mutu - Hanya Informasi (Tidak bisa diubah) -->
                            <div class="form-group">
                                <label>
                                    <i class="fa fa-kaaba mr-1"></i>Tipe Baku Mutu
                                </label>
                                <div>
                                    @if(($item->is_haji ?? 0) == 1)
                                        <span class="badge badge-warning badge-lg">
                                            <i class="fa fa-kaaba mr-1"></i>Baku Mutu Haji
                                        </span>
                                    @else
                                        <span class="badge badge-info badge-lg">
                                            <i class="fa fa-flask mr-1"></i>Baku Mutu Non-Haji
                                        </span>
                                    @endif
                                </div>
                                <small class="form-text text-muted">Tipe baku mutu tidak dapat diubah setelah dibuat</small>
                                <input type="hidden" name="is_haji" id="is_haji" value="{{ $item->is_haji ?? 0 }}">
                            </div>

                            @php
                                // Untuk baku mutu haji, selalu sembunyikan field specific
                                if (($item->is_haji ?? 0) == 1) {
                                    $status_display = 'style="display: none !important;"';
                                } else if ($item->is_khusus_baku_mutu == '1') {
                                    $status_display = '';
                                } else {
                                    $status_display = 'style="display: none"';
                                }
                            @endphp

                            <div class="card border-info display-data-khusus mb-3 single-section" {!! $status_display !!}>
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fa fa-sliders mr-2"></i>Data Specific</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="is_khusus">
                                                    <i class="fa fa-calendar-minus mr-1"></i>Umur Minimal (tahun)
                                                </label>
                                                <input type="number" class="form-control" name="minimal_umur_baku_mutu"
                                                    id="minimal_umur_baku_mutu" placeholder="Contoh: 18"
                                                    value="{{ $item->minimal_umur_baku_mutu ?? old('minimal_umur_baku_mutu') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="is_khusus">
                                                    <i class="fa fa-calendar-plus mr-1"></i>Umur Maksimal (tahun)
                                                </label>
                                                <input type="number" class="form-control" name="maksimal_umur_baku_mutu"
                                                    id="maksimal_umur_baku_mutu" placeholder="Contoh: 65"
                                                    value="{{ $item->maksimal_umur_baku_mutu ?? old('maksimal_umur_baku_mutu') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender_baku_mutu">
                                            <i class="fa fa-venus-mars mr-1"></i>Jenis Kelamin
                                            <span class="badge badge-danger ml-1">Wajib</span>
                                        </label>
                                        <select class="form-control" name="gender_baku_mutu" id="gender_baku_mutu">
                                            <option value="A" {{ $item->gender_baku_mutu == 'A' ? 'selected' : '' }}>
                                                <i class="fa fa-users"></i> Semua gender sama
                                            </option>
                                            <option value="L" {{ $item->gender_baku_mutu == 'L' ? 'selected' : '' }}>
                                                <i class="fa fa-mars"></i> Laki-laki
                                            </option>
                                            <option value="P" {{ $item->gender_baku_mutu == 'P' ? 'selected' : '' }}>
                                                <i class="fa fa-venus"></i> Perempuan
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="kesimpulan_baku_mutu">
                                            <i class="fa fa-comment-alt mr-1"></i>Kesimpulan Khusus
                                        </label>
                                        <textarea class="form-control" name="kesimpulan_baku_mutu" id="kesimpulan_baku_mutu" rows="3"
                                            placeholder="Isi kesimpulan khusus (opsional)">{{ $item->kesimpulan_baku_mutu ?? old('kesimpulan_baku_mutu') }}</textarea>
                                    </div>
                                    <div class="form-group form-check">
                                        <input type="checkbox" class="form-check-input" id="is_normal" name="is_normal"
                                            value="1"
                                            {{ isset($item->is_normal) && $item->is_normal ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_normal">
                                            <i class="fa fa-check-circle mr-1"></i>Tandai sebagai batas normal
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Nilai Baku Mutu -->
                            <div class="border rounded p-3 mb-3 single-section" style="background-color: #f8f9fa;">
                                <h6 class="mb-3"><i class="fa fa-chart-line mr-2"></i>Nilai Baku Mutu</h6>

                                <div class="form-group">
                                    <label for="min">
                                        <i class="fa fa-arrow-down mr-1"></i>Min (Minimum)
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Gunakan titik (.) untuk desimal. Contoh: 4.0 atau kosongkan jika tidak ada"></i>
                                    </label>
                                    <input type="text" class="form-control" id="min" name="min"
                                        value="{{ $item->min ?? '' }}" placeholder="Contoh: 4.0">
                                </div>

                                <div class="form-group">
                                    <label for="max">
                                        <i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Gunakan titik (.) untuk desimal. Contoh: 6.5 atau kosongkan jika tidak ada"></i>
                                    </label>
                                    <input type="text" class="form-control" id="max" name="max"
                                        value="{{ $item->max ?? '' }}" placeholder="Contoh: 6.5">
                                </div>

                                <div class="form-group">
                                    <label for="equal">
                                        <i class="fa fa-equals mr-1"></i>Nilai Sama Dengan
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Untuk nilai non-range seperti Positif/Negatif. Kosongkan jika menggunakan range Min-Max"></i>
                                    </label>
                                    @php
                                        // Cek apakah ParameterSatuanKlinik memiliki is_option = 1
                                        $isOption = false;
                                        $options = [];
                                        $equalValue = isset($item->equal) ? rubahNilaikeForm($item->equal, $numberFormat) : '';

                                        if (
                                            $item->parametersatuanklinik &&
                                            $item->parametersatuanklinik->is_option == 1
                                        ) {
                                            $isOption = true;
                                            if (!empty($item->parametersatuanklinik->option)) {
                                                $options = array_map(
                                                    'trim',
                                                    explode(',', $item->parametersatuanklinik->option),
                                                );
                                                $options = array_filter($options);
                                            }
                                        }
                                    @endphp

                                    <input type="text" class="form-control equal-input" id="equal" name="equal"
                                        value="{{ $equalValue }}" placeholder="Contoh: Positif"
                                        style="display: none;">

                                    @if ($isOption && count($options) > 0)
                                        <!-- Dropdown untuk is_option = 1 -->
                                        <select class="form-control equal-dropdown" id="equal_dropdown" name="equal">
                                            <option value="">- Kosongkan -</option>
                                            @foreach ($options as $opt)
                                                <option value="{{ $opt }}"
                                                    {{ $equalValue == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button"
                                            class="btn btn-sm btn-success open-editor-equal equal-editor-btn mt-2"
                                            data-target="equal" style="display: none;">
                                            <i class="fa fa-equals mr-1"></i>
                                            Edit Nilai Sama Dengan
                                        </button>
                                        <div class="mt-2 p-3 border rounded"
                                            style="background-color: #fff; min-height: 50px; display: none;"
                                            id="preview_equal_container">
                                            <small class="text-muted"><i
                                                    class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                            <div id="preview_equal" style="margin-top: 5px;">
                                                {!! isset($item->equal) && $item->equal ? rubahNilaikeHtml(rubahNilaikeForm($item->equal, $numberFormat)) : '-' !!}
                                            </div>
                                        </div>
                                    @else
                                        <!-- TinyMCE Editor untuk is_option = 0 -->
                                        <button type="button"
                                            class="btn btn-sm btn-success open-editor-equal equal-editor-btn"
                                            data-target="equal">
                                            <i class="fa fa-equals mr-1"></i>
                                            Edit Nilai Sama Dengan
                                        </button>
                                        <div class="mt-2 p-3 border rounded"
                                            style="background-color: #fff; min-height: 50px;"
                                            id="preview_equal_container">
                                            <small class="text-muted"><i
                                                    class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                            <div id="preview_equal" style="margin-top: 5px;">
                                                {!! isset($item->equal) && $item->equal ? rubahNilaikeHtml(rubahNilaikeForm($item->equal, $numberFormat)) : '-' !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group single-section">
                            <label for="nilai_baku_mutu">
                                <i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan
                            </label>
                            <input type="text" class="form-control nilai-input" id="nilai_baku_mutu"
                                name="nilai_baku_mutu" placeholder="Contoh: 4.0 - 6.5 atau Negatif"
                                value="{!! rubahNilaikeForm($item->nilai_baku_mutu ?? old('nilai_baku_mutu'), $numberFormat) !!}" style="display: none;">
                            <button type="button" class="btn btn-sm btn-primary open-editor-nilai nilai-editor-btn"
                                data-target="nilai_baku_mutu">
                                <i class="fa fa-file-alt mr-1"></i>
                                Edit Nilai Baku Mutu di Laporan
                            </button>
                            <div class="mt-2 p-3 border rounded" style="background-color: #fff; min-height: 50px;">
                                <small class="text-muted"><i
                                        class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                <div id="preview_nilai_baku_mutu" style="margin-top: 5px;">
                                    {!! isset($item->nilai_baku_mutu) && $item->nilai_baku_mutu
                                        ? rubahNilaikeHtml(rubahNilaikeForm($item->nilai_baku_mutu, $numberFormat))
                                        : '-' !!}
                                </div>
                            </div>
                            <small class="form-text text-muted">Teks yang akan muncul di laporan hasil</small>
                        </div>
                    </div>

                    <!-- Tools Helper -->
                    <div class="form-group single-section">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" id="toggle-bm-builder">
                                <i class="fa fa-magic mr-1"></i>Builder Otomatis
                            </button>
                            <button type="button" class="btn btn-outline-info" id="toggle-bulk" style="display:none">
                                <i class="fa fa-table mr-1"></i>Input Massal
                            </button>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fa fa-lightbulb"></i> Gunakan Builder untuk membuat nilai baku mutu dengan
                            mudah atau Input Massal untuk banyak data sekaligus
                        </small>
                    </div>
                </div>
        </div>

        <div class="card single-section" id="bm-builder-card" style="display: none">
            <div class="card-body">
                <div id="bm-builder-rows"></div>
                <button type="button" class="btn btn-primary btn-sm" id="bm-add-row">Tambah baris</button>
                <button type="button" class="btn btn-outline-primary btn-sm ml-1" id="bm-import-btn">Import
                    dari Excel</button>
                <div class="mt-2" id="bm-import-area" style="display:none">
                    <label class="mb-1">Tempelkan data dari Excel/Spreadsheet (2 kolom: Label[TAB]Nilai).
                        Satu baris per baris.</label>
                    <textarea class="form-control" id="bm-import" rows="3"
                        placeholder="Contoh:\nNormal\t4.0 - 5.6\nPrediabetes\t5.7 - 6.4\nDiabetes\t≥ 6.5"></textarea>
                    <div class="mt-2">
                        <button type="button" class="btn btn-secondary btn-sm" id="bm-import-apply">Tambahkan</button>
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <label>Preview</label>
                    <div class="p-2 border" id="bm-builder-preview" style="min-height:38px"></div>
                </div>
                <button type="button" class="btn btn-success btn-sm" id="bm-apply">Gunakan ke
                    field</button>
            </div>
        </div>

        <div class="card mt-3 border-primary" id="bulk-card" style="display:none">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fa fa-table mr-2"></i>Input Massal Baku Mutu
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fa fa-info-circle mr-2"></i>
                    <strong>Petunjuk:</strong> Gunakan mode input massal untuk menambahkan banyak data baku mutu
                    sekaligus. Data yang disimpan akan menjadi baris baru tanpa mengubah data yang sudah ada.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 100px;">
                                    <i class="fa fa-filter mr-1"></i>Tipe
                                    <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                        title="General atau Specific"></i>
                                </th>
                                <th style="width: 120px;">
                                    <i class="fa fa-venus-mars mr-1"></i>Gender
                                </th>
                                <th style="width: 110px;">
                                    <i class="fa fa-calendar-minus mr-1"></i>Umur Min
                                </th>
                                <th style="width: 110px;">
                                    <i class="fa fa-calendar-plus mr-1"></i>Umur Max
                                </th>
                                <th style="width: 200px;">
                                    <i class="fa fa-comment-alt mr-1"></i>Kesimpulan
                                </th>
                                <th>
                                    <i class="fa fa-file-alt mr-1"></i>Nilai di Laporan
                                </th>
                                <th style="width: 100px;">
                                    <i class="fa fa-arrow-down mr-1"></i>Min
                                </th>
                                <th style="width: 100px;">
                                    <i class="fa fa-arrow-up mr-1"></i>Max
                                </th>
                                <th style="width: 130px;">
                                    <i class="fa fa-equals mr-1"></i>Equal
                                </th>
                                <th style="width: 90px;" class="text-center">
                                    <i class="fa fa-check-circle mr-1"></i>Normal?
                                </th>
                                <th style="width: 60px;" class="text-center">
                                    <i class="fa fa-cog"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="bulk-rows"></tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary btn-sm" id="bulk-add-row">
                        <i class="fa fa-plus mr-1"></i>Tambah Baris
                    </button>
                    <button type="button" class="btn btn-success btn-sm ml-2" id="bulk-save">
                        <i class="fa fa-save mr-1"></i>Simpan Massal
                    </button>
                    <small class="text-muted ml-3">
                        <i class="fa fa-lightbulb"></i> Simpan massal akan menambah data baru tanpa mengubah
                        data yang sudah ada
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="parameter-satuan-with-sub" {!! count($data_parameter_sub_satuan_baku_mutu) > 0 ? '' : 'style="display: none"' !!}>
        @if (count($data_parameter_sub_satuan_baku_mutu) > 0)
            @foreach ($data_parameter_sub_satuan_baku_mutu as $key_dpssbm => $item_dpssbm)
                <hr>

                <h6 class="card-title">
                    <strong>{{ $item_dpssbm->parametersubsatuanklinik->name_parameter_sub_satuan_klinik }}</strong>
                </h6>

                <input type="hidden" class="form-control"
                    id="parameter_sub_satuan_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                    name="parameter_sub_satuan_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]"
                    value="{{ $item_dpssbm->parameter_sub_satuan_baku_mutu_detail_parameter_klinik }}" readonly>

                <div class="form-group">
                    <label for="unit_id_baku_mutu_detail_parameter_klinik">Satuan</label>

                    @if ($item_dpssbm->unit_id_baku_mutu_detail_parameter_klinik != null)
                        <select class="form-control unit_id"
                            id="unit_id_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                            name="unit_id_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]">
                            <option value="{{ $item_dpssbm->unit_id_baku_mutu_detail_parameter_klinik }}">
                                {{ $item_dpssbm->bakumutu->unit->name_unit }}</option>
                        </select>
                    @else
                        <select class="form-control unit_id"
                            id="unit_id_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                            name="unit_id_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]">
                            <option value=""></option>
                        </select>
                    @endif

                </div>

                <div class="form-group">
                    <label for="min">Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila
                            terdapan koma, maka
                            menggunakan .
                            (titik)
                            , apabila tidak ada kosongi)</b></label>

                    <input type="text" class="form-control"
                        id="min_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                        name="min_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]"
                        value="{{ $item_dpssbm->min_baku_mutu_detail_parameter_klinik ?? '' }}"
                        placeholder="Kadar Min Baku Mutu">
                </div>

                <div class="form-group">
                    <label for="max">Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila
                            terdapat koma, maka menggunakan
                            . (titik), apabila tidak ada kosongi)</b></label>

                    <input type="text" class="form-control"
                        id="max_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                        name="max_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]"
                        value="{{ $item_dpssbm->max_baku_mutu_detail_parameter_klinik ?? '' }}"
                        placeholder="Kadar Max Baku Mutu">
                </div>

                <div class="form-group">
                    <label for="equal">Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan
                            berupa range minimal maksimal
                            misal (Negatif atau Positif) maka isi disini, apabila tidak maka
                            kosongi)</b></label>

                    <input type="text" class="form-control"
                        id="equal_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                        name="equal_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]"
                        value="{{ $item_dpssbm->equal_baku_mutu_detail_parameter_klinik ?? '' }}"
                        placeholder="Nilai Harus Sama Dengan">
                </div>

                <div class="form-group">
                    <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan <span style="color: red">*</span></label>

                    <input type="text" class="form-control"
                        id="nilai_baku_mutu_detail_parameter_klinik_{{ $key_dpssbm }}"
                        name="nilai_baku_mutu_detail_parameter_klinik[{{ $key_dpssbm }}]" placeholder="Nilai Baku Mutu"
                        value="{!! rubahNilaikeForm($item_dpssbm->nilai_baku_mutu_detail_parameter_klinik ?? '', $numberFormat) !!}">
                </div>
            @endforeach
        @else
            <div class="parameter-satuan-with-sub-form"></div>
        @endif
    </div>

    <br>

    </form>
    <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
    <button type="button" onclick="document.location='{{ url('/elits-baku-mutu-klinik') }}'"
        class="btn btn-light">Kembali</button>
    </div>
    </div>

    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        // Inisialisasi tooltip
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        function goBack() {
            window.history.back();
        }

        $(function() {
            var CSRF_TOKEN = $('#csrf-token').val();

            $("#parameter_jenis_klinik_id").select2({
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
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih paramater jenis',
                allowClear: true
            });

            $("#parameter_satuan_klinik_id").select2({
                ajax: {
                    url: "{{ route('getParameterSatuanKlinik') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: CSRF_TOKEN,
                            search: params.term, // search term
                            param: $("#parameter_jenis_klinik_id").val()
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih paramater satuan',
                allowClear: true,
            });

            $('#parameter_jenis_klinik_id').on('select2:unselecting', function(e) {
                if ($("#parameter_satuan_klinik_id").hasClass("select2-hidden-accessible")) {
                    $("#parameter_satuan_klinik_id").val('').trigger('change');

                    $('.display-parameter-satuan-klinik-id').hide();
                    $('.parameter-satuan-non-sub').hide();
                    $('.parameter-satuan-with-sub').hide();
                }
            });

            $("#parameter_jenis_klinik_id").on('select2:selecting', function(e) {
                $('.display-parameter-satuan-klinik-id').show();
                $("#parameter_satuan_klinik_id").val('').trigger('change');

                $('.parameter-satuan-non-sub').hide();
                $('.parameter-satuan-with-sub').hide();

                $("#parameter_satuan_klinik_id").select2({
                    ajax: {
                        url: "{{ route('getParameterSatuanKlinik') }}",
                        type: "post",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                _token: CSRF_TOKEN,
                                search: params.term, // search term
                                param: $("#parameter_jenis_klinik_id").val()
                            };
                        },
                        processResults: function(response) {
                            return {
                                results: $.map(response, function(obj) {
                                    return {
                                        id: obj.id,
                                        text: obj.text
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Pilih paramater satuan',
                    allowClear: true,
                });
            })

            $("#library_id").select2({
                ajax: {
                    url: "{{ route('getLibrary') }}",
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
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih library',
                allowClear: true
            });

            $(".unit_id").select2({
                ajax: {
                    url: "{{ route('getDataUnitBySelect') }}",
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
                            results: $.map(response, function(obj) {
                                return {
                                    id: obj.id,
                                    text: obj.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: 'Pilih satuan',
                allowClear: true
            });

            // Jika form haji, sembunyikan field specific dan set is_khusus_baku_mutu = 0
            var isHajiForm = $('#is_haji').val() == '1';
            if (isHajiForm) {
                $('#is_khusus_baku_mutu').val(0);
                $('.display-data-khusus').hide();
            }
            
            $('#is_khusus_baku_mutu').change(function(e) {
                e.preventDefault();
                
                // Jika form haji, jangan tampilkan specific
                if (isHajiForm) {
                    $(this).val(0);
                    $('.display-data-khusus').hide();
                    return;
                }

                if ($('#is_khusus_baku_mutu').val() == 1) {
                    $('.display-data-khusus').show();
                } else {
                    $('.display-data-khusus').hide();
                }
            });
            
            // is_haji tidak bisa diubah lagi di form edit, jadi tidak perlu event handler


            // Helper function untuk convert ke HTML preview
            function sanitizeTableForStorage(html) {
                var v = String(html).replace(/^\s*<p[^>]*>\s*/i, '').replace(/\s*<\/p>\s*$/i, '').trim();
                if (!/<table[\s>]/i.test(v)) {
                    return v;
                }
                var $wrap = $('<div>').html(v);
                $wrap.find('table').each(function() {
                    var $table = $(this);
                    $table.attr('border', '0').addClass('bmu-nilai-table');
                    $table.add($table.find('td, th, tr')).each(function() {
                        var $el = $(this);
                        var style = ($el.attr('style') || '')
                            .replace(/(?:^|;)\s*border[^;]*/gi, '')
                            .replace(/^\s*;+\s*|\s*;+\s*$/g, '')
                            .trim();
                        if (style) {
                            $el.attr('style', style);
                        } else {
                            $el.removeAttr('style');
                        }
                    });
                });
                return $wrap.html();
            }

            var nilaiTinyMCETableOptions = {
                table_default_attributes: { border: '0' },
                table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
                table_style_by_css: true,
                table_cell_default_styles: { padding: '2px 6px' },
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; } table { border-collapse: collapse; width: 100%; } table td, table th { border: 1px dashed #bbb; padding: 2px 6px; }'
            };

            function convertToHTMLPreview(value) {
                if (!value) return '-';

                if (/<table[\s>]/i.test(String(value))) {
                    return value;
                }

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

            // ============================================
            // TinyMCE Integration for Equal Field
            // ============================================

            let currentTargetFieldEqual = null;
            let pendingEqualValue = null;

            // ============================================
            // TinyMCE Integration for Nilai Baku Mutu Field
            // ============================================

            let currentTargetFieldNilai = null;
            let pendingNilaiValue = null;

            // Conversion functions
            function convertToTinyMCE(value) {
                if (!value) return '';
                if (/<table[\s>]/i.test(String(value))) {
                    return sanitizeTableForStorage(value);
                }
                value = value.replace(/≤/g, '&le;');
                value = value.replace(/≥/g, '&ge;');
                value = value.replace(/±/g, '&plusmn;');
                value = value.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                value = value.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                return value;
            }

            function convertFromTinyMCE(value) {
                if (!value) return '';
                if (/<table[\s>]/i.test(String(value))) {
                    return sanitizeTableForStorage(value);
                }
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

            // Update preview box
            function updatePreview(targetId, value) {
                let previewId;
                // Handle different field IDs
                if (targetId === 'nilai_baku_mutu') {
                    previewId = 'preview_nilai_baku_mutu';
                } else if (targetId === 'equal') {
                    previewId = 'preview_equal';
                } else {
                    previewId = 'preview_' + targetId;
                }
                const htmlValue = convertToHTMLPreview(value);
                $('#' + previewId).html(htmlValue || '-');
            }

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
                    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
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

            // Inisialisasi preview equal jika ada nilai
            var equalValue = $('#equal').val();
            if (equalValue) {
                // Update preview equal
                var equalHtml = convertToHTMLPreview(equalValue);
                $('#preview_equal').html(equalHtml || '-');
            }

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

                tinymce.init($.extend({
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
                        'bold italic | superscript subscript | charmap | table | ' +
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist | removeformat | help',
                    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                    setup: function(editor) {
                        editor.on('init', function() {
                            console.log('TinyMCE Nilai initialized, setting content:',
                                pendingNilaiValue);
                            if (pendingNilaiValue) {
                                editor.setContent(pendingNilaiValue);
                            }
                        });
                    }
                }, nilaiTinyMCETableOptions));
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

            // Inisialisasi preview nilai jika ada nilai
            var nilaiValue = $('#nilai_baku_mutu').val();
            if (nilaiValue) {
                // Update preview nilai
                var nilaiHtml = convertToHTMLPreview(nilaiValue);
                $('#preview_nilai_baku_mutu').html(nilaiHtml || '-');
            }

            // Builder UX for nilai_baku_mutu
            function renderRow(index) {
                return '<div class="row mb-2 bm-row" data-index="' + index + '">' +
                    '<div class="col-md-3"><input type="text" class="form-control bm-label" placeholder="Label (mis. Normal)"></div>' +
                    '<div class="col-md-1 text-center">:</div>' +
                    '<div class="col-md-8"><input type="text" class="form-control bm-value" placeholder="Nilai/range (mis. 4.0 - 5.6)"></div>' +
                    '</div>';
            }

            function buildPreview() {
                var html = '';
                $('#bm-builder-rows .bm-row').each(function() {
                    var label = $(this).find('.bm-label').val();
                    var value = $(this).find('.bm-value').val();
                    if (label || value) {
                        html += '<div><strong>' + (label || '') + '</strong> : ' + (value || '') + '</div>';
                    }
                });
                $('#bm-builder-preview').html(html || '<span class="text-muted">Belum ada isi</span>');
            }

            $(document).on('input', '#bm-builder-rows input', buildPreview);

            $('#toggle-bm-builder').on('click', function() {
                $('#bm-builder-card').toggle();
                if ($('#bm-builder-rows .bm-row').length === 0) {
                    for (var i = 0; i < 3; i++) {
                        $('#bm-builder-rows').append(renderRow(i));
                    }
                    buildPreview();
                }
            });

            $('#bm-add-row').on('click', function() {
                var idx = $('#bm-builder-rows .bm-row').length;
                $('#bm-builder-rows').append(renderRow(idx));
            });

            $('#bm-import-btn').on('click', function() {
                $('#bm-import-area').toggle();
            });

            $('#bm-import-apply').on('click', function() {
                var raw = $('#bm-import').val() || '';
                var lines = raw.split(/\r?\n/);
                lines.forEach(function(line) {
                    if (!line.trim()) return;
                    var parts = line.split('\t');
                    var idx = $('#bm-builder-rows .bm-row').length;
                    $('#bm-builder-rows').append(renderRow(idx));
                    var $row = $('#bm-builder-rows .bm-row').last();
                    $row.find('.bm-label').val(parts[0] ? parts[0].trim() : '');
                    $row.find('.bm-value').val(parts[1] ? parts[1].trim() : '');
                });
                buildPreview();
            });

            $('#bm-apply').on('click', function() {
                var html = '';
                $('#bm-builder-rows .bm-row').each(function(idx) {
                    var label = $(this).find('.bm-label').val();
                    var value = $(this).find('.bm-value').val();
                    if (label || value) {
                        html += (idx > 0 ? '\n' : '') + label + ' : ' + value;
                    }
                });
                if (html) {
                    $('#nilai_baku_mutu').val(html);
                    swal({
                        title: 'Done',
                        text: 'Nilai Baku Mutu diisi otomatis.',
                        icon: 'success'
                    });
                }
            });

            // Bulk input (edit)
            function renderBulkRow() {
                return '<tr class="bulk-row">' +
                    '<td><select class="form-control form-control-sm bulk-specific">' +
                    '<option value="0">General</option>' +
                    '<option value="1">Specific</option>' +
                    '</select></td>' +
                    '<td><select class="form-control form-control-sm bulk-gender">' +
                    '<option value="">-</option>' +
                    '<option value="L">Laki-laki</option>' +
                    '<option value="P">Perempuan</option>' +
                    '</select></td>' +
                    '<td><input type="number" class="form-control form-control-sm bulk-umur-min" placeholder="18"></td>' +
                    '<td><input type="number" class="form-control form-control-sm bulk-umur-max" placeholder="65"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bulk-kesimpulan" placeholder="Opsional"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bulk-nilai" placeholder="Contoh: 4.0 - 6.5"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bulk-min" placeholder="4.0"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bulk-max" placeholder="6.5"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bulk-equal" placeholder="Positif"></td>' +
                    '<td class="text-center"><input type="checkbox" class="bulk-is-normal" value="1" title="Tandai sebagai normal"></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-danger bulk-remove" title="Hapus baris">' +
                    '<i class="fa fa-trash"></i>' +
                    '</button></td>' +
                    '</tr>';
            }

            $('#toggle-bulk').on('click', function() {
                $('#bulk-card').toggle();
                if ($('#bulk-rows .bulk-row').length === 0) {
                    for (var i = 0; i < 2; i++) {
                        $('#bulk-rows').append(renderBulkRow());
                    }
                    // Inisialisasi tooltip untuk baris baru
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });

            $('#bulk-add-row').on('click', function() {
                $('#bulk-rows').append(renderBulkRow());
                // Inisialisasi tooltip untuk baris baru
                $('[data-toggle="tooltip"]').tooltip();
            });

            $(document).on('click', '.bulk-remove', function() {
                $(this).closest('tr').remove();
            });

            function collectBulkPayload() {
                var rows = [];
                $('#bulk-rows .bulk-row').each(function() {
                    var row = {
                        is_khusus_baku_mutu: $(this).find('.bulk-specific').val(),
                        gender_baku_mutu: $(this).find('.bulk-gender').val() || null,
                        minimal_umur_baku_mutu: $(this).find('.bulk-umur-min').val() || null,
                        maksimal_umur_baku_mutu: $(this).find('.bulk-umur-max').val() || null,
                        kesimpulan_baku_mutu: $(this).find('.bulk-kesimpulan').val() || null,
                        nilai_baku_mutu: $(this).find('.bulk-nilai').val() || null,
                        min: $(this).find('.bulk-min').val() || null,
                        max: $(this).find('.bulk-max').val() || null,
                        equal: $(this).find('.bulk-equal').val() || null,
                        is_normal: $(this).find('.bulk-is-normal').is(':checked') ? 1 : 0
                    };
                    if (row.nilai_baku_mutu || row.min || row.max || row.equal) {
                        rows.push(row);
                    }
                });
                return rows;
            }

            $('#bulk-save').on('click', function() {
                syncOptionBeforeSubmit();
                var bulkRows = collectBulkPayload();
                if (bulkRows.length === 0) {
                    return swal('Info', 'Tidak ada baris yang diisi.', 'info');
                }
                $.ajax({
                    url: "{{ route('elits-baku-mutu-klinik.store') }}",
                    method: 'POST',
                    data: {
                        _token: $('#csrf-token').val(),
                        lab_id: $('#lab_id').val(),
                        parameter_jenis_klinik_id: $('#parameter_jenis_klinik_id').val(),
                        parameter_satuan_klinik_id: $('#parameter_satuan_klinik_id').val(),
                        unit_id: $('#unit_id').val(),
                        library_id: $('#library_id').val(),
                        bulk_rows: JSON.stringify(bulkRows)
                    },
                    success: function(resp) {
                        if (resp.status) {
                            swal('Success', resp.pesan, 'success');
                        } else {
                            swal('Error', resp.pesan || 'Gagal menyimpan massal', 'error');
                        }
                    },
                    error: function() {
                        swal('Error', 'Gagal menyimpan massal', 'error');
                    }
                });
            });

            // Tabs behavior (default single)
            $('#input-mode-tabs .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#input-mode-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                var mode = $(this).data('mode');
                if (mode === 'bulk') {
                    $('.single-section').hide();
                    $('#bm-builder-card').hide();
                    $('#bulk-card').show();
                    if ($('#bulk-rows .bulk-row').length === 0) {
                        for (var i = 0; i < 2; i++) {
                            $('#bulk-rows').append(renderBulkRow());
                        }
                    }
                } else {
                    $('.single-section').show();
                    $('#bulk-card').hide();
                }
            });

            // Function untuk update equal field berdasarkan ParameterSatuanKlinik
            function updateEqualFieldFromParameterSatuanKlinik(parameterSatuanKlinikId) {
                if (!parameterSatuanKlinikId) {
                    // Jika tidak ada parameter satuan, tampilkan TinyMCE editor
                    $('.equal-dropdown').hide().removeAttr('name');
                    $('.equal-editor-btn').show();
                    $('#preview_equal_container').show();
                    return;
                }

                $.ajax({
                    url: "{{ route('getParameterSatuanKlinikDetail') }}",
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        id: parameterSatuanKlinikId
                    },
                    success: function(response) {
                        if (response.status && response.data) {
                            var isOption = response.data.is_option == 1;
                            var options = response.data.options || [];

                            if (isOption && options.length > 0) {
                                // Tampilkan dropdown
                                var $dropdown = $('.equal-dropdown');
                                if ($dropdown.length === 0) {
                                    // Buat dropdown jika belum ada
                                    var dropdownHtml =
                                        '<select class="form-control equal-dropdown" id="equal_dropdown" name="equal">' +
                                        '<option value="">- Kosongkan -</option>';
                                    options.forEach(function(opt) {
                                        dropdownHtml += '<option value="' + opt + '">' + opt +
                                            '</option>';
                                    });
                                    dropdownHtml += '</select>';
                                    $('.equal-input').after(dropdownHtml);
                                } else {
                                    // Update dropdown yang sudah ada
                                    $dropdown.empty();
                                    $dropdown.append('<option value="">- Kosongkan -</option>');
                                    options.forEach(function(opt) {
                                        $dropdown.append('<option value="' + opt + '">' + opt +
                                            '</option>');
                                    });
                                }

                                // Set nilai jika sudah ada
                                var currentValue = $('#equal').val();
                                if (currentValue && options.includes(currentValue)) {
                                    $('.equal-dropdown').val(currentValue);
                                }

                                $('.equal-dropdown').show();
                                $('.equal-editor-btn').hide();
                                $('#preview_equal_container').hide();
                            } else {
                                // Tampilkan TinyMCE editor
                                $('.equal-dropdown').hide().removeAttr('name');
                                $('.equal-editor-btn').show();
                                $('#preview_equal_container').show();
                            }
                        }
                    },
                    error: function() {
                        // Default: tampilkan TinyMCE editor
                        $('.equal-dropdown').hide().removeAttr('name');
                        $('.equal-editor-btn').show();
                        $('#preview_equal_container').show();
                    }
                });
            }

            // Sync dropdown value ke input saat dropdown berubah
            $(document).on('change', '.equal-dropdown', function() {
                $('#equal').val($(this).val());
                // Update preview
                var selectedValue = $(this).val();
                if (selectedValue) {
                    $('#preview_equal').html(selectedValue);
                } else {
                    $('#preview_equal').html('-');
                }
            });

            // Inisialisasi saat page load jika parameter_satuan_klinik_id sudah ada
            $(document).ready(function() {
                var initialParameterSatuan = $('#parameter_satuan_klinik_id').val();
                if (initialParameterSatuan) {
                    updateEqualFieldFromParameterSatuanKlinik(initialParameterSatuan);
                }
            });

            //   logic untuk mendapatkan parameter satuan yang memiliki sub dan non-sub
            $('#parameter_satuan_klinik_id').change(function() {

                var parameter_jenis = $('#parameter_jenis_klinik_id').val();
                var parameter_satuan = $('#parameter_satuan_klinik_id').val();

                // Update equal field berdasarkan ParameterSatuanKlinik yang dipilih
                updateEqualFieldFromParameterSatuanKlinik(parameter_satuan);

                if (parameter_satuan !== null && parameter_satuan !== '') {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('checkBakuMutuSubParameterSatuan') }}",
                        data: {
                            _token: CSRF_TOKEN,
                            parameter_jenis: parameter_jenis,
                            parameter_satuan: parameter_satuan,
                        },
                        dataType: "JSON",
                        success: function(response) {
                            if (response.status == true) {
                                $('.parameter-satuan-non-sub').hide();
                                $('.parameter-satuan-with-sub').show();

                                $('#is_sub_parameter_satuan_baku_mutu').val(1);

                                // forloop sub parameter satuan
                                var sub_parameter_len = response.data_sub.length;
                                var card_html = '';

                                for (let i = 0; i < sub_parameter_len; i++) {
                                    card_html += '<hr>';

                                    card_html += '<h6 class="card-title"><strong>' + response
                                        .data_sub[i]
                                        .name_parameter_sub_satuan_klinik + '</strong></h6>';

                                    card_html +=
                                        '<input type="hidden" class="form-control" id="parameter_sub_satuan_baku_mutu_detail_parameter_klinik_' +
                                        i +
                                        '" name="parameter_sub_satuan_baku_mutu_detail_parameter_klinik[' +
                                        i +
                                        ']" value="' + response.data_sub[i]
                                        .id_parameter_sub_satuan_klinik + '" readonly>' +
                                        '</div>';

                                    card_html += '<div class="form-group">' +
                                        '<label for="unit_id_baku_mutu_detail_parameter_klinik">Satuan</label>' +
                                        '<select class="form-control unit_id" id="unit_id_baku_mutu_detail_parameter_klinik_' +
                                        i +
                                        '" name="unit_id_baku_mutu_detail_parameter_klinik[' +
                                        i +
                                        ']">' +
                                        '<option value=""></option>' +
                                        '</select>' +
                                        '</div>';

                                    card_html += '<div class="form-group">' +
                                        '<label for="min">Kadar Min Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapan koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>' +
                                        '<input type="text" class="form-control" id="min_baku_mutu_detail_parameter_klinik_' +
                                        i +
                                        '" name="min_baku_mutu_detail_parameter_klinik[' + i +
                                        ']" placeholder="Kadar Min Baku Mutu">' +
                                        '</div>';

                                    card_html += '<div class="form-group">' +
                                        '<label for="max">Kadar Max Baku Mutu <br><b>(Masukkan berupa angka dan apabila terdapat koma, maka menggunakan . (titik), apabila tidak ada kosongi)</b></label>' +
                                        '<input type="text" class="form-control" id="max_baku_mutu_detail_parameter_klinik_' +
                                        i +
                                        '" name="max_baku_mutu_detail_parameter_klinik[' + i +
                                        ']" placeholder="Kadar Max Baku Mutu">' +
                                        '</div>';

                                    card_html += '<div class="form-group">' +
                                        '<label for="equal">Nilai Harus Sama Dengan <br><b>(Apabila nilai baku mutu bukan berupa range minimal maksimal misal (Negatif atau Positif) maka isi disini, apabila tidak maka kosongi)</b></label>' +
                                        '<input type="text" class="form-control" id="equal_baku_mutu_detail_parameter_klinik_' +
                                        i +
                                        '" name="equal_baku_mutu_detail_parameter_klinik[' + i +
                                        ']" placeholder="Nilai Harus Sama Dengan">' +
                                        '</div>';

                                    card_html += '<div class="form-group">' +
                                        '<label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan <span style="color: red">*</span></label>' +
                                        '<input type="text" class="form-control" id="nilai_baku_mutu_detail_parameter_klinik_' +
                                        i +
                                        '" name="nilai_baku_mutu_detail_parameter_klinik[' + i +
                                        ']" placeholder="Nilai Baku Mutu" value="{{ old('nilai_baku_mutu_detail_parameter_klinik') }}" >' +
                                        '</div>';
                                }

                                $('.parameter-satuan-with-sub .parameter-satuan-with-sub-form')
                                    .html(card_html);

                                $(".unit_id").select2({
                                    ajax: {
                                        url: "{{ route('getDataUnitBySelect') }}",
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
                                                results: $.map(response, function(
                                                    obj) {
                                                    return {
                                                        id: obj.id,
                                                        text: obj.text
                                                    };
                                                })
                                            };
                                        },
                                        cache: true
                                    },
                                    placeholder: 'Pilih satuan',
                                    allowClear: true
                                });
                            }

                            if (response.status == false) {
                                $('.parameter-satuan-non-sub').show();
                                $('.parameter-satuan-with-sub').hide();

                                $('#is_sub_parameter_satuan_baku_mutu').val(0);
                            }
                        },
                        error: function() {
                            swal("Error!", "System gagal medapatkan data!", "error");
                        }
                    });
                }
            });
        })

        $(document).ready(function() {
            function submitEditForm(syncPermohonan, syncFromDate) {
                if ($('.equal-dropdown').is(':visible') && $('.equal-dropdown').val()) {
                    $('#equal').val($('.equal-dropdown').val());
                }

                var $btn = $('.btn-simpan');
                $btn.prop('disabled', true);

                $('#form').ajaxSubmit({
                    data: {
                        sync_permohonan: syncPermohonan ? 1 : 0,
                        sync_permohonan_from_date: syncFromDate || ''
                    },
                    success: function(response) {
                        if (response.status == true) {
                            var msg = response.pesan || 'Data berhasil disimpan';
                            if (response.synced_permohonan > 0) {
                                msg += ' (' + response.synced_permohonan + ' permohonan disesuaikan)';
                            }
                            swal({
                                    title: "Success!",
                                    text: msg,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location = '/elits-baku-mutu-klinik';
                                });
                        } else {
                            var pesan = "";
                            var data_pesan = response.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    console.log(value);
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
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            }

            function confirmSyncPermohonanThenSave() {
                swal({
                    title: 'Sesuaikan permohonan lama?',
                    text: 'Apakah baku mutu pada permohonan uji sebelumnya diubah mengikuti hasil baru? Penyesuaian mempertimbangkan gender dan usia pasien.',
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: 'Tidak',
                            visible: true,
                        },
                        confirm: {
                            text: 'Ya, sesuaikan',
                            value: true,
                        },
                    },
                    dangerMode: false,
                }).then(function(willSync) {
                    if (willSync !== true) {
                        submitEditForm(0, null);
                        return;
                    }

                    var $dateInput = $('<input>', {
                        type: 'date',
                        class: 'form-control',
                        id: 'edit-sync-from-date',
                        value: new Date().toISOString().slice(0, 10),
                    });

                    swal({
                        title: 'Tanggal mulai penyesuaian',
                        text: 'Permohonan uji sejak tanggal ini akan disesuaikan dengan baku mutu baru:',
                        content: {
                            element: $dateInput[0],
                        },
                        buttons: {
                            cancel: 'Batal',
                            confirm: {
                                text: 'Lanjutkan simpan',
                                closeModal: false,
                            },
                        },
                    }).then(function(ok) {
                        if (!ok) {
                            return;
                        }
                        var fromDate = $('#edit-sync-from-date').val();
                        if (!fromDate) {
                            swal('Error', 'Tanggal mulai wajib diisi', 'error');
                            return;
                        }
                        swal.close();
                        submitEditForm(1, fromDate);
                    });
                });
            }

            $('.btn-simpan').on('click', function() {
                confirmSyncPermohonanThenSave();
            });
        })
    </script>

    <!-- TinyMCE Editor Modal untuk Nilai Sama Dengan -->
    <div class="modal fade" id="editorModalEqual" tabindex="-1" role="dialog" aria-labelledby="editorModalEqualLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="editorModalEqualLabel">
                        <i class="fa fa-equals mr-2"></i>
                        Editor: Nilai Sama Dengan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <strong><i class="fa fa-info-circle"></i> Nilai Sama Dengan:</strong>
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
                        <i class="fa fa-file-alt mr-2"></i>
                        Editor: Nilai Baku Mutu di Laporan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-primary">
                        <strong><i class="fa fa-info-circle"></i> Nilai Baku Mutu di Laporan:</strong>
                        <p class="mb-2">Gunakan field ini untuk nilai yang akan muncul di laporan hasil. Contoh:
                            <b>74 - 106</b>, <b>Negatif</b>, <b>Positif</b>
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

    @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_library')
    @include('masterweb::module.admin.laboratorium.baku-mutu-klinik._modal_create_unit')
    @include('masterweb::module.admin.laboratorium.baku-mutu-klinik._modal_create_parameter_jenis_klinik')
    @include('masterweb::module.admin.laboratorium.baku-mutu-klinik._modal_create_parameter_satuan_klinik')
@endsection
