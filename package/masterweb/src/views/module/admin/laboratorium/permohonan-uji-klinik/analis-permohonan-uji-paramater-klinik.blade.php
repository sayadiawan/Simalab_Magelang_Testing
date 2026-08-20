@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Klinik
@endsection


@section('content')
    <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script> --}}
    <script src="{{asset('assets/admin/cdn-local/js/gijgo.min.js')}}" type="text/javascript"></script>
    <link href="{{asset('assets/admin/cdn-local/css/gijgo.min.css')}}" rel="stylesheet" type="text/css" />




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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan
                                        Uji Klinik
                                        Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>analis permohonan uji paket
                                        klinik</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form
        action="{{ route('elits-permohonan-uji-klinik.store-permohonan-uji-analis', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}"
        method="POST" enctype="multipart/form-data" id="form">
        {{-- <form action=""> --}}

        @csrf
        @method('PUT')

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
        <div class="card">
            <div class="card-header">
                <h4>Analis Permohonan Uji Paket Klinik
                </h4>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="250px">No. Register</th>
                                    <td>{{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">No. Rekam Medis</th>
                                    <td>
                                        {{ Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item_permohonan_uji_klinik->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Tgl. Register</th>
                                    <td>{{ $tgl_register_permohonan_uji_klinik }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Nama Pasien</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Usia</th>
                                    <td>
                                        {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik .
                                            ' tahun ' .
                                            $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik .
                                            ' bulan ' .
                                            $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik .
                                            ' hari' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Jenis Kelamin</th>
                                    <td>
                                        {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Alamat Pasien</th>
                                    <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item_permohonan_uji_klinik->pasien) }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">No. Telepon</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->phone_pasien }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="250px">No. Pasien</th>
                                    <td>{{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">No. KTP</th>
                                    <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien }}</td>
                                </tr>

                                <tr>
                                    <th width="250px">Tanggal Lahir</th>
                                    <td>
                                        {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                                            ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                                                'D MMMM Y',
                                            )
                                            : '' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th width="250px">Pengirim</th>
                                    <td>{{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 p-4">
                        <div class="form-group">
                            <label for="tglpengujian_permohonan_uji_klinik">Tanggal Pengujian</label>

                            <input type="text" class="form-control" name="tglpengujian_permohonan_uji_klinik"
                                id="tglpengujian_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                                value="{{ $tgl_pengujian ?? old('tglpengujian_permohonan_uji_klinik') }}">

                            <script>
                                var date = "{{ $tgl_pengujian }}";
                                var m = moment(Date.parse(date)).format('DD/MM/yyyy HH:mm');

                                $('#tglpengujian_permohonan_uji_klinik').datetimepicker({
                                    format: 'dd/mm/yyyy HH:MM',
                                    value: m,
                                    footer: true,
                                    modal: true
                                });
                            </script>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_name">Waktu Pengambilan Spesimen (Darah)</label>

                                    <input type="text" class="form-control spesimen_darah_permohonan_uji_klinik"
                                        name="spesimen_darah_permohonan_uji_klinik"
                                        id="spesimen_darah_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                                        value="{{ $tgl_spesimen_darah ?? old('spesimen_darah_permohonan_uji_klinik') }}">

                                    <script>
                                        var date = "{{ $tgl_spesimen_darah }}";
                                        if (date != null && date != undefined && date != "") {
                                            var m = moment(Date.parse(date)).format('DD/MM/yyyy HH:mm');
                                            $('#spesimen_darah_permohonan_uji_klinik').datetimepicker({
                                                format: 'dd/mm/yyyy HH:MM',
                                                value: m,
                                                footer: true,
                                                modal: true
                                            });
                                            $("#spesimen_darah_permohonan_uji_klinik").css('display', 'block')
                                        } else {
                                            var m = moment().format('DD/MM/yyyy HH:mm');
                                            $('#spesimen_darah_permohonan_uji_klinik').datetimepicker({
                                                format: 'dd/mm/yyyy HH:MM',
                                                value: m,
                                                footer: true,
                                                modal: true
                                            });
                                            $("#spesimen_darah_permohonan_uji_klinik").css('display', 'none')
                                        }
                                    </script>

                                </div>
                                <div class="form-group">
                                    <div class="form-check form-check-flat form-check-primary">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input is_darah" name="is_not_darah"
                                                id="is_darah" {{ !isset($tgl_spesimen_darah) ? 'checked' : '' }}>
                                            Tidak Ada Pengambilan Spesimen Darah
                                            <i class="input-helper"></i></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_name">Waktu Pengambilan Spesimen (Urine)</label>

                                    <input type="text" class="form-control spesimen_urine_permohonan_uji_klinik"
                                        name="spesimen_urine_permohonan_uji_klinik"
                                        id="spesimen_urine_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                                        value="{{ $tgl_spesimen_urine ?? old('spesimen_urine_permohonan_uji_klinik') }}">

                                    <script>
                                        var date = "{{ $tgl_spesimen_urine }}";

                                        if (date != null && date != undefined && date != "") {
                                            var m = moment(Date.parse(date)).format('DD/MM/yyyy HH:mm');
                                            $('#spesimen_urine_permohonan_uji_klinik').datetimepicker({
                                                format: 'dd/mm/yyyy HH:MM',
                                                value: m,
                                                footer: true,
                                                modal: true
                                            });
                                            $("#spesimen_urine_permohonan_uji_klinik").css('display', 'block')
                                        } else {
                                            var m = moment().format('DD/MM/yyyy HH:mm');
                                            $('#spesimen_urine_permohonan_uji_klinik').datetimepicker({
                                                format: 'dd/mm/yyyy HH:MM',
                                                value: m,
                                                footer: true,
                                                modal: true
                                            });
                                            $("#spesimen_urine_permohonan_uji_klinik").css('display', 'none')
                                        }
                                    </script>
                                </div>
                                <div class="form-group">
                                    <div class="form-check form-check-flat form-check-primary">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input is_urine" name="is_not_urine"
                                                id="is_urine" {{ !isset($tgl_spesimen_urine) ? 'checked' : '' }}>
                                            Tidak Ada Pengambilan Spesimen Urine
                                            <i class="input-helper"></i></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="analis_permohonan_uji_klinik">Analis<span style="color: red">*</span></label>
                  <input type="hidden" class="form-control" name="analis_permohonan_uji_klinik"
                    id="analis_permohonan_uji_klinik" placeholder="Masukkan nama analis"
                    value="{{ $item_permohonan_uji_klinik->analis_permohonan_uji_klinik ?? Auth::user()->id }}"
                    readonly>

                  <input type="text" class="form-control" name="analisdetail_permohonan_uji_klinik"
                    id="analisdetail_permohonan_uji_klinik" placeholder="Masukkan nama analis"
                    value="{{ $item_permohonan_uji_klinik->analis->name ?? Auth::user()->name }}" readonly>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label for="analisnip_permohonan_uji_klinik">NIP Analis</label>

                  <input type="text" class="form-control" name="analisnip_permohonan_uji_klinik"
                    id="analisnip_permohonan_uji_klinik" placeholder="Masukkan NIP analis"
                    value="{{ $item_permohonan_uji_klinik->analis->nip_users ?? '' }}" readonly>
                </div>
              </div>
            </div> --}}

                        <div class="form-group">
                            <label for="analis_permohonan_uji_klinik">Analis<span style="color: red">*</span></label>
                            <input type="hidden" class="form-control" name="analis_permohonan_uji_klinik"
                                id="analis_permohonan_uji_klinik" placeholder="Masukkan nama analis"
                                value="{{ $item_permohonan_uji_klinik->analis_permohonan_uji_klinik ?? Auth::user()->id }}"
                                readonly>

                            <input type="text" class="form-control" name="name_analis_permohonan_uji_klinik"
                                id="name_analis_permohonan_uji_klinik" placeholder="Masukkan nama analis"
                                value="{{ $item_permohonan_uji_klinik->name_analis_permohonan_uji_klinik ?? 'Estu Lentera' }}">
                            <br>
                            <input type="text" class="form-control" name="nip_analis_permohonan_uji_klinik"
                                id="nip_analis_permohonan_uji_klinik" placeholder="Masukkan NIP analis"
                                value="{{ $item_permohonan_uji_klinik->nip_analis_permohonan_uji_klinik ?? '111111111111111111' }}">

                        </div>
                        <div class="form-group">
                            <div class="form-check form-check-flat form-check-primary">
                                <label class="form-check-label">
                                    <input type="checkbox" data-id="{{ Auth::user()->id }}"
                                        data-name="{{ Auth::user()->name }}" data-nip="{{ Auth::user()->nip_users }}"
                                        class="form-check-input" name="by_account" id="by_account">
                                    Sesuai Akun
                                    <i class="input-helper"></i></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-parameter" class="table">
                        <thead>
                            <tr>
                                <th style="width: 20%">Nama Test</th>
                                <th style="width: 15%">Nilai Rujukan</th>
                                <th style="width: 15%">Satuan</th>
                                <th style="width: 15%">Hasil</th>
                                <th style="width: 15%">Flag</th>
                                <th style="width: 20%">Keterangan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $no = 0;
                            @endphp



                            @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                                <tr>
                                    <th colspan="6">
                                        <strong>{{ $item_parameter_jenis_klinik['name_parameter_jenis_klinik'] }}</strong>
                                    </th>
                                </tr>
                                @foreach ($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] as $key_satuan_klinik => $item_satuan_klinik)
                                    @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                                        <tr>
                                            <td colspan="6">
                                                -{{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}


                                                <input type="hidden"
                                                    name="permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="permohonan_uji_parameter_klinik_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}"
                                                    readonly>
                                            </td>
                                        </tr>

                                        @php
                                            $no_sub = 0;
                                        @endphp

                                        {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parametersubsatuan --}}
                                        @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                            <tr>
                                                <td style="width: 20%">
                                                    <p style="padding-left: 30px">
                                                        {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}
                                                        ~</p>

                                                    <input type="hidden"
                                                        name="parameter_sub_satuan_klinik_id[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="parameter_sub_satuan_klinik_id_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}"
                                                        readonly>
                                                </td>

                                                <td style="width: 15%">
                                                    <input type="text" class="form-control"
                                                        name="baku_mutu_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="baku_mutu_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}"
                                                        readonly>

                                                    <input type="hidden" class="form-control"
                                                        name="min_baku_mutu_detail_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="min_baku_mutu_detail_parameter_klinik_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['min_baku_mutu_detail_parameter_klinik'] }}"
                                                        readonly>
                                                    <input type="hidden" class="form-control"
                                                        name="max_baku_mutu_detail_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="max_baku_mutu_detail_parameter_klinik_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['max_baku_mutu_detail_parameter_klinik'] }}"
                                                        readonly>
                                                    <input type="hidden" class="form-control"
                                                        name="equal_baku_mutu_detail_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="equal_baku_mutu_detail_parameter_klinik_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['equal_baku_mutu_detail_parameter_klinik'] }}"
                                                        readonly>
                                                </td>

                                                <td style="width: 15%">
                                                    @if ($item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] != null)
                                                        {{-- @php
                        dd($item_satuan_klinik->unit->name_unit);
                        @endphp --}}

                                                        <select class="form-control satuan_permohonan_uji_parameter_klinik"
                                                            name="satuan_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                            id="satuan_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}">

                                                            <option
                                                                value="{{ $item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] }}"
                                                                selected>
                                                                {{ $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'] }}
                                                            </option>
                                                        </select>
                                                    @else
                                                        {{-- @php
                            dd($item_satuan_klinik->bakumutu->unit != null);
                            @endphp --}}

                                                        <select class="form-control satuan_permohonan_uji_parameter_klinik"
                                                            name="satuan_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                            id="satuan_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}">

                                                            <option value=""></option>
                                                        </select>
                                                    @endif
                                                </td>

                                                <td style="width: 15%">
                                                    <input type="text" class="form-control"
                                                        name="hasil_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="hasil_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? old('hasil_permohonan_uji_sub_parameter_klinik') }}"
                                                        onkeydown="GetHasilSubParameter({{ $no_sub }})"
                                                        onkeyup="GetHasilSubParameter({{ $no_sub }})">
                                                </td>

                                                <td style="width: 15%">
                                                    <p
                                                        id="flag_permohonan_uji_sub_parameter_klinik_text_{{ $no_sub }}">
                                                        {!! $item_subsatuan_klinik['flag_permohonan_uji_sub_parameter_klinik'] ?? '-' !!}
                                                    </p>

                                                    <input type="hidden" class="form-control"
                                                        name="flag_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        id="flag_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}"
                                                        value="{{ $item_subsatuan_klinik['flag_permohonan_uji_sub_parameter_klinik'] ?? old('flag_permohonan_uji_sub_parameter_klinik') }}"
                                                        readonly>
                                                </td>

                                                <td style="width: 20%">
                                                    <textarea class="form-control" id="keterangan_permohonan_uji_sub_parameter_klinik_{{ $no_sub }}"
                                                        name="keterangan_permohonan_uji_sub_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}][{{ $item_subsatuan_klinik['id_permohonan_uji_sub_parameter_klinik'] }}]"
                                                        cols="5" rows="5">{{ $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? old('keterangan_permohonan_uji_sub_parameter_klinik') }}</textarea>
                                                </td>
                                            </tr>

                                            @php
                                                $no_sub++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td style="width: 20%">
                                                -
                                                {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}


                                                <input type="hidden"
                                                    name="permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="permohonan_uji_parameter_klinik_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}"
                                                    readonly>
                                            </td>

                                            <td style="width: 15%">
                                                <input type="text" class="form-control"
                                                    name="baku_mutu_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="baku_mutu_permohonan_uji_parameter_klinik_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['nilai_baku_mutu'] }}" readonly>

                                                <input type="hidden" class="form-control"
                                                    name="min[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="min_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['min'] }}" readonly>
                                                <input type="hidden" class="form-control"
                                                    name="max[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="max_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['max'] }}" readonly>
                                                <input type="hidden" class="form-control"
                                                    name="equal[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="equal_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['equal'] }}" readonly>
                                            </td>

                                            {{-- kondisi jika data satuan pada permohonan uji parameter klinik belum terpilih dan suda terpilih --}}
                                            <td style="width: 15%">

                                                @if ($item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] != null)
                                                    {{-- @php
                                dd($item_satuan_klinik->unit->name_unit);
                                @endphp --}}

                                                    <select class="form-control satuan_permohonan_uji_parameter_klinik"
                                                        name="satuan_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                        id="satuan_permohonan_uji_parameter_klinik_{{ $no }}">

                                                        <option
                                                            value="{{ $item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] }}"
                                                            selected>
                                                            {{ $item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik'] }}
                                                        </option>
                                                    </select>
                                                @else
                                                    {{-- @php
                                            dd($item_satuan_klinik->bakumutu->unit != null);
                                            @endphp --}}

                                                    <select class="form-control satuan_permohonan_uji_parameter_klinik"
                                                        name="satuan_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                        id="satuan_permohonan_uji_parameter_klinik_{{ $no }}">

                                                        <option value=""></option>
                                                    </select>
                                                @endif

                                            </td>

                                            <td style="width: 15%">
                                                <input type="text" class="form-control"
                                                    name="hasil_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="hasil_permohonan_uji_parameter_klinik_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? old('hasil_permohonan_uji_parameter_klinik') }}"
                                                    onkeyup="GetHasilParameter({{ $no }})"
                                                    onchange="GetHasilParameter({{ $no }})">
                                            </td>

                                            <td style="width: 15%">
                                                <p id="flag_permohonan_uji_parameter_klinik_text_{{ $no }}">
                                                    {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                                                        ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                                                        : '' !!}</p>


                                                <input type="hidden" class="form-control"
                                                    name="flag_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="flag_permohonan_uji_parameter_klinik_{{ $no }}"
                                                    value="{{ $item_satuan_klinik['flag_permohonan_uji_parameter_klinik'] ?? old('flag_permohonan_uji_parameter_klinik') }}"
                                                    readonly>
                                            </td>

                                            <td style="width: 20%">
                                                <textarea class="form-control"
                                                    name="keterangan_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
                                                    id="keterangan_permohonan_uji_parameter_klinik_{{ $no }}" cols="5" rows="5">{{ $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? old('keterangan_permohonan_uji_parameter_klinik') }}</textarea>
                                            </td>
                                        </tr>
                                    @endif

                                    @php
                                        $no++;
                                    @endphp
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

    </form>
    <button type="button" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
    <button type="button" class="btn btn-light"
        onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
    </div>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

    <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>



    <script>
        function GetHasilParameter(row) {
            // mengkalkulasi hitungan nilai rujukan dan nilai hasil sehingga mendapatkan hasil flagnya dari baku mutu
            var val_baku_mutu = $('#baku_mutu_permohonan_uji_parameter_klinik_' + row).val();
            var val_min = $('#min_' + row).val();
            var val_max = $('#max_' + row).val();
            var val_equal = $('#equal_' + row).val();
            var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
            var cetak_hasil = '';



            $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

            if (val_hasil != null && val_hasil != '') {
                if (val_equal != null && val_equal != '') {
                    console.log(val_equal);
                    var cetak_hasil = "";

                    if (val_equal != val_hasil) {
                        cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                        $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                        $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
                    } else {

                        $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                        $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');
                    }

                } else {

                    var cetak_hasil = "";

                    if ((val_min != null && val_min != '') && (val_max != null && val_max != '')) {
                        var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
                        if (parseFloat(val_hasil) >= parseFloat(val_min) && parseFloat(val_hasil) <= parseFloat(val_max)) {
                            cetak_hasil = val_hasil;
                            $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

                        } else {
                            cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                            $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);

                        }


                    } else {

                        if (val_min != null && val_min != '') {

                            if (parseFloat(val_hasil) < parseFloat(val_min)) {
                                cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
                            } else {
                                cetak_hasil = val_hasil;
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

                            }
                        }

                        if (val_max != null && val_max != '') {
                            if (parseFloat(val_hasil) > parseFloat(val_max)) {

                                cetak_hasil = '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>';
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val(1);
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);

                            } else {
                                cetak_hasil = val_hasil;
                                $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');

                            }
                        }
                    }
                }


            } else {

                $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
                $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('');
            }

            // if (val_baku_mutu !== null && val_baku_mutu !== '-') {
            //   if (val_min != 0 && val_max != 0) {
            //     var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
            //     var cetak_hasil = "";

            //     console.log(val_hasil);

            //     // kondisi mendapatkan nilai between

            //     if (val_hasil == null || val_hasil == '') {
            //       console.log('kosong');
            //       $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
            //       $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('-');
            //     } else {
            //       if (val_hasil >= val_min && val_hasil <= val_max) {
            //         cetak_hasil = val_hasil;

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       } else {
            //         cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       }
            //     }
            //   }

            //   // mendeteksi jika yang dimasukkan positif atau negatif
            //   if (val_equal != null && val_equal != 0) {
            //     var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();

            //     if (val_hasil == null || val_hasil == '') {
            //       $('#flag_permohonan_uji_parameter_klinik_' + row).val('');
            //       $('#flag_permohonan_uji_parameter_klinik_text_' + row).html('-');
            //     } else {
            //       if (val_hasil == val_equal) {
            //         cetak_hasil = val_hasil;

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       } else {
            //         cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';

            //         $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
            //         $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
            //       }
            //     }
            //   }
            // }
        }

        function GetHasilSubParameter(row) {
            // mengkalkulasi hitungan nilai rujukan dan nilai hasil sehingga mendapatkan hasil flagnya dari baku mutu sub parameter
            var val_baku_mutu = $('#baku_mutu_permohonan_uji_sub_parameter_klinik_' + row).val();
            var val_min = $('#min_baku_mutu_detail_parameter_klinik_' + row).val();
            var val_max = $('#max_baku_mutu_detail_parameter_klinik_' + row).val();
            var val_equal = $('#equal_baku_mutu_detail_parameter_klinik_' + row).val();
            var cetak_hasil = '';


            // $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
            // $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(0);

            if (val_baku_mutu !== null && val_baku_mutu !== '-') {
                // mendeteksi jika yang dimasukkan nilai antara

                if (val_hasil != null && val_hasil != '') {

                    if (val_equal != null && val_equal != '') {
                        var cetak_hasil = "";
                        if (val_equal != val_hasil) {
                            cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                            $('#flag_permohonan_uji_parameter_klinik_' + row).val(cetak_hasil);
                            $('#flag_permohonan_uji_parameter_klinik_text_' + row).html(cetak_hasil);
                        } else {
                            cetak_hasil = val_hasil;
                            $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                            $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');
                        }

                    } else {
                        var cetak_hasil = "";

                        if ((val_min != null && val_min != '') && (val_max != null && val_max != '')) {
                            var val_hasil = $('#hasil_permohonan_uji_parameter_klinik_' + row).val();
                            if (parseFloat(val_hasil) >= parseFloat(val_min) && parseFloat(val_hasil) <= parseFloat(
                                    val_max)) {
                                cetak_hasil = val_hasil;
                                $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                                $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');

                            } else {
                                cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                                $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val(cetak_hasil);
                                $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(cetak_hasil);


                            }

                        } else {

                            if (val_min != null && val_min != '') {

                                if (parseFloat(val_hasil) < parseFloat(val_min)) {
                                    cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');
                                } else {
                                    cetak_hasil = val_hasil;
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val(cetak_hasil);
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(cetak_hasil);


                                }
                            }

                            if (val_max != null && val_max != '') {
                                if (parseFloat(val_hasil) > parseFloat(val_max)) {
                                    cetak_hasil = '<strong>' + val_hasil + '</strong> <sup style="color: red">*</sup>';
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');

                                } else {
                                    cetak_hasil = val_hasil;
                                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val(cetak_hasil);
                                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html(cetak_hasil);


                                }
                            }
                        }
                    }


                } else {

                    $('#flag_permohonan_uji_sub_parameter_klinik_' + row).val('');
                    $('#flag_permohonan_uji_sub_parameter_klinik_text_' + row).html('-');
                }



            }
        }

        $('#is_urine').on('change', function() {
            if ($(this).is(":checked")) {
                $("#spesimen_urine_permohonan_uji_klinik").css('display', 'none')
            } else {
                $("#spesimen_urine_permohonan_uji_klinik").css('display', 'block')
            }
        });

        $('#is_darah').on('change', function() {
            if ($(this).is(":checked")) {
                $("#spesimen_darah_permohonan_uji_klinik").css('display', 'none')
            } else {
                $("#spesimen_darah_permohonan_uji_klinik").css('display', 'block')
            }
        });

        $('#by_account').prop('checked', false);
        $('#by_account').on('change', function() {
            if ($(this).is(":checked")) {
                $("#name_analis_permohonan_uji_klinik").val($(this).data('name'))
                $("#nip_analis_permohonan_uji_klinik").val($(this).data('nip'))
                $("#analis_permohonan_uji_klinik").val($(this).data('id'))
                console.log("checked");

                // it is checked
            } else {
                $("#name_analis_permohonan_uji_klinik").val("Estu Lentera")
                $("#nip_analis_permohonan_uji_klinik").val("111111111111111111")
            }
        })
        var CSRF_TOKEN = $('#csrf-token').val();

        $("#dokter_permohonan_uji_klinik").select2({
            ajax: {
                url: "{{ route('get-dokter-by-select') }}",
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
            placeholder: 'Pilih dokter',
            allowClear: true
        });

        $(".satuan_permohonan_uji_parameter_klinik").select2({
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
                        results: response
                    };
                },
                cache: true,
            },
            placeholder: 'Pilih unit',
            allowClear: true
        });
        $(document).ready(function() {
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
                                    document.location = '/elits-permohonan-uji-klinik';
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

                    }
                })
            });
        })
    </script>
@endsection
