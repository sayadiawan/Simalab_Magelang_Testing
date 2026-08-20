@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
@endsection


@section('content')
  {{-- <script src="{{asset('assets/admin/cdn-local/js/jquery-3.3.1.min.js')}}"></script> --}}
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
                <li class="breadcrumb-item active" aria-current="page"><span>analis permohonan uji paket klinik</span>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Analis Permohonan Uji Paket Klinik
      </h4>
    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form
          action="{{ route('elits-permohonan-uji-klinik.store-permohonan-uji-analis', $item_permohonan_uji_klinik->id_permohonan_uji_klinik) }}"
          method="POST" enctype="multipart/form-data" id="form">
          {{-- <form action=""> --}}

          @csrf
          @method('PUT')

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

          <div class="row">
            <div class="col-md-6">
              <div class="table-responsive">
                <table class="table table-borderless">
                  <tr>
                    <th width="250px">No. Register</th>
                    <td>{{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">Tgl. Register</th>
                    <td>{{ $tgl_register_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">Nama Pasien</th>
                    <td>{{ $item_permohonan_uji_klinik->namapasien_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">Usia</th>
                    <td>
                      {{ $item_permohonan_uji_klinik->umurtahun_permohonan_uji_klinik . ' tahun ' . $item_permohonan_uji_klinik->umurbulan_permohonan_uji_klinik . ' bulan ' . $item_permohonan_uji_klinik->umurhari_permohonan_uji_klinik }}
                    </td>
                  </tr>

                  <tr>
                    <th width="250px">Jenis Kelamin</th>
                    <td>
                      {{ $item_permohonan_uji_klinik->gender_permohonan_uji_klinik == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </td>
                  </tr>

                  <tr>
                    <th width="250px">Alamat Pasien</th>
                    <td>{{ $item_permohonan_uji_klinik->alamat_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">No. Telepon</th>
                    <td>{{ $item_permohonan_uji_klinik->phone_permohonan_uji_klinik }}</td>
                  </tr>
                </table>
              </div>
            </div>

            <div class="col-md-6">
              <div class="table-responsive">
                <table class="table table-borderless">
                  <tr>
                    <th width="250px">No. Pasien</th>
                    <td>{{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">No. KTP</th>
                    <td>{{ $item_permohonan_uji_klinik->nikpasien_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">Tanggal Lahir</th>
                    <td>{{ $tgl_lahir_permohonan_uji_klinik }}</td>
                  </tr>

                  <tr>
                    <th width="250px">Pengirim</th>
                    <td>{{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 p-4">
              <div class="form-group">
                <label for="patient_name">NRP</label>
                <div class="input-group date">
                  <input type="text" class="form-control" name="nrp_permohonan_uji_klinik"
                    id="nrp_permohonan_uji_klinik" placeholder="Masukkan NRP"
                    value="{{ $item_permohonan_uji_klinik->nrp_permohonan_uji_klinik ?? old('nrp_permohonan_uji_klinik') }}">
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="patient_name">Divisi/Dept</label>
                    <div class="input-group date">
                      <input type="text" class="form-control" name="div_dept_permohonan_uji_klinik"
                        id="div_dept_permohonan_uji_klinik" placeholder="Masukkan divisi/dept"
                        value="{{ $item_permohonan_uji_klinik->div_dept_permohonan_uji_klinik ?? old('div_dept_permohonan_uji_klinik') }}">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="patient_name">Dokter</label>
                    <div class="input-group date">
                      <input type="text" class="form-control" name="dokter_permohonan_uji_klinik"
                        id="dokter_permohonan_uji_klinik" placeholder="Masukkan nama Dokter"
                        value="{{ $item_permohonan_uji_klinik->dokter_permohonan_uji_klinik ?? old('dokter_permohonan_uji_klinik') }}">
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="tglpengujian_permohonan_uji_klinik">Tanggal Pengujian</label>

                <input type="text" class="form-control" name="tglpengujian_permohonan_uji_klinik"
                  id="tglpengujian_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                  value="{{ $tgl_pengujian ?? old('tglpengujian_permohonan_uji_klinik') }}">

                <script>
                  $('#tglpengujian_permohonan_uji_klinik').datetimepicker({
                    format: 'dd/mm/yyyy HH:MM',
                    footer: true,
                    modal: true
                  });
                </script>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="patient_name">Waktu Pengambilan Spesimen (Darah)</label>

                    <input type="text" class="form-control" name="spesimen_darah_permohonan_uji_klinik"
                      id="spesimen_darah_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                      value="{{ $tgl_spesimen_darah ?? old('spesimen_darah_permohonan_uji_klinik') }}">

                    <script>
                      $('#spesimen_darah_permohonan_uji_klinik').datetimepicker({
                        format: 'dd/mm/yyyy HH:MM',
                        footer: true,
                        modal: true
                      });
                    </script>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="patient_name">Waktu Pengambilan Spesimen (Urine)</label>

                    <input type="text" class="form-control" name="spesimen_urine_permohonan_uji_klinik"
                      id="spesimen_urine_permohonan_uji_klinik" placeholder="--/--/--- --:--"
                      value="{{ $tgl_spesimen_urine ?? old('spesimen_urine_permohonan_uji_klinik') }}">

                    <script>
                      $('#spesimen_urine_permohonan_uji_klinik').datetimepicker({
                        format: 'dd/mm/yyyy HH:MM',
                        footer: true,
                        modal: true
                      });
                    </script>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table id="table-parameter" class="table">
              <thead>
                <tr>
                  <th style="width: 20%">Nama Test</th>
                  <th style="width: 15%">Hasil</th>
                  <th style="width: 15%">Flag</th>
                  <th style="width: 15%">Satuan</th>
                  <th style="width: 15%">Nilai Rujukan</th>
                  <th style="width: 20%">Keterangan</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($data_parameter_satuan_klinik as $key_psk => $item_psk)
                  {{-- Panggil data yang bukan sedimen --}}
                  @if (count($data_detail_uji_parameter_non_sub) > 0)
                    @foreach ($data_detail_uji_parameter_non_sub as $key_upns => $item_upns)
                      @if ($item_upns->parameter_satuan_klinik == $item_psk->id_parameter_satuan_klinik)
                        <tr id="row_non_sub_{{ $key_upns }}" class="tr_row">
                          <td style="width: 20%">
                            {{ $item_upns->parametersatuanklinik->name_parameter_satuan_klinik }}


                            <input type="hidden"
                              name="permohonan_uji_parameter_klinik[{{ $item_upns->id_permohonan_uji_parameter_klinik }}]"
                              value="{{ $item_upns->id_permohonan_uji_parameter_klinik }}" readonly>
                          </td>

                          <td style="width: 15%">
                            <input type="text" class="form-control"
                              name="hasil_permohonan_uji_parameter_klinik[{{ $item_upns->id_permohonan_uji_parameter_klinik }}]"
                              value="{{ $item_upns->hasil_permohonan_uji_parameter_klinik ?? old('hasil_permohonan_uji_parameter_klinik') }}">
                          </td>

                          <td style="width: 15%">
                            <input type="text" class="form-control"
                              name="flag_permohonan_uji_parameter_klinik[{{ $item_upns->id_permohonan_uji_parameter_klinik }}]"
                              value="{{ $item_upns->flag_permohonan_uji_parameter_klinik ?? old('flag_permohonan_uji_parameter_klinik') }}">
                          </td>

                          <td style="width: 15%">
                            <select class="form-control satuan_permohonan_uji_parameter_klinik"
                              name="satuan_permohonan_uji_parameter_klinik[{{ $item_upns->id_permohonan_uji_parameter_klinik }}]">
                              <option value="{{ $item_upns->satuan_permohonan_uji_parameter_klinik }}">
                                {{ $item_upns->unit->name_unit }}</option>
                            </select>
                          </td>

                          <td style="width: 15%">
                            <input type="text" class="form-control"
                              name="baku_mutu_permohonan_uji_parameter_klinik[{{ $item_upns->id_permohonan_uji_parameter_klinik }}]"
                              value="{{ $item_upns->bakumutu->nilai_baku_mutu ?? old('baku_mutu_permohonan_uji_parameter_klinik') }}"
                              readonly>
                          </td>

                          <td style="width: 20%">
                            <textarea class="form-control"
                              name="keterangan_permohonan_uji_parameter_klinik[{{ $item_upns->id_permohonan_uji_parameter_klinik }}]"
                              cols="5" rows="5">{{ $item_upns->keterangan_permohonan_uji_parameter_klinik ?? old('keterangan_permohonan_uji_parameter_klinik') }}</textarea>
                          </td>
                        </tr>
                      @endif
                    @endforeach
                  @endif
                @endforeach

                {{-- panggil data yang memiliki sub diparameternya --}}
                @if (count($data_detail_uji_parameter_with_sub) > 0)
                  @foreach ($data_detail_uji_parameter_with_sub as $key_upws => $item_upws)
                    <tr>
                      <th colspan="6">{{ $item_upws->parametersatuanklinik->name_parameter_satuan_klinik }}: ~</th>

                      <input type="hidden"
                        name="permohonan_uji_parameter_klinik[{{ $item_upws->id_permohonan_uji_parameter_klinik }}]"
                        value="{{ $item_upws->id_permohonan_uji_parameter_klinik }}" readonly>
                    </tr>


                    {{-- loop data parmter satuan yang memiliki sub --}}
                    @foreach ($item_upws->parametersatuanklinik->parametersubsatuanklinik as $key_pssk => $item_pssk)
                      <tr id="row_with_sub_{{ $key_upws }}" class="tr_row">
                        <td style="width: 20%">
                          <p style="padding-left: 30px">{{ $item_pssk->name_parameter_sub_satuan_klinik }} ~</p>

                          <input type="hidden"
                            name="parameter_sub_satuan_klinik_id[{{ $item_upws->id_permohonan_uji_parameter_klinik }}][]"
                            value="{{ $item_upws->id_permohonan_uji_parameter_klinik }}" readonly>
                        </td>

                        <td style="width: 15%">
                          <input type="text" class="form-control"
                            name="hasil_permohonan_uji_sub_parameter_klinik[{{ $item_upws->id_permohonan_uji_parameter_klinik }}][]"
                            value="{{ $item_upws->permohonanujisubparameterklinik->hasil_permohonan_uji_sub_parameter_klinik ?? old('hasil_permohonan_uji_sub_parameter_klinik') }}">
                        </td>

                        <td style="width: 15%">
                          <input type="text" class="form-control"
                            name="flag_permohonan_uji_sub_parameter_klinik[{{ $item_upws->id_permohonan_uji_parameter_klinik }}][]"
                            value="{{ $item_upws->permohonanujisubparameterklinik->flag_permohonan_uji_sub_parameter_klinik ?? old('flag_permohonan_uji_sub_parameter_klinik') }}">
                        </td>

                        <td style="width: 15%">
                          <select class="form-control satuan_permohonan_uji_parameter_klinik"
                            name="satuan_permohonan_uji_sub_parameter_klinik[{{ $item_upws->id_permohonan_uji_parameter_klinik }}][]">

                            @if ($item_upws->permohonanujisubparameterklinik !== null)
                              <option
                                value="{{ $item_upws->permohonanujisubparameterklinik->satuan_permohonan_uji_sub_parameter_klinik }}">
                                {{ $item_upws->unit->name_unit }}
                              </option>
                            @endif
                          </select>
                        </td>

                        <td style="width: 15%">
                          <input type="text" class="form-control"
                            name="baku_mutu_permohonan_uji_sub_parameter_klinik[{{ $item_upws->id_permohonan_uji_parameter_klinik }}][]"
                            value="{{ $item_pssk->bakumutudetailparmeterklinik->nilai_baku_mutu_detail_parameter_klinik ?? '-' }}"
                            readonly>
                        </td>

                        <td style="width: 20%">
                          <textarea class="form-control"
                            name="keterangan_permohonan_uji_sub_parameter_klinik[{{ $item_upws->id_permohonan_uji_parameter_klinik }}][]"
                            cols="5" rows="5">{{ $item_upws->permohonanujisubparameterklinik->keterangan_permohonan_uji_sub_parameter_klinik ?? old('keterangan_permohonan_uji_sub_parameter_klinik') }}</textarea>
                        </td>
                      </tr>
                    @endforeach
                  @endforeach
                @endif
              </tbody>
            </table>
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
    $(document).ready(function() {
      var CSRF_TOKEN = $('#csrf-token').val();

      $(".satuan_permohonan_uji_parameter_klinik").select2({
        ajax: {
          url: "{{ route('getDataUnitBySelect') }}",
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
          cache: true,
        },
        placeholder: 'Pilih unit',
        allowClear: true
      });

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
              var errorMsg = "";
              // errorMsg += response.pesan ? response.pesan + "\n" : "";
              var i;
              var error = response.pesan;

              for (i = 0; i < error.length; i++) {
                errorMsg += error[i];
              }

              swal({
                title: "Error!",
                html: true,
                text: errorMsg,
                icon: "warning"
              });
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
