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

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

          <div class="form-group">
            <label for="code_register"> No. REGISTER</label>
            <div class="input-group date">
              <input type="text" class="form-control" readonly name="noregister_permohonan_uji_klinik"
                id="noregister_permohonan_uji_klinik" placeholder="No. REGISTER"
                value="{{ $item->noregister_permohonan_uji_klinik }}">
            </div>
          </div>

          <script>
            var date_register = "{{ $item->tglregister_permohonan_uji_klinik }}"
          </script>

          <div class="card" id="display-new-pasien" style="margin-bottom: 20px">
            <div class="card-body">
              <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                  <a href="{{ route('elits-pasien.edit', [$item->pasien_permohonan_uji_klinik, 'permohonanujiklinik' => $item->id_permohonan_uji_klinik]) }}"
                    class="btn btn-sm btn-light float-right">
                    Klik disini untuk ubah data pasien!
                  </a>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="table-responsive">
                    <table class="table" collspan="5" cellpadding="1">
                      <tr>
                        <th style="width: 20%">NIK Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->nik_pasien ?? '-' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Nomor Rekam Medis</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->no_rekammedis_pasien ?? '-' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Nama Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->nama_pasien ?? '-' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Jenis Kelamin Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Tanggal Lahir Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->tgllahir_pasien != null ? \Carbon\Carbon::createFromFormat('Y-m-d', $item->pasien->tgllahir_pasien)->isoFormat('dddd, D MMMM Y') : '-' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Umur Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Nomor Telepon</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->phone_pasien ?? '-' }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Alamat Pasien</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item->pasien) }}
                        </td>
                      </tr>

                      <tr>
                        <th style="width: 20%">Divisi/instansi</th>
                        <th style="width: 2%">:</th>
                        <td style="width: 78%">
                          {{ $item->pasien->divisi_instansi_pasien ?? '-' }}
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group table-sample">
            <label for="date_get_sample">TGL.REGISTER</label>
            <div class="input-group date">
              <input type="text" class="form-control date_get_sample datepicker"
                name="tglregister_permohonan_uji_klinik" id="date_get_sample" placeholder="Tanggal Register"
                data-date-format="dd/mm/yyyy"
                value="{{ $item->tglregister_permohonan_uji_klinik != null ? \Carbon\Carbon::createFromFormat('Y-m-d', $item->tglregister_permohonan_uji_klinik)->format('d/m/Y') : '' }}"
                required>
              <div class="input-group-append">
                <span class="input-group-text">
                  <i class="fas fa-calendar-alt"></i>
                </span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="tglpengambilan_permohonan_uji_klinik">TANGGAL PENGAMBILAN</label>

            <input id="tglpengambilan_permohonan_uji_klinik" class="form-control"
              name="tglpengambilan_permohonan_uji_klinik" value="{{ $tgl_pengambilan }}" placeholder="--/--/--- --:--" />

            <script>
              $('#tglpengambilan_permohonan_uji_klinik').datetimepicker({
                format: 'dd/mm/yyyy HH:MM',
                footer: true,
                modal: true
              });
            </script>

          </div>

          <hr>

          <div class="form-group">
            <label for="patient_name">NAMA PENGIRIM</label>
            <div class="input-group date">

              <input type="text" class="form-control" name="namapengirim_permohonan_uji_klinik"
                id="namapengirim_permohonan_uji_klinik" value="{{ $item->namapengirim_permohonan_uji_klinik ?? '' }}"
                placeholder="Masukkan nama pengirim">
            </div>

          </div>

          <div class="form-group">
            <label for="dokter_permohonan_uji_klinik">NAMA DOKTER PERUJUK<span style="color: red">*</span></label>

            <input type="text" class="form-control" name="dokter_permohonan_uji_klinik"
              id="dokter_permohonan_uji_klinik" placeholder="Masukkan nama dokter perujuk"
              value="{{ $item->dokter_permohonan_uji_klinik ?? old('dokter_permohonan_uji_klinik') }}">
          </div>

          <div class="form-group">
            <label for="exampleFormControlTextarea1">DIAGNOSA</label>
            <textarea class="form-control" name="diagnosa_permohonan_uji_klinik" id="diagnosa_permohonan_uji_klinik"
              rows="3">{!! $item->diagnosa_permohonan_uji_klinik ?? old('diagnosa_permohonan_uji_klinik') !!}</textarea>
          </div>

          <div class="form-group">
            <div class="form-check form-check-flat form-check-primary">
              <label class="form-check-label">
                <input type="checkbox" class="form-check-input" name="is_lapangan_permohonan_uji_klinik"
                  id="is_lapangan_permohonan_uji_klinik"
                  {{ $item->is_lapangan_permohonan_uji_klinik == '1' ? 'checked' : '' }}>
                LAPANGAN/NON-LAPANGAN
                <i class="input-helper"></i></label>
            </div>
          </div>

        </form>
        <button id="btn-simpan" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
        <button type="button" class="btn btn-light"
          onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
      </li>
    </ul>
  </div>
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>
  <script src="//cdn.ckeditor.com/4.20.1/basic/ckeditor.js"></script>
  <script>
    $(document).ready(function() {
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
        format: 'dd/mm/yyyy',
        autoclose: true
      });

      var CSRF_TOKEN = $('#csrf-token').val();
      CKEDITOR.replace('diagnosa_permohonan_uji_klinik');

      // get data pengirim
      // $("#namapengirim_permohonan_uji_klinik").select2({
      //   ajax: {
      //     url: "{{ route('get-users-by-select') }}",
      //     type: "post",
      //     dataType: 'json',
      //     delay: 250,
      //     data: function(params) {
      //       return {
      //         _token: CSRF_TOKEN,
      //         search: params.term // search term
      //       };
      //     },
      //     processResults: function(response) {
      //       return {
      //         results: $.map(response, function(obj) {
      //           return {
      //             id: obj.id,
      //             text: obj.text
      //           };
      //         })
      //       };
      //     },
      //     cache: true
      //   },
      //   placeholder: 'Pilih pengirim',
      //   allowClear: true
      // });

      $('.datepicker').datepicker('update', moment(date_register, "YYYYY-MM-DD").toDate());
      // $(document).ready(function() {

      $('#btn-simpan').on('click', function(e) {
        e.preventDefault();
        $(document).ready(function() {
          console.log("dadaasd");

          $('#form').ajaxSubmit({
            success: function(response) {
              console.log(response);
              if (response.status == true) {
                swal({
                    title: "Success!",
                    text: response.pesan,
                    icon: "success"
                  })
                  .then(function() {
                    document.location = "{{ url('elits-permohonan-uji-klinik') }}";
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
              return false;
            },
            error: function(e) {
              e.preventDefault();
              swal("Error!", "System gagal menyimpan!", "error");
              return false;
            }
          })

        })
      })
    });
  </script>
@endsection
