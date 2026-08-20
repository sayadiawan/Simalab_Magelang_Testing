@extends('masterweb::template.admin.layout')
@section('title')
  Pasien Management - Edit
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-pasien') }}">Pasien Management</a></li>
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
        action="{{ route('elits-pasien.update', $item->id_pasien) }}" method="POST">

        @csrf
        @method('PUT')

        <input type="hidden" name="permohonanujiklinik_id" id="permohonanujiklinik_id"
          value="{{ Request::query('permohonanujiklinik') }}" readonly>

        <div class="form-group">
          <label for="parameter_satuan_klinik">NIK Pasien<span style="color: red">*</span></label>

          <input type="text" class="form-control" id="nik_pasien" name="nik_pasien" placeholder="NIK pasien.."
            value="{{ $item->nik_pasien ?? old('nik_pasien') }}">
        </div>

        <div class="form-group">
          <label for="name_parameter_paket_klinik">Nama Pasien<span style="color: red">*</span></label>

          <input type="text" class="form-control" id="nama_pasien" name="nama_pasien" placeholder="Nama pasien.."
            value="{{ $item->nama_pasien ?? old('nama_pasien') }}">
        </div>

        <div class="form-group">
          <label for="name_parameter_paket_klinik">Jenis Kelamin Pasien</label>

          <div class="form-check">
            <label class="form-check-label">
              <input type="radio" class="form-check-input" name="gender_pasien" id="gender_pasien1" value="L"
                {{ $item->gender_pasien == 'L' ? 'checked' : '' }}>
              Laki-laki
              <i class="input-helper"></i></label>
          </div>

          <div class="form-check">
            <label class="form-check-label">
              <input type="radio" class="form-check-input" name="gender_pasien" id="gender_pasien2" value="P"
                {{ $item->gender_pasien == 'P' ? 'checked' : '' }}>
              Perempuan
              <i class="input-helper"></i></label>
          </div>
        </div>

        <div class="form-group">
          <label for="datelab_samples">Tanggal Lahir Pasien<span style="color: red">*</span></label>
          <div class="input-group ">
            <input type="text" class="form-control date_birth" name="tgllahir_pasien" id="tgllahir_pasien"
              placeholder="dd/mm/yyyy" readonly
              value="{{ $item->tgllahir_pasien != null ? \Carbon\Carbon::createFromFormat('Y-m-d', $item->tgllahir_pasien)->format('d/m/Y') : '' }}">
          </div>
        </div>

        <div class="form-group">
          <label for="datelab_samples">Umur Pasien</label>

          <div class="row">
            <div class="col-sm">
              <div class="input-group">
                <input type="text" class="form-control" name="umurtahun_pasien" id="umurtahun_pasien"
                  placeholder="Umur" readonly value="{{ $item->umurtahun_pasien }}">

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
                  placeholder="Umur" readonly value="{{ $item->umurbulan_pasien }}">

                <div class="input-group-append">
                  <span class="input-group-text">
                    Bulan
                  </span>
                </div>
              </div>
            </div>

            <div class="col-sm">
              <div class="input-group">
                <input type="text" class="form-control" name="umurhari_pasien" id="umurhari_pasien" placeholder="Umur"
                  readonly value="{{ $item->umurhari_pasien }}">

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
          <label for="harga_parameter_paket_klinik">Nomor Telepon<span style="color: red">*</span></label>

          <input type="text" class="form-control" id="phone_pasien" name="phone_pasien"
            placeholder="Nomor telepon pasien.." value="{{ $item->phone_pasien ?? old('phone_pasien') }}">
        </div>

        <div class="form-group">
          <label for="name_parameter_paket_klinik">Alamat Pasien</label>

          <textarea class="form-control" name="alamat_pasien" id="alamat_pasien" cols="30" placeholder="Alamat pasien.."
            rows="10">{{ $item->alamat_pasien ?? old('alamat_pasien') }}</textarea>
        </div>

        <br>

      </form>
      <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
      {{-- <button type="button" onclick="document.location='{{ url('/elits-pasien') }}'"
        class="btn btn-light">Kembali</button> --}}
      <button type="button" onclick="history.back();" class="btn btn-light">Kembali</button>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    function goBack() {
      window.history.back();
    }

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
      $(".date_birth").datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true
      });

      // $('.date_birth').datepicker('update', moment("YYYYY-MM-DD").toDate());

      $(".date_birth").change(function() {
        var datevalue = $(this).val()
        getAge(datevalue);
      });

      function getAge(dateString) {
        var today = moment().toDate();
        var birthDate = moment(dateString, "DD/MM/YYYY").toDate();

        var diff = moment.preciseDiff(today, birthDate, true);

        // var diff = today.diff(birthDate, 'days');


        $("#umurtahun_pasien").val(diff[0])
        $("#umurbulan_pasien").val(diff[1])
        $("#umurhari_pasien").val(diff[2])
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
                  document.location = response.url_back;
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
