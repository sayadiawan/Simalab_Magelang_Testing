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
                                <li class="breadcrumb-item"><a
                                        href="{{ url('/elits-permohonan-uji-klinik-2/permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}">
                                        Permohonan Uji Paket Klinik</a>
                                </li>
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
            <small style="color: #999; font-weight: bold;"><!-- FILE: parameter/add-paramater.blade.php (SUBFOLDER) - Dynamic Layout v2.0 --></small>
        </div>

        <div class="card-body">
            <form
                action="{{ route('elits-permohonan-uji-klinik-2.store-permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}"
                method="POST" enctype="multipart/form-data" id="form">

                @csrf

                <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik" value="{{ $id }}"
                    readonly>

                <div class="paper-container">
                    <div class="info-section">
                        <div class="info-row">
                            <div class="info-label">No. Sample:</div>
                            <div class="info-value">{{ $code }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">No. Rekam Medis:</div>
                            <div class="info-value">
                                {{ Carbon\Carbon::createFromFormat('Y-m-d', $pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nama Pasien:</div>
                            <div class="info-value" style="text-transform: uppercase;">{{ $pasien->nama_pasien }}</div>
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

                    {{-- Dynamic Category Layout from Database --}}
                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.parameter-category-layout', [
                        'categoryLayouts' => $categoryLayouts ?? collect(),
                        'parameter_jenis_klinik' => $parameter_jenis_klinik,
                        'parameter_paket_extra' => $parameter_paket_extra,
                        'paket' => $paket ?? [],
                        'paket_extra' => $paket_extra ?? [],
                    ])
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary ml-2 mr-2 btn-simpan">Simpan</button>
                    <button type="button" class="btn btn-light"
                        onclick="document.location='{{ route('elits-permohonan-uji-klinik-2.index') }}'">Kembali</button>
                </div>
            </form>
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
                    success: function(response) {
                        if (response.status === true) {
                            swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            }).then(function() {
                                if (response.urlNextStep) {
                                    document.location = response.urlNextStep;
                                } else {
                                    document.location =
                                        "{{ url('/elits-permohonan-uji-klinik-2/permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}";
                                }
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
                    error: function() {
                        $button.prop('disabled', false);
                        $button.html('Simpan');
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });
        });
    </script>
@endsection
