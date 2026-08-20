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
                                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
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
                action="{{ route('elits-baku-mutu-' . $lab_link . '.store') }}" method="POST" id="form">
                @csrf

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
                                        <option value="" selected disabled>Pilih Jenis Sampel</option>
                                        @foreach ($sample_types as $sample_type)
                                            <option value="{{ $sample_type->id_sample_type }}">
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

                        <div class="form-group jenis_makanan" style="display: none;">
                            <label for="jenis_makanan_id">
                                <i class="fa fa-utensils mr-1"></i>Jenis Makanan
                                <span class="badge badge-danger ml-1 jenis-makanan-required" style="display: none;">Wajib</span>
                                <span class="badge badge-info ml-1 jenis-makanan-optional" style="display: none;">Opsional</span>
                            </label>
                            <select id="jenis_makanan_id" name="jenis_makanan_id"
                                class="js-customer-basic-multiple js-states form-control" style="width: 100%">
                                <option value="" selected>Pilih Jenis Makanan (Opsional)</option>
                                @foreach ($all_jenis_makanan as $jenis_makanan)
                                    <option value="{{ $jenis_makanan->id_jenis_makanan }}">
                                        {{ $jenis_makanan->name_jenis_makanan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group tipe_nilai_baku_mutu" style="display: none;">
                            <label for="tipe_nilai_baku_mutu">
                                <i class="fa fa-balance-scale mr-1"></i>Tipe Nilai Baku Mutu
                                <span class="badge badge-danger ml-1">Wajib</span>
                            </label>
                            <select id="tipe_nilai_baku_mutu" name="tipe_nilai_baku_mutu" class="form-control"
                                style="width: 100%">
                                <option value="" selected disabled>Pilih Tipe Nilai Baku Mutu</option>
                                <option value="kuantitatif">Kuantitatif</option>
                                <option value="kualitatif">Kualitatif</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="method_id">
                                <i class="fa fa-list mr-1"></i>Parameter
                            </label>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1 mr-2">
                                    <select name="method_id" class="form-control" id="method_id" style="width: 100%">
                                        <option value="" selected disabled>Pilih Parameter</option>
                                        @foreach ($methods as $method)
                                            <option value="{{ $method->id_method }}">{{ $method->params_method }}</option>
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
                                style="display: none;"></textarea>
                            <button type="button" class="btn btn-sm btn-primary open-editor-name-report"
                                data-target="name_report">
                                <i class="fa fa-file-text mr-1"></i>
                                Edit Nama Parameter di Laporan
                            </button>
                            <div class="mt-2 p-3 border rounded" style="background-color: #fff; min-height: 50px;">
                                <small class="text-muted"><i
                                        class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                <div id="preview_name_report" style="margin-top: 5px;">-</div>
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
                                        <option value="" selected disabled>Pilih Acuan Baku Mutu</option>
                                        @foreach ($libraries as $library)
                                            <option value="{{ $library->id_library }}">{{ $library->title_library }}
                                            </option>
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
                                        <option value="" selected disabled>Pilih Satuan</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id_unit }}">{!! $unit->shortname_unit !!}</option>
                                        @endforeach
                                        <option value="-">-</option>
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

                        {{-- Opsi Lokasi khusus untuk Kualitas Udara --}}
                        <div class="form-group" id="lokasi_option_container" style="display: none;">
                            <label>
                                <input type="checkbox" id="use_lokasi" name="use_lokasi" value="1">
                                <i class="fa fa-map-marker-alt mr-1"></i>Gunakan Lokasi / Ruangan (untuk baku mutu berbeda per ruangan)
                            </label>
                            <small class="form-text text-muted">Centang jika ingin menambahkan baku mutu untuk lokasi/ruangan tertentu. Jika tidak dicentang, baku mutu akan berlaku untuk semua lokasi.</small>
                        </div>

                        <div id="lokasi_container" style="display: none;">
                            <div class="form-group">
                                <label><i class="fa fa-map-marker-alt mr-1"></i>Lokasi / Ruangan dengan Baku Mutu</label>
                                <div id="lokasi_list">
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
                    <input type="radio" id="html" name="is_sub" value="true"> Ya<br>
                    <input type="radio" id="css" name="is_sub" value="false" checked> Tidak<br>
                </div>

                <!-- Konfigurasi Baku Mutu -->
                <div class="no_sub" id="no_sub_container" style="display: none;">
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
                                    <input type="text" class="form-control" id="min" name="min_no_sub"
                                        placeholder="Contoh: 4.0">
                                </div>

                                <div class="form-group">
                                    <label for="max">
                                        <i class="fa fa-arrow-up mr-1"></i>Max (Maksimum)
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Gunakan titik (.) untuk desimal. Contoh: 6.5 atau kosongkan jika tidak ada"></i>
                                    </label>
                                    <input type="text" class="form-control" id="max" name="max_no_sub"
                                        placeholder="Contoh: 6.5">
                                </div>

                                <div class="form-group">
                                    <label for="equal">
                                        <i class="fa fa-equals mr-1"></i>Nilai Sama Dengan
                                        <i class="fa fa-question-circle text-info ml-1" data-toggle="tooltip"
                                            title="Untuk nilai non-range seperti Positif/Negatif. Kosongkan jika menggunakan range Min-Max"></i>
                                    </label>
                                    <input type="text" class="form-control equal-input" id="equal_no_sub"
                                        name="equal_no_sub" placeholder="Contoh: Positif" style="display: none;">
                                    <button type="button"
                                        class="btn btn-sm btn-success open-editor-equal equal-editor-btn"
                                        data-target="equal_no_sub">
                                        <i class="fa fa-equals mr-1"></i>
                                        Edit Nilai Sama Dengan
                                    </button>
                                    <div class="mt-2 p-3 border rounded"
                                        style="background-color: #fff; min-height: 50px;">
                                        <small class="text-muted"><i
                                                class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                        <div id="preview_equal_no_sub" style="margin-top: 5px;">-</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nilai_baku_mutu">
                                        <i class="fa fa-file-alt mr-1"></i>Nilai Baku Mutu di Laporan
                                    </label>
                                    <textarea class="form-control" id="nilai_baku_mutu_no_sub" name="nilai_baku_mutu_no_sub"
                                        placeholder="Nilai Baku Mutu" style="display: none;"></textarea>
                                    <button type="button" class="btn btn-sm btn-primary open-editor-nilai"
                                        data-target="nilai_baku_mutu_no_sub">
                                        <i class="fa fa-file-text mr-1"></i>
                                        Edit Nilai Baku Mutu
                                    </button>
                                    <div class="mt-2 p-3 border rounded"
                                        style="background-color: #fff; min-height: 50px;">
                                        <small class="text-muted"><i
                                                class="fa fa-desktop mr-1"></i><strong>Preview:</strong></small>
                                        <div id="preview_nilai_baku_mutu_no_sub" style="margin-top: 5px;">-</div>
                                    </div>
                                    <small class="form-text text-muted">Teks yang akan muncul di laporan hasil</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sub" style="display: none;">
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
                                    <div style="position: relative;">
                                        <input type="text" class="form-control" id="equal_0" name="equal[0]"
                                            placeholder="Nilai Harus Sama Dengan" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-primary open-editor-modal-baku-mutu"
                                            data-target="equal_0" data-field-name="Nilai Harus Sama Dengan (Sub 1)">
                                            <i class="fa fa-edit mr-1"></i>
                                            Edit dengan Editor
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan</label>
                                    <div style="position: relative;">
                                        <input type="text" class="form-control" id="nilai_baku_mutu_0"
                                            name="nilai_baku_mutu[0]" placeholder="Nilai Baku Mutu"
                                            style="display: none;">
                                        <button type="button" class="btn btn-sm btn-primary open-editor-modal-baku-mutu"
                                            data-target="nilai_baku_mutu_0"
                                            data-field-name="Nilai Baku Mutu di Laporan (Sub 1)">
                                            <i class="fa fa-edit mr-1"></i>
                                            Edit dengan Editor
                                        </button>
                                    </div>
                                </div>
                                <button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i
                                        class="fas fa-plus"></i> Sub Baku Mutu</button>

                            </div>

                        </div>
                    </div>

                </div>

            </form>

            <!-- TinyMCE Editor Modal untuk Nilai Harus Sama Dengan -->
            <div class="modal fade" id="editorModalEqual" tabindex="-1" role="dialog"
                aria-labelledby="editorModalEqualLabel" aria-hidden="true">
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
            <div class="modal fade" id="editorModalNilai" tabindex="-1" role="dialog"
                aria-labelledby="editorModalNilaiLabel" aria-hidden="true">
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
                                <p class="mb-2">Nilai yang akan DITAMPILKAN di laporan. Bisa berupa range atau nilai
                                    spesifik.</p>
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
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">
@endsection

