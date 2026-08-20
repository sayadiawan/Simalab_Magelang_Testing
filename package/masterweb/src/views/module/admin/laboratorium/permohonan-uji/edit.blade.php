@extends('masterweb::template.admin.layout')
@section('title')
    Permohonan Uji Management
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
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji') }}">Permohonan Uji
                                        Management</a>
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
        <div class="card-header">
            <h4>Ubah Permohonan Uji</h4>

        </div>

        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <form class="forms-sample" id="form"
                    action="{{ route('elits-permohonan-uji.update', $item_permohonan_uji->id_permohonan_uji) }}"
                    method="POST">

                    @csrf
                    @method('PUT')

                    <input type="hidden" name="code_permohonan_uji" id="code_permohonan_uji"
                        value="{{ $item_permohonan_uji->code_permohonan_uji }}">

                    <div class="form-group ">
                        <label for="code_permohonan_uji">Nama pelanggan/perusahaan</label>

                        <select id="customerAttributes" name="customer"
                            class="js-customer-basic-multiple js-states form-control" style="width: 100%">
                            <option value="{{ $item_permohonan_uji->customer_id ?? '' }}" {!! $item_permohonan_uji->customer_id != null ? 'selected' : '' !!}>
                                {{ $item_permohonan_uji->customer->name_customer ?? '' }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="card old_customer">

                            <div class="card-body">

                                <div class="form-group ">
                                    <label for="name_customer"> Nama Pelanggan :</label>
                                    <div class="input-group date">
                                        <span id="name_customer">
                                            <h5>{{ $item_permohonan_uji->customer->name_customer }}</h5>
                                        </span>

                                        {{-- <input type="text" class="form-control" name="new_customer" id="new_customer"
                    placeholder="Masukkan Nama Pelanggan Baru" value=""> --}}
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="address_customer"> Alamat Pelanggan :</label>
                                    <div class="input-group date">
                                        <span id="address_customer">
                                            <h5>{{ $item_permohonan_uji->customer->address_customer }}</h5>
                                        </span>

                                        {{-- <input type="text" class="form-control" name="new_customer" id="new_customer"
                    placeholder="Masukkan Nama Pelanggan Baru" value=""> --}}
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label for="kecamatan_customer"> Kecamatan Pelanggan :</label>
                                    <div class="input-group date">
                                        <span id="kecamatan_customer">
                                            <h5>{{ $item_permohonan_uji->customer->kecamatan_customer }}</h5>
                                        </span>

                                        {{-- <input type="text" class="form-control" name="new_customer" id="new_customer"
                    placeholder="Masukkan Nama Pelanggan Baru" value=""> --}}
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label for="email_customer"> Email Pelanggan :</label>
                                    <div class="input-group date">
                                        <span id="email_customer">
                                            <h5>{{ $item_permohonan_uji->customer->email_customer }}</h5>
                                        </span>

                                        {{-- <input type="text" class="form-control" name="new_customer" id="new_customer"
                    placeholder="Masukkan Nama Pelanggan Baru" value=""> --}}
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label for="cp_customer"> Contact Person Pelanggan :</label>
                                    <div class="input-group date">
                                        <span id="cp_customer">
                                            <h5>{{ $item_permohonan_uji->customer->cp_customer }}</h5>
                                        </span>

                                        {{-- <input type="text" class="form-control" name="new_customer" id="new_customer"
                    placeholder="Masukkan Nama Pelanggan Baru" value=""> --}}
                                    </div>
                                </div>

{{--                                <div class="form-group">--}}
{{--                                  <label for="address_customer">Alamat Hasil :</label>--}}
{{--                                  <textarea class="form-control" id="alamat_customer_edited" name="alamat_customer_edited" placeholder="Isikan Alamat">{{ $item_permohonan_uji->address_customer_edited ?? $item_permohonan_uji->customer->address_customer }}</textarea>--}}
{{--                                </div>--}}


                                {{-- <div class="form-group">

                <label for="address_customer">Alamat</label>
                <textarea class="form-control" id="new_address_customer" name="new_address_customer"
                  placeholder="Isikan Alamat"></textarea>
              </div>

              <div class="form-group">
                <label for="kecamatan">Kecamatan</label>
                <select name="new_kecamatan" class="form-control" id="new_kecamatan" onchange="CheckKecamatan(this)">
                  <option value="" selected disabled>Pilih Kecamatan</option>
                  <option value="Colomadu">Colomadu</option>
                  <option value="Gondangrejo">Gondangrejo</option>
                  <option value="Jaten">Jaten</option>
                  <option value="Jatipuro">Jatipuro</option>
                  <option value="Jatiyoso">Jatiyoso</option>
                  <option value="Jenawi">Jenawi</option>
                  <option value="Jumapolo">Jumapolo</option>
                  <option value="Jumantono">Jumantono</option>
                  <option value="BOYOLALI">BOYOLALI</option>
                  <option value="Karangpandan">Karangpandan</option>
                  <option value="Kebakkramat">Kebakkramat</option>
                  <option value="Kerjo">Kerjo</option>
                  <option value="Matesih">Matesih</option>
                  <option value="Ngargoyoso">Ngargoyoso</option>
                  <option value="Mojogedang">Mojogedang</option>
                  <option value="Tasikmadu">Tasikmadu</option>
                  <option value="Ngargoyoso">Ngargoyoso</option>
                  <option value="Tawangmangu">Tawangmangu</option>
                  <option value="0">Lainnya</option>
                </select>
              </div>

              <div class="form-group">
                <input type="text" class="form-control mt-10" id="new_kecamatan_other" style='display:none;'
                  id="new_kecamatan_other" name="new_kecamatan_other" placeholder="Isikan Kecamatan Lain">
              </div>

              <div class="form-group">
                <label for="new_email_customer">Email</label>
                <input type="text" class="form-control" id="new_email_customer" name="new_email_customer"
                  placeholder="Isikan Email">
              </div>

              <div class="form-group">
                <label for="new_category_customer">Kategori</label>
                <select name="new_category_customer" class="form-control" id="new_category_customer">
                  <option value="">Pilih Category</option>
                  @foreach ($categories as $category)
                  <option value="{{ $category->id_industry }}">{{ $category->name_industry }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="new_cp_customer">Contact Person</label>
                <textarea class="form-control" id="new_cp_customer" name="new_cp_customer"
                  placeholder="Isikan Contact Person"></textarea>
              </div> --}}

                            </div>
                        </div>
                    </div>

                    <div class="form-group table-sample">
                        <label for="date_get_sample">Tanggal Masuk Pengajuan</label>
                        <div class="input-group date">
                            <input type="text" class="form-control date_get_sample datepicker date_get_sample"
                                name="date" id="date_get_sample" placeholder="Isikan Tanggal"
                                data-date-format="dd/mm/yyyy"
                                value="{{ Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $item_permohonan_uji->date_permohonan_uji)->format('d/m/Y') }}"
                                required>

                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="form-group">
          <label for="pengirim_sample">Nama Pengirim Sampel/ Pengambil Sampel (Khusus PUDAM):</label>
          <div class="input-group date">
            <input type="text" class="form-control" name="pengirim_sample" id="pengirim_sample"
              placeholder="Masukkan Nama Pengirim Sampel/ Pengambil Sampel (Khusus PUDAM)"
              value="{{ $item_permohonan_uji->pengirim_sample }}">

          </div>
        </div> --}}

                    <div class="form-group">
                        <label for="pengirim_sample"> Nama Pengirim Sampel/ Pengambil Sampel (Muncul di Nota):</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="pengirim_sample" id="pengirim_sample"
                                placeholder="Masukkan/ Nama Pengirim Sampel/ Pengambil Sampel (Muncul di Nota)"
                                value="{{ $item_permohonan_uji->pengirim_sample }}">

                        </div>
                    </div>

                    {{-- <div class="form-group">
                        <label for="location_permohonan_uji"> Lokasi Umum Sampel (Khusus Mikro dan Khusus MikMan
                            (Kimia)):</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="location_permohonan_uji"
                                id="location_permohonan_uji" placeholder="Lokasi Umum Sampel (Khusus Mikro)"
                                value="{{ $item_permohonan_uji->location_permohonan_uji }}">

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pdam_pengirim_sample"> Pengambil Sampel (Khusus Mikro, Khusus MikMan (Kimia) dan Kimia
                            PUDAM):</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="pdam_pengirim_sample" id="pdam_pengirim_sample"
                                placeholder="Pengambil Sampel (Khusus PUDAM)"
                                value="{{ $item_permohonan_uji->pdam_pengirim_sample }}">

                        </div>
                    </div> --}}

                    <div class="form-group">
                        <label for="name_sampling"> Petugas Sampling (Untuk Mikro):</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" name="name_sampling" id="name_sampling"
                                placeholder="Masukkan Petugas Sampling" value="{{ $item_permohonan_uji->name_sampling }}">

                        </div>
                    </div>


                    <div class="form-group">
                        <label for="petugas_penerima"> Petugas Penerima Sampel (Muncul di Permintaan Periksaan):</label>
                        <div class="input-group date">
                            <input type="text" class="form-control" value="{{ $item_permohonan_uji->petugas_penerima }}"
                                name="petugas_penerima" id="petugas_penerima"
                                placeholder="Masukkan Petugas Penerima Sampel (Muncul di Permintaan Periksaan)">

                        </div>
                    </div>

                    <div class="form-group">
                        <label for="exampleFormControlTextarea1">Catatan</label>
                        <textarea class="form-control" name="catatan" id="exampleFormControlTextarea1" rows="3">{{ $item_permohonan_uji->catatan }}</textarea>
                    </div>


                </form>
                <button type="submit" id="submitAll" class="btn btn-primary mr-2">Simpan</button>
                <a href="/elits-permohonan-uji" class="btn btn-light">Kembali</a>
            </li>
        </ul>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

    <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
        integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous">
    </script>

    <script>
        $('.not_found').click(function() {
            $('.new_customer').css('display', 'block');
            $('.cancel_customer_new').click(function() {
                $("#customerAttributes").prop("disabled", false);
                $('.new_customer').css('display', 'none');
            })
            $("#customerAttributes").prop("disabled", true);
        });


        $('.date_get_sample').datepicker({
            format: 'dd/mm/yyyy'
        }).on('change', function() {
            var jsDate = $('.date_get_sample').datepicker('getDate');

            // console.log(jsDate)


            var curr_date = jsDate.getDate();
            var curr_month = jsDate.getMonth() + 1; //Months are zero based
            var curr_year = jsDate.getFullYear();


            var date2 = new Date(curr_year + "/" + curr_month + "/" + curr_date);
            date2.setDate(date2.getDate() + 14);
            $('.datelab_samples').datepicker('update', date2);
        });

        $('.datelab_samples').datepicker({
            format: 'dd/mm/yyyy'
        })

        var date = new Date();
        date.setDate(date.getDate() + 14);
        $('.datelab_samples').datepicker('update', date);

        $.fn.select2.defaults.set("theme", "classic");

        $('.js-customer-basic-multiple').select2({
            placeholder: "Pilih Customer",
            allowClear: true,
            ajax: {
                url: "{{ url('/api/customer/') }}",
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
        }).on('change', function(e) {
            var getID = $(this).select2('data');
            if (getID[0] != "" && getID[0] != undefined) {

                // console.log(getID[0]['id']);
                var id = getID[0]['id'];

                var url = "{{ route('customer.detail', '#') }}"
                url = url.replace("#", id);
                // console.log(id)
                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        $("#name_customer h5").text(response.name_customer);
                        $("#address_customer h5").text(response.address_customer);
                        $("#kecamatan_customer h5").text(response.kecamatan_customer);
                        $("#email_customer h5").text(response.email_customer);
                        $("#category_customer h5").text(response.name_industry);
                        $("#cp_customer h5").text(response.cp_customer);
                        $('.old_customer').css('display', 'block');
                        $('.new_customer').css('display', 'none');
                    }
                })
            } else {
                $('.old_customer').css('display', 'none');
            }
        });

        $(document).ready(function() {
            $('#submitAll').on('click', function() {
                $('#form').ajaxSubmit({
                    success: function(response) {
                        if (response.status == true) {
                            swal({
                                    title: "Success!",
                                    text: response.pesan,
                                    icon: "success"
                                })
                                .then(function() {
                                    document.location = '/elits-permohonan-uji';
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
    </script>
@endsection
