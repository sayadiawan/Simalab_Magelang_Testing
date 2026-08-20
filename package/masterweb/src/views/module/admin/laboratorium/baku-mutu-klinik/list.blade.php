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
                                <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">


        <div class="card-body">
            <style>
                #empTable .bmu-nilai-table-wrap table,
                #empTableHaji .bmu-nilai-table-wrap table {
                    border-collapse: collapse;
                    width: 100%;
                    margin: 0;
                }
                #empTable .bmu-nilai-table-wrap td,
                #empTable .bmu-nilai-table-wrap th,
                #empTableHaji .bmu-nilai-table-wrap td,
                #empTableHaji .bmu-nilai-table-wrap th {
                    border: none !important;
                    padding: 1px 4px;
                    vertical-align: top;
                }
            </style>
            <input type="hidden" id="csrf-token" value="{{ csrf_token() }}">
            <div class="d-flex">
                <div class="mr-auto p-2">
                </div>

                @if (getAction('create'))
                    <div class="p-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-icon-text dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="localStorage.clear();">
                                Tambah Data
                                <i class="fa fa-plus btn-icon-append"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('elits-baku-mutu-klinik.create') }}">
                                    <i class="fa fa-flask mr-2"></i>Baku Mutu Biasa
                                </a>
                                <a class="dropdown-item" href="{{ route('elits-baku-mutu-klinik.create-haji') }}">
                                    <i class="fa fa-kaaba mr-2"></i>Baku Mutu Haji
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <div class="row">

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="col-12">
                    <!-- Tab untuk memisahkan Non-Haji dan Haji -->
                    <ul class="nav nav-tabs mb-3" id="bakuMutuTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="non-haji-tab" data-toggle="tab" href="#non-haji" role="tab" aria-controls="non-haji" aria-selected="true">
                                <i class="fa fa-flask mr-2"></i>Baku Mutu Biasa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="haji-tab" data-toggle="tab" href="#haji" role="tab" aria-controls="haji" aria-selected="false">
                                <i class="fa fa-kaaba mr-2"></i>Baku Mutu Haji
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="bakuMutuTabContent">
                        <!-- Tab Non-Haji -->
                        <div class="tab-pane fade show active" id="non-haji" role="tabpanel" aria-labelledby="non-haji-tab">
                            <div class="mb-2 d-flex align-items-center">
                                <div class="form-check mr-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="grouping-toggle" checked>
                                    <label class="form-check-label" for="grouping-toggle">Group by Jenis + Parameter Satuan</label>
                                </div>
                            </div>
                            <div class="table-responsive">
                        <table id='empTable' class="table" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="15">No</th>
                                    <th>Jenis Parameter</th>
                                    <th>Parameter Satuan</th>
                                    <th>Acuan Baku Mutu</th>
                                    <th>Nilai Baku Mutu</th>
                                    <th>Status Data Khusus</th>
                                    <th>Detail Data Khusus</th>
                                    <th>Tipe</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>

                            <tbody id="tabel-body">

                            </tbody>
                        </table>
                            </div>
                        </div>
                        
                        <!-- Tab Haji -->
                        <div class="tab-pane fade" id="haji" role="tabpanel" aria-labelledby="haji-tab">
                            <div class="mb-2 d-flex align-items-center">
                                <div class="form-check mr-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="grouping-toggle-haji" checked>
                                    <label class="form-check-label" for="grouping-toggle-haji">Group by Jenis + Parameter Satuan</label>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id='empTableHaji' class="table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th width="15">No</th>
                                            <th>Jenis Parameter</th>
                                            <th>Parameter Satuan</th>
                                            <th>Acuan Baku Mutu</th>
                                            <th>Nilai Baku Mutu</th>
                                            <th>Status Data Khusus</th>
                                            <th>Detail Data Khusus</th>
                                            <th>Tipe</th>
                                            <th width="200">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabel-body-haji">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Modal edit satuan (khusus haji) -->
                    <div class="modal fade" id="modal-edit-satuan" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="fa fa-edit mr-2"></i>Edit Satuan
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <style>
                                    /* Make Select2 dropdown full width in modal */
                                    #modal-edit-satuan .select2-container {
                                        width: 100% !important;
                                    }
                                    #modal-edit-satuan .select2-selection {
                                        width: 100% !important;
                                    }
                                    #modal-edit-satuan .select2-dropdown {
                                        width: 100% !important;
                                        min-width: 100% !important;
                                    }
                                    #modal-edit-satuan .select2-results {
                                        width: 100% !important;
                                    }
                                    #modal-edit-satuan .select2-search--dropdown {
                                        width: 100% !important;
                                    }
                                    #modal-edit-satuan .select2-search__field {
                                        width: 100% !important;
                                    }
                                    /* Ensure dropdown matches modal width */
                                    #modal-edit-satuan .select2-container--open .select2-dropdown {
                                        width: 100% !important;
                                        left: 0 !important;
                                    }
                                </style>
                                <div class="modal-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="fa fa-info-circle mr-2"></i>
                                        <strong>Petunjuk:</strong> Edit data satuan untuk parameter haji. Perubahan akan diterapkan pada semua data dalam grup yang sama.
                                    </div>
                                    <form id="form-edit-satuan">
                                        <input type="hidden" id="es-jenis" name="parameter_jenis_klinik_id">
                                        <input type="hidden" id="es-satuan" name="parameter_satuan_klinik_id">
                                        <input type="hidden" id="es-lab" name="lab_id">
                                        
                                        <div class="form-group">
                                            <label for="es-library">
                                                <i class="fa fa-book mr-1"></i>Acuan Baku Mutu
                                                <span class="badge badge-danger ml-1">Wajib</span>
                                            </label>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1 mr-2">
                                                    <select class="form-control" name="library_id" id="es-library">
                                                        <option value=""></option>
                                                    </select>
                                                </div>
                                                <button type="button" class="btn btn-primary flex-shrink-0"
                                                    data-toggle="modal" data-target="#modalCreateLibrary"
                                                    title="Buat acuan baku mutu baru">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="es-unit">
                                                <i class="fa fa-ruler mr-1"></i>Satuan
                                                <span class="badge badge-danger ml-1">Wajib</span>
                                            </label>
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1 mr-2">
                                                    <select class="form-control" name="unit_id" id="es-unit">
                                                        <option value=""></option>
                                                    </select>
                                                </div>
                                                <button type="button" class="btn btn-warning flex-shrink-0"
                                                    data-toggle="modal" data-target="#modalCreateUnit"
                                                    title="Buat satuan baru">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="es-min">
                                                <i class="fa fa-arrow-down mr-1"></i>Min
                                            </label>
                                            <input type="text" class="form-control" name="min" id="es-min" placeholder="Min">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="es-max">
                                                <i class="fa fa-arrow-up mr-1"></i>Max
                                            </label>
                                            <input type="text" class="form-control" name="max" id="es-max" placeholder="Max">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="es-equal">
                                                <i class="fa fa-equals mr-1"></i>Equal
                                            </label>
                                            <input type="text" class="form-control" name="equal" id="es-equal" placeholder="Equal">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="es-nilai">
                                                <i class="fa fa-tag mr-1"></i>Nilai Baku Mutu
                                            </label>
                                            <input type="text" class="form-control" name="nilai_baku_mutu" id="es-nilai" placeholder="Nilai Baku Mutu">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary" id="es-save">
                                        <i class="fa fa-save mr-2"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal edit group -->
                    <div class="modal fade" id="modal-edit-group" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title mb-0">
                                        <i class="fa fa-edit mr-2"></i>Edit Massal Grup
                                        <small class="d-block mt-1 font-weight-normal" id="eg-modal-parameter-title" style="font-size: 0.85rem; opacity: .92;"></small>
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-secondary mb-3 py-2" id="eg-parameter-info">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fa fa-flask mr-1"></i>Jenis Parameter:</strong>
                                                <span id="eg-info-jenis">-</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fa fa-list mr-1"></i>Parameter Satuan:</strong>
                                                <span id="eg-info-satuan">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mb-3">
                                        <i class="fa fa-info-circle mr-2"></i>
                                        <strong>Petunjuk:</strong> Edit data baku mutu dalam grup ini.
                                        Hapus baris yang tidak diperlukan (ikon tempat sampah), lalu simpan.
                                        Saat simpan, seluruh data grup diganti hanya dengan baris yang ada di tabel.
                                    </div>
                                    <style>
                                        #modal-edit-group .modal-dialog {
                                            max-width: 95%;
                                            height: calc(100vh - 2rem);
                                            max-height: calc(100vh - 2rem);
                                            margin: 1rem auto;
                                            display: flex;
                                            flex-direction: column;
                                        }

                                        #modal-edit-group .modal-content {
                                            flex: 1 1 auto;
                                            min-height: 0;
                                            max-height: 100%;
                                            overflow: hidden;
                                            display: flex;
                                            flex-direction: column;
                                        }

                                        #modal-edit-group .modal-header,
                                        #modal-edit-group .modal-footer {
                                            flex-shrink: 0;
                                        }

                                        #modal-edit-group .modal-body {
                                            overflow-y: auto !important;
                                            overflow-x: hidden;
                                            flex: 1 1 auto;
                                            min-height: 0;
                                        }

                                        #modal-edit-group .select2-container {
                                            width: 100% !important;
                                        }

                                        #egEditorModalNilai .modal-dialog {
                                            max-width: 900px;
                                            height: calc(100vh - 2rem);
                                            max-height: calc(100vh - 2rem);
                                            margin: 1rem auto;
                                            display: flex;
                                            flex-direction: column;
                                        }

                                        #egEditorModalNilai .modal-content {
                                            flex: 1 1 auto;
                                            min-height: 0;
                                            max-height: 100%;
                                            overflow: hidden;
                                            display: flex;
                                            flex-direction: column;
                                        }

                                        #egEditorModalNilai .modal-header,
                                        #egEditorModalNilai .modal-footer {
                                            flex-shrink: 0;
                                        }

                                        #egEditorModalNilai .modal-body {
                                            overflow-y: auto !important;
                                            overflow-x: hidden;
                                            flex: 1 1 auto;
                                            min-height: 0;
                                        }

                                        #eg-table th {
                                            white-space: nowrap;
                                        }

                                        #eg-table th:nth-child(1) {
                                            width: 150px;
                                        }

                                        #eg-table th:nth-child(2) {
                                            width: 150px;
                                        }

                                        #eg-table th:nth-child(3) {
                                            width: 130px;
                                        }

                                        #eg-table th:nth-child(4) {
                                            width: 130px;
                                        }

                                        #eg-table th:nth-child(5) {
                                            width: 300px;
                                        }

                                        #eg-table th:nth-child(6) {
                                            width: 140px;
                                        }

                                        #eg-table th:nth-child(7) {
                                            width: 140px;
                                        }

                                        #eg-table th:nth-child(8) {
                                            width: 160px;
                                        }

                                        #eg-table th:nth-child(9) {
                                            width: 300px;
                                        }

                                        #eg-table th:nth-child(10) {
                                            width: 130px;
                                        }

                                        #eg-table .form-control {
                                            width: 100%;
                                            min-width: 100%;
                                        }
                                        
                                        #eg-table .form-control-sm {
                                            font-size: 0.875rem;
                                            padding: 0.25rem 0.5rem;
                                        }
                                        
                                        #eg-table td {
                                            vertical-align: middle;
                                        }
                                        
                                        .eg-nilai-preview table,
                                        #preview_eg-nilai-shared table,
                                        .bmu-nilai-table {
                                            border-collapse: collapse;
                                            width: 100%;
                                        }
                                        .eg-nilai-preview table td,
                                        .eg-nilai-preview table th,
                                        #preview_eg-nilai-shared table td,
                                        #preview_eg-nilai-shared table th,
                                        .bmu-nilai-table td,
                                        .bmu-nilai-table th {
                                            border: none !important;
                                            padding: 2px 6px;
                                        }
                                        .eg-nilai-preview table tr,
                                        #preview_eg-nilai-shared table tr,
                                        .bmu-nilai-table tr {
                                            border: none !important;
                                        }
                                        
                                        #eg-table input[type="text"],
                                        #eg-table input[type="number"],
                                        #eg-table select {
                                            border: 1px solid #ced4da;
                                            border-radius: 0.25rem;
                                        }
                                        
                                        #eg-table input[type="text"]:focus,
                                        #eg-table input[type="number"]:focus,
                                        #eg-table select:focus {
                                            border-color: #80bdff;
                                            outline: 0;
                                            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                                        }
                                    </style>
                                    <input type="hidden" id="eg-jenis">
                                    <input type="hidden" id="eg-satuan">
                                    <input type="hidden" id="eg-lab">
                                    <input type="hidden" id="eg-haji" value="0">

                                    <div class="card border-light mb-3">
                                        <div class="card-body py-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-md-0">
                                                        <label for="eg-library">
                                                            <i class="fa fa-book mr-1"></i>Acuan Baku Mutu
                                                            <span class="badge badge-danger ml-1">Wajib</span>
                                                        </label>
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-grow-1 mr-2">
                                                                <select class="form-control" id="eg-library" style="width: 100%">
                                                                    <option value=""></option>
                                                                </select>
                                                            </div>
                                                            <button type="button" class="btn btn-primary flex-shrink-0"
                                                                data-toggle="modal" data-target="#modalCreateLibrary"
                                                                title="Buat acuan baku mutu baru">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0">
                                                        <label for="eg-unit">
                                                            <i class="fa fa-ruler mr-1"></i>Satuan
                                                            <span class="badge badge-danger ml-1">Wajib</span>
                                                        </label>
                                                        <div class="d-flex align-items-start">
                                                            <div class="flex-grow-1 mr-2">
                                                                <select class="form-control" id="eg-unit" style="width: 100%">
                                                                    <option value=""></option>
                                                                </select>
                                                            </div>
                                                            <button type="button" class="btn btn-warning flex-shrink-0"
                                                                data-toggle="modal" data-target="#modalCreateUnit"
                                                                title="Buat satuan baru">
                                                                <i class="fa fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-light mb-3" id="eg-nilai-mode-card">
                                        <div class="card-body py-3">
                                            <label class="font-weight-bold d-block mb-2">
                                                <i class="fa fa-file-alt mr-1"></i>Mode Nilai di Laporan
                                            </label>
                                            <div class="form-check form-check-inline mb-2">
                                                <input class="form-check-input" type="checkbox" id="eg-is-massal" value="1">
                                                <label class="form-check-label font-weight-bold text-info" for="eg-is-massal">
                                                    <i class="fa fa-clone mr-1"></i>Is Massal Nilai di Laporan
                                                    <small class="text-muted font-weight-normal ml-1">(tampilkan 1 nilai untuk seluruh grup di laporan)</small>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="eg_nilai_mode"
                                                    id="eg-nilai-mode-sama" value="sama_semua" checked>
                                                <label class="form-check-label" for="eg-nilai-mode-sama">Sama semua</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="eg_nilai_mode"
                                                    id="eg-nilai-mode-berbeda" value="berbeda">
                                                <label class="form-check-label" for="eg-nilai-mode-berbeda">Berbeda per gender / umur</label>
                                            </div>
                                            <div id="eg-nilai-sama-semua-panel" class="mt-3">
                                                <label class="mb-1 text-muted">Nilai Baku Mutu di Laporan (berlaku untuk semua baris)</label>
                                                <input type="hidden" id="eg-nilai-shared" value="">
                                                <button type="button" class="btn btn-sm btn-primary eg-open-nilai-editor"
                                                    data-target="eg-nilai-shared">
                                                    <i class="fa fa-edit mr-1"></i>Edit Nilai di Laporan
                                                </button>
                                                <div class="mt-2 p-2 border rounded bg-white eg-nilai-preview"
                                                    id="preview_eg-nilai-shared" style="min-height: 40px;">-</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover" id="eg-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 100px;">
                                                        <i class="fa fa-filter mr-1"></i>Tipe
                                                        <i class="fa fa-question-circle text-info ml-1"
                                                            data-toggle="tooltip" title="General atau Specific"></i>
                                                    </th>
                                                    <th style="width: 120px;">
                                                        <i class="fa fa-venus-mars mr-1"></i>Gender
                                                    </th>
                                                    <th style="width: 110px;" class="eg-umur-col">
                                                        <i class="fa fa-calendar-minus mr-1"></i>Umur Min
                                                    </th>
                                                    <th style="width: 110px;" class="eg-umur-col">
                                                        <i class="fa fa-calendar-plus mr-1"></i>Umur Max
                                                    </th>
                                                    <th class="eg-nilai-col">
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
                                                    <th style="width: 200px;">
                                                        <i class="fa fa-comment-alt mr-1"></i>Kesimpulan
                                                    </th>
                                                    <th style="width: 90px;" class="text-center">
                                                        <i class="fa fa-check-circle mr-1"></i>Normal?
                                                    </th>
                                                    <th style="width: 60px;" class="text-center">
                                                        <i class="fa fa-cog"></i>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="eg-table-tbody"></tbody>
                                        </table>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-primary btn-sm" id="eg-add-row">
                                                <i class="fa fa-plus mr-1"></i>Tambah Baris
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fa fa-times mr-1"></i>Batal
                                    </button>
                                    <button type="button" class="btn btn-primary" id="eg-save">
                                        <i class="fa fa-save mr-1"></i>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TinyMCE Editor Modal untuk Nilai di Laporan (Edit Massal Grup) -->
    <div class="modal fade" id="egEditorModalNilai" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-file-alt mr-2"></i>Editor: Nilai Baku Mutu di Laporan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea id="egTinyMCEEditorNilai"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="eg-save-editor-nilai">
                        <i class="fa fa-check mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('masterweb::module.admin.laboratorium.baku-mutu._modal_create_library')
    @include('masterweb::module.admin.laboratorium.baku-mutu-klinik._modal_create_unit')
