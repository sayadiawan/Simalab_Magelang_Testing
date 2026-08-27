@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection

@section('content')
    <style>
        .paper-container {
            background-color: #f5f5dc;
            border: 3px solid #4caf50;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .paper-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #4caf50;
        }

        .paper-container::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #4caf50;
        }

        .category-header {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 1px;
            margin: 20px 0 15px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .parameter-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px 15px;
            margin-bottom: 25px;
            align-items: start;
        }

        @media (max-width: 1200px) {
            .parameter-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .parameter-list {
                grid-template-columns: 1fr;
            }
        }

        .parameter-item {
            display: flex;
            align-items: flex-start;
            padding: 10px 12px;
            background: white;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            min-height: 44px;
            box-sizing: border-box;
        }

        .parameter-item.parameter-empty {
            background: transparent !important;
            border: none !important;
            padding: 10px 12px !important;
            min-height: 44px !important;
            visibility: hidden !important;
            /* Force the cell to take up space in grid */
            content: '' !important;
        }

        .parameter-item.parameter-empty:hover {
            background: transparent !important;
            border: none !important;
            transform: none !important;
        }

        .parameter-item:hover {
            background: #f0f0f0;
            border-color: #4caf50;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .parameter-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .parameter-item label {
            margin: 0;
            margin-left: 20pt;
            cursor: pointer;
            flex: 1;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .info-section {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: bold;
            width: 180px;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        /* Fix untuk input currency agar tidak ada angka membayang */
        #terbayar_permohonan_uji_payment_klinik {
            background-color: #ffffff !important;
            color: #333333 !important;
            text-shadow: none !important;
            -webkit-text-fill-color: #333333 !important;
            -webkit-appearance: none !important;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075) !important;
            font-family: inherit !important;
            font-weight: normal !important;
            line-height: 1.5 !important;
            text-align: left !important;
            caret-color: #333333 !important;
        }

        #terbayar_permohonan_uji_payment_klinik:focus {
            background-color: #ffffff !important;
            color: #333333 !important;
            text-shadow: none !important;
            -webkit-text-fill-color: #333333 !important;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25) !important;
            outline: none !important;
        }

        #terbayar_permohonan_uji_payment_klinik::selection {
            background-color: #007bff !important;
            color: white !important;
        }

        /* Styling untuk input group */
        .input-group-text {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 60px;
        }

        /* Hover effect untuk input field */
        #terbayar_permohonan_uji_payment_klinik:hover {
            border-color: #0b3a5c !important;
            box-shadow: 0 0 0 0.1rem rgba(11, 58, 92, 0.15) !important;
        }

        /* Focus state untuk input */
        #terbayar_permohonan_uji_payment_klinik:focus {
            border-color: #0b3a5c !important;
            box-shadow: 0 0 0 0.2rem rgba(11, 58, 92, 0.25) !important;
        }

        /* Styling untuk input group prepend */
        .input-group-prepend .input-group-text {
            border-right: none !important;
        }

        /* Placeholder styling */
        #terbayar_permohonan_uji_payment_klinik::placeholder {
            color: #999;
            font-style: italic;
            opacity: 0.8;
        }

        /* Prevent any webkit autofill styling */
        #terbayar_permohonan_uji_payment_klinik:-webkit-autofill,
        #terbayar_permohonan_uji_payment_klinik:-webkit-autofill:hover,
        #terbayar_permohonan_uji_payment_klinik:-webkit-autofill:focus {
            -webkit-text-fill-color: #333333 !important;
            -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
            transition: background-color 5000s ease-in-out 0s !important;
        }

        /* Modal pembayaran — bisa di-scroll saat konten tinggi */
        #modal-payment .modal-dialog {
            max-width: 700px;
            max-height: calc(100vh - 2rem);
            margin: 1rem auto;
            display: flex;
            flex-direction: column;
        }

        #modal-payment .modal-dialog.modal-dialog-centered {
            align-items: stretch;
            min-height: 0;
        }

        #modal-payment .modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #modal-payment .modal-content > form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
            margin-bottom: 0;
        }

        #modal-payment .modal-header,
        #modal-payment .modal-footer {
            flex-shrink: 0;
        }

        #modal-payment .modal-body {
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            min-height: 0;
        }
    </style>

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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan
                                        Uji Klinik
                                        Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>create permohonan uji paket
                                        klinik</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Tambah Permohonan Uji Paket Klinik</h4>
            <small style="color: #999;"><!-- FILE: add-paramater.blade.php (ROOT) --></small>
        </div>

        <div class="card-body">
            <form action="{{ route('elits-permohonan-uji-klinik-2.store-parameter') }}" method="POST"
                enctype="multipart/form-data" id="form">

                @csrf


                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik" value="{{ $id }}"
                    readonly>

                <div class="paper-container">
                    @php
                        $paket = $paket ?? [];
                        $paket_extra = $paket_extra ?? [];
                    @endphp
                    <div class="info-section">
                        <div class="info-row">
                            <div class="info-label">No. Sample:</div>
                            <div class="info-value">{{ $code }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">No. Rekam Medis:</div>
                            <div class="info-value">
                                {{ $item->getNoRekamMedis() }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nama Pasien:</div>
                            <div class="info-value">{{ $pasien->nama_pasien }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Umur/Jenis Kelamin:</div>
                            <div class="info-value">
                                {{ $umur_string }}
                                /
                                {{ $pasien->gender_pasien == 'L' || $pasien->gender_pasien == 'male' ? 'Laki-laki' : 'Perempuan' }}
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Category Layout from Database with Grid Support --}}
                    @if(isset($categoryLayouts) && count($categoryLayouts) > 0)
                        @foreach($categoryLayouts as $category)
                            <div class="category-header">
                                {{ $category->category_code }}. {{ $category->category_name }}
                            </div>

                            @php
                                $emptyPosition = $category->empty_column_position ?? 'none';
                                $gridRows = $category->grid_rows ?? 0;
                                $gridColumns = $category->grid_columns ?? 3;
                                $items = $category->categoryItems ?? collect();

                                // Check if using grid positioning
                                $useGrid = $items->where('row_position', '!=', null)->count() > 0;

                                if ($useGrid) {
                                    // Build grid array - items already sorted by controller
                                    $grid = [];
                                    $maxRow = 0;
                                    $maxCol = 0;

                                    foreach ($items as $item) {
                                        if ($item->parameterPaketKlinik && $item->row_position && $item->column_position) {
                                            $row = (int)$item->row_position;
                                            $col = (int)$item->column_position;

                                            if (!isset($grid[$row])) {
                                                $grid[$row] = [];
                                            }

                                            // Store item in grid by row and column
                                            $grid[$row][$col] = $item;

                                            if ($row > $maxRow) {
                                                $maxRow = $row;
                                            }
                                            if ($col > $maxCol) {
                                                $maxCol = $col;
                                            }
                                        }
                                    }

                                    // Always use gridRows/gridColumns if set, otherwise use detected max
                                    $actualRows = $gridRows > 0 ? (int)$gridRows : max($maxRow, 1);
                                    $actualColumns = $gridColumns > 0 ? (int)$gridColumns : max($maxCol, 3);
                                }
                            @endphp

                            @if($useGrid)
                                {{-- Grid-based rendering --}}
                                <div class="parameter-list" style="grid-template-columns: repeat({{ $actualColumns }}, 1fr);">
                                    @for($r = 1; $r <= $actualRows; $r++)
                                        @for($c = 1; $c <= $actualColumns; $c++)
                                            @if(isset($grid[$r][$c]) && $grid[$r][$c]->parameterPaketKlinik)
                                                @php $item = $grid[$r][$c]; @endphp
                                                <div class="parameter-item">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                                        @if (in_array($item->parameterPaketKlinik->name_parameter_paket_klinik, array_column($paket ?? [], 'name_parameter_paket_klinik'))) checked @endif
                                                        id="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                    <label for="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                        {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                                    </label>
                                                </div>
                                            @else
                                                <div class="parameter-item parameter-empty">
                                                    <span style="opacity: 0;">&nbsp;</span>
                                                </div>
                                            @endif
                                        @endfor
                                    @endfor
                                </div>
                            @else
                                {{-- Legacy list rendering --}}
                                <div class="parameter-list">
                                    @if($emptyPosition == 'left')
                                        <div class="parameter-item parameter-empty">
                                            <span style="opacity: 0;">&nbsp;</span>
                                        </div>
                                    @endif

                                    @if($items && count($items) > 0)
                                        @foreach($items->sortBy('sort_order') as $item)
                                            @if($item->parameterPaketKlinik)
                                                <div class="parameter-item">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                                        @if (in_array($item->parameterPaketKlinik->name_parameter_paket_klinik, array_column($paket ?? [], 'name_parameter_paket_klinik'))) checked @endif
                                                        id="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                    <label for="param_{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                        {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif

                                    @if($emptyPosition == 'right')
                                        <div class="parameter-item parameter-empty">
                                            <span style="opacity: 0;">&nbsp;</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @else
                        {{-- Fallback: old hardcoded layout --}}
                        <div class="category-header">A. HEMATOLOGI</div>
                        <div class="parameter-list">
                        @php
                            $hematologi_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                                return str_contains($nama, 'darah') || str_contains($nama, 'hematologi') || str_contains($nama, 'hemoglobin');
                            });
                            $hematologi_pakets = collect();
                            foreach ($hematologi_jenis as $jenis) {
                                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                                    $hematologi_pakets = $hematologi_pakets->merge($jenis->pakets);
                                }
                            }
                            $hematologi_pakets = $hematologi_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
                        @endphp
                        @foreach ($hematologi_pakets as $paket_item)
                            <div class="parameter-item">
                                <input type="checkbox" class="form-check-input"
                                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket ?? [], 'name_parameter_paket_klinik'))) checked @endif
                                    id="hematologi_{{ $paket_item->id_parameter_paket_klinik }}">
                                <label
                                    for="hematologi_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="category-header">B. URIN</div>
                    <div class="parameter-list">
                        @php
                            // Cari jenis parameter yang sesuai dengan URIN
                            $urin_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                                return str_contains($nama, 'urin') ||
                                    str_contains($nama, 'urine') ||
                                    str_contains($nama, 'narkoba');
                            });
                            $urin_pakets = collect();
                            foreach ($urin_jenis as $jenis) {
                                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                                    $urin_pakets = $urin_pakets->merge($jenis->pakets);
                                }
                            }
                            // Deduplikasi berdasarkan ID
                            $urin_pakets = $urin_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
                        @endphp
                        @foreach ($urin_pakets as $paket_item)
                            <div class="parameter-item">
                                <input type="checkbox" class="form-check-input"
                                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket ?? [], 'name_parameter_paket_klinik'))) checked @endif
                                    id="urin_{{ $paket_item->id_parameter_paket_klinik }}">
                                <label
                                    for="urin_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="category-header">C. IMUNOLOGI</div>
                    <div class="parameter-list">
                        @php
                            // Cari jenis parameter yang sesuai dengan IMUNOLOGI
                            $imunologi_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                                return str_contains($nama, 'imunologi') ||
                                    str_contains($nama, 'serologi') ||
                                    str_contains($nama, 'widal') ||
                                    str_contains($nama, 'dengue') ||
                                    str_contains($nama, 'hepatitis');
                            });
                            $imunologi_pakets = collect();
                            foreach ($imunologi_jenis as $jenis) {
                                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                                    $imunologi_pakets = $imunologi_pakets->merge($jenis->pakets);
                                }
                            }
                            // Deduplikasi berdasarkan ID
                            $imunologi_pakets = $imunologi_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
                        @endphp
                        @foreach ($imunologi_pakets as $paket_item)
                            <div class="parameter-item">
                                <input type="checkbox" class="form-check-input"
                                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket ?? [], 'name_parameter_paket_klinik'))) checked @endif
                                    id="imunologi_{{ $paket_item->id_parameter_paket_klinik }}">
                                <label
                                    for="imunologi_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="category-header">D. KIMIA DARAH</div>
                    <div class="parameter-list">
                        @php
                            // Cari jenis parameter yang sesuai dengan KIMIA DARAH
                            $kimia_jenis = $parameter_jenis_klinik->filter(function ($jenis) {
                                $nama = strtolower($jenis->name_parameter_jenis_klinik);
                                return str_contains($nama, 'kimia') || str_contains($nama, 'klinik');
                            });
                            $kimia_pakets = collect();
                            foreach ($kimia_jenis as $jenis) {
                                if ($jenis->pakets && $jenis->pakets->isNotEmpty()) {
                                    $kimia_pakets = $kimia_pakets->merge($jenis->pakets);
                                }
                            }
                            // Deduplikasi berdasarkan ID
                            $kimia_pakets = $kimia_pakets->sortBy('sort')->unique('id_parameter_paket_klinik')->values();
                        @endphp
                        @foreach ($kimia_pakets as $paket_item)
                            <div class="parameter-item">
                                <input type="checkbox" class="form-check-input"
                                    name="jenis_parameters[{{ $paket_item->id_parameter_paket_klinik }}][pakets][]"
                                    value="{{ $paket_item->id_parameter_paket_klinik }}_{{ $paket_item->harga_parameter_paket_klinik }}"
                                    @if (in_array($paket_item->name_parameter_paket_klinik, array_column($paket ?? [], 'name_parameter_paket_klinik'))) checked @endif
                                    id="kimia_{{ $paket_item->id_parameter_paket_klinik }}">
                                <label
                                    for="kimia_{{ $paket_item->id_parameter_paket_klinik }}">{{ $paket_item->name_parameter_paket_klinik }}</label>
                            </div>
                        @endforeach
                    </div>
                    @endif
                    {{-- End of category layout --}}

                    @if ($parameter_paket_extra->isNotEmpty())
                        <div class="category-header">PAKET EXTRA</div>
                        <div class="parameter-list">
                            @foreach ($parameter_paket_extra as $val)
                                <div class="parameter-item">
                                    <input type="checkbox" class="form-check-input"
                                        name="paket_extra[{{ $val->id_parameter_paket_extra }}]"
                                        value="{{ $val->id_parameter_paket_extra }}_{{ $val->harga_parameter_paket_extra }}"
                                        {{ in_array($val->id_parameter_paket_extra, $paket_extra) ? 'checked' : '' }}
                                        id="extra_{{ $val->id_parameter_paket_extra }}">
                                    <label
                                        for="extra_{{ $val->id_parameter_paket_extra }}">{{ $val->nama_parameter_paket_extra }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary ml-2 mr-2 btn-simpan">Simpan</button>
                    <button type="button" class="btn btn-light"
                        onclick="document.location='{{ route('elits-permohonan-uji-klinik-2.index') }}'">Kembali</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL PAYMENT --}}
    <div class="modal fade" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="form-payment" method="POST">
                    @csrf
                    <input type="hidden" id="id_permohonan_uji_klinik" name="id_permohonan_uji_klinik">
                    <input type="hidden" id="total_harga" name="total_harga">

                    <div class="modal-header"
                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white;">
                        <h5 class="modal-title" id="paymentModalLabel">
                            <i class="fa fa-cash-register mr-2"></i>
                            <span>Proses Pembayaran</span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            style="color: white;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="background-color: #f8f9fa; padding: 30px;">
                        <!-- Patient Info Card -->
                        <div class="card mb-3" style="border-left: 4px solid #0b3a5c;">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="fa fa-user-circle mr-2"></i>Informasi Pasien
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Nama:</strong></p>
                                        <p id="display_nama_pasien" class="ml-3">-</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Alamat:</strong></p>
                                        <p id="display_alamat_pasien" class="ml-3">-</p>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Petugas:</strong></p>
                                        <p id="display_petugas" class="ml-3">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Payment Card -->
                        <div class="card mb-3"
                            style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 text-center mb-3">
                                        <h6 class="mb-2"><i class="fa fa-file-invoice-dollar mr-2"></i>Total Pembayaran</h6>
                                        <h2 id="display_total_harga" class="mb-0 font-weight-bold">Rp. 0</h2>
                                    </div>
                                </div>
                                <div id="partial-payment-section" style="display: none;">
                                    <hr style="border-color: rgba(255,255,255,0.3); margin: 10px 0;">
                                    <div class="row">
                                        <div class="col-6">
                                            <small><i class="fa fa-check mr-1"></i>Sudah dibayar:</small>
                                        </div>
                                        <div class="col-6 text-right">
                                            <small id="display_sudah_dibayar" class="font-weight-bold">Rp. 0</small>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <small><i class="fa fa-exclamation-circle mr-1"></i>Sisa tagihan:</small>
                                        </div>
                                        <div class="col-6 text-right">
                                            <small id="display_sisa_tagihan" class="font-weight-bold">Rp. 0</small>
                                        </div>
                                    </div>
                                </div>
                                <!-- Biaya Pengambilan Sampel (if any) -->
                                <div id="biaya_pengambilan_section" style="display: none;">
                                    <hr style="border-color: rgba(255,255,255,0.3); margin: 15px 0;">
                                    <div class="row">
                                        <div class="col-6">
                                            <small><i class="fa fa-vial mr-1"></i>Biaya Parameter:</small>
                                        </div>
                                        <div class="col-6 text-right">
                                            <small id="display_biaya_parameter" class="font-weight-bold">Rp. 0</small>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <small><i class="fa fa-home mr-1"></i>Biaya Pengambilan Sampel:</small>
                                        </div>
                                        <div class="col-6 text-right">
                                            <small id="display_biaya_pengambilan" class="font-weight-bold">Rp. 0</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Pemeriksaan Card -->
                        <div class="card mb-3" id="detail-pemeriksaan-card" style="display: none;">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fa fa-list-alt mr-2 text-info"></i>Detail Pemeriksaan</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 10;">
                                            <tr>
                                                <th style="width: 80px;">Tipe</th>
                                                <th>Nama Pemeriksaan</th>
                                                <th style="width: 150px;" class="text-right">Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payment-items-body">
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">
                                                    <i class="fa fa-spinner fa-spin mr-2"></i>Memuat data...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Input Card -->
                        <div class="payment-input-card"
                            style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                            <div class="payment-field-label mb-3" style="font-weight: 600; color: #333;">
                                <i class="fa fa-wallet mr-2"></i> Nominal Dibayarkan
                            </div>
                            <div class="input-group mb-2" style="height: 50px;">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"
                                        style="height: 50px; font-size: 18px; font-weight: 600; background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white; border: none; border-radius: 10px 0 0 10px;">
                                        Rp.
                                    </div>
                                </div>
                                <input type="text" class="form-control" id="terbayar_permohonan_uji_payment_klinik"
                                    name="terbayar_permohonan_uji_payment_klinik"
                                    style="font-size: 18px; height: 50px; border: 2px solid #e0e0e0; border-left: none; border-radius: 0 10px 10px 0;"
                                    placeholder="Enter Amount Here" autocomplete="off">
                            </div>
                            <div class="payment-error-message" id="payment-error"
                                style="display: none; background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px; border-radius: 5px;">
                                <i class="fa fa-exclamation-triangle mr-2"></i>
                                <span id="payment-error-text"></span>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="quick-amount-buttons" style="display: flex; gap: 10px; margin-top: 15px;">
                                <button type="button" class="quick-amount-btn exact"
                                    style="flex: 1; padding: 10px; border: 2px solid #28a745; background: white; color: #28a745; border-radius: 8px; font-weight: 600; cursor: pointer;"
                                    data-action="exact">
                                    <i class="fa fa-check mr-1"></i> Pas
                                </button>
                                <button type="button" class="quick-amount-btn"
                                    style="flex: 1; padding: 10px; border: 2px solid #0b3a5c; background: white; color: #0b3a5c; border-radius: 8px; font-weight: 600; cursor: pointer;"
                                    data-amount="50000">
                                    + 50rb
                                </button>
                                <button type="button" class="quick-amount-btn"
                                    style="flex: 1; padding: 10px; border: 2px solid #0b3a5c; background: white; color: #0b3a5c; border-radius: 8px; font-weight: 600; cursor: pointer;"
                                    data-amount="100000">
                                    + 100rb
                                </button>
                            </div>
                        </div>

                        <!-- Change Card -->
                        <div class="card" id="change-card"
                            style="display: none; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                            <div class="card-body text-center">
                                <h6 class="mb-2"><i class="fa fa-hand-holding-usd mr-2"></i>Kembalian</h6>
                                <h3 id="display_kembalian" class="mb-0 font-weight-bold">Rp. 0</h3>
                            </div>
                        </div>

                        <!-- Hidden fields for form submission -->
                        <input type="hidden" id="nota_petugas_permohonan_uji_payment_klinik"
                            name="nota_petugas_permohonan_uji_payment_klinik">
                        <input type="hidden" id="nota_namapetugas_permohonan_uji_payment_klinik"
                            name="nota_namapetugas_permohonan_uji_payment_klinik">
                        <input type="hidden" id="total_harga_permohonan_uji_payment_klinik"
                            name="total_harga_permohonan_uji_payment_klinik">
                        <input type="hidden" id="sisa_tagihan" name="sisa_tagihan" value="0">
                        <input type="hidden" id="sudah_dibayar" name="sudah_dibayar" value="0">
                    </div>

                    <div class="modal-footer" style="background-color: #f8f9fa;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fa fa-times mr-2"></i>
                            <span>Batal</span>
                        </button>

                        <button type="button" class="btn btn-primary ml-2" id="btnSavePayment">
                            <i class="fa fa-check-circle mr-2"></i>
                            <span id="btnSavePaymentText">Proses Pembayaran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/cdn-local/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        var CSRF_TOKEN = $('#csrf-token').val();

        // Format Rupiah
        function formatRupiah(angka) {
            if (!angka) return '0';
            var number_string = angka.toString().replace(/[^,\d]/g, '');
            var split = number_string.split(',');
            var sisa = split[0].length % 3;
            var rupiah = split[0].substr(0, sisa);
            var ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                var separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Format Number (remove dots)
        function formatNumber(angka) {
            if (!angka) return '';
            return angka.toString().replace(/\./g, '');
        }

        function formatTitleCase(text) {
            if (!text || text === '-') return '-';
            return text.toString().toLowerCase().replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

        function buildSelectedExaminationItems() {
            var items = [];

            $('input[type="checkbox"][name^="jenis_parameters"]:checked').each(function() {
                var $checkbox = $(this);
                var valueParts = ($checkbox.val() || '').split('_');
                var harga = parseInt(valueParts[1]) || 0;
                var name = $checkbox.closest('.parameter-item').find('label').text().trim() || '-';

                items.push({
                    type: 'Paket',
                    name: name,
                    harga: harga
                });
            });

            $('input[type="checkbox"][name^="paket_extra"]:checked').each(function() {
                var $checkbox = $(this);
                var valueParts = ($checkbox.val() || '').split('_');
                var harga = parseInt(valueParts[1]) || 0;
                var name = $checkbox.closest('.parameter-item').find('label').text().trim() || '-';

                items.push({
                    type: 'Paket Extra',
                    name: name,
                    harga: harga
                });
            });

            return items;
        }

        function renderPaymentItems(items) {
            if (!items || !items.length) {
                $('#detail-pemeriksaan-card').hide();
                $('#payment-items-body').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-info-circle mr-2"></i>Belum ada pemeriksaan yang dipilih</td></tr>');
                return;
            }

            var itemsHtml = '';
            $.each(items, function(index, item) {
                var typeBadge = '';
                if (item.type === 'Paket Extra') {
                    typeBadge = '<span class="badge badge-warning badge-pill"><i class="fa fa-star mr-1"></i>Paket Extra</span>';
                } else if (item.type === 'Paket') {
                    typeBadge = '<span class="badge badge-primary badge-pill"><i class="fa fa-box mr-1"></i>Paket</span>';
                } else {
                    typeBadge = '<span class="badge badge-info badge-pill"><i class="fa fa-flask mr-1"></i>Parameter</span>';
                }

                itemsHtml += '<tr>';
                itemsHtml += '<td>' + typeBadge + '</td>';
                itemsHtml += '<td>' + (item.name || '-') + '</td>';
                itemsHtml += '<td class="text-right font-weight-bold">Rp. ' + formatRupiah(item.harga || 0) + '</td>';
                itemsHtml += '</tr>';
            });

            $('#payment-items-body').html(itemsHtml);
            $('#detail-pemeriksaan-card').show();
        }

        function ensurePaymentItems(data) {
            if (data && data.items && data.items.length > 0) {
                return data.items;
            }

            return buildSelectedExaminationItems();
        }
        $(document).ready(function() {
            // Track selected parameters to prevent duplicates
            var selectedParameters = {};

            // Monitor all checkboxes
            $('input[type="checkbox"][name^="jenis_parameters"]').on('change', function() {
                var checkbox = $(this);
                var parameterId = checkbox.val().split('_')[0];
                var parameterName = checkbox.closest('.parameter-item').find('label').text().trim();

                if (checkbox.is(':checked')) {
                    // Check if already selected
                    if (selectedParameters[parameterId]) {
                        swal({
                            title: "Parameter Sudah Dipilih!",
                            text: "Parameter \"" + parameterName + "\" sudah dipilih sebelumnya.",
                            icon: "warning"
                        });
                        checkbox.prop('checked', false);
                        return;
                    }

                    // Add to selected list
                    selectedParameters[parameterId] = parameterName;
                } else {
                    // Remove from selected list
                    delete selectedParameters[parameterId];
                }
            });

            // Initialize: check already checked items on page load
            $('input[type="checkbox"][name^="jenis_parameters"]:checked').each(function() {
                var parameterId = $(this).val().split('_')[0];
                var parameterName = $(this).closest('.parameter-item').find('label').text().trim();
                selectedParameters[parameterId] = parameterName;
            });

            $('.btn-simpan').on('click', function(event) {
                event.preventDefault();

                var $button = $(this);
                $button.prop('disabled', true);
                $button.html('Loading...');

                $('#form').ajaxSubmit({
                    timeout: 120000,
                    success: function(response) {
                        console.log('Full Response:', response);
                        console.log('Payment Data:', response.payment_data);

                        if (response.status === true) {
                            swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            }).then(function() {
                                document.location = "{{ url('/elits-permohonan-uji-klinik/registrasi') }}";
                            });
                        } else {
                            $button.prop('disabled', false);
                            $button.html('Simpan');

                            var pesan = "";
                            const wrapper = document.createElement('div');

                            if (typeof response.pesan === 'object') {
                                $.each(response.pesan, function(key, value) {
                                    pesan += value + '.<br>';
                                });
                                wrapper.innerHTML = pesan;
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
                    error: function(xhr, textStatus) {
                        $button.prop('disabled', false);
                        $button.html('Simpan');

                        var message = 'System gagal menyimpan!';
                        if (textStatus === 'timeout') {
                            message += ' Request timeout, coba lagi.';
                        } else if (xhr.responseJSON && xhr.responseJSON.pesan) {
                            message = xhr.responseJSON.pesan;
                        } else if (xhr.responseText) {
                            try {
                                var parsed = JSON.parse(xhr.responseText);
                                if (parsed.pesan) {
                                    message = parsed.pesan;
                                }
                            } catch (e) {
                                if (xhr.status) {
                                    message += ' (HTTP ' + xhr.status + ')';
                                }
                            }
                        }

                        swal('Error!', message, 'error');
                    }
                });
            });
            // Function to update payment modal if already open
            function updatePaymentModalIfOpen(data) {
                // Check if modal is open in current window
                if ($('#modal-payment').hasClass('show') || $('#modal-payment').is(':visible')) {
                    var totalHarga = parseInt(data.total_harga) || 0;
                    var sudahDibayar = parseInt(data.sudah_dibayar) || 0;
                    var sisaTagihan = parseInt(data.sisa_tagihan);
                    if (isNaN(sisaTagihan)) {
                        sisaTagihan = Math.max(0, totalHarga - sudahDibayar);
                    }

                    // Update total harga
                    $('#total_harga').val(totalHarga);
                    $('#sudah_dibayar').val(sudahDibayar);
                    $('#sisa_tagihan').val(sisaTagihan);
                    $('#total_harga_permohonan_uji_payment_klinik').val(totalHarga);
                    $('#display_total_harga').text(data.total_harga_custom || 'Rp. 0');

                    if (sudahDibayar > 0) {
                        $('#display_sudah_dibayar').text(data.sudah_dibayar_custom || ('Rp. ' + formatRupiah(sudahDibayar)));
                        $('#display_sisa_tagihan').text(data.sisa_tagihan_custom || ('Rp. ' + formatRupiah(sisaTagihan)));
                        $('#partial-payment-section').show();
                    } else {
                        $('#partial-payment-section').hide();
                    }

                    // Update detail pemeriksaan if exists
                    renderPaymentItems(ensurePaymentItems(data));

                    // Update biaya pengambilan sampel if exists
                    if (data.biaya_pengambilan_sampel && data.biaya_pengambilan_sampel > 0) {
                        if ($('#display_biaya_parameter').length) {
                            $('#display_biaya_parameter').text('Rp. ' + formatRupiah(data.total_harga_parameter));
                            $('#display_biaya_pengambilan').text('Rp. ' + formatRupiah(data.biaya_pengambilan_sampel));
                            $('#biaya_pengambilan_section').show();
                        }
                    }

                    // Recalculate change if payment input has value
                    var terbayar = $('#terbayar_permohonan_uji_payment_klinik').val();
                    if (terbayar) {
                        $('#terbayar_permohonan_uji_payment_klinik').trigger('input');
                    }

                    return true; // Modal was updated
                }
                return false; // Modal was not open
            }

            // Function to show payment modal
            function showPaymentModal(data) {
                // Check if modal is already open, if yes, just update it
                if (updatePaymentModalIfOpen(data)) {
                    return; // Modal already updated, don't show again
                }

                // Reset form
                $('#form-payment').trigger('reset');
                $('#terbayar_permohonan_uji_payment_klinik').val('');
                $('#payment-error').hide();
                $('#change-card').hide();
                $('#btnSavePayment').prop('disabled', true);

                var totalHarga = parseInt(data.total_harga) || 0;
                var sudahDibayar = parseInt(data.sudah_dibayar) || 0;
                var sisaTagihan = parseInt(data.sisa_tagihan);
                if (isNaN(sisaTagihan)) {
                    sisaTagihan = Math.max(0, totalHarga - sudahDibayar);
                }

                // Set data to form
                $('#id_permohonan_uji_klinik').val(data.id_permohonan_uji_klinik);
                $('#total_harga').val(totalHarga);
                $('#sudah_dibayar').val(sudahDibayar);
                $('#sisa_tagihan').val(sisaTagihan);
                $('#nota_petugas_permohonan_uji_payment_klinik').val(data.nota_petugas);
                $('#nota_namapetugas_permohonan_uji_payment_klinik').val(data.nota_namapetugas);
                $('#total_harga_permohonan_uji_payment_klinik').val(totalHarga);

                // Display data
                $('#display_nama_pasien').text((data.nama_pasien || '-').toUpperCase());
                $('#display_alamat_pasien').text(formatTitleCase(data.alamat_pasien || '-'));
                $('#display_petugas').text((data.nota_namapetugas || '-').toUpperCase());
                $('#display_total_harga').text(data.total_harga_custom || 'Rp. 0');

                if (sudahDibayar > 0) {
                    $('#display_sudah_dibayar').text(data.sudah_dibayar_custom || ('Rp. ' + formatRupiah(sudahDibayar)));
                    $('#display_sisa_tagihan').text(data.sisa_tagihan_custom || ('Rp. ' + formatRupiah(sisaTagihan)));
                    $('#partial-payment-section').show();
                } else {
                    $('#partial-payment-section').hide();
                }

                // Handle biaya pengambilan sampel if exists
                if (data.biaya_pengambilan_sampel && data.biaya_pengambilan_sampel > 0) {
                    // Controller sudah menghitung total_harga = parameter + biaya pengambilan
                    $('#display_biaya_parameter').text('Rp. ' + formatRupiah(data.total_harga_parameter));
                    $('#display_biaya_pengambilan').text('Rp. ' + formatRupiah(data.biaya_pengambilan_sampel));
                    $('#biaya_pengambilan_section').show();
                } else {
                    $('#biaya_pengambilan_section').hide();
                }

                // Display detail pemeriksaan (items)
                renderPaymentItems(ensurePaymentItems(data));

                // Show modal
                $('#modal-payment').modal('show');

                // Focus on payment input after modal is shown
                $('#modal-payment').on('shown.bs.modal', function() {
                    // Clear any existing value and reset formatting
                    var $input = $('#terbayar_permohonan_uji_payment_klinik');
                    $input.val('');

                    // Focus with slight delay to ensure modal is fully rendered
                    setTimeout(function() {
                        $input.focus();
                    }, 300);
                });
            }

            // Quick amount buttons
            $(document).on('click', '.quick-amount-btn', function() {
                var action = $(this).data('action');
                var amount = $(this).data('amount');
                var sisaTagihan = parseInt($('#sisa_tagihan').val());
                var totalHarga = parseInt($('#total_harga').val()) || 0;
                if (isNaN(sisaTagihan) || sisaTagihan <= 0) {
                    sisaTagihan = totalHarga;
                }
                var $input = $('#terbayar_permohonan_uji_payment_klinik');

                if (action === 'exact') {
                    $input.val(formatRupiah(sisaTagihan));
                } else if (amount) {
                    var currentVal = parseInt(formatNumber($input.val())) || 0;
                    $input.val(formatRupiah(currentVal + amount));
                }

                // Trigger input event to update calculations
                $input.trigger('input');
            });

            // Format input field as currency and calculate change
            var isFormatting = false; // Flag to prevent infinite loop

            function formatPaymentInput() {
                if (isFormatting) return; // Prevent recursive calls
                isFormatting = true;

                var $input = $('#terbayar_permohonan_uji_payment_klinik');
                var input = $input.val();
                var number = formatNumber(input);
                var hasInput = number !== '';

                // Format display
                if (number) {
                    var formatted = formatRupiah(number);
                    if ($input.val() !== formatted) {
                        $input.val(formatted);
                    }
                } else {
                    $input.val('');
                }

                // Calculate change against sisa tagihan (bukan total penuh jika sudah ada bayaran)
                var sisaTagihan = parseInt($('#sisa_tagihan').val());
                var totalHarga = parseInt($('#total_harga').val()) || 0;
                if (isNaN(sisaTagihan) || sisaTagihan < 0) {
                    sisaTagihan = totalHarga;
                }
                var terbayar = parseInt(number) || 0;
                var kembalian = terbayar - sisaTagihan;

                // Hide error and change card first
                $('#payment-error').hide();
                $('#change-card').hide();

                if (hasInput) {
                    if (terbayar < sisaTagihan) {
                        // Show warning if less than remaining
                        $('#payment-error-text').text(
                            'Nominal kurang dari sisa tagihan. Status akan menjadi "Belum Lunas".');
                        $('#payment-error').show();
                        $('#btnSavePayment').prop('disabled', false);
                    } else if (kembalian > 0) {
                        // Show change
                        $('#display_kembalian').text('Rp. ' + formatRupiah(kembalian));
                        $('#change-card').show();
                        $('#btnSavePayment').prop('disabled', false);
                    } else {
                        // Exact amount
                        $('#btnSavePayment').prop('disabled', false);
                    }
                } else {
                    $('#btnSavePayment').prop('disabled', true);
                }

                isFormatting = false; // Reset flag
            }

            // Bind input event with debounce to prevent multiple calls
            var formatTimeout;
            $('#terbayar_permohonan_uji_payment_klinik').on('input', function() {
                clearTimeout(formatTimeout);
                formatTimeout = setTimeout(formatPaymentInput, 100);
            });


            // Save payment button
            var isPaymentSubmitting = false;
            $('#btnSavePayment').click(function() {
                if (isPaymentSubmitting) {
                    return false;
                }

                // Validate payment amount
                var rawNominal = formatNumber($('#terbayar_permohonan_uji_payment_klinik').val());
                var terbayar = parseInt(rawNominal) || 0;

                if (rawNominal === '') {
                    $('#payment-error-text').text('Silakan masukkan nominal yang dibayarkan!');
                    $('#payment-error').show();
                    $('#terbayar_permohonan_uji_payment_klinik').focus();
                    return false;
                }

                isPaymentSubmitting = true;
                $('#btnSavePaymentText').html('<i class="fa fa-spinner fa-spin mr-2"></i>Memproses...');
                $('#btnSavePayment').prop('disabled', true);

                $.ajax({
                    url: "{{ route('permohonan-uji-klinik-store-payment2') }}",
                    type: "POST",
                    data: $('#form-payment').serialize(),
                    dataType: "JSON",
                    success: function(data) {
                        if (data.status == true) {
                            $('#modal-payment').modal('hide');
                            swal({
                                icon: "success",
                                title: "Pembayaran Berhasil!",
                                text: data.pesan,
                            }).then(function() {
                                // Redirect to registrasi page
                                document.location =
                                    "{{ url('/elits-permohonan-uji-klinik/registrasi') }}";
                            });
                        } else {
                            isPaymentSubmitting = false;
                            $('#btnSavePaymentText').html(
                                '<i class="fa fa-check-circle mr-2"></i>Proses Pembayaran');
                            $('#btnSavePayment').prop('disabled', false);

                            var pesan = "";
                            var data_pesan = data.pesan;
                            const wrapper = document.createElement('div');

                            if (typeof(data_pesan) == 'object') {
                                jQuery.each(data_pesan, function(key, value) {
                                    pesan += value + '<br>';
                                });
                                wrapper.innerHTML = pesan;
                                swal({
                                    icon: "warning",
                                    title: "Pembayaran Gagal",
                                    content: wrapper,
                                });
                            } else {
                                swal({
                                    icon: "warning",
                                    title: "Pembayaran Gagal",
                                    text: data_pesan,
                                });
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        isPaymentSubmitting = false;
                        $('#btnSavePaymentText').html(
                            '<i class="fa fa-check-circle mr-2"></i>Proses Pembayaran');
                        $('#btnSavePayment').prop('disabled', false);

                        swal("Error", "Terjadi kesalahan saat memproses pembayaran!", "error");
                        console.error('Payment error:', textStatus, errorThrown);
                    }
                });
            });
        });
    </script>
@endsection
