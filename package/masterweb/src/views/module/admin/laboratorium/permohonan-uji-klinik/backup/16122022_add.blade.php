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

          <div class="form-group">
            <label for="code_register"> No. REGISTER</label>
            <div class="input-group date">
              <input type="text" class="form-control" readonly name="noregister_permohonan_uji_klinik"
                id="noregister_permohonan_uji_klinik" placeholder="No. REGISTER" value="{{ $code }}">
            </div>
          </div>

          <input type="hidden" class="form-control" readonly name="nopasien_permohonan_uji_klinik"
            id="nopasien_permohonan_uji_klinik">

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

          {{-- view untuk menampilkan data pasien yang sudah ada --}}
          <div class="card" id="display-detail-pasien" style="display: none; margin-bottom: 20px">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="table-responsive">
                    <table class="table" collspan="5" cellpadding="1">
                      <tr>
                        <th style="width: 20%">NIK Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="nik_detail_pasien"></p>
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Nomor Rekam Medis</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="no_rekammedis_detail_pasien"></p>
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Nama Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="nama_detail_pasien"></p>
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Jenis Kelamin Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="gender_detail_pasien"></p>
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Tanggal Lahir Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="tgllahir_detail_pasien"></p>
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Nomor Telepon</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="phone_detail_pasien"></p>
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Alamat Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          <p id="alamat_detail_pasien"></p>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- view untuk menampilkan data pasien baru --}}
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
                <label for="no_rekammedis_pasien">NOMOR REKAM MEDIS</label>

                <input type="text" class="form-control no_rekammedis_pasien" name="no_rekammedis_pasien"
                  id="no_rekammedis_pasien" placeholder="Nomor rekam medis">
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

          <input type="hidden" class="form-control date_birth" name="tgllahir_pasien_permohonan_uji_klinik"
            id="tgllahir_pasien_permohonan_uji_klinik" placeholder="dd/mm/yyyy" readonly>

          <div class="form-group">
            <label for="datelab_samples">Umur Pasien</label>

            <div class="row">
              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" name="umurtahun_pasien_permohonan_uji_klinik"
                    id="umurtahun_pasien_permohonan_uji_klinik" placeholder="Umur" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">
                      tahun
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" name="umurbulan_pasien_permohonan_uji_klinik"
                    id="umurbulan_pasien_permohonan_uji_klinik" placeholder="Umur" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">
                      Bulan
                    </span>
                  </div>
                </div>
              </div>

              <div class="col-sm">
                <div class="input-group">
                  <input type="text" class="form-control" name="umurhari_pasien_permohonan_uji_klinik"
                    id="umurhari_pasien_permohonan_uji_klinik" placeholder="Umur" readonly>
                  <div class="input-group-append">
                    <span class="input-group-text">
                      Hari
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group table-sample">
            <label for="date_get_register">TGL.REGISTER<span style="color: red">*</span></label>
            <div class="input-group date">
              <input type="text" class="form-control date_get_register datepicker"
                name="tglregister_permohonan_uji_klinik" id="date_get_register" placeholder="dd/mm/yyyy"
                data-date-format="dd/mm/yyyy" readonly>

              {{-- <input type="text" class="form-control datepicker" name="tglregister_permohonan_uji_klinik"
                id="date_get_register" placeholder="dd/mm/yyyy" readonly
                value="{{ date('Y-m-d', strtotime(date('d/m/Y'))) }}"> --}}

              <div class="input-group-append">
                <span class="input-group-text">
                  <i class="fas fa-calendar-alt datepicker"></i>
                </span>
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
              {{-- <input type="hidden" class="form-control" name="namapengirim_permohonan_uji_klinik"
                id="namapengirim_permohonan_uji_klinik" placeholder="Masukkan nama pengirim"
                value="{{ Auth::user()->id }}" readonly>

              <input type="text" class="form-control" name="namapengirimdetail_permohonan_uji_klinik"
                id="namapengirimdetail_permohonan_uji_klinik" placeholder="Masukkan nama pengirim"
                value="{{ Auth::user()->name }}" readonly> --}}
            </div>

            <select class="form-control" name="namapengirim_permohonan_uji_klinik"
              id="namapengirim_permohonan_uji_klinik">
              <option value=""></option>
            </select>
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">DIAGNOSA</label>
            <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik"
              rows="3" placeholder="Isikan diagnosa pasien"></textarea>
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
  <script src="//cdn.ckeditor.com/4.20.1/basic/ckeditor.js"></script>

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
      CKEDITOR.replace('diagnosa_permohonan_uji_klinik');

      // get data pasien by slect2
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

      $("#umurtahun_pasien_permohonan_uji_klinik").val('')
      $("#umurbulan_pasien_permohonan_uji_klinik").val('')
      $("#umurhari_pasien_permohonan_uji_klinik").val('')

      // kodisi jika user memilih pasien yang sudah ada
      // akan ada tampilan detail dari data pasien tersebut
      $("#pasien_permohonan_uji_klinik").change(function() {
        $('#display-new-pasien').css('display', 'none');
        $('#display-detail-pasien').css('display', 'block');

        $("#umurtahun_pasien_permohonan_uji_klinik").val('')
        $("#umurbulan_pasien_permohonan_uji_klinik").val('')
        $("#umurhari_pasien_permohonan_uji_klinik").val('')

        if ($("#pasien_permohonan_uji_klinik").val()) {
          $.ajax({
            type: "POST",
            url: "{{ route('get-pasien-by-id') }}",
            data: {
              _token: CSRF_TOKEN,
              pasien_id: $("#pasien_permohonan_uji_klinik").val()
            },
            dataType: "JSON",
            success: function(response) {
              $('#nik_detail_pasien').text(response.nik_pasien);
              $('#no_rekammedis_detail_pasien').text(response.no_rekammedis_pasien);
              $('#nama_detail_pasien').text(response.nama_pasien);
              $("#gender_detail_pasien").text(response.gender_pasien);
              $('#tgllahir_detail_pasien').text(response.tgllahir_pasien);
              $('#phone_detail_pasien').text(response.phone_pasien);
              $('#alamat_detail_pasien').text(response.alamat_pasien);

              $('#nopasien_permohonan_uji_klinik').val(response.nik_pasien);
              $('#tgllahir_pasien_permohonan_uji_klinik').val(response.tgllahir_pasien_normal);

              getAge(response.tgllahir_pasien_normal);
            },
            error: function() {
              swal("Error!", "System gagal mendapatkan data pasien!", "error");
            }
          });
        } else {
          $('#display-detail-pasien').css('display', 'none');
        }
      })

      // jika element tersebut kosong maka form umur kosong
      if ($('#tgllahir_pasien_permohonan_uji_klinik').val().length === 0) {
        $("#umurtahun_pasien_permohonan_uji_klinik").val('')
        $("#umurbulan_pasien_permohonan_uji_klinik").val('')
        $("#umurhari_pasien_permohonan_uji_klinik").val('')
      }

      // jika user mengisi data pasien baru
      // jika user mengisikan data pasien maka isian select2 kosong dan tmapilan detail pasien hilang
      $('.btn-new-pasien').click(function() {
        $('#display-new-pasien').css('display', 'block');
        $('#display-detail-pasien').css('display', 'none');

        // reset data detail pasien
        $("#pasien_permohonan_uji_klinik").html('');
        $('#nik_detail_pasien').text('');
        $('#nama_detail_pasien').text('');
        $("#gender_detail_pasien").text('');
        $('#tgllahir_detail_pasien').text('');
        $('#phone_detail_pasien').text('');
        $('#alamat_detail_pasien').text('');
        $('#tgllahir_pasien_permohonan_uji_klinik').val('');
        $('#nopasien_permohonan_uji_klinik').val('');

        $('.cancel-new-pasien').click(function() {
          // reset form
          $('#nik_pasien').val('');
          $('#nama_pasien').val('');
          $('#gender_pasien1').prop('checked', true);
          $('#tgllahir_pasien').val('');
          $('#phone_pasien').val('');
          $('#alamat_pasien').val('');

          $("#umurtahun_pasien_permohonan_uji_klinik").val('');
          $("#umurbulan_pasien_permohonan_uji_klinik").val('');
          $("#umurhari_pasien_permohonan_uji_klinik").val('');

          $('#nopasien_permohonan_uji_klinik').val('');

          $("#pasien_permohonan_uji_klinik").prop("disabled", false);
          $('#display-new-pasien').css('display', 'none');
        })

        $("#pasien_permohonan_uji_klinik").prop("disabled", true);
      });

      // mengisikan input nik
      $('#nik_pasien').keyup(function(e) {
        $('#nopasien_permohonan_uji_klinik').val($(this).val());
      });

      // get data pengirim
      $("#namapengirim_permohonan_uji_klinik").select2({
        ajax: {
          url: "{{ route('get-users-by-select') }}",
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
        placeholder: 'Pilih pengirim',
        allowClear: true
      });

      $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
      });

      var a = $('#date_get_register').datepicker().datepicker('setDate', 'today');

      console.log(a);

      $(".date_birth").datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
      });

      $('.date_birth').datepicker('update', moment("YYYYY-MM-DD").toDate());

      // $('.datepicker').datepicker('update', moment("YYYYY-MM-DD").toDate());

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


        $("#umurtahun_pasien_permohonan_uji_klinik").val(diff[0])
        $("#umurbulan_pasien_permohonan_uji_klinik").val(diff[1])
        $("#umurhari_pasien_permohonan_uji_klinik").val(diff[2])
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
