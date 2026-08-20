@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
@endsection


@section('content')
  {{--  --}}
  {{--  --}}

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
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
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
      <h4>Tambah Permohonan Uji Klinik
      </h4>
    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form action="{{ route('elits-permohonan-uji-klinik.store') }}" method="POST" enctype="multipart/form-data"
          id="form">

          @csrf

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
          <input type="hidden" class="form-control" name="nourut_permohonan_uji_klinik" id="nourut_permohonan_uji_klinik"
            value="{{ $set_count }}" readonly>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="code_register"> No. REGISTER</label>
                <div class="input-group date">
                  <input type="text" class="form-control" readonly name="noregister_permohonan_uji_klinik"
                    id="noregister_permohonan_uji_klinik" placeholder="No. REGISTER" value="{{ $code }}">
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="patient_number"> No. PASIEN</label>
                <div class="input-group date">
                  <input type="text" class="form-control" readonly name="nopasien_permohonan_uji_klinik"
                    id="nopasien_permohonan_uji_klinik" placeholder="No. PASIEN" value="{{ $code }}">
                </div>
              </div>
            </div>
          </div>

          <div class="form-group table-sample">
            <label for="date_get_sample">TGL.REGISTER<span style="color: red">*</span></label>
            <div class="input-group date">
              <input type="text" class="form-control date_get_sample datepicker"
                name="tglregister_permohonan_uji_klinik" id="date_get_sample" placeholder="dd/mm/yyyy"
                data-date-format="dd/mm/yyyy" readonly>

              <div class="input-group-append">
                <span class="input-group-text">
                  <i class="fas fa-calendar-alt datepicker"></i>
                </span>
              </div>
            </div>
          </div>


          <div class="form-group">
            <label for="parameter_jenis_klinik_id">PILIH PASIEN<span style="color: red">*</span></label>

            <select class="form-control" name="pasien_permohonan_uji_klinik" id="pasien_permohonan_uji_klinik">
              <option value=""></option>
            </select>

            <div class="row">
              <div class="col-md-12">
                <a href="javascript:void(0)" class="btn btn-link btn-new-pasien">Klik disini jika data pasien belum
                  ada!</a>
              </div>
            </div>
          </div>


          <div class="card" id="display-new-pasien" style="display: none; margin-bottom: 20px">
            <div class="card-body">
              <button type="button" class="close cancel-new-pasien" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="patient_name">NIK PASIEN<span style="color: red">*</span></label>
                    <div class="input-group date">
                      <input type="text" class="form-control" name="nik_pasien" id="nik_pasien"
                        placeholder="Masukkan NIK Pasien" value="{{ old('nikpasien_pasien') }}">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="namapasien_pasien">NAMA PASIEN<span style="color: red">*</span></label>
                    <div class="input-group date">
                      <input type="text" class="form-control" name="nama_pasien" id="nama_pasien"
                        placeholder="Masukkan Nama Pasien">
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="name_parameter_paket_klinik">JENIS KELAMIN</label>

                <div class="form-check">
                  <label class="form-check-label">
                    <input type="radio" class="form-check-input" name="gender_pasien" id="gender_pasien1"
                      value="L" checked>
                    Laki-laki
                    <i class="input-helper"></i></label>
                </div>

                <div class="form-check">
                  <label class="form-check-label">
                    <input type="radio" class="form-check-input" name="gender_pasien" id="gender_pasien2"
                      value="P">
                    Perempuan
                    <i class="input-helper"></i></label>
                </div>
              </div>

              <div class="form-group">
                <label for="datelab_samples">TANGGAL LAHIR<span style="color: red">*</span></label>

                <input type="text" class="form-control date_birth" name="tgllahir_pasien" id="tgllahir_pasien"
                  placeholder="dd/mm/yyyy" readonly>
              </div>

              <div class="form-group">
                <label for="datelab_samples">UMUR</label>

                <div class="row">
                  <div class="col-sm">
                    <div class="input-group">
                      <input type="text" class="form-control" name="umurtahun_pasien" id="umurtahun_pasien"
                        placeholder="Umur" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">
                          tahun
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm">
                    <div class="input-group">
                      <input type="text" class="form-control" name="umurbulan_pasien" id="umurbulan_pasien"
                        placeholder="Umur" readonly>
                      <div class="input-group-append">
                        <span class="input-group-text">
                          Bulan
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm">
                    <div class="input-group">
                      <input type="text" class="form-control" name="umurhari_pasien" id="umurhari_pasien"
                        placeholder="Umur" readonly>
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
                <label for="patient_name">NO TELP/HP<span style="color: red">*</span></label>

                <input type="text" class="form-control" name="phone_pasien" id="phone_pasien"
                  placeholder="Masukkan NO TELP/HP">
              </div>

              <div class="form-group">
                <label for="exampleFormControlTextarea1">ALAMAT<span style="color: red">*</span></label>

                <textarea class="form-control" name="alamat_pasien" id="alamat_pasien" rows="3"></textarea>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="tglpengambilan_permohonan_uji_klinik">TANGGAL PENGAMBILAN</label>

            <input id="tglpengambilan_permohonan_uji_klinik" class="form-control"
              name="tglpengambilan_permohonan_uji_klinik" placeholder="--/--/--- --:--" />

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
                id="namapengirim_permohonan_uji_klinik" placeholder="Masukkan nama pengirim">
            </div>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">DIAGNOSA</label>
            <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik"
              rows="3"></textarea>
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
  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

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

    $(document).ready(function() {
      var CSRF_TOKEN = $('#csrf-token').val();

      $("#pasien_permohonan_uji_klinik").select2({
        ajax: {
          url: "{{ route('get-pasien-by-select') }}",
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
        placeholder: 'Pilih pasien',
        allowClear: true
      });


      $('.btn-new-pasien').click(function() {
        $('#display-new-pasien').css('display', 'block');

        $('.cancel-new-pasien').click(function() {
          // reset form
          $('#nik_pasien').val('');
          $('#nama_pasien').val('');
          $('#gender_pasien1').prop('checked', true);
          $('#tgllahir_pasien').val('');
          $('#umurtahun_pasien').val('');
          $('#umurbulan_pasien').val('');
          $('#umurhari_pasien').val('');
          $('#umurhari_pasien').val('');
          $('#phone_pasien').val('');
          $('#alamat_pasien').val('');

          $("#pasien_permohonan_uji_klinik").prop("disabled", false);
          $('#display-new-pasien').css('display', 'none');
        })

        $("#pasien_permohonan_uji_klinik").prop("disabled", true);
      });

      $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
      });

      $(".date_birth").datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
      });

      $('.date_birth').datepicker('update', moment("YYYYY-MM-DD").toDate());

      $('.datepicker').datepicker('update', moment("YYYYY-MM-DD").toDate());


      $.fn.select2.defaults.set("theme", "classic");


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


        $("#umurtahun_pasien").val(diff[0])
        $("#umurbulan_pasien").val(diff[1])
        $("#umurhari_pasien").val(diff[2])
        // console.log(m)
        // console.log(d)

        // if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate()))
        // {
        //     age--;
        // }

        // return diff;
      }

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
                  /* document.location =
                    '/elits-permohonan-uji-klinik/bukti-daftar-permohonan-uji-parameter/' +
                    response
                    .id_permohonan_uji_klinik; */

                  // document.location = '/elits-permohonan-uji-klinik';

                  document.location = response.urlNextStep;
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
      })
    });
  </script>
@endsection
