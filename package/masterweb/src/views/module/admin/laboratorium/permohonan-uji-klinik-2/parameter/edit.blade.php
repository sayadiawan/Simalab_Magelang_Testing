@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection


@section('content')
    <style>
        .select2-container {
            min-width: 10em !important;
        }

        .blue-header {
            background-color: #3a95b5;
            color: white;
            font-weight: bold;
            font-size: 15px;
            letter-spacing: 1px;
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
            <h4>Tambah Permohonan Uji Paket Klinik
            </h4>
        </div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <form
                    action="{{ route('elits-permohonan-uji-klinik-2.store-permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}"
                    method="POST" enctype="multipart/form-data" id="form">

                    @csrf

                    <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="250px">No. Sample</th>
                                <td>{{ $code }}</td>

                                <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik"
                                    value="{{ $id }}" readonly>
                            </tr>

                            <tr>
                                <th width="250px">No. Rekam Medis</th>
                                <td>
                                    {{ Carbon\Carbon::createFromFormat('Y-m-d', $pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                                </td>
                            </tr>

                            {{-- <tr>
                <th width="250px">Tgl. Sampling</th>
                <td>{{ $tgl_sampling }}</td>
              </tr> --}}

                            <tr>
                                <th width="250px">Nama Pasien</th>
                                <td>{{ $pasien->nama_pasien }}</td>
                            </tr>

                            <tr>
                                <th width="250px">Umur/Jenis Kelamin</th>
                                <td>
                                    {{ $umur_string }}
                                    /
                                    {{ $pasien->gender_pasien == 'L' || $pasien->gender_pasien == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{--                    <br> --}}
                    {{--                    <div class="ml-2"> --}}
                    {{--                        <h5>Parameter Paket Extra</h5> --}}
                    {{--                    </div> --}}
                    {{--                    @foreach ($parameter_paket_extra as $val) --}}
                    {{--                        <div class="col-md-12 mt-4 mb-2"> --}}
                    {{--                            <div class="form-check"> --}}
                    {{--                                <label class="form-check-label"> --}}
                    {{--                                    <input type="checkbox" class="form-check-input" --}}
                    {{--                                        name="paket_extra[{{ $val->id_parameter_paket_extra }}]" --}}
                    {{--                                        value="{{ $val->id_parameter_paket_extra }}_{{ $val->harga_parameter_paket_extra }}"> --}}
                    {{--                                    {{ $val->nama_parameter_paket_extra }} - Harga: --}}
                    {{--                                    {{ $val->harga_parameter_paket_extra }} --}}
                    {{--                                    <i class="input-helper"></i> --}}
                    {{--                                </label> --}}
                    {{--                            </div> --}}
                    {{--                            @if ($val->parameterSubPaketExtra) --}}
                    {{--                                <div class="ml-4 mt-2"> --}}
                    {{--                                    --}}{{-- <h6>Sub Paket:</h6> --}}
                    {{--                                    <ul> --}}
                    {{--                                        @foreach ($val->parameterSubPaketExtra as $paket) --}}
                    {{--                                            <input type="hidden" class="form-control" --}}
                    {{--                                                name="sub_paket[{{ $val->id_parameter_paket_extra }}][]" --}}
                    {{--                                                value="{{ $paket->parameterPaketKlinik->id_parameter_paket_klinik }}"> --}}
                    {{--                                            <li>{{ $paket->parameterPaketKlinik->name_parameter_paket_klinik }}</li> --}}
                    {{--                                        @endforeach --}}
                    {{--                                    </ul> --}}
                    {{--                                </div> --}}
                    {{--                            @endif --}}
                    {{--                        </div> --}}
                    {{--                    @endforeach --}}


                    {{--                    <br> --}}
                    {{--                    <div class="ml-2"> --}}
                    {{--                        <h5>Jenis Parameter Paket</h5> --}}
                    {{--                    </div> --}}

                    <div class="mx-lg-3">
                        @if(isset($categoryLayouts) && count($categoryLayouts) > 0)
                            @foreach($categoryLayouts as $category)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="blue-header" style="padding: 2px 4px;">{{ $category->category_code }}. {{ $category->category_name }}</div>
                                    </div>
                                </div>
                                <div class="row">
                                    @if($category->categoryItems && count($category->categoryItems) > 0)
                                        @foreach($category->categoryItems as $item)
                                            @if($item->parameterPaketKlinik)
                                                <div class="col-md-4">
                                                    <div class="col-md-12 form-group pt-3">
                                                        <div class="form-check">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="jenis_parameters[{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}][pakets][]"
                                                                    value="{{ $item->parameterPaketKlinik->id_parameter_paket_klinik }}_{{ $item->parameterPaketKlinik->harga_parameter_paket_klinik }}"
                                                                    @if (in_array($item->parameterPaketKlinik->name_parameter_paket_klinik, array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                                {{ $item->parameterPaketKlinik->name_parameter_paket_klinik }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        @else
                            {{-- Fallback to old hardcoded layout if no category layout found --}}
                            <div class="row">
                            <div class="col-md-6">
                                <div class="blue-header" style="padding: 2px 4px;">HEMATOLOGI</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Diferensial Count (Hitung Jenins Lekosit)')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Diferensial Count (Hitung Jenins Lekosit)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Diferensial Count (Hitung Jenins Lekosit)')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Diferensial Count (Hitung Jenins Lekosit)', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Diferensial Count (Hitung Jenins Lekosit)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Laju Endap Darah (LED)')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Laju Endap Darah (LED)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Laju Endap Darah (LED)')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Laju Endap Darah (LED)', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Laju Endap Darah (LED)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Golongan Darah A, B, O dan Rhesus')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Golongan Darah A, B, O dan Rhesus')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Golongan Darah A, B, O dan Rhesus')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Golongan Darah A, B, O dan Rhesus', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Golongan Darah A, B, O dan Rhesus
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="blue-header" style="padding: 2px 4px;">IMUNOSEROLOGI</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Widal')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Widal')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Widal')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Widal', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Widal
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('HBsAG Strip')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('HBsAG Strip')->id_parameter_paket_klinik }}_{{ getPaketKlinik('HBsAG Strip')->harga_parameter_paket_klinik }}"
                                                @if (in_array('HBsAG Strip', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            HBsAG Strip
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Dengue NS1')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Dengue NS1')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Dengue NS1')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Dengue NS1', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Dengue NS1
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('IgG Anti Dengue')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('IgG Anti Dengue')->id_parameter_paket_klinik }}_{{ getPaketKlinik('IgG Anti Dengue')->harga_parameter_paket_klinik }}"
                                                @if (in_array('IgG Anti Dengue', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            IgG Anti Dengue
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('IgM Anti Dengue')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('IgM Anti Dengue')->id_parameter_paket_klinik }}_{{ getPaketKlinik('IgM Anti Dengue')->harga_parameter_paket_klinik }}"
                                                @if (in_array('IgM Anti Dengue', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            IgM Anti Dengue
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="blue-header" style="padding: 2px 4px;">URINALISA</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Urine Rutin')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Urine Rutin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Urine Rutin')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Urine Rutin', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Urine Rutin
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Sedimen')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Sedimen')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Sedimen')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Sedimen', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Sedimen
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Test Kehamilan')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Test Kehamilan')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Test Kehamilan')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Test Kehamilan', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Test Kehamilan
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Mikro Albumin')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Mikro Albumin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Mikro Albumin')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Mikro Albumin', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Mikro Albumin
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="blue-header" style="padding: 2px 4px;">KIMIA KLINIK</div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6><b>DIABETES</b></h6>
                                        <div class="col-md-12 form-group pt-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Gula Darah Puasa')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Gula Darah Puasa')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Gula Darah Puasa')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Gula Darah Puasa', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Gula Darah Puasa
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Gula Darah 2 Jam PP')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Gula Darah 2 Jam PP')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Gula Darah 2 Jam PP')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Gula Darah 2 Jam PP', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Gula Darah 2 Jam PP
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Gula Darah Sewaktu')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Gula Darah Sewaktu')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Gula Darah Sewaktu')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Gula Darah Sewaktu', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Gula Darah Sewaktu
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('HbA1C')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('HbA1C')->id_parameter_paket_klinik }}_{{ getPaketKlinik('HbA1C')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('HbA1C', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    HbA1C
                                                </label>
                                            </div>
                                        </div>
                                        <h6><b>LEMAK</b></h6>
                                        <div class="col-md-12 form-group pt-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Kolesterol Total')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Kolesterol Total')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Kolesterol Total')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Kolesterol Total', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Kolesterol Total
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('HDL Kolesterol')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('HDL Kolesterol')->id_parameter_paket_klinik }}_{{ getPaketKlinik('HDL Kolesterol')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('HDL Kolesterol', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    HDL Kolesterol
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('LDL Kolesterol')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('LDL Kolesterol')->id_parameter_paket_klinik }}_{{ getPaketKlinik('LDL Kolesterol')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('LDL Kolesterol', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    LDL Kolesterol
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Trigliserida')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Trigliserida')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Trigliserida')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Trigliserida', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Trigliserida
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><b>FAAL HATI</b></h6>
                                        <div class="col-md-12 form-group pt-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('SGOT (AST)')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('SGOT (AST)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('SGOT (AST)')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('SGOT (AST)', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    SGOT (AST)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('SGPT (ALT)')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('SGPT (ALT)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('SGPT (ALT)')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('SGPT (ALT)', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    SGPT (ALT)
                                                </label>
                                            </div>
                                        </div>
                                        <h6><b>GINJAL</b></h6>
                                        <div class="col-md-12 form-group pt-3">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Ureum')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Ureum')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Ureum')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Ureum', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Ureum
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Kreatin')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Kreatin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Kreatin')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Kreatin', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Kreatin
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Asam Urat')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Asam Urat')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Asam Urat')->harga_parameter_paket_klinik }}"
                                                        @if (in_array('Asam Urat', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                                    Asam Urat
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="blue-header" style="padding: 2px 4px;">LAIN-LAIN</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Narkoba')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Narkoba')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Narkoba')->harga_parameter_paket_klinik }}"
                                                @if (in_array('Narkoba', array_column($paket, 'name_parameter_paket_klinik'))) checked @endif>
                                            Narkoba
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" disabled>
                                            ........................
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" disabled>
                                            ........................
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" disabled>
                                            ........................
                                        </label>
                                    </div>
                                </div>
                                <div class="blue-header" style="padding: 2px 4px;">SPESIMEN</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                @if ($item->jenis_spesimen == 'Darah Beku') checked @else disabled @endif>
                                            Darah Beku
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                @if ($item->jenis_spesimen == 'NaF') checked @else disabled @endif>
                                            NaF
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                @if ($item->jenis_spesimen == 'EDTA') checked @else disabled @endif>
                                            EDTA
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                @if ($item->jenis_spesimen == 'Urine') checked @else disabled @endif>
                                            Urine
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div style="font-size: 14px;" class="mb-2">
                                        <b>( * ) Puasa 8-12 Jam</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- End of category layout --}}
                    </div>
                    {{--                    @foreach ($parameter_jenis_klinik as $val) --}}
                    {{--                        @if ($val->pakets->isNotEmpty()) --}}
                    {{--                            <div class="col-md-12 mt-4 mb-2"> --}}
                    {{--                                <div class="col-md-12 blue-header">{{ $val->name_parameter_jenis_klinik }}</div> --}}
                    {{--                                @if ($val->pakets->isNotEmpty()) --}}
                    {{--                                    @foreach ($val->pakets as $paket) --}}
                    {{--                                        <div class="col-md-12 form-group pt-3"> --}}
                    {{--                                            <div class="form-check"> --}}
                    {{--                                                <label class="form-check-label"> --}}
                    {{--                                                    <input type="checkbox" class="form-check-input" --}}
                    {{--                                                        name="jenis_parameters[{{ $val->id_parameter_jenis_klinik }}][pakets][]" --}}
                    {{--                                                        value="{{ $paket->id_parameter_paket_klinik }}_{{ $paket->harga_parameter_paket_klinik }}"> --}}
                    {{--                                                    {{ $paket->name_parameter_paket_klinik }} - Harga: --}}
                    {{--                                                    {{ $paket->harga_parameter_paket_klinik }} --}}
                    {{--                                                    <i class="input-helper"></i> --}}
                    {{--                                                </label> --}}
                    {{--                                            </div> --}}
                    {{--                                        </div> --}}
                    {{--                                    @endforeach --}}
                    {{--                                @else --}}
                    {{--                                    <p>No pakets available.</p> --}}
                    {{--                                @endif --}}
                    {{--                            </div> --}}
                    {{--                        @endif --}}
                    {{--                    @endforeach --}}



                    <button type="submit" class="btn btn-primary ml-2 mr-2 btn-simpan">Simpan</button>
                    <button type="button" class="btn btn-light"
                        onclick="document.location='{{ route('elits-permohonan-uji-klinik-2.index') }}'">Kembali</button>
                </form>


                {{-- <a href="{{ route('elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', $item->id_permohonan_uji_klinik) }}"
  class="btn btn-primary mr-2 btn-lanjutkan">Lanjutkan</a> --}}
            </li>
        </ul>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

    <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        var CSRF_TOKEN = $('#csrf-token').val();

        $(document).ready(function() {
            // Track selected parameters to prevent duplicates and limit per row
            var selectedParameters = {};
            
            // Monitor all checkboxes
            $('input[type="checkbox"][name^="jenis_parameters"]').on('change', function() {
                var checkbox = $(this);
                var parameterId = checkbox.val().split('_')[0];
                var parameterName = checkbox.closest('label').text().trim();
                
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
                var parameterName = $(this).closest('label').text().trim();
                selectedParameters[parameterId] = parameterName;
            });
            
            $('.btn-simpan').on('click', function(event) {
                event.preventDefault(); // Mencegah form submission standar

                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status === true) {
                            swal({
                                title: "Success!",
                                text: response.pesan,
                                icon: "success"
                            }).then(function() {
                                document.location =
                                    "{{ url('/elits-permohonan-uji-klinik-2/permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}";
                            });
                        } else {
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
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });
        });
    </script>
@endsection
