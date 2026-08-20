@extends('masterweb::template.admin.layout')
@section('title')
  Permintaan Pemeriksaan Klinik
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji') }}">Permintaan Pemeriksaan Klinik
                    Management</a></li>
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
      <h4>Tambah Permintaan Pemeriksaan Klinik
      </h4>

    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form action="{{ route('elits-permohonan-uji.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label for="code_register"> No. REGISTER :</label>
            <div class="input-group date">
              <input type="text" class="form-control" readonly name="code_register" id="code_register"
                placeholder="No. REGISTER" value="{{ $code }}">
            </div>
          </div>

          <div class="form-group">
            <label for="patient_number"> No. PASIEN :</label>
            <div class="input-group date">
              <input type="text" class="form-control" readonly name="patient_number" id="patient_number"
                placeholder="No. PASIEN" value="{{ $code }}">
            </div>
          </div>

          <div class="form-group table-sample">
            <label for="date_get_sample">TGL.REGISTER</label>
            <div class="input-group date">
              <input type="text" class="form-control date_get_sample datepicker" name="date" id="date_get_sample"
                placeholder="Tanggal Register" data-date-format="dd/mm/yyyy" required>
              <div class="input-group-append">
                <span class="input-group-text">
                  <i class="fas fa-calendar-alt"></i>
                </span>
              </div>
            </div>
          </div>


          <div class="form-group">
            <label for="patient_name">NIK PASIEN:</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="patient_nik" id="patient_nik"
                placeholder="Masukkan NIK Pasien">
            </div>
          </div>

          <div class="form-group">
            <label for="patient_name">NAMA PASIEN:</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="patient_name" id="patient_name"
                placeholder="Masukkan Nama Pasien">
            </div>
          </div>

          <div class="form-group">
            <label for="patient_gender">Jenis Kelamin:</label>
            <div class="input-group date">
              <select name="patient_gender" id="patient_gender" class="form-control">
                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                <option value="pria">Laki - Laki</option>
                <option value="perempuan">Perempuan</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="datelab_samples">TANGGAL LAHIR</label>
            <div class="input-group ">
              <input type="text" class="form-control date_birth" name="date_birth" id="date_birth"
                placeholder="dd-mm-yyyy" value="" required>
            </div>
          </div>

          <div class="form-group">
            <label for="datelab_samples">UMUR</label>

            <div class="row">
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" readonly name="age_year" id="age_year" placeholder="Umur"
                    required>
                  <div class="input-group-append">
                    <span class="input-group-text">
                      tahun
                    </span>
                  </div>
                </div>
              </div>
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" readonly name="age_mount" id="age_mount"
                    placeholder="Umur" required>
                  <div class="input-group-append">
                    <span class="input-group-text">
                      bulan
                    </span>
                  </div>
                </div>
              </div>
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" readonly name="age_day" id="age_day"
                    placeholder="Umur" required>
                  <div class="input-group-append">
                    <span class="input-group-text">
                      hari
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- <div class="form-group">
                              <label for="pengirim_sample"> Nama Pengirim Sampel:</label>
                              <div class="input-group date">
                                  <input type="text" class="form-control" name="pengirim_sample" id="pengirim_sample" placeholder="Masukkan Nama Pengirim Sampel">

                              </div>
                          </div> -->
          <div class="form-group">
            <label for="exampleFormControlTextarea1">Alamat</label>
            <textarea class="form-control" name="address_patient" id="address_patient" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label for="patient_name">NO TELP/HP:</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="patient_phone" id="patient_phone"
                placeholder="Masukkan NO TELP/HP">
            </div>
          </div>

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
              $('#datesampling_samples').datetimepicker({
                format: 'dd/mm/yyyy HH:MM',
                footer: true,
                modal: true
              });
            </script>

          </div>

          <div class="form-group">
            <label for="patient_name">NAMA PENGIRIM :</label>
            <div class="input-group date">
              <input type="text" class="form-control" name="patient_pengirim" id="patient_pengirim"
                placeholder="Masukkan NAMA PENGIRIM">
            </div>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">DIAGNOSA :</label>
            <textarea class="form-control" name="diagnosa" id="diagnosa" rows="3"></textarea>
          </div>


          <button type="submit" id="submitAll" class="btn btn-primary mr-2">Simpan</button>
          <button type="button" class="btn btn-light" onclick="window.history.back()">Kembali</button>
        </form>

      </li>


    </ul>

  </div>
@endsection

@section('scripts')
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

    $(".date_birth").datepicker({
      format: 'dd/mm/yyyy'
    });

    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.datepicker').datepicker('update', new Date());
    $('.datelab_samples').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.datelab_samples').datepicker('update', new Date());


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
      $("#age").val(getAge(datevalue));
    });

    function getAge(dateString) {
      var today = moment().toDate();
      var birthDate = moment(dateString, "dd/mm/yyyy").toDate();
      console.log(dateString)
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


      $("#age_year").val(diff[0])
      $("#age_mount").val(diff[1])
      $("#age_day").val(diff[2])
      // console.log(m)
      // console.log(d)

      // if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate()))
      // {
      //     age--;
      // }

      // return diff;
    }
  </script>
@endsection