@section('scripts')
    <!-- TinyMCE CDN from jsDelivr (Free, no API key required) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.7/tinymce.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi tooltip
            $(function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $.fn.select2.defaults.set("theme", "classic");

            // Auto-fill form jika ada parameter dari URL
            var urlParams = new URLSearchParams(window.location.search);
            var autoFill = urlParams.get('auto_fill');

            if (autoFill === '1') {
                // Auto-fill berdasarkan parameter URL
                var methodId = urlParams.get('method_id');
                var sampleTypeId = urlParams.get('sampletype_id');
                var jenisMakananId = urlParams.get('jenis_makanan_id');

                if (methodId) {
                    $('#method_id').val(methodId).trigger('change');
                }

                if (sampleTypeId) {
                    $('#sampletype_id').val(sampleTypeId).trigger('change');
                }

                if (jenisMakananId) {
                    // Delay untuk memastikan jenis makanan dropdown sudah muncul
                    setTimeout(function() {
                        $('#jenis_makanan_id').val(jenisMakananId).trigger('change');
                    }, 500);
                }

                // Tampilkan notifikasi
                swal({
                    title: "Auto Fill Aktif!",
                    text: "Form telah diisi otomatis berdasarkan parameter yang dipilih dari halaman baca hasil.",
                    icon: "info",
                    timer: 3000
                });
            }

            $('#jenis_makanan_id').select2({
                placeholder: "Pilih Jenis Makanan (Opsional)",
                allowClear: true
            });

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

                    console.log('Selected Jenis Sampel:', selectedSampleText); // Debug

                    // Show/hide jenis makanan based on sample type and lab
                    // Untuk lab kimia: jika jenis sampel makanan/minuman/lainnya, tampilkan jenis makanan tapi opsional
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
                    if (selectedSampleText && selectedSampleText.toLowerCase().includes('udara')) {
                        $('#lokasi_option_container').show();
                    } else {
                        $('#lokasi_option_container').hide();
                        $('#use_lokasi').prop('checked', false);
                        $('#lokasi_container').hide();
                        $('#no_sub_container').show();
                    }
                });

            $('#method_id').select2({
                placeholder: "Pilih Parameter",
                width: '100%',
                dropdownParent: $('body')
            });

            $('#library_id').select2({
                placeholder: "Pilih Acuan Baku Mutu",
                width: '100%',
                dropdownParent: $('body')
            });

            $('#unitAttributes').select2({
                placeholder: "Pilih Satuan",
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
                                    // Cek jika ada parameter auto_fill, maka kembali ke halaman sebelumnya
                                    var urlParams = new URLSearchParams(window.location
                                        .search);
                                    var autoFill = urlParams.get('auto_fill');

                                    if (autoFill === '1') {
                                        // Tutup tab ini dan refresh parent window jika ada
                                        if (window.opener) {
                                            window.opener.location.reload();
                                            window.close();
                                        } else {
                                            // Jika tidak ada parent window, kembali ke halaman sebelumnya
                                            window.history.back();
                                        }
                                    } else {
                                        // Behavior normal - redirect ke index
                                        var link = "{{ $lab_link }}";
                                        if (link == "kimia") {
                                            document.location =
                                                "{{ route('elits-baku-mutu-kimia.index') }}";
                                        } else {
                                            document.location =
                                                "{{ route('elits-baku-mutu-mikro.index') }}";
                                        }
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

            // ============================================
            // TinyMCE Integration for Baku Mutu Fields - 2 Modal Terpisah
            // ============================================

            let currentTargetFieldEqual = null;
            let currentTargetFieldNilai = null;
            let currentTargetFieldNameReport = null;

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

            // Update preview box (global so handlers outside can access)
            window.updatePreview = function(targetId, value) {
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
                const convertedValue = convertToTinyMCE(currentValue);

                // Show modal
                $('#editorModalEqual').modal('show');

                // Set value in editor after modal is shown
                setTimeout(function() {
                    if (tinymce.get('tinyMCEEditorEqual')) {
                        tinymce.get('tinyMCEEditorEqual').setContent(convertedValue);
                    }
                }, 300);
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
                            console.log('TinyMCE Equal initialized');
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
                const convertedValue = convertToTinyMCE(currentValue);

                $('#editorModalNilai').modal('show');

                setTimeout(function() {
                    if (tinymce.get('tinyMCEEditorNilai')) {
                        tinymce.get('tinyMCEEditorNilai').setContent(convertedValue);
                    }
                }, 300);
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
                            console.log('TinyMCE Nilai initialized');
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
                const convertedValue = convertToTinyMCE(currentValue);

                $('#editorModalNameReport').modal('show');

                setTimeout(function() {
                    if (tinymce.get('tinyMCEEditorNameReport')) {
                        tinymce.get('tinyMCEEditorNameReport').setContent(convertedValue);
                    }
                }, 300);
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
                            console.log('TinyMCE Name Report initialized');
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

        })

        $('#method_id').change(function() {
            var data = $("#method_id option:selected").text();
            $('#name_report').val(data);
            updatePreview('name_report', data);
        });

        $(".no_sub").css("display", "block")
        $(".sub").css("display", "none")

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

        $(':radio[name="is_sub"]').filter('[value="false"]').attr('checked', true);

        var no = 1;

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
                                <div style="position: relative;">
                                    <input type="text" class="form-control" id="equal_` + (no - 1) + `" name="equal[` +
                (no - 1) + `]" placeholder="Nilai Harus Sama Dengan" style="display: none;">
                                    <button type="button"
                                        class="btn btn-sm btn-primary open-editor-modal-baku-mutu"
                                        data-target="equal_` + (no - 1) + `"
                                        data-field-name="Nilai Harus Sama Dengan (Sub ` + no + `)">
                                        <i class="fa fa-edit mr-1"></i>
                                        Edit dengan Editor
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="nilai_baku_mutu">Nilai Baku Mutu di Laporan</label>
                                <div style="position: relative;">
                                    <input type="text" class="form-control" id="nilai_baku_mutu_` + (no - 1) +
                `" name="nilai_baku_mutu[` +
                (no - 1) + `]" placeholder="Nilai Baku Mutu" style="display: none;">
                                    <button type="button"
                                        class="btn btn-sm btn-primary open-editor-modal-baku-mutu"
                                        data-target="nilai_baku_mutu_` + (no - 1) + `"
                                        data-field-name="Nilai Baku Mutu di Laporan (Sub ` + no + `)">
                                        <i class="fa fa-edit mr-1"></i>
                                        Edit dengan Editor
                                    </button>
                                </div>
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
        var lokasiIndex = 1;
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
    </script>

    <style>
        /* Pastikan Select2 tampil dan bisa diklik di atas komponen lain */
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-dropdown {
            z-index: 2050;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
@endsection
