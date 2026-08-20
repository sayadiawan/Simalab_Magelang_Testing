@extends('masterweb::template.admin.layout')

@section('title')
  Pembacaan Data Pencahayaan
@endsection


@section('content')








  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/gijgo.min.js')}}" type="text/javascript"></script>
  <link href="{{asset('assets/admin/cdn-local/css/gijgo.min.css')}}" rel="stylesheet" type="text/css" />


  <style>
    .right {
      float: right;
    }
  </style>

  @php

    if ($pencahayaan != null) {
        $date_sampling = Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $pencahayaan->tanggal_sampling)->format('d/m/Y');
        $lat = $pencahayaan->lat_location_sampling;
        $long = $pencahayaan->lng_location_sampling;
        $cuaca = $pencahayaan->cuaca_pencahayaan;
        $arah_angin = $pencahayaan->arah_angin;
        $waktu_pengukuran = $pencahayaan->waktu_pengukuran;
        $pukul_pengukuran = Carbon\Carbon::createFromFormat('H:i:s', $pencahayaan->pukul_pengukuran)->format('H:i');
        $hasil_pengujian = $pencahayaan->hasil_pengujian;
    } else {
        $date_sampling = '';
        $lat = '';
        $long = '';
        $cuaca = '';
        $arah_angin = '';
        $waktu_pengukuran = '';
        $pukul_pengukuran = '';
        $hasil_pengujian = '';
    }

  @endphp



  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-pencahayaan') }}">Laboraturium</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Pembacaan Data Pencahayaan</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">

    <div class="card-header">
      <h4>Pembacaan Data Pencahayaan</h4>
    </div>


    <div class="card-body">
      <div class="d-flex">
        <div class="mr-auto p-2">
          {{-- <div id="datepicker-popup" class="input-group date datepicker">
                <input type="text" class="form-control">
                <span class="input-group-addon input-group-append border-left">
                    <span class="far fa-calendar input-group-text"></span>
                </span>
            </div> --}}
        </div>

      </div>

      <div class="row">
        @if (session('status_delegation'))
          @php
            alert()->success('Berhasil', session('status_delegation'));
          @endphp
        @endif
        <div class="col-12">

          <form enctype="multipart/form-data" method="post" id="form_pencahayaan" action="">
            @csrf
            <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <div class="row">
                  <div class="col-md-3">
                    <label for="codesample_samples">Nama perusahaan</label>
                  </div>
                  <div class="col-md-0">
                    <label for="codesample_samples">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="codesample_samples">{{ $permohonan_uji->name_customer }}</label>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-3">
                    <label for="blanko_titration1">Alamat</label>
                  </div>
                  <div class="col-md-0">
                    <label for="blanko_titration1">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="blanko_titration1" style="align:left">{{ $permohonan_uji->address_customer }}</label>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-3">
                    <label for="blanko_titration1">Jenis perusahaan</label>
                  </div>
                  <div class="col-md-0">
                    <label for="blanko_titration1">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="blanko_titration1" style="align:left">{{ $permohonan_uji->name_industry }}</label>
                  </div>
                </div>
              </li>

              <li class="list-group-item">
                <br>
                <div class="form-group table-sample">
                  <label for="date_sampling">Tanggal Sampling</label>
                  <div class="input-group date">

                    <input type="text" class="form-control date_sampling" value="{{ $date_sampling }}"
                      name="date_sampling" id="date_sampling" placeholder="Isikan Tanggal Sampling"
                      data-date-format="dd/mm/yyyy" required>
                    <div class="input-group-append">
                      <span class="input-group-text">
                        <i class="fas fa-calendar-alt"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </li>

              <li class="list-group-item">
                <div class="form-card">
                  <center>
                    <h4>Titik Lokasi</h4>
                  </center>

                  <div class="container-fluid">
                    <div class="row">

                      <div class="col-lg-12">

                        <div id="pac-input-div" class="col-md-7 mb-3">
                          <input id="pac-input" class="form-control" type="text" placeholder="Pencarian Lokasi"
                            autofocus>
                        </div>

                        <div class="col">
                        </div>
                        <div style=" height: 500px;">
                          {!! Mapper::render() !!}
                        </div>
                        Click the button to get your live location.
                        <button id="get_location">Get Location</button>

                      </div>
                      <div class="col-lg-12">
                        <div class="form-row">
                          <div class="col">
                            <label for="name">Lat</label>
                            <input type="text" id="lat" name="lat" class="form-control"
                              value="{{ $lat }}" placeholder="Lat">
                          </div>
                          <div class="col">
                            <label for="name">Long</label>
                            <input type="text" id="lng" name="lng" class="form-control"
                              value="{{ $long }}" placeholder="Long">
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>


              </li>
              <li class="list-group-item">
                <div class="form-group">
                  <label for="name_customer">Cuaca</label>
                  <select name="cuaca" id="cuaca" class=" form-control" style="margin-bottom:4px;">
                    <option value="" {{ $cuaca == '' ? 'selected' : '' }} disabled>Pilih Cuaca</option>
                    <option value="cerah" {{ $cuaca == 'cerah' ? 'selected' : '' }}>Cerah</option>
                    <option value="mendung" {{ $cuaca == 'mendung' ? 'selected' : '' }}>Mendung</option>
                    <option value="hujan" {{ $cuaca == 'hujan' ? 'selected' : '' }}>Hujan</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="name_customer">Arah Angin</label>

                  <select name="arah_angin" id="arah_angin" class=" form-control" style="margin-bottom:4px;">
                    <option value="" {{ $arah_angin == '' ? 'selected' : '' }} disabled>Pilih Arah Angin</option>
                    <option value="u" {{ $arah_angin == 'U' ? 'selected' : '' }}>Utara</option>
                    <option value="t" {{ $arah_angin == 'T' ? 'selected' : '' }}>Timur</option>
                    <option value="s" {{ $arah_angin == 'S' ? 'selected' : '' }}>Selatan</option>
                    <option value="b" {{ $arah_angin == 'B' ? 'selected' : '' }}>Barat</option>
                  </select>
                </div>
              </li>
              <li class="list-group-item">
                <div class="row">
                  <div class="col-md-3">
                    <label for="codesample_samples">Metode</label>
                  </div>
                  <div class="col-md-0">
                    <label for="codesample_samples">:</label>
                  </div>
                  <div class="col-md-8">
                    <label for="codesample_samples">Sesaat</label>
                  </div>
                </div>
              </li>
              <li class="list-group-item">
                <div class="form-group">
                  <label for="name_customer">Waktu Pengukuran</label>
                  <select name="waktu_pengukuran" id="waktu_pengukuran" class=" form-control"
                    style="margin-bottom:4px;">
                    <option value="" {{ $waktu_pengukuran == '' ? 'selected' : '' }} disabled>Pilih Waktu
                      Pengukuran
                    </option>
                    <option value="pagi" {{ $waktu_pengukuran == 'pagi' ? 'selected' : '' }}>Pagi</option>
                    <option value="siang" {{ $waktu_pengukuran == 'siang' ? 'selected' : '' }}>Siang</option>
                    <option value="malam" {{ $waktu_pengukuran == 'malam' ? 'selected' : '' }}>Malam</option>
                  </select>

                </div>
                <div class="form-group" style="margin-top: 30px">

                  <label>Pukul</label>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <input id="timepicker" value="{{ $pukul_pengukuran }}" name="pukul_pengukuran"
                        width="312" />
                      <script>
                        function checkTime(i) {
                          if (i < 10) {
                            i = "0" + i;
                          }
                          return i;
                        }


                        var date = new Date();
                        var jam = date.getHours();
                        var menit = date.getMinutes();

                        var time = "{{ $pukul_pengukuran }}";
                        if (time == "") {
                          time = checkTime(jam) + ':' + checkTime(menit)
                        }

                        $('#timepicker').timepicker({
                          timeFormat: 'HH:MM',
                          format: 'HH:MM',
                          value: time,
                          mode: '24hr',
                          allowInputToggle: true
                        });
                      </script>
                    </div>
                  </div>

                </div>


              </li>

              <li class="list-group-item">

                <center>
                  <h4>Hasil</h4>
                </center>

                <table id="result_table">
                  <tr>
                    @if ($pencahayaan_detail != null)
                      <script>
                        var number = (Math.ceil((parseInt("{{ count($pencahayaan_detail) }}") / 6)) * 6);
                      </script>
                      @for ($i = 0; $i < ceil(count($pencahayaan_detail) / 6) * 6; $i++)
                        <td>
                          <div class="input-group">
                            <div class="input-group-append">
                              <span class="input-group-text">
                                {{ $i + 1 }}.
                              </span>
                            </div>
                            <input type="text"
                              value="{{ isset($pencahayaan_detail[$i]['hasil_pencahayaan_detail']) ? $pencahayaan_detail[$i]['hasil_pencahayaan_detail'] : '' }}"
                              name="result_data1" class="form-control">
                          </div>
                        </td>
                        @if (($i + 1) % 6 == 0 && $i != 0 && $i != ceil(count($pencahayaan_detail) / 6) * 6)
                  </tr>
                  <tr>
                    @endif
                    @endfor
                  @else
                    <script>
                      var number = 6;
                    </script>
                    @for ($i = 0; $i < 6; $i++)
                      <td>
                        <div class="input-group">
                          <div class="input-group-append">
                            <span class="input-group-text">
                              {{ $i + 1 }}.
                            </span>
                          </div>
                          <input type="text" name="result_data1" class="form-control">
                        </div>
                      </td>
                      @if (($i + 1) % 6 == 0 && $i != 0 && $i != 6)
                  </tr>
                  <tr>
                    @endif
                    @endfor
                    @endif

                  </tr>
                </table>
                <div class="right">
                  <input type="button" value="Tambah Data" onclick="addRowTable()" />
                </div>
                <script>
                  function addRowTable() {
                    // (B1) GET TABLE

                    var table = document.getElementById("result_table");

                    // (B2) INSERT ROW
                    var row = table.insertRow();

                    // (B3) INSERT CELLS
                    var cell
                    var i
                    for (i = number + 1; i < number + 7; i++) {
                      cell = row.insertCell();
                      cell.innerHTML = '<div class="input-group"><div class="input-group-append"><span class="input-group-text">' + i +
                        '.</span></div><input name="result_data' + i +
                        '" type="text" class="form-control" name="Appointment_time"></div>';

                    }

                    number = i - 1;


                  }
                </script>
              </li>
              <li class="list-group-item">
                <div class="form-group">
                  <label for="hasil_pengujian">Hasil Pengujian</label>
                  <input type="text" class="form-control" value="{{ $hasil_pengujian }}" id="hasil_pengujian"
                    name="hasil_pengujian" placeholder="Isikan Hasil Pengujian">
                </div>
              </li>
            </ul>
          </form>




          <br>

          <br>
          @php
            $no = 1;
          @endphp
          <div class="table-responsive">

            @csrf
            {{-- <table class="table table-bordered ">
              <thead>
                <tr>
                    <th>No</th>
                    <th>Method</th>
                    <th>Analis</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($method as $mymethod)
                <tr>
                    <td>{{$no}}</td>
                    <td>{{$mymethod->params_method}}</td>
                    <td>
                    <div class="form-group">
                        <select name="office_{{$mymethod->id_method}}" id="office_{{$mymethod->id_method}}" class=" js-lab-officer-basic-multiple js-states form-control" required>
                            @foreach ($delegation as $mydelegation)
                                @if ($mydelegation->method_id == $mymethod->id_method)
                                    <option value="{{$mydelegation->id_user}}" selected>{{$mydelegation->user_name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    </td>
                </tr>
                @php
                $no++;
                @endphp
                @endforeach
              </tbody>
            </table> --}}
            <br>
            <br>
            <div class="right">
              <button type="submit" class="btn btn-primary mr-2" id="start">Mulai Analisa</button>

            </div>
            </form>
            <div class="right">
              <button class="btn btn-primary mr-2" type="submit" id="save">Simpan</button>
            </div>
            <button class="btn btn-light" onclick="goBack()">Kembali</button>

          </div>
        </div>
      </div>


    </div>
  </div>
@endsection

@section('scripts')
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <script>
    $('.datetimepicker').datetimepicker();

    var pencahayaan = @json($pencahayaan);
    if (pencahayaan.tanggal_sampling != null) {
      $('.date_sampling').datepicker('update', "{{ $date_sampling }}");
    } else {
      $('.date_sampling').datepicker('update', new Date());
    }



    $('#save').on('click', function(event) {
      var form_result = $('#form_pencahayaan').serializeArray()
      var data = {}
      var data_table = []

      form_result.forEach(result => {
        // console.log(result.name.substring(0,11))
        if (result.name.substring(0, 11) == "result_data") {
          data_table.push(result.value)
        } else {
          data[result.name] = result.value
        }
      })
      data["data_table"] = data_table;
      data["permohonan_uji_id"] = "{{ request()->segment(2) }}";
      // data["_token"]= "{{ csrf_token() }}";
      // console.log(data)




      $.ajax({
        url: "{{ route('elits-pencahayaan.save', ['id' => Request::segment(2)]) }}",
        data: data,
        dataType: 'JSON',
        type: 'POST',
        success: function(data) {
          Swal.fire({
            title: 'Berhasil!',
            text: data.success,
            icon: 'success',
            confirmButtonText: 'Ok'
          })
        }
      });


    })







    function goBack() {
      window.history.back();
    }
  </script>

  @include('sweetalert::alert')
@endsection
