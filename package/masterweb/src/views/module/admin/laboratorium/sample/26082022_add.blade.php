@extends('masterweb::template.admin.layout')
@section('title')
    Input Data Sampel
@endsection

@section('css')
    <style>
        * {
            margin: 0;
            padding: 0
        }

        html {
            height: 100%
        }
    </style>
@endsection

@section('content')
    <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"
        integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script>
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-samples', [Request::segment(3)]) }}">
                                        Input Data
                                        Sampel</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Input Data Sampel Non Klinik</h4>
        </div>

        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-fill-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('elits-samples.store', [Request::segment(3)]) }}" method="POST">
                @csrf

                <h5 class="card-title">Detail Sampel</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">

                        <div class="col-lg-12">
                            <div class="row">

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="code_permohonan_uji"> Kode Sampel:</label>
                                        <div class="input-group date">
                                            <input type="text" class="form-control" readonly name="code_sample"
                                                id="code_sample" placeholder="Kode Sampel" value="{{ $code }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">

                                    <div class="form-group">
                                        <label for="datesampling_samples">Tanggal Pengambilan</label>


                                        <input id="datesampling_samples" class="form-control" name="datesampling_samples"
                                            placeholder="--/--/--- --:--" />
                                        <!-- <div class="input-group-append">
                                                                                                                      <span class="input-group-text">
                                                                                                                          <i class="fas fa-calendar-alt"></i>
                                                                                                                      </span>
                                                                                                                  </div> -->
                                        <script>
                                            var m = moment(new Date()).format('DD/MM/yyyy HH:mm');

                                            $('#datesampling_samples').datetimepicker({
                                                format: 'dd/mm/yyyy HH:MM',
                                                value: m,
                                                footer: true,
                                                modal: true
                                            });
                                        </script>

                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="datelab_samples">Tanggal Pengiriman</label>
                                        <input id="date_sending" class="form-control" name="date_sending"
                                            placeholder="--/--/--- --:--" />
                                        <!-- <div class="input-group-append">
                                                                                                                          <span class="input-group-text">
                                                                                                                              <i class="fas fa-calendar-alt"></i>
                                                                                                                          </span>
                                                                                                                      </div> -->
                                        <script>
                                            var m = moment(new Date()).format('DD/MM/yyyy HH:mm');

                                            $('#date_sending').datetimepicker({
                                                format: 'dd/mm/yyyy HH:MM',
                                                value: m,
                                                footer: true,
                                                modal: true
                                            });
                                        </script>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="row">

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label for="lokasi_pengambilan">Objek (Lokasi, Makanan, Minuman, Alat Makan, dll)
                                            Pengambilan:</label>
                                        <div class="input-group date">
                                            <input type="text" class="form-control" name="lokasi_pengambilan"
                                                id="lokasi_pengambilan" placeholder="Lokasi Pengambilan"
                                                value="{{ old('lokasi_pengambilan') }}">

                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="nama_pengambilan"> Nama Pengambil:</label>
                                        <div class="input-group date">
                                            <input type="text" class="form-control" name="nama_pengambil"
                                                id="nama_pengambil" placeholder="Nama Pengambil"
                                                value="{{ old('nama_pengambil') }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg">
                                                <label for="customer_samples"><span style="color: red">*</span>Jenis
                                                    Sampel:</label>
                                                <select id="jenis_sampel" name="jenis_sampel"
                                                    class="js-customer-basic-multiple js-states form-control" required
                                                    style="width: 100%">
                                                    <option value="" disabled selected> Pilih Jenis Sampel</option>
                                                    @foreach ($sampletypes as $sampletype)
                                                        <option value="{{ $sampletype->id_sample_type }}">
                                                            {{ $sampletype->name_sample_type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg is_paket" style="display: none">
                                                <label for="customer_samples">&nbsp;</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" id="is_paket" name="is_paket"
                                                        type="checkbox" value="true" id="flexCheckDefault">
                                                    <label class="form-check-label" for="flexCheckDefault">
                                                        Paket
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group jenis_makanan_id" style="display: none">
                                        <label for="packet">Jenis Makanan:</label>
                                        <select id="jenis_makanan_id" name="jenis_makanan_id"
                                            class="js-customer-basic-multiple js-states form-control">
                                            <option value="" disabled selected> Pilih Jenis Makanan</option>
                                            @foreach ($all_jenis_makanan as $jenis_makanan)
                                                <option value="{{ $jenis_makanan->id_jenis_makanan }}">
                                                    {{ $jenis_makanan->name_jenis_makanan }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <div class="form-group packet" style="display: none">
                                        <label for="packet">Paket:</label>
                                        <select id="packet" name="packet"
                                            class="js-customer-basic-multiple js-states form-control">
                                            <option value="" disabled selected> Pilih Jenis Sampel</option>
                                            @foreach ($sampletypes as $sampletype)
                                                <option value="{{ $sampletype->id_sample_type }}">
                                                    {{ $sampletype->name_sample_type }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>


                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="row">

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="name_send_sample"> Nama Pengirim Sampel (<span
                                                style="color: red">*Nama Titik
                                                Lokasi</span>):</label>
                                        <div class="input-group date">
                                            <input type="text" class="form-control" name="name_send_sample"
                                                id="name_send_sample" placeholder="Nama Pengirim Sampel"
                                                value="{{ old('name_send_sample') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="code_sample_customer"> Kode Sampel Pelanggan:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="code_sample_customer"
                                                id="code_sample_customer" placeholder="Kode Sampel Pelanggan"
                                                value="{{ old('code_sample_customer') }}">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="customer_samples"><span style="color: red">*</span>Program:</label>
                                        <select id="program_samples" name="program_samples"
                                            class="js-customer-basic-multiple js-states form-control" required>
                                            <option value="" disabled selected> Pilih Program</option>
                                            @foreach ($programs as $program)
                                                <option value="{{ $program->id_program }}">{{ $program->name_program }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label for="datelab_samples">Catatan Sampel</label>
                                        <textarea class="form-control" name="note" id="exampleFormControlTextarea1" rows="3">{{ old('note') }}</textarea>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">

                            {{-- <label for="name_customer">Parameter</label>


            <select id="methodAttributes" class="js-example-basic-multiple" multiple="multiple" required>
            </select> --}}


                            <div class="container method">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            @php
                                                $char = 'A';
                                            @endphp
                                            @for ($i = 0; $i < count($data_methods); $i++)
                                                @if ($i % 2 == 0 && $i != 0)
                                                    <div class="col-4">
                                                        <table>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <div class="form-group">
                                                                        <div class="form-check">
                                                                            <h5>{{ $char }}. Parameter
                                                                                {{ $data_methods[$i]->name }} : </h5>
                                                                            @php
                                                                                $char++;
                                                                            @endphp
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @foreach ($data_methods[$i]->method as $method)
                                                                <tr>
                                                                    <td>
                                                                        <div class="form-group">
                                                                            <div class="form-check">
                                                                                <input name="method[]"
                                                                                    class="form-check-input checkbox checkbox-{{ $method->id_method }}"
                                                                                    data-price="{{ $method->price_method }}"
                                                                                    data-idlabs="{{ $data_methods[$i]->id_lab }}"
                                                                                    data-idmethod="{{ $method->id_method }}"
                                                                                    type="checkbox"
                                                                                    value="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}"
                                                                                    id="defaultCheck3">
                                                                                <label class="form-check-label"
                                                                                    for="defaultCheck3">
                                                                                    {{ $method->name_method }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>

                                        </div>
                                        <div class="row">
                                        @else
                                            <div class="col-4">
                                                <table>
                                                    <tr>
                                                        <td colspan="2">
                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <h5>{{ $char }}. Parameter
                                                                        {{ $data_methods[$i]->name }} : </h5>
                                                                    @php
                                                                        $char++;
                                                                    @endphp
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @foreach ($data_methods[$i]->method as $method)
                                                        <tr>
                                                            <td>
                                                                <div class="form-group">
                                                                    <div class="form-check">
                                                                        <input name="method[]"
                                                                            class="form-check-input checkbox checkbox-{{ $method->id_method }}"
                                                                            data-price="{{ $method->price_method }}"
                                                                            data-idlabs="{{ $data_methods[$i]->id_lab }}"
                                                                            data-idmethod="{{ $method->id_method }}"
                                                                            type="checkbox"
                                                                            value="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}"
                                                                            id="{{ $method->id_method }}_{{ $data_methods[$i]->id_lab }}">
                                                                        <label class="form-check-label"
                                                                            for="defaultCheck3">
                                                                            {{ $method->name_method }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                            @endif
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cost_samples">Harga</label>
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        Rp.
                                    </span>
                                </div>
                                <input type="number" class="form-control" id="cost_samples" name="cost_samples"
                                    value="0" placeholder="Isikan Harga" readonly required>
                            </div>
                        </div>
                    </li>
                </ul>

                <br>
                <h5 class="card-title"><span style="color: red">*</span>Penerimaan Sampel</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="col-md-12">


                            <div class="form-group">
                                <label for="wadah_samples"><b>1. Wadah</b></label>
                                @foreach ($containers as $container)
                                    <div class="form-check">
                                        <input class="form-check-input" name="wadah" type="radio"
                                            value="{{ $container->id_container }}" id="wadah">
                                        <label class="form-check-label" for="flexCheckChecked">
                                            {{ $container->name_container }}
                                        </label>
                                    </div>
                                @endforeach
                                <div class="form-check">
                                    <input class="form-check-input" name="wadah" type="radio" value="0"
                                        id="wadah">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Lainnya
                                    </label>
                                    <input type="text" class="form-control" id="wadah_samples_others"
                                        style='display:none;' id="wadah_samples" name="wadah_samples"
                                        placeholder="Isikan Wadah">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="wadah_samples"><b>2. Pengawet</b></label>

                                <div class="form-check">
                                    <input class="form-check-input" name="pengawet" type="radio" value="asam"
                                        id="asam">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Asam H<sub>2</sub>SO<sub>4</sub>, HCI, H<sub>3</sub>PO<sub>4</sub>, HNO<sub>3</sub>
                                        (untuk logam)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="pengawet" type="radio" value="naoh"
                                        id="naoh">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        NaOH (untuk cyanida)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="pengawet" type="radio" value="toluen"
                                        id="toluen">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Toluen (untuk NO2, NO3 dan NH3)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="pengawet" type="radio" value="pendinginan"
                                        id="pendinginan">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Pendinginan
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="pengawet" type="radio" value="-"
                                        id="pendinginan">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Tanpa Pengawet
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="pengawet" type="radio" value="0"
                                        id="pengawet_lainnya">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Lainnya
                                    </label>
                                    <input type="text" class="form-control" id="pengawet_others"
                                        style='display:none;' name="pengawet_others_sample"
                                        placeholder="Isikan Pengawet">
                                </div>
                            </div>



                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="volume"><b>3. Volume</b></label>
                                        <input type="number" class="form-control" id="volume" step="any"
                                            name="volume" value="" placeholder="Isikan Volume">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <br>
                                    <select id="unitAttributes" name="unit"
                                        class="form-control js-example-basic-multiple">
                                        <option value="" disabled selected> Pilih Satuan</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id_unit }}">{{ $unit->shortname_unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="wadah_samples"><b>4. Kondisi Sampel</b></label>

                                <div class="form-check">
                                    <input class="form-check-input" name="kondisi_sample" type="radio" value="baik"
                                        id="baik">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Baik
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="kondisi_sample" type="radio" value="rusak"
                                        id="rusak">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Rusak
                                    </label>
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="wadah_samples"><b>5. Sampel</b></label>

                                <div class="form-check">
                                    <input class="form-check-input" name="validation_sample" type="radio"
                                        value="diterima" id="diterima">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Diterima
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="validation_sample" type="radio"
                                        value="ditolak" id="ditolak">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Ditolak
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" name="validation_sample" type="radio"
                                        value="dirujuk" id="dirujuk">
                                    <label class="form-check-label" for="flexCheckChecked">
                                        Dirujuk
                                    </label>
                                </div>

                            </div>
                        </div>
                    </li>
                </ul>
                <br>
                <button type="submit" id="submitAll" class="btn btn-primary mr-2">Simpan</button>


                <button type="button" class="btn btn-light" onclick="window.history.back()">Kembali</button>
            </form>
        </div>


    </div>




@endsection

@section('scripts')
    <script>
        var methods

        var methods_sample_type = []
        var jenis_sample, jenis_makanan

        var price_sample_type = 0;

        $('#ispacket').val("false")
        $(".checkbox").change(function() {
            var total = 0;
            methods = [];
            $(".checkbox:checked").each(function() {
                var idmethod = $(this).data('idmethod');
                var foundMethod = methods.find(function(item) {
                    return item == idmethod;
                });
                if (!foundMethod) {
                    total = total + parseInt($(this).data('price'))
                    methods.push(idmethod)
                }
            });
            let difference = methods.filter(x => !methods_sample_type.includes(x));

            if (arrayContainsArray(methods, methods_sample_type)) {
                $('#ispacket').val("true")
                if (price_sample_type != 0) {
                    let difference = methods.filter(x => !methods_sample_type.includes(x));
                    var total_difference = 0;
                    $(".checkbox:checked").each(function() {

                        var idmethod = $(this).data('idmethod');
                        var foundMethod = difference.find(function(item) {
                            return item == idmethod;
                        });
                        if (foundMethod) {
                            total_difference = total_difference + $(this).data('price')
                            // total=total+parseInt($(this).data('price'))
                            // methods.push(idmethod)
                        }
                    });
                    $('#cost_samples').val(price_sample_type + total_difference)
                } else {
                    $('#cost_samples').val(total)
                }
            } else {
                $('#ispacket').val("false")
                $('#cost_samples').val(total)
            }


        });


        let arrayContainsArray = (a_array, b_array) => {


            for (let i = 0; i < b_array.length; i++) {
                if (a_array.includes(b_array[i])) {
                    let index = a_array.indexOf(b_array[i])
                    a_array.splice(index, 1)
                } else {
                    return false
                }
            }
            return true
        }





        // $(function() {
        //     $('#datetimepicker1').datetimepicker();
        // });

        // $('#input').datetimepicker({ footer: true, modal: true });


        $(".packet").css('display', 'none');

        $(".checkbox").prop("disabled", true);

        $("#is_paket").change(function() {

            $(".checkbox").prop("disabled", true);
            $(".checkbox").attr("data-toggle", "tooltip");
            $(".checkbox").attr("data-placement", "right");
            $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
            $("[data-toggle='tooltip']").tooltip();
            $('#cost_samples').val(0);
            $(".checkbox").prop("readonly", false);

            var jenis_sample_text = $("#jenis_sampel").children(":selected").text();


            if (jenis_sample_text.includes("Makanan")) {
                jenis_makanan = $('#jenis_makanan_id').val();
                var url = "{{ route('elits-sampletypes.getbaku_mutu', ['#', '%']) }}"
                url = url.replace('#', jenis_sample);
                url = url.replace('%', jenis_makanan);




                $.ajax({
                    url: url,
                    type: "GET",
                    datatype: 'json',
                    success: function(response) {
                        var results = response.data;
                        results.forEach(result => {
                            $(".checkbox-" + result.id_method).prop("disabled", false);
                            $(".checkbox-" + result.id_method).removeAttr("title")
                            $(".checkbox-" + result.id_method).removeAttr("data-toggle");
                            $(".checkbox-" + result.id_method).removeAttr("data-placement");
                            $(".checkbox-" + result.id_method).removeAttr(
                            "data-original-title");

                        })
                    },
                })

                if (this.checked) {
                    $(".packet").css('display', 'block');

                    $('#packet').select2()

                    methods_sample_type = [];
                    price_sample_type = 0;
                    $(".checkbox").prop("checked", false);
                    $('#cost_samples').val(0);


                    if (jenis_sample != null && jenis_sample != undefined) {

                        $(".checkbox").prop("checked", false);


                        jenis_makanan = $('#jenis_makanan_id').val();
                        var url = "{{ route('elits-sampletypes.getbaku_mutu', ['#', '%']) }}"
                        url = url.replace('#', jenis_sample);
                        url = url.replace('%', jenis_makanan);

                        $.ajax({
                            url: url,
                            type: "GET",
                            datatype: 'json',
                            success: function(response) {
                                var results = response.data;
                                results.forEach(result => {
                                    $(".checkbox-" + result.id_method).prop("disabled", false);
                                    $(".checkbox-" + result.id_method).removeAttr("title")
                                    $(".checkbox-" + result.id_method).removeAttr(
                                    "data-toggle");
                                    $(".checkbox-" + result.id_method).removeAttr(
                                        "data-placement");
                                    $(".checkbox-" + result.id_method).removeAttr(
                                        "data-original-title");

                                })
                            },
                        })
                        var url = "/api/packet/#/%"
                        jenis_makanan = $('#jenis_makanan_id').val();
                        url = url.replace('#', jenis_sample);
                        url = url.replace('%', jenis_makanan);

                        $.ajax({
                            url: url,
                            type: "POST",
                            datatype: 'json',
                            success: function(response) {
                                $(".is_paket").css('display', 'block');

                                // $(".packet").css('display', 'none');
                                // $("#is_paket").prop('checked', false);
                                // $('#packet').val(null).trigger("change");
                                $('#packet')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="" disabled selected >Pilih Paket</option>');
                                var results = response.results
                                results.forEach(result => {
                                    $('#packet')
                                        .append('<option value="' + result.id +
                                            '" data-extra="' + result.data_extra + '">' +
                                            result.text + '</option>');
                                })

                $("#packet").change(function() {
                  var packet = $(this).val();
                  var url = "{{ route('elits-sampletypes.getdetail_sample_type', ['#']) }}"

                  url = url.replace('#', packet);

                                    $('#ispacket').val("true")

                                    $.ajax({
                                        url: url,
                                        type: "GET",
                                        datatype: 'json',
                                        success: function(response) {


                                            methods_sample_type = response.methods;
                                            $(".checkbox").prop("checked", false);
                                            var harga = 0;
                                            response.data.forEach(data => {
                                                harga = harga + parseInt(
                                                    data[
                                                        'price_total_method'
                                                        ]);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "checked", true);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "readonly", true);
                                                // $(".checkbox-"+data['method_id']).prop("disabled", true);
                                            })

                                            if (response['price'] == 0) {
                                                $('#cost_samples').val(harga)
                                            } else {
                                                $('#cost_samples').val(response[
                                                    'price'])

                                                price_sample_type = response[
                                                    'price'];
                                            }


                                            //   var url = "{{ route('elits-packet.index') }}";
                                            //   window.location.href = url;

                                        },
                                        error: function(XMLHttpRequest, textStatus,
                                            errorThrown) {
                                            alert(XMLHttpRequest.responseJSON
                                                .message);
                                        }
                                    });

                                })
                            },
                        })

                    }

                } else {


                    $(".packet").css('display', 'none');
                    methods_sample_type = [];
                    price_sample_type = 0;
                    $(".checkbox").prop("checked", false);
                    $('#cost_samples').val(0);
                }
            } else {
                var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
                url = url.replace('#', jenis_sample);



                $.ajax({
                    url: url,
                    type: "GET",
                    datatype: 'json',
                    success: function(response) {
                        var results = response.data;
                        results.forEach(result => {
                            $(".checkbox-" + result.id_method).prop("disabled", false);
                            $(".checkbox-" + result.id_method).removeAttr("title")
                            $(".checkbox-" + result.id_method).removeAttr("data-toggle");
                            $(".checkbox-" + result.id_method).removeAttr("data-placement");
                            $(".checkbox-" + result.id_method).removeAttr(
                            "data-original-title");

                        })
                    },
                })

                if (this.checked) {
                    $(".packet").css('display', 'block');

                    $('#packet').select2()

                    methods_sample_type = [];
                    price_sample_type = 0;
                    $(".checkbox").prop("checked", false);
                    $('#cost_samples').val(0);


                    if (jenis_sample != null && jenis_sample != undefined) {

                        $(".checkbox").prop("checked", false);



            var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
            url = url.replace('#', jenis_sample);


                        $.ajax({
                            url: url,
                            type: "GET",
                            datatype: 'json',
                            success: function(response) {
                                var results = response.data;
                                results.forEach(result => {
                                    $(".checkbox-" + result.id_method).prop("disabled", false);
                                    $(".checkbox-" + result.id_method).removeAttr("title")
                                    $(".checkbox-" + result.id_method).removeAttr(
                                    "data-toggle");
                                    $(".checkbox-" + result.id_method).removeAttr(
                                        "data-placement");
                                    $(".checkbox-" + result.id_method).removeAttr(
                                        "data-original-title");

                                })
                            },
                        })
                        var url = "/api/packet/#"
                        url = url.replace('#', jenis_sample);


                        $.ajax({
                            url: url,
                            type: "POST",
                            datatype: 'json',
                            success: function(response) {
                                $(".is_paket").css('display', 'block');

                                // $(".packet").css('display', 'none');
                                // $("#is_paket").prop('checked', false);
                                // $('#packet').val(null).trigger("change");
                                $('#packet')
                                    .find('option')
                                    .remove()
                                    .end()
                                    .append('<option value="" disabled selected >Pilih Paket</option>');
                                var results = response.results
                                results.forEach(result => {
                                    $('#packet')
                                        .append('<option value="' + result.id +
                                            '" data-extra="' + result.data_extra + '">' +
                                            result.text + '</option>');
                                })

                                $("#packet").change(function() {
                                    var packet = $(this).val();
                                    var url =
                                        "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}"
                                    url = url.replace('#', packet);


                                    $('#ispacket').val("true")

                                    $.ajax({
                                        url: url,
                                        type: "GET",
                                        datatype: 'json',
                                        success: function(response) {


                                            methods_sample_type = response.methods;
                                            $(".checkbox").prop("checked", false);
                                            var harga = 0;
                                            response.data.forEach(data => {
                                                harga = harga + parseInt(
                                                    data[
                                                        'price_total_method'
                                                        ]);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "checked", true);
                                                $(".checkbox-" + data[
                                                    'method_id']).prop(
                                                    "readonly", true);
                                                // $(".checkbox-"+data['method_id']).prop("disabled", true);
                                            })

                                            if (response['price'] == 0) {
                                                $('#cost_samples').val(harga)
                                            } else {
                                                $('#cost_samples').val(response[
                                                    'price'])

                                                price_sample_type = response[
                                                    'price'];
                                            }


                                            //   var url = "{{ route('elits-packet.index') }}";
                                            //   window.location.href = url;

                                        },
                                        error: function(XMLHttpRequest, textStatus,
                                            errorThrown) {
                                            alert(XMLHttpRequest.responseJSON
                                                .message);
                                        }
                                    });

                                })
                            },
                        })

                    }

                } else {


                    $(".packet").css('display', 'none');
                    methods_sample_type = [];
                    price_sample_type = 0;
                    $(".checkbox").prop("checked", false);
                    $('#cost_samples').val(0);
                }
            }

        })

        $("#jenis_sampel").change(function() {

            jenis_sample = $(this).val();

            var jenis_sample_text = $(this).children(":selected").text();


      if(jenis_sample_text.includes("Makanan")){
          $(".jenis_makanan_id").css("display","block")

          $(document).ready(function() {

            $.fn.select2.defaults.set("theme", "classic");
            $('#jenis_makanan_id').select2();
            console.log("Jenis: "+jenis_makanan)
            $('#jenis_makanan_id').change(function() {

              jenis_makanan=$(this).val();


              methods_sample_type = [];
              price_sample_type = 0;
              $(".checkbox").prop("checked", false);
              $(".checkbox").prop("disabled", true);
              $(".checkbox").attr("data-toggle", "tooltip");
              $(".checkbox").attr("data-placement", "right");
              $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
              $("[data-toggle='tooltip']").tooltip();
              $('#cost_samples').val(0);

              $(".checkbox").prop("readonly", false);

              // if (jenis_sample != undefined) {

                jenis_makanan=$(this).val();
                var url = "{{ route('elits-sampletypes.getbaku_mutu', ['#','%']) }}"
                console.log("Jenis2: "+jenis_makanan)
                console.log("Jenis2: "+url)

                url = url.replace('#', jenis_sample);
                url = url.replace('%', jenis_makanan);


                $('#ispacket').val("true")

                $.ajax({
                  url: url,
                  type: "GET",
                  datatype: 'json',
                  success: function(response) {
                    console.log(response)
                    var results = response.data;
                    results.forEach(result => {
                      $(".checkbox-" + result.id_method).prop("disabled", false);
                      $(".checkbox-" + result.id_method).removeAttr("title")
                      $(".checkbox-" + result.id_method).removeAttr("data-toggle");
                      $(".checkbox-" + result.id_method).removeAttr("data-placement");
                      $(".checkbox-" + result.id_method).removeAttr("data-original-title");

                    })
                  },
                })
                jenis_makanan=$('#jenis_makanan_id').val();
                var url = "/api/packet/#/%"
                url = url.replace('#', jenis_sample);
                url = url.replace('%', jenis_makanan);



                $.ajax({
                  url: url,
                  type: "POST",
                  datatype: 'json',
                  success: function(response) {
                    $(".is_paket").css('display', 'block');
                    $(".packet").css('display', 'none');
                    $("#is_paket").prop('checked', false);
                    // $('#packet').val(null).trigger("change");
                    $('#packet')
                      .find('option')
                      .remove()
                      .end()
                      .append('<option value="" disabled selected >Pilih Paket</option>');
                    var results = response.results
                    results.forEach(result => {
                      $('#packet')
                        .append('<option value="' + result.id + '" data-extra="' + result.data_extra + '">' + result
                          .text + '</option>');
                    })

                    $("#packet").change(function() {
                      var packet = $(this).val();
                      var url = "{{ route('elits-sampletypes.getdetail_sample_type', ['#']) }}"
                      url = url.replace('#', packet);
                      $('#ispacket').val("true")

                      $.ajax({
                        url: url,
                        type: "GET",
                        datatype: 'json',
                        success: function(response) {


                          methods_sample_type = response.methods;
                          $(".checkbox").prop("checked", false);
                          var harga = 0;
                          response.data.forEach(data => {
                            harga = harga + parseInt(data['price_total_method']);
                            $(".checkbox-" + data['method_id']).prop("checked", true);
                            $(".checkbox-" + data['method_id']).prop("readonly", true);
                          })

                          if (response['price'] == 0) {
                            $('#cost_samples').val(harga)
                          } else {
                            $('#cost_samples').val(response['price'])

                            price_sample_type = response['price'];
                          }


                          //   var url = "{{ route('elits-packet.index') }}";
                          //   window.location.href = url;

                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                          alert(XMLHttpRequest.responseJSON.message);
                        }
                      });

                    })
                  },
                })

              // }
            })

          })
      }else{
          $(".jenis_makanan_id").css("display","none")
          methods_sample_type = [];
          price_sample_type = 0;
          $(".checkbox").prop("checked", false);
          $(".checkbox").prop("disabled", true);
          $(".checkbox").attr("data-toggle", "tooltip");
          $(".checkbox").attr("data-placement", "right");
          $(".checkbox").attr("data-original-title", "Data Baku Mutu Belum di input");
          $("[data-toggle='tooltip']").tooltip();
          $('#cost_samples').val(0);

          $(".checkbox").prop("readonly", false);

          if (jenis_sample != undefined) {

            var url = "{{ route('elits-sampletypes.getbaku_mutu', '#') }}"
            url = url.replace('#', jenis_sample);


            $('#ispacket').val("true")

            $.ajax({
              url: url,
              type: "GET",
              datatype: 'json',
              success: function(response) {
                console.log(response)
                var results = response.data;
                results.forEach(result => {
                  $(".checkbox-" + result.id_method).prop("disabled", false);
                  $(".checkbox-" + result.id_method).removeAttr("title")
                  $(".checkbox-" + result.id_method).removeAttr("data-toggle");
                  $(".checkbox-" + result.id_method).removeAttr("data-placement");
                  $(".checkbox-" + result.id_method).removeAttr("data-original-title");

                })
              },
            })

            var url = "/api/packet/#"
            url = url.replace('#', jenis_sample);


            $.ajax({
              url: url,
              type: "POST",
              datatype: 'json',
              success: function(response) {
                $(".is_paket").css('display', 'block');
                $(".packet").css('display', 'none');
                $("#is_paket").prop('checked', false);
                // $('#packet').val(null).trigger("change");
                $('#packet')
                  .find('option')
                  .remove()
                  .end()
                  .append('<option value="" disabled selected >Pilih Paket</option>');
                var results = response.results
                results.forEach(result => {
                  $('#packet')
                    .append('<option value="' + result.id + '" data-extra="' + result.data_extra + '">' + result
                      .text + '</option>');
                })

                $("#packet").change(function() {
                  var packet = $(this).val();
                  var url = "{{ route('elits-sampletypes.getdetail_sample_type', '#') }}"
                  url = url.replace('#', packet);


                  $('#ispacket').val("true")

                  $.ajax({
                    url: url,
                    type: "GET",
                    datatype: 'json',
                    success: function(response) {


                      methods_sample_type = response.methods;
                      $(".checkbox").prop("checked", false);
                      var harga = 0;
                      response.data.forEach(data => {
                        harga = harga + parseInt(data['price_total_method']);
                        $(".checkbox-" + data['method_id']).prop("checked", true);
                        $(".checkbox-" + data['method_id']).prop("readonly", true);
                      })

                      if (response['price'] == 0) {
                        $('#cost_samples').val(harga)
                      } else {
                        $('#cost_samples').val(response['price'])

                        price_sample_type = response['price'];
                      }


                      //   var url = "{{ route('elits-packet.index') }}";
                      //   window.location.href = url;

                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                      alert(XMLHttpRequest.responseJSON.message);
                    }
                  });

                })
              },
            })

          }
      }


        });





        $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");
      $('#jenis_sampel').select2();


    })



        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('.datepicker').datepicker('update', new Date());

        $('.datelab_samples').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('.datelab_samples').datepicker('update', new Date());
        $('.datesampling_samples').datepicker('update', new Date());


        function CheckWadah(val) {
            var element = document.getElementById('wadah_samples_others');

            if (val == '0')
                element.style.display = 'block';
            else
                element.style.display = 'none';
        }

        $(document).ready(function() {

            $.fn.select2.defaults.set("theme", "classic");
            $('#unitAttributes').select2({
                placeholder: "Pilih Unit",
                allowClear: true
            });

            $('.js-unit-basic-multiple').select2({
                placeholder: "Pilih Unit",
                allowClear: true,
                ajax: {
                    url: "{{ url('/api/unit/') }}",
                    method: "post",
                    dataType: 'json',
                    params: { // extra parameters that will be passed to ajax
                        contentType: "application/json;",
                    },
                    data: function(term) {
                        return {
                            term: term.term || '',
                            page: term.page || 1
                        };
                    },
                    cache: true
                }
            });
            var element = document.getElementById('wadah_samples_others');

            console.log($('input[type=radio][name=wadah]:checked').val())
            if ($('input[type=radio][name=wadah]:checked').val() == '0') {

                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }

            $('input[type=radio][name=wadah]').change(function() {

                if (this.value == '0') {
                    element.style.display = 'block';
                } else {
                    element.style.display = 'none';
                }

            });

            var element2 = document.getElementById('pengawet_others');


            $('input[type=radio][name=pengawet]').change(function() {

                if (this.value == '0') {
                    element2.style.display = 'block';
                } else {
                    element2.style.display = 'none';
                }

            });

        });
    </script>
@endsection