@endsection


@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Pindahkan modal ke body agar scroll & overlay tidak terpotong parent
            $('#modal-edit-group, #egEditorModalNilai, #modalCreateLibrary, #modalCreateUnit').appendTo('body');

            // DataTable Non-Haji
            function getAjaxUrl() {
                var base = "{{ route('elits-baku-mutu-klinik.data-baku-mutu-klinik') }}";
                return base + ($('#grouping-toggle').is(':checked') ? '?group=1&is_haji=0' : '?is_haji=0');
            }
            
            function getAjaxUrlHaji() {
                var base = "{{ route('elits-baku-mutu-klinik.data-baku-mutu-klinik') }}";
                return base + ($('#grouping-toggle-haji').is(':checked') ? '?group=1&is_haji=1' : '?is_haji=1');
            }

            var table = $('#empTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                responsive: true,
                ajax: {
                    url: getAjaxUrl(),
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'jenis_parameter',
                        name: 'jenis_parameter'
                    },
                    {
                        data: 'parameter_satuan',
                        name: 'parameter_satuan'
                    },
                    {
                        data: 'library',
                        name: 'library'
                    },
                    {
                        data: 'nilai_baku_mutu',
                        name: 'nilai_baku_mutu'
                    },
                    {
                        data: 'is_khusus_baku_mutu',
                        name: 'is_khusus_baku_mutu'
                    },
                    {
                        data: 'detail_data_khusus',
                        name: 'detail_data_khusus'
                    },
                    {
                        data: 'is_haji',
                        name: 'is_haji',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // datatables responsive
            new $.fn.dataTable.FixedHeader(table);

            $('#grouping-toggle').on('change', function() {
                table.ajax.url(getAjaxUrl()).load();
            });
            
            // DataTable Haji - inisialisasi lazy saat tab diklik
            var tableHaji = null;
            
            function initTableHaji() {
                if (tableHaji === null) {
                    tableHaji = $('#empTableHaji').DataTable({
                        processing: true,
                        serverSide: true,
                        stateSave: true,
                        responsive: true,
                        ajax: {
                            url: getAjaxUrlHaji(),
                            type: "GET"
                        },
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'jenis_parameter',
                                name: 'jenis_parameter'
                            },
                            {
                                data: 'parameter_satuan',
                                name: 'parameter_satuan'
                            },
                            {
                                data: 'library',
                                name: 'library'
                            },
                            {
                                data: 'nilai_baku_mutu',
                                name: 'nilai_baku_mutu'
                            },
                            {
                                data: 'is_khusus_baku_mutu',
                                name: 'is_khusus_baku_mutu'
                            },
                            {
                                data: 'detail_data_khusus',
                                name: 'detail_data_khusus'
                            },
                            {
                                data: 'is_haji',
                                name: 'is_haji',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });
                    
                    // datatables responsive untuk haji
                    new $.fn.dataTable.FixedHeader(tableHaji);
                    
                    $('#grouping-toggle-haji').on('change', function() {
                        tableHaji.ajax.url(getAjaxUrlHaji()).load();
                    });
                }
            }
            
            // Load DataTable Haji saat tab haji diklik
            $('#haji-tab').on('shown.bs.tab', function (e) {
                initTableHaji();
            });

            // Variabel global untuk menyimpan opsi equal
            var egEqualOptions = [];
            var egIsOption = false;
            var egCurrentNilaiTarget = null;
            var egPendingNilaiValue = null;

            var egNilaiTinyMCETableOptions = {
                table_default_attributes: { border: '0' },
                table_default_styles: { 'border-collapse': 'collapse', 'width': '100%' },
                table_style_by_css: true,
                table_cell_default_styles: { padding: '2px 6px' },
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; } table { border-collapse: collapse; width: 100%; } table td, table th { border: 1px dashed #bbb; padding: 2px 6px; }'
            };

            function egSanitizeTableForStorage(html) {
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

            function egConvertToHTMLPreview(value) {
                if (!value) return '-';
                var v = String(value);
                if (/<table[\s>]/i.test(v)) {
                    return v;
                }
                var openSupCount = (v.match(/\^\(/g) || []).length;
                var openSubCount = (v.match(/\_\(/g) || []).length;
                var closeCount = (v.match(/\)/g) || []).length;
                var totalOpen = openSupCount + openSubCount;
                if (totalOpen > closeCount) {
                    for (var i = 0; i < (totalOpen - closeCount); i++) {
                        v += ')';
                    }
                }
                v = v.replace(/<=|≤/g, '&#8804;');
                v = v.replace(/>=|≥/g, '&#8805;');
                v = v.replace(/\+\-|±/g, '&plusmn;');
                v = v.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                v = v.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                return v;
            }

            function egConvertToTinyMCE(value) {
                if (!value) return '';
                var v = String(value);
                if (/<table[\s>]/i.test(v)) {
                    return egSanitizeTableForStorage(v);
                }
                v = v.replace(/≤/g, '&le;');
                v = v.replace(/≥/g, '&ge;');
                v = v.replace(/±/g, '&plusmn;');
                v = v.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                v = v.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                return v;
            }

            function egConvertFromTinyMCE(value) {
                if (!value) return '';
                var v = String(value);
                if (/<table[\s>]/i.test(v)) {
                    return egSanitizeTableForStorage(v);
                }
                v = v.replace(/<sup>([^<]*)<\/sup>/gi, '^($1)');
                v = v.replace(/<sub>([^<]*)<\/sub>/gi, '_($1)');
                v = v.replace(/<br\s*\/?>/gi, '\n');
                v = v.replace(/<\/p>/gi, '\n');
                v = v.replace(/<p[^>]*>/gi, '');
                v = v.replace(/<[^>]*>/g, '');
                v = v.replace(/&le;/gi, '≤');
                v = v.replace(/&ge;/gi, '≥');
                v = v.replace(/&lt;/g, '<');
                v = v.replace(/&gt;/g, '>');
                v = v.replace(/&plusmn;/g, '±');
                v = v.replace(/&nbsp;/g, ' ');
                return v.trim();
            }

            function updateEgNilaiPreview($input) {
                if (!$input || !$input.length) return;
                var val = $input.val() || '';
                var $preview = $input.is('#eg-nilai-shared')
                    ? $('#preview_eg-nilai-shared')
                    : $input.closest('td').find('.eg-nilai-preview');
                $preview.html(egConvertToHTMLPreview(val));
            }

            function detectEgNilaiMode(items) {
                if (!items || items.length === 0) {
                    return 'sama_semua';
                }
                var nilais = items.map(function(it) {
                    return (it.nilai_baku_mutu || '').trim();
                }).filter(function(n) { return n !== ''; });
                if (nilais.length === 0) {
                    return 'sama_semua';
                }
                var first = nilais[0];
                return nilais.every(function(n) { return n === first; }) ? 'sama_semua' : 'berbeda';
            }

            function applyEgNilaiMode(mode) {
                if (mode === 'sama_semua') {
                    $('#eg-nilai-sama-semua-panel').show();
                    $('#eg-table .eg-nilai-col').hide();
                } else {
                    $('#eg-nilai-sama-semua-panel').hide();
                    $('#eg-table .eg-nilai-col').show();
                }
            }

            function setEgNilaiMode(mode) {
                $('input[name="eg_nilai_mode"][value="' + mode + '"]').prop('checked', true);
                applyEgNilaiMode(mode);
            }

            $('input[name="eg_nilai_mode"]').on('change', function() {
                var mode = $(this).val();
                if (mode === 'sama_semua') {
                    var firstNilai = egGetTbodyRows().first().find('.eg-nilai').val() || '';
                    if (firstNilai && !$('#eg-nilai-shared').val()) {
                        $('#eg-nilai-shared').val(firstNilai);
                        updateEgNilaiPreview($('#eg-nilai-shared'));
                    }
                } else {
                    var sharedNilai = $('#eg-nilai-shared').val() || '';
                    if (sharedNilai) {
                        egGetTbodyRows().find('.eg-nilai').each(function() {
                            if (!$(this).val()) {
                                $(this).val(sharedNilai);
                                updateEgNilaiPreview($(this));
                            }
                        });
                    }
                }
                applyEgNilaiMode(mode);
            });

            $(document).on('click', '.eg-open-nilai-editor', function() {
                var ri = $(this).attr('data-row-idx');
                if (ri !== undefined && ri !== '') {
                    egCurrentNilaiTarget = $('#egf-nilai-' + ri);
                } else {
                    egCurrentNilaiTarget = $('#eg-nilai-shared');
                }
                egPendingNilaiValue = egConvertToTinyMCE(egCurrentNilaiTarget.val() || '');
                $('#egEditorModalNilai').modal('show');
            });

            $('#egEditorModalNilai').on('shown.bs.modal', function() {
                if (typeof tinymce === 'undefined') {
                    return;
                }
                if (tinymce.get('egTinyMCEEditorNilai')) {
                    tinymce.get('egTinyMCEEditorNilai').remove();
                }
                tinymce.init($.extend({
                    selector: '#egTinyMCEEditorNilai',
                    height: 280,
                    menubar: false,
                    plugins: ['advlist autolink lists charmap paste code help table'],
                    toolbar: 'undo redo | bold italic | superscript subscript | charmap | table | removeformat',
                    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                    setup: function(editor) {
                        editor.on('init', function() {
                            if (egPendingNilaiValue) {
                                editor.setContent(egPendingNilaiValue);
                            }
                        });
                    }
                }, egNilaiTinyMCETableOptions));
            });

            $('#egEditorModalNilai').on('hidden.bs.modal', function() {
                if (typeof tinymce !== 'undefined' && tinymce.get('egTinyMCEEditorNilai')) {
                    tinymce.get('egTinyMCEEditorNilai').remove();
                }
                egCurrentNilaiTarget = null;
                egPendingNilaiValue = null;
            });

            $('#eg-save-editor-nilai').on('click', function() {
                if (!egCurrentNilaiTarget || typeof tinymce === 'undefined' || !tinymce.get('egTinyMCEEditorNilai')) {
                    $('#egEditorModalNilai').modal('hide');
                    return;
                }
                var content = egConvertFromTinyMCE(tinymce.get('egTinyMCEEditorNilai').getContent());
                egCurrentNilaiTarget.val(content);
                updateEgNilaiPreview(egCurrentNilaiTarget);
                $('#egEditorModalNilai').modal('hide');
            });

            // Function untuk update equal field berdasarkan ParameterSatuanKlinik
            function updateEqualFieldFromParameterSatuanKlinik(parameterSatuanKlinikId) {
                if (!parameterSatuanKlinikId) {
                    egIsOption = false;
                    egEqualOptions = [];
                    if (egGetTbodyRows().length) {
                        updateAllEqualFields();
                    }
                    return;
                }

                $.ajax({
                    url: "{{ route('getParameterSatuanKlinikDetail') }}",
                    type: 'POST',
                    data: {
                        _token: $('#csrf-token').val(),
                        id: parameterSatuanKlinikId
                    },
                    success: function(response) {
                        if (response.status && response.data) {
                            egIsOption = response.data.is_option == 1;
                            egEqualOptions = response.data.options || [];
                        } else {
                            egIsOption = false;
                            egEqualOptions = [];
                        }
                        if (egGetTbodyRows().length) {
                            updateAllEqualFields();
                        }
                    },
                    error: function() {
                        egIsOption = false;
                        egEqualOptions = [];
                        if (egGetTbodyRows().length) {
                            updateAllEqualFields();
                        }
                    }
                });
            }

            // Function untuk update semua equal field di tabel
            function updateAllEqualFields() {
                egGetTbodyRows().each(function() {
                    var $row = $(this);
                    var ri = $row.attr('data-row-idx');
                    if (ri === undefined || ri === '') return;
                    var equalCellId = 'egf-equal-cell-' + ri;
                    var currentValue = egGetFieldFromRow($row, ri, 'equal') || '';
                    var $equalCell = $('#' + equalCellId);
                    if (!$equalCell.length) return;

                    if (egIsOption && egEqualOptions.length > 0) {
                        var dropdownHtml = '<select id="egf-equal-' + ri + '" class="form-control form-control-sm eg-equal">' +
                            '<option value="">- Kosongkan -</option>';
                        egEqualOptions.forEach(function(opt) {
                            var selected = (currentValue == opt) ? 'selected' : '';
                            dropdownHtml += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                        });
                        dropdownHtml += '</select>';
                        $equalCell.html(dropdownHtml);
                    } else {
                        $equalCell.html(
                            '<input id="egf-equal-' + ri + '" type="text" class="form-control form-control-sm eg-equal" placeholder="Positif" value="' +
                            currentValue + '">');
                    }
                });
            }

            // open edit group modal
            var egOriginalIds = [];
            var egIsHaji = false;
            var egGroupLoadXhr = null;
            var egGroupLoadSeq = 0;

            function egSyncRowSnapshot($row) {
                $row.attr('data-eg-min', ($row.find('.eg-min').val() || '').trim());
                $row.attr('data-eg-max', ($row.find('.eg-max').val() || '').trim());
                $row.attr('data-eg-kesimpulan', ($row.find('.eg-kesimpulan').val() || '').trim());
                $row.attr('data-eg-equal', ($row.find('.eg-equal').val() || '').trim());
            }

            var egLibraryInitialized = false;
            var egUnitInitialized = false;

            function egSetSelect2Value(selector, id, text) {
                var $el = $(selector);
                if (!$el.length) {
                    return;
                }
                $el.empty();
                if (id) {
                    $el.append(new Option(text || id, id, true, true)).trigger('change');
                } else {
                    $el.trigger('change');
                }
            }

            function initSelect2ForEditGroup() {
                if (!egLibraryInitialized) {
                    $('#eg-library').select2({
                        ajax: {
                            url: "{{ route('getLibrary') }}",
                            type: 'post',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    _token: $('#csrf-token').val(),
                                    search: params.term
                                };
                            },
                            processResults: function(response) {
                                return {
                                    results: $.map(response, function(obj) {
                                        return { id: obj.id, text: obj.text };
                                    })
                                };
                            },
                            cache: true
                        },
                        placeholder: 'Pilih acuan baku mutu',
                        allowClear: true,
                        dropdownParent: $('#modal-edit-group'),
                        width: '100%'
                    });
                    egLibraryInitialized = true;
                }

                if (!egUnitInitialized) {
                    $('#eg-unit').select2({
                        ajax: {
                            url: "{{ route('getDataUnitBySelect') }}",
                            type: 'post',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    _token: $('#csrf-token').val(),
                                    search: params.term
                                };
                            },
                            processResults: function(response) {
                                return { results: response };
                            },
                            cache: true
                        },
                        placeholder: 'Pilih satuan',
                        allowClear: true,
                        dropdownParent: $('#modal-edit-group'),
                        width: '100%'
                    });
                    egUnitInitialized = true;
                }
            }

            function handleEditGroup() {
                var $btn = $(this);
                $('#eg-jenis').val($btn.data('jenis'));
                $('#eg-satuan').val($btn.data('satuan'));
                $('#eg-lab').val($btn.data('lab'));
                egIsHaji = $btn.data('haji') == 1 || $btn.data('haji') == '1';
                $('#eg-haji').val(egIsHaji ? '1' : '0');

                var jenisNama = $btn.data('jenis-nama') || '-';
                var satuanNama = $btn.data('satuan-nama') || '-';
                $('#eg-info-jenis').text(jenisNama);
                $('#eg-info-satuan').text(satuanNama);
                $('#eg-modal-parameter-title').text(jenisNama + ' — ' + satuanNama);

                initSelect2ForEditGroup();
                egSetSelect2Value('#eg-library', $btn.data('library'), $btn.data('library-text'));
                egSetSelect2Value('#eg-unit', $btn.data('unit'), $btn.data('unit-text'));

                // Toggle kolom umur berdasarkan is_haji (termasuk header)
                if (egIsHaji) {
                    $('.eg-umur-col').hide();
                    $('#eg-table thead .eg-umur-col').hide();
                } else {
                    $('.eg-umur-col').show();
                    $('#eg-table thead .eg-umur-col').show();
                }

                // Fetch detail ParameterSatuanKlinik untuk equal field
                updateEqualFieldFromParameterSatuanKlinik($('#eg-satuan').val());

                // fetch group items for prefill
                if (egGroupLoadXhr && egGroupLoadXhr.readyState !== 4) {
                    egGroupLoadXhr.abort();
                }
                var loadSeq = ++egGroupLoadSeq;
                egGetTbody().empty();
                egRowCounter = 0;
                egGroupLoadXhr = $.post("{{ route('elits-baku-mutu-klinik.get-group') }}", {
                    _token: $('#csrf-token').val(),
                    parameter_jenis_klinik_id: $('#eg-jenis').val(),
                    parameter_satuan_klinik_id: $('#eg-satuan').val(),
                    lab_id: $('#eg-lab').val(),
                    is_haji: egIsHaji ? '1' : '0'
                }, function(resp) {
                    if (loadSeq !== egGroupLoadSeq) {
                        return;
                    }
                    var tbody = egGetTbody();
                    tbody.empty();
                    egRowCounter = 0;
                    var items = resp.items || [];
                    egOriginalIds = items.map(function(it) {
                        return it.id_baku_mutu;
                    });
                    if (items.length === 0) {
                        addEgRow();
                    } else {
                        items.forEach(function(it) {
                            addEgRow(it);
                        });
                    }
                    var nilaiMode = detectEgNilaiMode(items);
                    var isMassal = parseInt(resp.is_massal_nilai_di_laporan, 10) === 1
                        || items.some(function(it) { return parseInt(it.is_massal_nilai_di_laporan, 10) === 1; });
                    $('#eg-is-massal').prop('checked', isMassal);
                    if (isMassal) {
                        setEgNilaiMode('sama_semua');
                    } else {
                        setEgNilaiMode(nilaiMode);
                    }
                    if (isMassal || nilaiMode === 'sama_semua') {
                        var sharedNilai = (resp.shared_nilai_baku_mutu || '').trim();
                        if (!sharedNilai && items.length > 0) {
                            sharedNilai = (items.find(function(it) {
                                return (it.nilai_baku_mutu || '').trim() !== '';
                            }) || {}).nilai_baku_mutu || '';
                            sharedNilai = (sharedNilai || '').trim();
                        }
                        $('#eg-nilai-shared').val(sharedNilai);
                        updateEgNilaiPreview($('#eg-nilai-shared'));
                    }
                    $('#modal-edit-group').data('eg-items', items);
                    var finalizeItems = items.slice();
                    setTimeout(function() {
                        if (loadSeq !== egGroupLoadSeq) {
                            return;
                        }
                        updateAllEqualFields();
                        egFinalizeLoadedRows(finalizeItems);
                    }, 100);
                    $('#modal-edit-group').modal('show');
                });
            }
            
            // Attach handler untuk kedua tabel
            $('#empTable').on('click', '.btn-edit-group', handleEditGroup);
            $('#empTableHaji').on('click', '.btn-edit-group', handleEditGroup);

            var egRowCounter = 0;

            function egGetTbody() {
                return $('#eg-table-tbody');
            }

            function egGetTbodyRows() {
                return egGetTbody().children('tr.eg-row');
            }

            $(document).on('change', 'select.eg-specific', function() {
                var $row = $(this).closest('tr.eg-row');
                var isKhusus = $(this).val() === '1';
                egApplyRowKhususState($row, isKhusus);
                if (egIsHaji || !isKhusus) {
                    return;
                }
                if (!$row.find('.eg-umin').val()) {
                    $row.find('.eg-umin').val(18);
                }
                if (!$row.find('.eg-umax').val()) {
                    $row.find('.eg-umax').val(99);
                }
            });

            var EG_ROW_FIELD_CLASS = {
                gender_baku_mutu: '.eg-gender',
                minimal_umur_baku_mutu: '.eg-umin',
                maksimal_umur_baku_mutu: '.eg-umax',
                nilai_baku_mutu: '.eg-nilai',
                min: '.eg-min',
                max: '.eg-max',
                equal: '.eg-equal',
                kesimpulan_baku_mutu: '.eg-kesimpulan',
                is_normal: '.eg-normal',
                id_baku_mutu: '.eg-id'
            };

            function egParseIsKhusus(value) {
                return value === 1 || value === '1' || value === true || value === 'true';
            }

            function egResolveIsKhususFromData(data) {
                if (!data) {
                    return false;
                }
                if (egParseIsKhusus(data.is_khusus_baku_mutu)) {
                    return true;
                }
                if (!egIsHaji && (data.minimal_umur_baku_mutu != null && data.minimal_umur_baku_mutu !== ''
                    || data.maksimal_umur_baku_mutu != null && data.maksimal_umur_baku_mutu !== '')) {
                    return true;
                }
                return false;
            }

            function egBuildSpecificSelectHtml(ri, isKhusus) {
                return '<select id="egf-specific-' + ri + '" class="form-control form-control-sm eg-specific">' +
                    '<option value="0"' + (isKhusus ? '' : ' selected') + '>General</option>' +
                    '<option value="1"' + (isKhusus ? ' selected' : '') + '>Specific</option>' +
                    '</select>';
            }

            function egApplyRowKhususState($row, isKhusus) {
                var val = isKhusus ? '1' : '0';
                $row.attr('data-is-khusus', val);
                var $sel = $row.find('select.eg-specific');
                $sel.val(val);
                $sel.find('option').prop('selected', false);
                $sel.find('option[value="' + val + '"]').prop('selected', true);
            }

            function egFinalizeLoadedRows(items) {
                var byId = {};
                (items || []).forEach(function(it) {
                    if (it && it.id_baku_mutu) {
                        byId[it.id_baku_mutu] = it;
                    }
                });
                egGetTbodyRows().each(function(index) {
                    var $row = $(this);
                    var rowId = ($row.find('.eg-id').val() || $row.attr('data-id') || '').trim();
                    var it = (rowId && byId[rowId]) ? byId[rowId] : ((items && items[index]) ? items[index] : null);
                    var isKhusus = it ? egResolveIsKhususFromData(it) : egParseIsKhusus($row.attr('data-is-khusus'));
                    egApplyRowKhususState($row, isKhusus);
                    if (!egIsHaji && isKhusus) {
                        if (it && it.minimal_umur_baku_mutu != null && it.minimal_umur_baku_mutu !== '') {
                            $row.find('.eg-umin').val(it.minimal_umur_baku_mutu);
                        } else if (!$row.find('.eg-umin').val()) {
                            $row.find('.eg-umin').val(18);
                        }
                        if (it && it.maksimal_umur_baku_mutu != null && it.maksimal_umur_baku_mutu !== '') {
                            $row.find('.eg-umax').val(it.maksimal_umur_baku_mutu);
                        } else if (!$row.find('.eg-umax').val()) {
                            $row.find('.eg-umax').val(99);
                        }
                    }
                });
            }

            function egReadIsKhususFromRow($row) {
                var v = $row.find('select.eg-specific').val();
                if (v === '1' || v === 1) {
                    return true;
                }
                return $row.attr('data-is-khusus') === '1';
            }

            function egGetRowByIdx(ri) {
                return egGetTbody().children('tr.eg-row[data-row-idx="' + ri + '"]');
            }

            function egGetFieldFromRow($row, ri, field) {
                if (field === 'is_khusus_baku_mutu') {
                    return egReadIsKhususFromRow($row) ? 1 : 0;
                }
                var selector = EG_ROW_FIELD_CLASS[field];
                var $el = selector ? $row.find(selector).first() : $();
                if ($el.length === 0) {
                    return null;
                }
                if ($el.attr('type') === 'checkbox') {
                    return $el.is(':checked') ? 1 : 0;
                }
                var v = ($el.val() || '').trim();
                return v === '' ? null : v;
            }

            function egGetField(ri, field) {
                var $row = egGetRowByIdx(ri);
                if (!$row.length) {
                    return null;
                }
                return egGetFieldFromRow($row, ri, field);
            }

            function addEgRow(data) {
                var ri = egRowCounter++;
                var isKhususRow = egResolveIsKhususFromData(data);
                var row = $('<tr class="eg-row" data-row-idx="' + ri + '" data-is-khusus="' + (isKhususRow ? '1' : '0') + '"></tr>');
                var idVal = (data && data.id_baku_mutu) ? data.id_baku_mutu : '';
                if (idVal) {
                    row.attr('data-id', idVal);
                }
                row.append(
                    '<td>' +
                    '<input type="hidden" class="eg-id" value="' + idVal + '">' +
                    egBuildSpecificSelectHtml(ri, isKhususRow) +
                    '</td>'
                );
                row.append(
                    '<td><select id="egf-gender-' + ri + '" class="form-control form-control-sm eg-gender">' +
                    '<option value="">-</option>' +
                    '<option value="A">Semua gender sama</option>' +
                    '<option value="L">Laki-laki</option>' +
                    '<option value="P">Perempuan</option>' +
                    '</select></td>'
                );
                var umurMinCell = '<td class="eg-umur-col"><input id="egf-umin-' + ri + '" type="number" class="form-control form-control-sm eg-umin" placeholder="18"></td>';
                var umurMaxCell = '<td class="eg-umur-col"><input id="egf-umax-' + ri + '" type="number" class="form-control form-control-sm eg-umax" placeholder="65"></td>';
                if (egIsHaji) {
                    umurMinCell = '<td class="eg-umur-col" style="display:none;"><input id="egf-umin-' + ri + '" type="number" class="form-control form-control-sm eg-umin" placeholder="18"></td>';
                    umurMaxCell = '<td class="eg-umur-col" style="display:none;"><input id="egf-umax-' + ri + '" type="number" class="form-control form-control-sm eg-umax" placeholder="65"></td>';
                }
                row.append(umurMinCell);
                row.append(umurMaxCell);
                row.append(
                    '<td class="eg-nilai-col">' +
                    '<input id="egf-nilai-' + ri + '" type="hidden" class="eg-nilai" value="">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary eg-open-nilai-editor py-0 px-2" data-row-idx="' + ri + '" title="Edit nilai di laporan">' +
                    '<i class="fa fa-edit"></i> Edit</button>' +
                    '<div class="eg-nilai-preview small border rounded p-1 mt-1 bg-white" style="min-height:32px;max-height:90px;overflow:auto;">-</div>' +
                    '</td>'
                );
                row.append(
                    '<td><input id="egf-min-' + ri + '" type="text" class="form-control form-control-sm eg-min" placeholder="4.0"></td>'
                );
                row.append(
                    '<td><input id="egf-max-' + ri + '" type="text" class="form-control form-control-sm eg-max" placeholder="6.5"></td>'
                );
                if (egIsOption && egEqualOptions.length > 0) {
                    var dropdownHtml = '<select id="egf-equal-' + ri + '" class="form-control form-control-sm eg-equal">' +
                        '<option value="">- Kosongkan -</option>';
                    egEqualOptions.forEach(function(opt) {
                        var selected = (data && data.equal == opt) ? 'selected' : '';
                        dropdownHtml += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                    dropdownHtml += '</select>';
                    row.append('<td id="egf-equal-cell-' + ri + '">' + dropdownHtml + '</td>');
                } else {
                    row.append(
                        '<td id="egf-equal-cell-' + ri + '"><input id="egf-equal-' + ri + '" type="text" class="form-control form-control-sm eg-equal" placeholder="Positif" value="' +
                        (data && data.equal ? data.equal : '') + '"></td>'
                    );
                }
                row.append(
                    '<td><input id="egf-kesimpulan-' + ri + '" type="text" class="form-control form-control-sm eg-kesimpulan" placeholder="Opsional"></td>'
                );
                row.append(
                    '<td class="text-center"><input id="egf-normal-' + ri + '" type="checkbox" class="eg-normal" value="1" title="Tandai sebagai normal"></td>'
                );
                row.append(
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-danger eg-remove" title="Hapus baris">' +
                    '<i class="fa fa-trash"></i>' +
                    '</button></td>'
                );
                if (data) {
                    egApplyRowKhususState(row, isKhususRow);
                    row.find('.eg-gender').val(data.gender_baku_mutu || '');
                    if (!egIsHaji) {
                        if (data.minimal_umur_baku_mutu != null && data.minimal_umur_baku_mutu !== '') {
                            row.find('.eg-umin').val(data.minimal_umur_baku_mutu);
                        } else if (isKhususRow) {
                            row.find('.eg-umin').val(18);
                        }
                        if (data.maksimal_umur_baku_mutu != null && data.maksimal_umur_baku_mutu !== '') {
                            row.find('.eg-umax').val(data.maksimal_umur_baku_mutu);
                        } else if (isKhususRow) {
                            row.find('.eg-umax').val(99);
                        }
                    }
                    row.find('.eg-nilai').val(data.nilai_baku_mutu || '');
                    row.find('.eg-min').val(data.min != null && data.min !== '' ? data.min : '');
                    row.find('.eg-max').val(data.max != null && data.max !== '' ? data.max : '');
                    row.find('.eg-equal').val(data.equal || '');
                    row.find('.eg-kesimpulan').val(data.kesimpulan_baku_mutu || '');
                    row.find('.eg-normal').prop('checked', (data.is_normal == 1 || data.is_normal === '1' || data.is_normal === true));
                    egSyncRowSnapshot(row);
                }
                egGetTbody().append(row);
                if (data) {
                    egApplyRowKhususState(row, isKhususRow);
                    egSyncRowSnapshot(row);
                    updateEgNilaiPreview(row.find('.eg-nilai'));
                }
            }

            $(document).on('input change', '#eg-table .eg-min, #eg-table .eg-max, #eg-table .eg-kesimpulan, #eg-table .eg-equal', function() {
                egSyncRowSnapshot($(this).closest('tr.eg-row'));
            });


            $('#eg-add-row').on('click', function() {
                addEgRow();
                applyEgNilaiMode($('input[name="eg_nilai_mode"]:checked').val() || 'sama_semua');
                // Update equal field untuk baris baru
                setTimeout(function() {
                    updateAllEqualFields();
                }, 50);
                // Inisialisasi tooltip untuk baris baru
                $('[data-toggle="tooltip"]').tooltip();
            });
            $(document).on('click', '.eg-remove', function() {
                $(this).closest('tr').remove();
            });

            // Inisialisasi tooltip saat modal dibuka
            $('#modal-edit-group').on('shown.bs.modal', function() {
                initSelect2ForEditGroup();
                $('[data-toggle="tooltip"]').tooltip();
                applyEgNilaiMode($('input[name="eg_nilai_mode"]:checked').val() || 'sama_semua');
                egFinalizeLoadedRows($('#modal-edit-group').data('eg-items') || []);
                // Pastikan kolom umur tersembunyi/tampil sesuai is_haji saat modal dibuka
                if (egIsHaji) {
                    $('.eg-umur-col').hide();
                    $('#eg-table thead .eg-umur-col').hide();
                } else {
                    $('.eg-umur-col').show();
                    $('#eg-table thead .eg-umur-col').show();
                }
            });

            // Reset saat modal ditutup
            $('#modal-edit-group').on('hidden.bs.modal', function() {
                egGroupLoadSeq++;
                egIsHaji = false;
                egRowCounter = 0;
                if (egGroupLoadXhr && egGroupLoadXhr.readyState !== 4) {
                    egGroupLoadXhr.abort();
                }
                egGroupLoadXhr = null;
                egGetTbody().empty();
                $('#modal-edit-group').removeData('eg-items');
                $('#eg-haji').val('0');
                $('.eg-umur-col').show();
                $('#eg-table thead .eg-umur-col').show();
                $('#eg-nilai-shared').val('');
                $('#preview_eg-nilai-shared').html('-');
                $('#eg-is-massal').prop('checked', false);
                setEgNilaiMode('sama_semua');
                $('#eg-info-jenis').text('-');
                $('#eg-info-satuan').text('-');
                $('#eg-modal-parameter-title').text('');
                $('#eg-library').val(null).trigger('change');
                $('#eg-unit').val(null).trigger('change');
            });

            $('#eg-is-massal').on('change', function() {
                if ($(this).is(':checked')) {
                    setEgNilaiMode('sama_semua');
                }
            });

            function egPrepareCollectContext() {
                var nilaiMode = $('input[name="eg_nilai_mode"]:checked').val() || 'sama_semua';
                var isMassal = $('#eg-is-massal').is(':checked');
                if (isMassal || nilaiMode === 'sama_semua') {
                    var shared = ($('#eg-nilai-shared').val() || '').trim();
                    if (!shared) {
                        egGetTbodyRows().find('.eg-nilai').each(function() {
                            var v = ($(this).val() || '').trim();
                            if (v) {
                                shared = v;
                                return false;
                            }
                        });
                        if (shared) {
                            $('#eg-nilai-shared').val(shared);
                            updateEgNilaiPreview($('#eg-nilai-shared'));
                        }
                    }
                }
            }

            function egSubmitReplaceGroup() {
                var $saveBtn = $('#eg-save');
                if ($saveBtn.prop('disabled')) {
                    return;
                }
                if (!$('#eg-library').val()) {
                    swal('Error', 'Acuan Baku Mutu wajib dipilih', 'error');
                    return;
                }
                if (!$('#eg-unit').val()) {
                    swal('Error', 'Satuan wajib dipilih', 'error');
                    return;
                }
                egPrepareCollectContext();
                var rows = collectEgRows();
                var visibleCount = egGetTbodyRows().length;
                if (rows.length < visibleCount) {
                    swal('Error', 'Gagal membaca semua baris tabel (' + rows.length + ' dari ' + visibleCount + '). Muat ulang halaman lalu coba lagi.', 'error');
                    return;
                }
                if (rows.length === 0) {
                    swal('Error', 'Tidak ada baris untuk disimpan', 'error');
                    return;
                }
                egConfirmSyncPermohonanThenSave(rows);
            }

            function egConfirmSyncPermohonanThenSave(rows) {
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
                        egSubmitReplaceGroupPayload(rows, 0, null);
                        return;
                    }

                    var $dateInput = $('<input>', {
                        type: 'date',
                        class: 'form-control',
                        id: 'eg-sync-from-date',
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
                        var fromDate = $('#eg-sync-from-date').val();
                        if (!fromDate) {
                            swal('Error', 'Tanggal mulai wajib diisi', 'error');
                            return;
                        }
                        swal.close();
                        egSubmitReplaceGroupPayload(rows, 1, fromDate);
                    });
                });
            }

            function egSubmitReplaceGroupPayload(collectedRows, syncPermohonan, syncFromDate) {
                var $saveBtn = $('#eg-save');
                if ($saveBtn.prop('disabled')) {
                    return;
                }
                $saveBtn.prop('disabled', true);
                var payload = {
                    _token: $('#csrf-token').val(),
                    parameter_jenis_klinik_id: $('#eg-jenis').val(),
                    parameter_satuan_klinik_id: $('#eg-satuan').val(),
                    lab_id: $('#eg-lab').val(),
                    unit_id: $('#eg-unit').val(),
                    library_id: $('#eg-library').val(),
                    is_haji: $('#eg-haji').val(),
                    is_massal_nilai_di_laporan: $('#eg-is-massal').is(':checked') ? 1 : 0,
                    shared_nilai_baku_mutu: $('#eg-nilai-shared').val() || '',
                    sync_permohonan: syncPermohonan ? 1 : 0,
                    rows: collectedRows
                };
                if (syncPermohonan && syncFromDate) {
                    payload.sync_permohonan_from_date = syncFromDate;
                }
                $.ajax({
                    url: "{{ route('elits-baku-mutu-klinik.replace-group') }}",
                    method: 'POST',
                    contentType: 'application/json; charset=UTF-8',
                    data: JSON.stringify(payload),
                    success: function(resp) {
                        if (resp.status) {
                            $('#modal-edit-group').modal('hide');
                            if (egIsHaji && tableHaji) {
                                tableHaji.ajax.reload(null, false);
                            } else {
                                table.ajax.reload(null, false);
                            }
                            var created = resp.created || collectedRows.length;
                            var syncMsg = (resp.synced_permohonan > 0)
                                ? ', ' + resp.synced_permohonan + ' permohonan disesuaikan'
                                : '';
                            if (created < collectedRows.length) {
                                swal('Peringatan', 'Hanya ' + created + ' dari ' + collectedRows.length + ' baris tersimpan. Periksa kembali data grup.' + syncMsg, 'warning');
                            } else {
                                swal('Success', 'Grup diperbarui: hapus ' + (resp.deleted || 0) + ' baris lama, simpan ' + created + ' baris baru' + syncMsg, 'success');
                            }
                        } else {
                            swal('Error', resp.pesan || 'Gagal update grup', 'error');
                        }
                    },
                    error: function() {
                        swal('Error', 'Gagal update grup', 'error');
                    },
                    complete: function() {
                        $saveBtn.prop('disabled', false);
                    }
                });
            }

            $('#eg-save').on('click', function() {
                egSubmitReplaceGroup();
            });

            function collectEgRows() {
                var rows = [];
                var nilaiMode = $('input[name="eg_nilai_mode"]:checked').val() || 'sama_semua';
                var isMassal = $('#eg-is-massal').is(':checked') ? 1 : 0;
                var useSharedNilai = isMassal === 1 || nilaiMode === 'sama_semua';
                var sharedNilai = useSharedNilai ? (($('#eg-nilai-shared').val() || '').trim() || null) : null;
                egGetTbodyRows().each(function() {
                    var $row = $(this);
                    var ri = $row.attr('data-row-idx');
                    if (ri === undefined || ri === '') return;
                    var isKhusus = egReadIsKhususFromRow($row) ? 1 : 0;
                    if (!isKhusus && !egIsHaji) {
                        var uminCheck = ($row.find('.eg-umin').val() || '').trim();
                        var umaxCheck = ($row.find('.eg-umax').val() || '').trim();
                        if (uminCheck !== '' || umaxCheck !== '') {
                            isKhusus = 1;
                        }
                    }
                    var rowNilai = useSharedNilai
                        ? sharedNilai
                        : (($row.find('.eg-nilai').val() || '').trim() || null);
                    var minVal = ($row.find('.eg-min').val() || '').trim();
                    var maxVal = ($row.find('.eg-max').val() || '').trim();
                    var equalVal = ($row.find('.eg-equal').val() || '').trim();
                    var kesimpulanVal = ($row.find('.eg-kesimpulan').val() || '').trim();
                    var isNormalVal = $row.find('.eg-normal').is(':checked') ? 1 : 0;
                    var rowData = {
                        is_khusus_baku_mutu: isKhusus,
                        gender_baku_mutu: ($row.find('.eg-gender').val() || '').trim() || null,
                        nilai_baku_mutu: rowNilai,
                        is_massal_nilai_di_laporan: isMassal,
                        min: minVal !== '' ? minVal : null,
                        max: maxVal !== '' ? maxVal : null,
                        equal: equalVal !== '' ? equalVal : null,
                        kesimpulan_baku_mutu: kesimpulanVal !== '' ? kesimpulanVal : null,
                        is_normal: isNormalVal
                    };
                    if (egIsHaji) {
                        rowData.minimal_umur_baku_mutu = null;
                        rowData.maksimal_umur_baku_mutu = null;
                    } else if (isKhusus === 1) {
                        var umin = ($row.find('.eg-umin').val() || '').trim();
                        var umax = ($row.find('.eg-umax').val() || '').trim();
                        rowData.minimal_umur_baku_mutu = umin !== '' ? umin : 18;
                        rowData.maksimal_umur_baku_mutu = umax !== '' ? umax : 99;
                    } else {
                        rowData.minimal_umur_baku_mutu = null;
                        rowData.maksimal_umur_baku_mutu = null;
                    }
                    rows.push(rowData);
                });
                return rows;
            }

            table.on('draw', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            $('#empTable').on('click', '.btn-hapus', function() {
                var kode = $(this).data('id');
                var nama = $(this).data('nama');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus data : " + nama,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'ajax',
                                method: 'get',
                                url: '/elits-baku-mutu-klinik-destroy/' + kode,
                                async: true,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status == true) {
                                        swal({
                                                title: "Success!",
                                                text: response.pesan,
                                                icon: "success"
                                            })
                                            .then(function() {
                                                document.location =
                                                    '/elits-baku-mutu-klinik';
                                            });
                                    } else {
                                        swal("Hapus Data Gagal!", {
                                            icon: "warning",
                                            title: "Failed!",
                                            text: response.pesan,
                                        });
                                    }
                                },
                                error: function() {
                                    swal("ERROR", "System tidak dapat menghapus data!",
                                        "error");
                                }
                            });
                        } else {
                            swal("Cancelled", "Hapus data dibatalkan!", "error");
                        }
                    });
            });

            // Handler untuk tombol Edit Satuan (khusus haji) - untuk kedua tabel
            function handleEditSatuan() {
                var jenis = $(this).data('jenis');
                var satuan = $(this).data('satuan');
                var lab = $(this).data('lab');
                
                $('#es-jenis').val(jenis);
                $('#es-satuan').val(satuan);
                $('#es-lab').val(lab);
                
                // Clear previous values
                if ($('#es-library').hasClass('select2-hidden-accessible')) {
                    $('#es-library').val(null).trigger('change');
                }
                if ($('#es-unit').hasClass('select2-hidden-accessible')) {
                    $('#es-unit').val(null).trigger('change');
                } else {
                    $('#es-library').val('');
                    $('#es-unit').val('');
                }
                $('#es-min').val('');
                $('#es-max').val('');
                $('#es-equal').val('');
                $('#es-nilai').val('');
                
                // Initialize Select2 jika belum
                initSelect2ForEditSatuan();
                
                // Fetch data satuan
                $.post("{{ route('elits-baku-mutu-klinik.get-satuan') }}", {
                    _token: $('#csrf-token').val(),
                    parameter_jenis_klinik_id: jenis,
                    parameter_satuan_klinik_id: satuan
                }, function(resp) {
                    if (resp.status && resp.data) {
                        // Set library
                        if (resp.data.library_id) {
                            var newOption = new Option(resp.data.library_text, resp.data.library_id, true, true);
                            $('#es-library').append(newOption).trigger('change');
                        }
                        
                        // Set unit
                        if (resp.data.unit_id) {
                            var newOption = new Option(resp.data.unit_text, resp.data.unit_id, true, true);
                            $('#es-unit').append(newOption).trigger('change');
                        }
                        
                        // Set min, max, equal, nilai_baku_mutu
                        $('#es-min').val(resp.data.min || '');
                        $('#es-max').val(resp.data.max || '');
                        $('#es-equal').val(resp.data.equal || '');
                        $('#es-nilai').val(resp.data.nilai_baku_mutu || '');
                        
                        $('#modal-edit-satuan').modal('show');
                    } else {
                        swal('Error', resp.pesan || 'Gagal mengambil data', 'error');
                    }
                }).fail(function(xhr, status, error) {
                    console.error('Error fetching satuan:', error, xhr.responseText);
                    swal('Error', 'Gagal mengambil data: ' + error, 'error');
                });
            }
            
            // Attach handler untuk kedua tabel
            $('#empTable').on('click', '.btn-edit-satuan', handleEditSatuan);
            $('#empTableHaji').on('click', '.btn-edit-satuan', handleEditSatuan);

            // Inisialisasi Select2 untuk Library dan Unit di modal edit satuan
            var esLibraryInitialized = false;
            var esUnitInitialized = false;
            
            function initSelect2ForEditSatuan() {
                if (!esLibraryInitialized) {
                    $("#es-library").select2({
                        ajax: {
                            url: "{{ route('getLibrary') }}",
                            type: "post",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    _token: $('#csrf-token').val(),
                                    search: params.term
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
                        allowClear: true,
                        dropdownParent: $('#modal-edit-satuan'),
                        width: '100%'
                    });
                    esLibraryInitialized = true;
                }
                
                if (!esUnitInitialized) {
                    $("#es-unit").select2({
                        ajax: {
                            url: "{{ route('getDataUnitBySelect') }}",
                            type: "post",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    _token: $('#csrf-token').val(),
                                    search: params.term
                                };
                            },
                            processResults: function(response) {
                                return {
                                    results: response
                                };
                            },
                            cache: true
                        },
                        placeholder: 'Pilih unit',
                        allowClear: true,
                        dropdownParent: $('#modal-edit-satuan'),
                        width: '100%'
                    });
                    esUnitInitialized = true;
                }
            }
            
            // Initialize Select2 saat modal dibuka
            $('#modal-edit-satuan').on('shown.bs.modal', function() {
                initSelect2ForEditSatuan();
            });

            // Handler untuk simpan edit satuan
            $('#es-save').on('click', function() {
                if (!$('#es-library').val()) {
                    swal('Error', 'Acuan Baku Mutu harus dipilih', 'error');
                    return;
                }
                if (!$('#es-unit').val()) {
                    swal('Error', 'Satuan harus dipilih', 'error');
                    return;
                }
                
                $.ajax({
                    url: "{{ route('elits-baku-mutu-klinik.update-satuan') }}",
                    method: 'POST',
                    data: {
                        _token: $('#csrf-token').val(),
                        parameter_jenis_klinik_id: $('#es-jenis').val(),
                        parameter_satuan_klinik_id: $('#es-satuan').val(),
                        library_id: $('#es-library').val(),
                        unit_id: $('#es-unit').val(),
                        min: $('#es-min').val(),
                        max: $('#es-max').val(),
                        equal: $('#es-equal').val(),
                        nilai_baku_mutu: $('#es-nilai').val()
                    },
                    success: function(resp) {
                        if (resp.status) {
                            $('#modal-edit-satuan').modal('hide');
                            table.ajax.reload(null, false);
                            swal('Success', resp.pesan || 'Data satuan berhasil diperbarui', 'success');
                        } else {
                            swal('Error', resp.pesan || 'Gagal update satuan', 'error');
                        }
                    },
                    error: function() {
                        swal('Error', 'Gagal update satuan', 'error');
                    }
                });
            });

            // Reset form saat modal ditutup
            $('#modal-edit-satuan').on('hidden.bs.modal', function() {
                $('#form-edit-satuan')[0].reset();
                $('#es-library').val(null).trigger('change');
                $('#es-unit').val(null).trigger('change');
            });
        });
    </script>
@endsection
