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
                <form action="{{ route('elits-permohonan-uji-klinik-2.store-parameter') }}" method="POST"
                    enctype="multipart/form-data" id="form">

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

                    <br>
                    {{-- <div class="ml-2">
                       <h5>Paket</h5>
                   </div>
                   @foreach ($parameter_paket_extra as $val)
                       <div class="col-md-12 mt-4 mb-2">
                           <div class="form-check">
                               <label class="form-check-label">
                                   <input type="checkbox" class="form-check-input"
                                       name="paket_extra[{{ $val->id_parameter_paket_extra }}]"
                                       value="{{ $val->id_parameter_paket_extra }}_{{ $val->harga_parameter_paket_extra }}">
                                   {{ $val->nama_parameter_paket_extra }} - Harga:
                                   {{ $val->harga_parameter_paket_extra }}
                                   <i class="input-helper"></i>
                               </label>
                           </div>
                           @if ($val->parameterSubPaketExtra)
                               <div class="ml-4 mt-2">
                                  <h6>Sub Paket:</h6>
                                   <ul>
                                       @foreach ($val->parameterSubPaketExtra as $paket)
                                           <input type="hidden" class="form-control"
                                               name="sub_paket[{{ $val->id_parameter_paket_extra }}][]"
                                               value="{{ $paket->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                           <li>{{ $paket->parameterPaketKlinik->name_parameter_paket_klinik }}</li>
                                       @endforeach
                                   </ul>
                               </div>
                           @endif
                       </div>
                   @endforeach --}}


                    {{--                    <br> --}}
                    {{--                    <div class="ml-2"> --}}
                    {{--                        <h5>Jenis Parameter Paket</h5> --}}
                    {{--                    </div> --}}

                    <div class="mx-lg-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="blue-header" style="padding: 2px 4px;">PAKET</div>
                                <div class="col-md-12 form-group pt-3">
                                    <!-- Pastikan parameter_paket_extra ada -->
                                    @if ($parameter_paket_extra->isNotEmpty())
                                        @foreach ($parameter_paket_extra as $val)
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <!-- Checkbox for Paket Extra -->
                                                    <input type="checkbox" class="form-check-input"
                                                        name="paket_extra[{{ $val->id_parameter_paket_extra }}]"
                                                        value="{{ $val->id_parameter_paket_extra }}_{{ $val->harga_parameter_paket_extra }}">
                                                    {{ $val->nama_parameter_paket_extra }}

                                                    <!-- Display Sub-Paket -->
                                                    {{-- @if ($val->parameterSubPaketExtra->isNotEmpty())
                                        <span class="sub-paket-list">
                                            (
                                            @foreach ($val->parameterSubPaketExtra as $index => $paket)
                                                <!-- Hidden input for Sub-Paket -->
                                                <input type="hidden" class="form-control"
                                                    name="sub_paket[{{ $val->id_parameter_paket_extra }}][]"
                                                    value="{{ $paket->parameterPaketKlinik->id_parameter_paket_klinik }}">
                                                {{ $paket->parameterPaketKlinik->name_parameter_paket_klinik }}
                                                @if (!$loop->last), @endif
                                            @endforeach
                                            )
                                        </span>
                                    @endif --}}

                                                    <i class="input-helper"></i>
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>Tidak ada paket yang tersedia.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="blue-header" style="padding: 2px 4px;">HEMATOLOGI</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)')->harga_parameter_paket_klinik }}">
                                            Hematologi Rutin (Hb, Ht, Leko, AT, Index Eri)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Diferensial Count (Hitung Jenins Lekosit)')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Diferensial Count (Hitung Jenins Lekosit)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Diferensial Count (Hitung Jenins Lekosit)')->harga_parameter_paket_klinik }}">
                                            Diferensial Count (Hitung Jenins Lekosit)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Laju Endap Darah (LED)')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Laju Endap Darah (LED)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Laju Endap Darah (LED)')->harga_parameter_paket_klinik }}">
                                            Laju Endap Darah (LED)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Golongan Darah A, B, O dan Rhesus')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Golongan Darah A, B, O dan Rhesus')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Golongan Darah A, B, O dan Rhesus')->harga_parameter_paket_klinik }}">
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
                                                value="{{ getPaketKlinik('Widal')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Widal')->harga_parameter_paket_klinik }}">
                                            Widal
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('HBsAG Strip')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('HBsAG Strip')->id_parameter_paket_klinik }}_{{ getPaketKlinik('HBsAG Strip')->harga_parameter_paket_klinik }}">
                                            HBsAG Strip
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Dengue NS1')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Dengue NS1')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Dengue NS1')->harga_parameter_paket_klinik }}">
                                            Dengue NS1
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('IgG Anti Dengue')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('IgG Anti Dengue')->id_parameter_paket_klinik }}_{{ getPaketKlinik('IgG Anti Dengue')->harga_parameter_paket_klinik }}">
                                            IgG Anti Dengue
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('IgM Anti Dengue')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('IgM Anti Dengue')->id_parameter_paket_klinik }}_{{ getPaketKlinik('IgM Anti Dengue')->harga_parameter_paket_klinik }}">
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
                                            <input type="checkbox" class="form-check-input" id="urinalisisUrinRutin"
                                                name="jenis_parameters[{{ getPaketKlinik('Urinalisis Urin Rutin')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Urinalisis Urin Rutin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Urinalisis Urin Rutin')->harga_parameter_paket_klinik }}">
                                            Urine Rutin
                                            {{-- <input type="checkbox" class="form-check-input" id="urineRutin" name="jenis_parameters[{{ getPaketKlinik('Urine Rutin')->id_parameter_paket_klinik }}][pakets][]" value="{{ getPaketKlinik('Urine Rutin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Urine Rutin')->harga_parameter_paket_klinik }}">
                      Urine Rutin
                      <input type="checkbox" class="form-check-input" id="sedimen" name="jenis_parameters[{{ getPaketKlinik('Sedimen')->id_parameter_paket_klinik }}][pakets][]" value="{{ getPaketKlinik('Sedimen')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Sedimen')->harga_parameter_paket_klinik }}" style="display: none"> --}}
                                        </label>
                                    </div>
                                    {{--                  <div class="form-check"> --}}
                                    {{--                    <label class="form-check-label"> --}}
                                    {{--                      <input type="checkbox" class="form-check-input" name="jenis_parameters[{{ getPaketKlinik('Sedimen')->id_parameter_paket_klinik }}][pakets][]" value="{{ getPaketKlinik('Sedimen')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Sedimen')->harga_parameter_paket_klinik }}"> --}}
                                    {{--                      Sedimen --}}
                                    {{--                    </label> --}}
                                    {{--                  </div> --}}
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Test Kehamilan')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Test Kehamilan')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Test Kehamilan')->harga_parameter_paket_klinik }}">
                                            Test Kehamilan
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Mikro Albumin')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Mikro Albumin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Mikro Albumin')->harga_parameter_paket_klinik }}">
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
                                                        value="{{ getPaketKlinik('Gula Darah Puasa')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Gula Darah Puasa')->harga_parameter_paket_klinik }}">
                                                    Gula Darah Puasa
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Gula Darah 2 Jam PP')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Gula Darah 2 Jam PP')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Gula Darah 2 Jam PP')->harga_parameter_paket_klinik }}">
                                                    Gula Darah 2 Jam PP
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Gula Darah Sewaktu')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Gula Darah Sewaktu')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Gula Darah Sewaktu')->harga_parameter_paket_klinik }}">
                                                    Gula Darah Sewaktu
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('HbA1C')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('HbA1C')->id_parameter_paket_klinik }}_{{ getPaketKlinik('HbA1C')->harga_parameter_paket_klinik }}">
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
                                                        value="{{ getPaketKlinik('Kolesterol Total')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Kolesterol Total')->harga_parameter_paket_klinik }}">
                                                    Kolesterol Total
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('HDL Kolesterol')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('HDL Kolesterol')->id_parameter_paket_klinik }}_{{ getPaketKlinik('HDL Kolesterol')->harga_parameter_paket_klinik }}">
                                                    HDL Kolesterol
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('LDL Kolesterol')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('LDL Kolesterol')->id_parameter_paket_klinik }}_{{ getPaketKlinik('LDL Kolesterol')->harga_parameter_paket_klinik }}">
                                                    LDL Kolesterol
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Trigliserida')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Trigliserida')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Trigliserida')->harga_parameter_paket_klinik }}">
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
                                                        value="{{ getPaketKlinik('SGOT (AST)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('SGOT (AST)')->harga_parameter_paket_klinik }}">
                                                    SGOT (AST)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('SGPT (ALT)')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('SGPT (ALT)')->id_parameter_paket_klinik }}_{{ getPaketKlinik('SGPT (ALT)')->harga_parameter_paket_klinik }}">
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
                                                        value="{{ getPaketKlinik('Ureum')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Ureum')->harga_parameter_paket_klinik }}">
                                                    Ureum
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Kreatin')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Kreatin')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Kreatin')->harga_parameter_paket_klinik }}">
                                                    Kreatin
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="jenis_parameters[{{ getPaketKlinik('Asam Urat')->id_parameter_paket_klinik }}][pakets][]"
                                                        value="{{ getPaketKlinik('Asam Urat')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Asam Urat')->harga_parameter_paket_klinik }}">
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
                                                value="{{ getPaketKlinik('Narkoba')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Narkoba')->harga_parameter_paket_klinik }}">
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
                                <div class="col-md-12 mb-4">
                                    <div style="font-size: 14px;" class="mb-2">
                                        <b>( * ) Puasa 8-12 Jam</b>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="blue-header" style="padding: 2px 4px;">MIKROBIOLOGI</div>
                                <div class="col-md-12 form-group pt-3">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input"
                                                name="jenis_parameters[{{ getPaketKlinik('Sputum BTA')->id_parameter_paket_klinik }}][pakets][]"
                                                value="{{ getPaketKlinik('Sputum BTA')->id_parameter_paket_klinik }}_{{ getPaketKlinik('Sputum BTA')->harga_parameter_paket_klinik }}">
                                            Sputum BTA
                                        </label>
                                    </div>

                                </div>

                            </div>
                        </div>


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

    {{-- MODAL PAYMENT --}}
    <div class="modal fade" id="modal-payment" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="form-payment" method="POST">
                    @csrf
                    <input type="hidden" id="id_permohonan_uji_klinik" name="id_permohonan_uji_klinik">
                    <input type="hidden" id="total_harga" name="total_harga">

                    <div class="modal-header"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
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
                        <div class="card mb-3" style="border-left: 4px solid #667eea;">
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
                            <div class="card-body text-center">
                                <h6 class="mb-2"><i class="fa fa-file-invoice-dollar mr-2"></i>Total Pembayaran</h6>
                                <h2 id="display_total_harga" class="mb-0 font-weight-bold">Rp. 0</h2>
                            </div>
                        </div>

                        <!-- Payment Input Card -->
                        <div class="payment-input-card"
                            style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                            <div class="payment-field-label mb-3" style="font-weight: 600; color: #333;">
                                <i class="fa fa-wallet mr-2"></i> Nominal Dibayarkan
                            </div>
                            <div class="payment-input-field" style="position: relative;">
                                <span class="payment-input-prefix"
                                    style="position: absolute; left: 15px; top: 12px; font-size: 18px; color: #666;">Rp</span>
                                <input type="text" class="form-control" id="terbayar_permohonan_uji_payment_klinik"
                                    name="terbayar_permohonan_uji_payment_klinik"
                                    style="padding-left: 45px; font-size: 18px; height: 50px; border: 2px solid #e0e0e0; border-radius: 10px;"
                                    placeholder="0" autocomplete="off">
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
                                    style="flex: 1; padding: 10px; border: 2px solid #667eea; background: white; color: #667eea; border-radius: 8px; font-weight: 600; cursor: pointer;"
                                    data-amount="50000">
                                    + 50rb
                                </button>
                                <button type="button" class="quick-amount-btn"
                                    style="flex: 1; padding: 10px; border: 2px solid #667eea; background: white; color: #667eea; border-radius: 8px; font-weight: 600; cursor: pointer;"
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

        $(document).ready(function() {
            $('.btn-simpan').on('click', function(event) {
                event.preventDefault(); // Mencegah form submission standar

                var $button = $(this); // Simpan referensi tombol simpan
                $button.prop('disabled', true); // Disable tombol simpan
                $button.html('Loading...'); // Ganti teks tombol dengan "Loading..."

                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status === true) {
                            $button.prop('disabled', false);
                            $button.html('Simpan');

                            // Check if payment modal should be shown
                            if (response.show_payment && response.payment_data) {
                                // Show success message first
                                swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success",
                                    timer: 1500,
                                    buttons: false
                                }).then(function() {
                                    // Show payment modal
                                    showPaymentModal(response.payment_data);
                                });
                            } else {
                                // Original behavior: redirect to registrasi
                                swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                }).then(function() {
                                    document.location =
                                        "{{ url('/elits-permohonan-uji-klinik/registrasi') }}";
                                });
                            }
                        } else {
                            $button.prop('disabled',
                                false); // Aktifkan kembali tombol simpan jika ada error
                            $button.html('Simpan'); // Kembalikan teks tombol semula

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
                    error: function(xhr) {
                        $button.prop('disabled',
                            false); // Aktifkan kembali tombol simpan jika ada error
                        $button.html('Simpan'); // Kembalikan teks tombol semula
                        console.error(xhr.responseText);
                        swal("Error!", "System gagal menyimpan!", "error");
                    }
                });
            });

            // Function to show payment modal
            function showPaymentModal(data) {
                // Reset form
                $('#form-payment').trigger('reset');
                $('#terbayar_permohonan_uji_payment_klinik').val('');
                $('#payment-error').hide();
                $('#change-card').hide();
                $('#btnSavePayment').prop('disabled', true);

                // Set data to form
                $('#id_permohonan_uji_klinik').val(data.id_permohonan_uji_klinik);
                $('#total_harga').val(data.total_harga);
                $('#nota_petugas_permohonan_uji_payment_klinik').val(data.nota_petugas);
                $('#nota_namapetugas_permohonan_uji_payment_klinik').val(data.nota_namapetugas);
                $('#total_harga_permohonan_uji_payment_klinik').val(data.total_harga);

                // Display data
                $('#display_nama_pasien').text((data.nama_pasien || '-').toUpperCase());
                $('#display_alamat_pasien').text(data.alamat_pasien || '-');
                $('#display_petugas').text(data.nota_namapetugas || '-');
                $('#display_total_harga').text(data.total_harga_custom || 'Rp. 0');

                // Show modal
                $('#modal-payment').modal('show');

                // Focus on payment input after modal is shown
                $('#modal-payment').on('shown.bs.modal', function() {
                    $('#terbayar_permohonan_uji_payment_klinik').focus();
                });
            }

            // Quick amount buttons
            $(document).on('click', '.quick-amount-btn', function() {
                var action = $(this).data('action');
                var amount = $(this).data('amount');
                var totalHarga = parseInt($('#total_harga').val()) || 0;

                if (action === 'exact') {
                    $('#terbayar_permohonan_uji_payment_klinik').val(totalHarga).trigger('keyup');
                } else if (amount) {
                    var currentVal = parseInt(formatNumber($('#terbayar_permohonan_uji_payment_klinik')
                        .val())) || 0;
                    $('#terbayar_permohonan_uji_payment_klinik').val(currentVal + amount).trigger('keyup');
                }
            });

            // Format input field as currency and calculate change
            $('#terbayar_permohonan_uji_payment_klinik').on('keyup', function() {
                var input = $(this).val();
                var number = formatNumber(input);

                // Format display
                if (number) {
                    $(this).val(formatNumber(number));
                } else {
                    $(this).val('');
                }

                // Calculate change
                var totalHarga = parseInt($('#total_harga').val()) || 0;
                var terbayar = parseInt(number) || 0;
                var kembalian = terbayar - totalHarga;

                // Hide error and change card first
                $('#payment-error').hide();
                $('#change-card').hide();

                if (terbayar > 0) {
                    if (terbayar < totalHarga) {
                        // Show warning if less than total
                        $('#payment-error-text').text(
                            'Nominal kurang dari total pembayaran. Status akan menjadi "Belum Lunas".');
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
            });

            // Save payment button
            $('#btnSavePayment').click(function() {
                // Validate payment amount
                var terbayar = parseInt(formatNumber($('#terbayar_permohonan_uji_payment_klinik').val())) ||
                    0;

                if (terbayar <= 0) {
                    $('#payment-error-text').text('Silakan masukkan nominal yang dibayarkan!');
                    $('#payment-error').show();
                    $('#terbayar_permohonan_uji_payment_klinik').focus();
                    return false;
                }

                $('#btnSavePaymentText').html('<i class="fa fa-spinner fa-spin mr-2"></i>Memproses...');
                $('#btnSavePayment').prop('disabled', true);

                $.ajax({
                    url: "{{ route('permohonan-uji-klinik-store-payment2') }}",
                    type: "POST",
                    data: $('#form-payment').serialize(),
                    dataType: "JSON",
                    success: function(data) {
                        $('#btnSavePaymentText').html(
                            '<i class="fa fa-check-circle mr-2"></i>Proses Pembayaran');
                        $('#btnSavePayment').prop('disabled', false);

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
    <script>
        // document.getElementById('urineRutin').addEventListener('change', function() {
        //   const sedimenCheckbox = document.getElementById('sedimen');
        //   sedimenCheckbox.checked = !!this.checked;
        // });
    </script>
@endsection
