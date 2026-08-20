@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
@endsection


@section('content')
  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/gijgo.min.js')}}" type="text/javascript"></script>
  <link href="{{asset('assets/admin/cdn-local/css/gijgo.min.css')}}" rel="stylesheet" type="text/css" />
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCvkw3POs-1ZAeDNh83LazabKECKU8i024&libraries=places"></script>


  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                    Management</a></li>
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
      <h4>Edit Permohonan Uji Klinik
      </h4>

    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form action="{{ route('elits-permohonan-uji-klinik.update', $item->id_permohonan_uji_klinik) }}" method="POST"
          enctype="multipart/form-data" id="form">

          @csrf
          @method('PUT')

          <div class="form-group">
            <label for="code_register"> No. REGISTER</label>
            <div class="input-group date">
              <input type="text" class="form-control" readonly name="noregister_permohonan_uji_klinik"
                id="noregister_permohonan_uji_klinik" placeholder="No. REGISTER"
                value="{{ $item->noregister_permohonan_uji_klinik }}">
            </div>
          </div>

          <div class="form-group">
            <label for="patient_number"> No. PASIEN</label>
            <div class="input-group date">
              <input type="text" class="form-control" readonly name="nopasien_permohonan_uji_klinik"
                id="nopasien_permohonan_uji_klinik" placeholder="No. PASIEN"
                value="{{ $item->nopasien_permohonan_uji_klinik }}">
            </div>
          </div>

          <div class="form-group table-sample">
            <label for="date_get_sample">TGL.REGISTER</label>
            <div class="input-group date">
              <input type="text" class="form-control date_get_sample datepicker"
                name="tglregister_permohonan_uji_klinik" id="date_get_sample" placeholder="Tanggal Register"
                data-date-format="dd/mm/yyyy" value="{{ $item->tglregister_permohonan_uji_klinik }}" required>
              <div class="input-group-append">
                <span class="input-group-text">
                  <i class="fas fa-calendar-alt"></i>
                </span>
              </div>
            </div>
          </div>
          <script>
            var date_register = "{{ $item->tglregister_permohonan_uji_klinik }}"
          </script>

          <div class="form-group">
            <label for="patient_name">NIK PASIEN</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="nikpasien_permohonan_uji_klinik"
                id="nikpasien_permohonan_uji_klinik" placeholder="Masukkan NIK Pasien"
                value="{{ $item->nikpasien_permohonan_uji_klinik ?? old('nikpasien_permohonan_uji_klinik') }}">
            </div>
          </div>

          <div class="form-group">
            <label for="namapasien_permohonan_uji_klinik">NAMA PASIEN</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="namapasien_permohonan_uji_klinik"
                id="namapasien_permohonan_uji_klinik" placeholder="Masukkan Nama Pasien"
                value="{{ $item->namapasien_permohonan_uji_klinik ?? old('namapasien_permohonan_uji_klinik') }}">
            </div>
          </div>

          <div class="form-group">
            <label for="gender_permohonan_uji_klinik">JENIS KELAMIN</label>
            <div class="input-group date">
              <select name="gender_permohonan_uji_klinik" id="gender_permohonan_uji_klinik" class="form-control">
                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                <option value="L" {{ $item->gender_permohonan_uji_klinik == 'L' ? 'selected' : '' }}>Laki - Laki
                </option>
                <option value="P" {{ $item->gender_permohonan_uji_klinik == 'P' ? 'selected' : '' }}>Perempuan
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="datelab_samples">TANGGAL LAHIR</label>
            <div class="input-group ">
              <input type="text" class="form-control date_birth" name="tgllahir_permohonan_uji_klinik"
                id="tgllahir_permohonan_uji_klinik" placeholder="dd-mm-yyyy" required
                value="{{ $item->tgllahir_permohonan_uji_klinik }}">
            </div>

            <script>
              var date_birth = "{{ $item->tgllahir_permohonan_uji_klinik }}"
            </script>
          </div>

          <div class="form-group">
            <label for="datelab_samples">UMUR</label>

            <div class="row">
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" readonly name="umurtahun_permohonan_uji_klinik"
                    id="umurtahun_permohonan_uji_klinik" placeholder="Umur" required
                    value="{{ $item->umurtahun_permohonan_uji_klinik }}">
                  <div class="input-group-append">
                    <span class="input-group-text">
                      tahun
                    </span>
                  </div>
                </div>
              </div>
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" readonly name="umurbulan_permohonan_uji_klinik"
                    id="umurbulan_permohonan_uji_klinik" placeholder="Umur" required
                    value="{{ $item->umurbulan_permohonan_uji_klinik ?? old('umurbulan_permohonan_uji_klinik') }}">
                  <div class="input-group-append">
                    <span class="input-group-text">
                      Bulan
                    </span>
                  </div>
                </div>
              </div>
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" name="umurhari_permohonan_uji_klinik"
                    id="umurhari_permohonan_uji_klinik" readonly placeholder="Umur" required
                    value="{{ $item->umurhari_permohonan_uji_klinik ?? old('umurhari_permohonan_uji_klinik') }}">
                  <div class="input-group-append">
                    <span class="input-group-text">
                      Hari
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">ALAMAT</label>
            <textarea class="form-control" name="alamat_permohonan_uji_klinik" id="alamat_permohonan_uji_klinik" rows="3">{{ $item->alamat_permohonan_uji_klinik ?? old('alamat_permohonan_uji_klinik') }}</textarea>
          </div>

          <div class="form-group">
            <label for="patient_name">NO TELP/HP</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="phone_permohonan_uji_klinik"
                id="phone_permohonan_uji_klinik" placeholder="Masukkan NO TELP/HP"
                value="{{ $item->phone_permohonan_uji_klinik ?? old('phone_permohonan_uji_klinik') }}">
            </div>
          </div>

          <div class="form-group">
            <label for="tglpengambilan_permohonan_uji_klinik">TANGGAL PENGAMBILAN</label>

            <input id="tglpengambilan_permohonan_uji_klinik" class="form-control"
              name="tglpengambilan_permohonan_uji_klinik" value="{{ $tgl_pengambilan }}"
              placeholder="--/--/--- --:--" />

            <script>
              $('#tglpengambilan_permohonan_uji_klinik').datetimepicker({
                format: 'dd/mm/yyyy HH:MM',
                footer: true,
                modal: true
              });
            </script>

          </div>

          <div class="form-group">
            <label for="patient_name">NAMA PENGIRIM</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="namapengirim_permohonan_uji_klinik"
                id="namapengirim_permohonan_uji_klinik"
                value="{{ $item->namapengirim_permohonan_uji_klinik ?? old('namapengirim_permohonan_uji_klinik') }}"
                placeholder="Masukkan nama pengirim">
            </div>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">DIAGNOSA</label>
            <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik"
              rows="3">{{ $item->diagnosa_permohonan_uji_klinik }}</textarea>
          </div>


        </form>
        <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
        <button type="button" class="btn btn-light"
          onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
      </li>
    </ul>
  </div>
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>

  <script>
    (function(e) {
      var t = {
        nodiff: "",
        year: "year",
        years: "years",
        month: "month",
        months: "months",
        day: "day",
        days: "days",
        hour: "hour",
        hours: "hours",
        minute: "minute",
        minutes: "minutes",
        second: "second",
        seconds: "seconds",
        delimiter: " "
      };
      e.fn.preciseDiff = function(t) {
        return e.preciseDiff(this, t)
      };
      e.preciseDiff = function(n, r) {
        function d(e, n) {
          return e + " " + t[n + (e === 1 ? "" : "s")]
        }
        var i = e(n),
          s = e(r);
        if (i.isSame(s)) {
          return t.nodiff
        }
        if (i.isAfter(s)) {
          var o = i;
          i = s;
          s = o
        }
        var u = s.year() - i.year();
        var a = s.month() - i.month();
        var f = s.date() - i.date();
        var l = s.hour() - i.hour();
        var c = s.minute() - i.minute();
        var h = s.second() - i.second();
        if (h < 0) {
          h = 60 + h;
          c--
        }
        if (c < 0) {
          c = 60 + c;
          l--
        }
        if (l < 0) {
          l = 24 + l;
          f--
        }
        if (f < 0) {
          var p = e(s.year() + "-" + (s.month() + 1), "YYYY-MM").subtract("months", 1).daysInMonth();
          if (p < i.date()) {
            f = p + f + (i.date() - p)
          } else {
            f = p + f
          }
          a--
        }
        if (a < 0) {
          a = 12 + a;
          u--
        }
        var v = [];
        if (u) {
          v.push(u)
        } else {
          v.push(0)
        }
        if (a) {
          v.push(a)
        } else {
          v.push(0)
        }
        if (f) {
          v.push(f)
        } else {
          v.push(0)
        }
        // if(l){v.push(d(l,"hour"))}
        // if(c){v.push(d(c,"minute"))}
        // if(h){v.push(d(h,"second"))}

        return v
      }
    })(moment)

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy'
    });

    $(".date_birth").datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.date_birth').datepicker('update', moment(date_birth, "YYYYY-MM-DD").toDate());

    $('.datepicker').datepicker('update', moment(date_register, "YYYYY-MM-DD").toDate());


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
    });


    $(".date_birth").change(function() {
      var datevalue = $(this).val()
      getAge(datevalue);
    });

    function getAge(dateString) {
      var today = moment().toDate();
      var birthDate = moment(dateString, "DD/MM/YYYY").toDate();
      // console.log(birthDate)
      // var age = today.getFullYear() - birthDate.getFullYear();
      // var m = today.getMonth() - birthDate.getMonth();
      // var d = age;
      // // var m = today - birthDate

      // var diff = Math.floor(today.getTime() - birthDate.getTime());
      // var day = 1000* 60 * 60 * 24;
      // var days = Math.floor(diff/day);
      // var months = Math.floor(days/31);
      // var years = Math.floor(months/12);

      // months=months-years*12

      // days=days-(years*365)-(months*31)

      // var starts = moment(birthDate);
      // var ends   = moment();

      // // var duration = moment.duration(ends.diff(starts));

      // // with ###moment precise date range plugin###
      // // it will tell you the difference in human terms

      var diff = moment.preciseDiff(today, birthDate, true);

      // console.log(duration)
      // var days = Math.floor((diff - years * 365*60*60*60*60*24 - months*30*60*60*60*60*24)/ (60*60*60*60*24));


      $("#umurtahun_permohonan_uji_klinik").val(diff[0])
      $("#umurbulan_permohonan_uji_klinik").val(diff[1])
      $("#umurhari_permohonan_uji_klinik").val(diff[2])
      // console.log(m)
      // console.log(d)

      // if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate()))
      // {
      //     age--;
      // }

      // return diff;
    }

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
