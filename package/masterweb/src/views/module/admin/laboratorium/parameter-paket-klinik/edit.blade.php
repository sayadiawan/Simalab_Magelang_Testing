@extends('masterweb::template.admin.layout')
@section('title')
    Parameter Paket Klinik
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/home') }}">
                                        <i class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-parameter-paket-klinik') }}">Parameter Paket
                                        Klinik</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page"><span>edit</span></li>
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
                action="{{ route('elits-parameter-paket-klinik.update', $item->id_parameter_paket_klinik) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name_parameter_paket_klinik">Nama Parameter Paket</label>

                    <input type="text" class="form-control" id="name_parameter_paket_klinik"
                        name="name_parameter_paket_klinik" placeholder="Nama parameter paket klinik.."
                        value="{{ $item->name_parameter_paket_klinik ?? old('name_parameter_paket_klinik') }}">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="singkatan_laporan">Singkatan Laporan</label>
                        <input type="text" class="form-control" id="singkatan_laporan" name="singkatan_laporan"
                            placeholder="Contoh: HDL, GDN, LED.."
                            value="{{ $item->singkatan_laporan ?? old('singkatan_laporan') }}">
                        <small class="form-text text-muted">Dipakai di laporan klinik harian/tahunan (termasuk Haji).</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="kategori_laporan">Kategori Laporan</label>
                        <select class="form-control" id="kategori_laporan" name="kategori_laporan">
                            <option value="">Otomatis</option>
                            <option value="kimia" {{ old('kategori_laporan', $item->kategori_laporan ?? '') === 'kimia' ? 'selected' : '' }}>Kimia klinik</option>
                            <option value="lain" {{ old('kategori_laporan', $item->kategori_laporan ?? '') === 'lain' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="is_agregat_laporan">Tipe Hitung</label>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="is_agregat_laporan" name="is_agregat_laporan" value="1"
                                {{ old('is_agregat_laporan', $item->is_agregat_laporan ?? 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_agregat_laporan">
                                Paket gabungan (1 permohonan = 1, mis. Urin Rutin / Narkoba / Darah Rutin)
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="tampil_di_laporan" name="tampil_di_laporan" value="1"
                                {{ old('tampil_di_laporan', $item->tampil_di_laporan ?? 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="tampil_di_laporan">
                                Tampilkan di laporan klinik (/report-annual-clinic)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="parameter_jenis">
                                    {{-- fleksibel card untuk jenis parameter --}}

                                    @php
                                        $no_parent = 1;
                                    @endphp

                                    @if (count($item->parameterpaketjenisklinik) > 0)
                                        @foreach ($item->parameterpaketjenisklinik as $index_parameterpaketjenisklinik => $value_parameterpaketjenisklinik)
                                            <div class="form-group parameter_jenis_card"
                                                id="parameter_jenis_card_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}">
                                                <div class="card-body">
                                                    <button type="button" class="close"
                                                        onclick="minus({{ $value_parameterpaketjenisklinik->sort ?? $no_parent }})"
                                                        id="minus_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}"
                                                        data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                    <h5 class="card-title">
                                                        <center>Parameter Jenis Klinik
                                                            {{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}
                                                        </center>
                                                    </h5>
                                                    <div class="form-group">
                                                        <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

                                                        <select class="form-control"
                                                            name="parameter_jenis_klinik[{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}]"
                                                            id="parameter_jenis_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}">
                                                            <option
                                                                value="{{ $value_parameterpaketjenisklinik->parameter_jenis_klinik_id }}"
                                                                selected>
                                                                {{ $value_parameterpaketjenisklinik->parameterjenisklinik->name_parameter_jenis_klinik . ' - ' . $value_parameterpaketjenisklinik->parameterjenisklinik->code_parameter_jenis_klinik }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    {{-- display parameter satuan paket --}}
                                                    <div class="row"
                                                        id="display-parameter-satuan-paket-{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}">
                                                        <div class="col-md-12">
                                                            <div id="form-detail-parameter-satuan-paket-{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}"
                                                                class="mb-3">
                                                                <div class="table-responsive">
                                                                    <table class="table table-parameter-satuan-paket"
                                                                        style="width: 100%">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width:50%;">Nama Parameter Satuan
                                                                                </th>
                                                                                <th style="width:30%;">Urutan</th>
                                                                                <th style="width:20%;">Aksi</th>
                                                                            </tr>
                                                                        </thead>

                                                                        <tbody class="parameter-satuan-paket">
                                                                            @if (count($value_parameterpaketjenisklinik->parametersatuanpaketklinik) > 0)
                                                                                @php
                                                                                    $no_child = 1;
                                                                                @endphp

                                                                                @foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $index_parametersatuanpaketklinik => $value_parametersatuanpaketklinik)
                                                                                    <tr
                                                                                        id="row_parameter_satuan_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }}">
                                                                                        <td>
                                                                                            <select
                                                                                                class="form-control select2_parameter_satuan_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }}"
                                                                                                name="parameter_satuan_klinik[{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}][]"
                                                                                                id="parameter_satuan_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }}"
                                                                                                style="width: 100%">
                                                                                                <option
                                                                                                    value="{{ $value_parametersatuanpaketklinik->parameter_satuan_klinik }}"
                                                                                                    selected>
                                                                                                    {{ isset($value_parametersatuanpaketklinik->parametersatuanklinik) && $value_parametersatuanpaketklinik->parametersatuanklinik ? $value_parametersatuanpaketklinik->parametersatuanklinik->name_parameter_satuan_klinik : '-' }}
                                                                                                </option>
                                                                                            </select>
                                                                                        </td>

                                                                                        <td>
                                                                                            <input type="number"
                                                                                                class="form-control"
                                                                                                name="sorting_parameter_satuan_paket_klinik[{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}][]"
                                                                                                id="sorting_parameter_satuan_paket_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }}"
                                                                                                value="{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }}">
                                                                                        </td>

                                                                                        <td>
                                                                                            <button type="button"
                                                                                                class="btn btn-primary btn-add-parameter-satuan-paket"
                                                                                                data-parameter-satuan-paket="{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}"
                                                                                                onclick="addParameterSatuanPaket({{ $value_parameterpaketjenisklinik->sort ?? $no_parent }},{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }})">
                                                                                                <i class="fas fa-plus"></i>
                                                                                            </button>

                                                                                            @if (
                                                                                                ($value_parametersatuanpaketklinik->sorting != 1 && $value_parametersatuanpaketklinik->sorting != 0) ||
                                                                                                    ($no_child != 1 && $no_child != 0))
                                                                                                <button type="button"
                                                                                                    class="btn btn-danger btn-remove-parameter-satuan-paket"
                                                                                                    data-parameter-satuan-paket="{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}"
                                                                                                    onclick="removeParameterSatuanPaket({{ $value_parameterpaketjenisklinik->sort ?? $no_parent }},{{ $value_parametersatuanpaketklinik->sorting ?? $no_child }})">
                                                                                                    <i
                                                                                                        class="fas fa-minus"></i>
                                                                                                </button>
                                                                                            @endif
                                                                                        </td>
                                                                                    </tr>

                                                                                    <script>
                                                                                        $(function() {
                                                                                            var CSRF_TOKEN = "{{ csrf_token() }}";

                                                                                            $("#parameter_jenis_klinik_{{ $no_parent }}").select2({
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
                                                                                                },
                                                                                                placeholder: "Parameter Jenis",
                                                                                            });


                                                                                            $("#parameter_jenis_klinik_{{ $no_parent }}").change(function(e) {
                                                                                                $("#parameter_satuan_klinik_{{ $no_parent }}_{{ $no_child }}").val(0).trigger(
                                                                                                    'change');

                                                                                                $("#parameter_satuan_klinik_{{ $no_parent }}_{{ $no_child }}").select2({
                                                                                                    ajax: {
                                                                                                        url: "{{ route('getParameterSatuanKlinik') }}",
                                                                                                        type: "post",
                                                                                                        dataType: 'json',
                                                                                                        delay: 250,
                                                                                                        data: function(params) {
                                                                                                            return {
                                                                                                                _token: CSRF_TOKEN,
                                                                                                                search: params.term, // search term
                                                                                                                param: $("#parameter_jenis_klinik_{{ $no_parent }}").val()
                                                                                                            };
                                                                                                        },
                                                                                                        processResults: function(response) {
                                                                                                            return {
                                                                                                                results: response
                                                                                                            };
                                                                                                        },
                                                                                                        cache: true
                                                                                                    },
                                                                                                    placeholder: "Parameter Satuan",
                                                                                                    allowClear: true,
                                                                                                    theme: "classic"
                                                                                                });
                                                                                            })



                                                                                            $("#parameter_satuan_klinik_{{ $no_parent }}_{{ $no_child }}").select2({
                                                                                                ajax: {
                                                                                                    url: "{{ route('getParameterSatuanKlinik') }}",
                                                                                                    type: "post",
                                                                                                    dataType: 'json',
                                                                                                    delay: 250,
                                                                                                    data: function(params) {
                                                                                                        return {
                                                                                                            _token: CSRF_TOKEN,
                                                                                                            search: params.term, // search term
                                                                                                            param: $("#parameter_jenis_klinik_{{ $no_parent }}").val()
                                                                                                        };
                                                                                                    },
                                                                                                    processResults: function(response) {
                                                                                                        return {
                                                                                                            results: response
                                                                                                        };
                                                                                                    },
                                                                                                    cache: true
                                                                                                },
                                                                                                placeholder: "Parameter Satuan",
                                                                                                allowClear: true,
                                                                                                theme: "classic"
                                                                                            });
                                                                                        })
                                                                                    </script>

                                                                                    @php
                                                                                        $no_child++;
                                                                                    @endphp
                                                                                @endforeach
                                                                            @else
                                                                                <tr
                                                                                    id="row_parameter_satuan_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_1">
                                                                                    <td>
                                                                                        <select
                                                                                            class="form-control select2_parameter_satuan_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_1"
                                                                                            name="parameter_satuan_klinik[{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}][]"
                                                                                            id="parameter_satuan_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_1"
                                                                                            style="width: 100%">
                                                                                            <option value=""></option>
                                                                                        </select>
                                                                                    </td>

                                                                                    <td>
                                                                                        <input type="number"
                                                                                            class="form-control"
                                                                                            name="sorting_parameter_satuan_paket_klinik[{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}][]"
                                                                                            id="sorting_parameter_satuan_paket_klinik_{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}_1"
                                                                                            value="1">
                                                                                    </td>

                                                                                    <td>
                                                                                        <button type="button"
                                                                                            class="btn btn-primary btn-add-parameter-satuan-paket"
                                                                                            data-parameter-satuan-paket="{{ $value_parameterpaketjenisklinik->sort ?? $no_parent }}"
                                                                                            onclick="addParameterSatuanPaket({{ $value_parameterpaketjenisklinik->sort ?? $no_parent }},1)">
                                                                                            <i class="fas fa-plus"></i>
                                                                                        </button>
                                                                                    </td>
                                                                                </tr>
                                                                            @endif

                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if ($no_parent == count($item->parameterpaketjenisklinik))
                                                        <button type="button" id="tambah"
                                                            class="tambah btn btn-primary btn-lg btn-block"><i
                                                                class="fas fa-plus"></i>
                                                            Parameter Jenis Klinik</button>
                                                    @endif
                                                </div>
                                            </div>

                                            <script>
                                                $(function() {
                                                    $("#minus_{{ $no_parent }}").click(function() {
                                                        sorting()
                                                    });
                                                })
                                            </script>

                                            @php
                                                $no_parent++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <div class="form-group parameter_jenis_card" id="parameter_jenis_card_1">
                                            <div class="card-body ">
                                                <button type="button" class="close" onclick="minus(1)" id="minus_1"
                                                    data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                                <h5 class="card-title">
                                                    <center>Parameter Jenis Klinik 1</center>
                                                </h5>
                                                <div class="form-group">
                                                    <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

                                                    <select class="form-control" name="parameter_jenis_klinik[1]"
                                                        id="parameter_jenis_klinik_1">
                                                        <option value=""></option>
                                                    </select>
                                                </div>

                                                {{-- display parameter satuan paket --}}
                                                <div class="row" id="display-parameter-satuan-paket-1">
                                                    <div class="col-md-12">
                                                        <div id="form-detail-parameter-satuan-paket-1" class="mb-3">
                                                            <div class="table-responsive">
                                                                <table class="table table-parameter-satuan-paket"
                                                                    style="width: 100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="width:50%;">Nama Parameter Satuan
                                                                            </th>
                                                                            <th style="width:30%;">Urutan</th>
                                                                            <th style="width:20%;">Aksi</th>
                                                                        </tr>
                                                                    </thead>

                                                                    <tbody class="parameter-satuan-paket">
                                                                        <tr id="row_parameter_satuan_klinik_1_1">
                                                                            <td>
                                                                                <select
                                                                                    class="form-control select2_parameter_satuan_klinik_1_1"
                                                                                    name="parameter_satuan_klinik[1][]"
                                                                                    id="parameter_satuan_klinik_1_1"
                                                                                    style="width: 100%">
                                                                                    <option value=""></option>
                                                                                </select>
                                                                            </td>

                                                                            <td>
                                                                                <input type="number" class="form-control"
                                                                                    name="sorting_parameter_satuan_paket_klinik[1][]"
                                                                                    id="sorting_parameter_satuan_paket_klinik_1_1"
                                                                                    value="1">
                                                                            </td>

                                                                            <td>
                                                                                <button type="button"
                                                                                    class="btn btn-primary btn-add-parameter-satuan-paket"
                                                                                    data-parameter-satuan-paket="1"
                                                                                    onclick="addParameterSatuanPaket(1,1)">
                                                                                    <i class="fas fa-plus"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="button" id="tambah"
                                                    class="tambah btn btn-primary btn-lg btn-block">
                                                    <i class="fas fa-plus"></i>
                                                    Parameter Jenis Klinik
                                                </button>
                                            </div>
                                        </div>

                                        <script>
                                            $(function() {
                                                $("#parameter_jenis_klinik_1").select2({
                                                    ajax: {
                                                        url: "{{ route('getParameterJenisKlinik') }}",
                                                        type: "post",
                                                        dataType: 'json',
                                                        delay: 250,
                                                        data: function(params) {
                                                            return {
                                                                _token: "{{ csrf_token() }}",
                                                                search: params.term // search term
                                                            };
                                                        },
                                                        processResults: function(response) {
                                                            return {
                                                                results: response
                                                            };
                                                        },
                                                        cache: true
                                                    },
                                                    placeholder: "Parameter Jenis",
                                                });


                                                $("#parameter_jenis_klinik_1").change(function(e) {
                                                    $("#parameter_satuan_klinik_1_1").val(0).trigger('change');

                                                    $("#parameter_satuan_klinik_1_1").select2({
                                                        ajax: {
                                                            url: "{{ route('getParameterSatuanKlinik') }}",
                                                            type: "post",
                                                            dataType: 'json',
                                                            delay: 250,
                                                            data: function(params) {
                                                                return {
                                                                    _token: "{{ csrf_token() }}",
                                                                    search: params.term, // search term
                                                                    param: $("#parameter_jenis_klinik_1").val()
                                                                };
                                                            },
                                                            processResults: function(response) {
                                                                return {
                                                                    results: response
                                                                };
                                                            },
                                                            cache: true
                                                        },
                                                        placeholder: "Parameter Satuan",
                                                        allowClear: true,
                                                        theme: "classic"
                                                    });
                                                })
                                            })
                                        </script>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="harga_parameter_paket_klinik">Harga Parameter Paket (Rupiah)</label>

                    <input type="number" class="form-control" id="harga_parameter_paket_klinik"
                        name="harga_parameter_paket_klinik" placeholder="Harga parameter paket klinik.."
                        value="{{ $item->harga_parameter_paket_klinik ?? old('harga_parameter_paket_klinik') }}">
                </div>

                <br>

                <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
                <button type="button" onclick="document.location='{{ url('/elits-parameter-paket-klinik') }}'"
                    class="btn btn-light">Kembali</button>
            </form>
        </div>
    </div>

    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert.min.js') }}"></script>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery.form.min.js') }}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        function goBack() {
            window.history.back();
        }

        $(function() {
            var CSRF_TOKEN = "{{ csrf_token() }}";

            $('.btn-simpan').on('click', function() {
                $('#form').ajaxForm({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location = '/elits-parameter-paket-klinik';
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

        var no = parseInt("{{ count($item->parameterpaketjenisklinik) ?? 1 }}");
        var no_child = 1;
        // var no_urut = 1;

        function tambah(no) {
            $('#tambah').remove();

            var new_field = $(`
            <div class="form-group parameter_jenis_card" id="parameter_jenis_card_${no}">
              <div class="card-body ">
                <button type="button" class="close" onclick="minus(${no})" id="minus_${no}" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>

                <h5 class="card-title">
                  <center>Parameter Jenis Klinik ${no}</center>
                </h5>

                <div class="form-group">
                  <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

                  <select class="form-control" name="parameter_jenis_klinik[${no}]" id="parameter_jenis_klinik_${no}">
                    <option value=""></option>
                  </select>
                </div>

                {{-- display parameter satuan paket --}}
                <div class="row" id="display-parameter-satuan-paket-${no}">
                  <div class="col-md-12">
                    <div id="form-detail-parameter-satuan-paket-${no}" class="mb-3">
                      <div class="table-responsive">
                        <table class="table table-parameter-satuan-paket" style="width: 100%">
                          <thead>
                            <tr>
                              <th style="width:50%;">Nama Parameter Satuan</th>
                              <th style="width:30%;">Urutan</th>
                              <th style="width:20%;">Aksi</th>
                            </tr>
                          </thead>

                          <tbody class="parameter-satuan-paket">
                            <tr id="row_parameter_satuan_klinik_${no}_1">
                              <td>
                                <select class="form-control select2_parameter_satuan_klinik_${no}_1"
                                  name="parameter_satuan_klinik[${no}][]" id="parameter_satuan_klinik_${no}_1"
                                  style="width: 100%">
                                  <option value=""></option>
                                </select>
                              </td>

                              <td>
                                <input type="number" class="form-control"
                                  name="sorting_parameter_satuan_paket_klinik[${no}][]"
                                  id="sorting_parameter_satuan_paket_klinik_${no}_1" value="1">
                              </td>

                              <td>
                                <button type="button" class="btn btn-primary btn-add-parameter-satuan-paket"
                                  data-parameter-satuan-paket="${no}" onclick="addParameterSatuanPaket(${no},1)">
                                  <i class="fas fa-plus"></i>
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>

                <button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i>
                  Parameter Jenis Klinik</button>
              </div>
            </div>`);

            $(".parameter_jenis").append(new_field);

            $(function() {
                $("select").on("select2:select", function(evt) {
                    var element = evt.params.data.element;
                    var $element = $(element);

                    $element.detach();
                    $(this).append($element);
                    $(this).trigger("change");
                });

                var CSRF_TOKEN = "{{ csrf_token() }}";

                $("#parameter_jenis_klinik_" + no).select2({
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
                    },
                    placeholder: "Parameter Jenis",
                });

                $("#parameter_jenis_klinik_" + no).change(function(e) {
                    $("#parameter_satuan_klinik_" + no + "_" + no_child).val(0).trigger('change');

                    $("#parameter_satuan_klinik_" + no + "_" + no_child).select2({
                        ajax: {
                            url: "{{ route('getParameterSatuanKlinik') }}",
                            type: "post",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    _token: CSRF_TOKEN,
                                    search: params.term, // search term
                                    param: $("#parameter_jenis_klinik_" + no).val()
                                };
                            },
                            processResults: function(response) {
                                return {
                                    results: response
                                };
                            },
                            cache: true
                        },
                        placeholder: "Parameter Satuan",
                        allowClear: true,
                        theme: "classic"
                    });
                })


                $("#tambah").click(function() {
                    tambah(no + 1)
                    sorting()
                });

                sorting()
            })
        }

        function addParameterSatuanPaket(no, no_child) {
            /* var tableParameterSatuanPaketLength = $(".table-parameter-satuan-paket tbody tr").length;

            for (x = 0; x < tableParameterSatuanPaketLength; x++) {
              var tr = $(".table-parameter-satuan-paket tbody tr")[x];
              var count = $(tr).attr('id');
              count = parseInt(count.substring(28));
            } // /for

            var count_table_tbody_tr = $(".table-parameter-satuan-paket tbody tr").length; */

            var tableParameterSatuanPaketLength = $("#parameter_jenis_card_" + no +
                    " .table-parameter-satuan-paket tbody tr")
                .length;

            for (x = 0; x < tableParameterSatuanPaketLength; x++) {
                var tr = $("#parameter_jenis_card_" + no + " .table-parameter-satuan-paket tbody tr")[x];
                var count = $(tr).attr('id');
                count = parseInt(count.substring(30));
            }

            // no = no + 1;
            no_child = count + 1;
            var no_urut = count + 1;

            var dom_html = `
      <tr id="row_parameter_satuan_klinik_${no}_${no_child}">
        <td>
          <select class="form-control select2_parameter_satuan_klinik_${no}_${no_child}"
            name="parameter_satuan_klinik[${no}][]" id="parameter_satuan_klinik_${no}_${no_child}"
            style="width: 100%">
            <option value=""></option>
          </select>
        </td>

        <td>
          <input type="number" class="form-control"
            name="sorting_parameter_satuan_paket_klinik[${no}][]"
            id="sorting_parameter_satuan_paket_klinik_${no}_${no_child}" value="${no_urut}">
        </td>

        <td>
          <button type="button" class="btn btn-primary btn-add-parameter-satuan-paket"
            data-parameter-satuan-paket="${no}" onclick="addParameterSatuanPaket(${no}, ${no_child})">
            <i class="fas fa-plus"></i>
          </button>

          <button type="button" class="btn btn-danger btn-remove-parameter-satuan-paket"
            data-parameter-satuan-paket="${no}" onclick="removeParameterSatuanPaket(${no}, ${no_child})">
            <i class="fas fa-minus"></i>
          </button>
        </td>
      </tr>`;

            if (tableParameterSatuanPaketLength >= 1) {
                $("#parameter_jenis_card_" + no + " .table-parameter-satuan-paket tbody tr:last").after(dom_html);
            } else {
                $("#parameter_jenis_card_" + no + " .table-parameter-satuan-paket tbody").html(dom_html);
            }

            $("#parameter_satuan_klinik_" + no + "_" + no_child).select2({
                ajax: {
                    url: "{{ route('getParameterSatuanKlinik') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: "{{ csrf_token() }}",
                            search: params.term, // search term
                            param: $("#parameter_jenis_klinik_" + no).val()
                        };
                    },
                    processResults: function(response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                },
                placeholder: "Parameter Satuan",
                allowClear: true,
                theme: "classic"
            });
        }

        function removeParameterSatuanPaket(no, no_child) {
            /* var count_table_tbody_tr = $(".table-parameter-satuan-paket tbody tr").length;

            if (count_table_tbody_tr > 1) {
              $(".table-parameter-satuan-paket tbody tr#row_parameter_satuan_klinik_" + no + "_" + no_child).remove();
            } */
            $("#parameter_jenis_card_" + no + " .table-parameter-satuan-paket tbody tr#row_parameter_satuan_klinik_" + no +
                "_" + no_child).remove();
        }

        function minus(no) {
            var count = $(".parameter_jenis .parameter_jenis_card").children().length;

            if (count > 1) {
                $('#parameter_jenis_card_' + no).remove();
                sorting()

                if (no == count) {
                    $('#parameter_jenis_card_' + (count - 1) + ' .card-body').append(
                        '<button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i> Sub Baku Mutu</button>'
                    )

                    $("#tambah").click(function() {
                        tambah(no + 1)
                        sorting()
                    });
                }
            }

        }

        $("#minus_" + no).click(function() {
            sorting()
        });

        function sorting() {
            $(".parameter_jenis .parameter_jenis_card").each(function(i, element) {
                // $(element).find('.card-title');
                // console.log( $(element).find('.card-title'))
                $(element).find('.card-title').html("<center>Parameter Jenis Klinik " + (i + 1) + "</center>");
                $(element).find('.close').prop("id", "minus_" + (i + 1));
                $(element).find('.close').attr("onclick", "minus(" + (i + 1) + ")");
                $(element).prop("id", "parameter_jenis_card_" + (i + 1));
                // $(element).find('#parameter_jenis_klinik_' + (i + 1)).prop("name", "parameter_jenis_klinik[" + (i) + "]");
                // $(element).find('#parameter_satuan_klinik_' + (i + 1)).prop("name", "parameter_satuan_klinik[" + (i) + "][]");

                // no = i + 1;
            });
        }

        $("#tambah").click(function() {
            tambah(no + 1)
        });
    </script>
@endsection
