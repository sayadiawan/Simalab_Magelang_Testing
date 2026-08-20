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
                <li class="breadcrumb-item active" aria-current="page"><span>edit permohonan uji paket klinik</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Ubah Permohonan Uji Paket Klinik
      </h4>

    </div>

    <form
      action="{{ route('elits-permohonan-uji-klinik.update-permohonan-uji-parameter', $item->id_permohonan_uji_klinik) }}"
      method="POST" enctype="multipart/form-data" id="form">

      @csrf
      @method('PUT')

      <div class="table-responsive">
        <table class="table table-stripped">
          <tr>
            <th width="250px">No. Register</th>
            <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
          </tr>

          <tr>
            <th width="250px">Tgl. Register</th>
            <td>{{ $tgl_register }}</td>
          </tr>

          <tr>
            <th width="250px">Nama Pasien</th>
            <td>{{ $item->namapasien_permohonan_uji_klinik }}</td>
          </tr>

          <tr>
            <th width="250px">Umur/Jenis Kelamin</th>
            <td>
              {{ $item->umurtahun_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_permohonan_uji_klinik . ' bulan ' . $item->umurhari_permohonan_uji_klinik }}
              / {{ $item->gender_permohonan_uji_klinik == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
          </tr>
        </table>
      </div>

      <div class="table-responsive">
        <table id="table-parameter" class="table">
          <thead>
            <tr>
              <th style="width: 5%">No</th>
              <th style="width: 15%">Jenis Parameter</th>
              <th style="width: 15%">Paket</th>
              <th style="width: 25%">Parameter</th>
              <th style="width: 20%">Harga</th>
              <th style="width: 15%">Aksi</th>
            </tr>
          </thead>

          <tbody>

            @if ($data_detail_uji_paket)
              @php
                $x = 1;
              @endphp

              @foreach ($data_detail_uji_paket as $key => $value)
                <tr id="row_{{ $x }}" class="tr_row">
                  <td style="width: 5%">
                    <h5>{{ $x }}</h5>
                  </td>

                  <td style="width: 15%">
                    <select class="form-control jenis_parameter" name="jenis_parameter[{{ $x }}]"
                      id="jenis_parameter_{{ $x }}" onchange="setDataJenisParameter({{ $x }})">
                      <option value="{{ $value->parameter_jenis_klinik }}">
                        {{ $value->parameterjenisklinik->name_parameter_jenis_klinik }}</option>
                    </select>
                  </td>

                  <td style="width: 15%">
                    <select class="form-control type_parameter" name="type_parameter[{{ $x }}]"
                      id="type_parameter_{{ $x }}" onchange="setDataPaketParameter({{ $x }})">
                      <option value="P" {{ $value->parameter_paket_klinik == 'P' ? 'selected' : '' }}>Paket</option>
                      <option value="C" {{ $value->parameter_paket_klinik == 'C' ? 'selected' : '' }}>Custom
                      </option>
                    </select>
                  </td>

                  <td style="width: 35%">

                    @if ($value->parameter_paket_klinik == 'P')
                      @php
                        $item_paket_satuan_parameter = Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $value->id_permohonan_uji_paket_klinik)
                            ->whereHas('parametersatuanklinik', function ($query) {
                                return $query->where('deleted_at', $value)->whereNull('deleted_at');
                            })
                            ->where('permohonan_uji_klinik', $item->id_permohonan_uji_klinik)
                            ->get();
                      @endphp

                      <select class="form-control satuan_parameter" name="satuan_parameter[{{ $x }}][]"
                        id="satuan_parameter_{{ $x }}"
                        onchange="setDataSatuanParameter({{ $x }})">
                        <option value="{{ }}"></option>
                      </select>
                    @elseif ($value->parameter_paket_klinik == 'C')
                      @php
                        $item_custom_satuan_parameter = Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $value->id_permohonan_uji_paket_klinik)
                            ->where('permohonan_uji_klinik', $item->id_permohonan_uji_klinik)
                            ->get();
                      @endphp

                      <select class="form-control satuan_parameter" name="satuan_parameter[{{ $x }}][]"
                        id="satuan_parameter_{{ $x }}" onchange="setDataSatuanParameter({{ $x }})"
                        multiple>
                        <option value=""></option>
                      </select>
                    @else
                      <select class="form-control satuan_parameter" name="satuan_parameter[{{ $x }}][]"
                        id="satuan_parameter_{{ $x }}"
                        onchange="setDataSatuanParameter({{ $x }})">
                        <option value=""></option>
                      </select>
                    @endif

                  </td>

                  <td style="width: 15%">
                    <input type="number" class="form-control harga_parameter"
                      name="harga_parameter[{{ $x }}]" id="harga_parameter_{{ $x }}"
                      value="0" readonly>
                  </td>

                  <td style="width: 15%">
                    <button type="button" class="btn btn-primary btn-add-row" data-row="{{ $x }}"
                      onclick="addRow({{ $x }})">
                      <i class="fas fa-plus"></i>
                    </button>

                    <button type="button" class="btn btn-danger btn-remove-row" data-row="{{ $x }}"
                      onclick="removeRow({{ $x }})">
                      <i class="fas fa-minus"></i>
                    </button>
                  </td>
                </tr>

                @php
                  $x++;
                @endphp
              @endforeach
            @else
              <tr id="row_1" class="tr_row">
                <td style="width: 5%">
                  <h5>1</h5>
                </td>

                <td style="width: 15%">
                  <select class="form-control jenis_parameter" name="jenis_parameter[1]" id="jenis_parameter_1"
                    onchange="setDataJenisParameter(1)">
                    <option value=""></option>
                  </select>
                </td>

                <td style="width: 15%">
                  <select class="form-control type_parameter" name="type_parameter[1]" id="type_parameter_1"
                    onchange="setDataPaketParameter(1)">
                    <option value="P" selected>Paket</option>
                    <option value="C">Custom</option>
                  </select>
                </td>

                <td style="width: 35%">
                  <select class="form-control satuan_parameter" name="satuan_parameter[1][]" id="satuan_parameter_1"
                    onchange="setDataSatuanParameter(1)">
                    <option value=""></option>
                  </select>
                </td>

                <td style="width: 15%">
                  <input type="number" class="form-control harga_parameter" name="harga_parameter[1]"
                    id="harga_parameter_1" value="0" readonly>
                </td>

                <td style="width: 15%">
                  <button type="button" class="btn btn-primary btn-add-row" data-row="1" onclick="addRow(1)">
                    <i class="fas fa-plus"></i>
                  </button>

                  <button type="button" class="btn btn-danger btn-remove-row" data-row="1" onclick="removeRow(1)">
                    <i class="fas fa-minus"></i>
                  </button>
                </td>
              </tr>
            @endif

          </tbody>

          <tr>
            <th style="width: 250px" colspan="4" class="text-right">Total Harga</th>
            <td>
              <input type="text" class="form-control" name="subamount_harga_parameter"
                id="subamount_harga_parameter" readonly>
            </td>
          </tr>
        </table>
      </div>

    </form>
    <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
    <button type="button" class="btn btn-light"
      onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
  </div>
@endsection

@section('scripts')
  <script src="{{asset('assets/admin/cdn-local/js/moment.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    var CSRF_TOKEN = $('#csrf-token').val();
    getSubAmount()

    function setDataJenisParameter(row) {
      $("#satuan_parameter_" + row).val([]);
      var val_type_parameter = $('#type_parameter_' + row + ' option:selected').val();
      $("#harga_parameter_" + row).val(0);
      getSubAmount()
      setDataSatuanParameter(row);

      $("#satuan_parameter_" + row).select2({
        ajax: {
          url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
          type: "POST",
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              _token: CSRF_TOKEN,
              jenis_parameter: $('#jenis_parameter_' + row).val(),
              type_parameter: val_type_parameter,
              search: params.term // search term
            };
          },
          processResults: function(response) {
            /* return {
              results: response
            }; */

            return {
              results: $.map(response, function(obj) {
                return {
                  id: obj.id,
                  text: obj.text,
                  harga: obj.harga
                };
              })
            };

            getSubAmount()
          },
          cache: true,
        },
        placeholder: 'Pilih parameter',
        theme: 'classic',
        allowClear: true
      });
    }

    function setDataPaketParameter(row) {
      $("#satuan_parameter_" + row).val([]);
      var val_type_parameter = $('#type_parameter_' + row + ' option:selected').val();
      $("#harga_parameter_" + row).val(0);
      getSubAmount()
      setDataSatuanParameter(row);

      if (val_type_parameter == "P") {
        $('#satuan_parameter_' + row).removeAttr('multiple');
      } else {
        $('#satuan_parameter_' + row).attr('multiple', 'multiple');
      }

      $("#satuan_parameter_" + row).select2({
        ajax: {
          url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
          type: "POST",
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              _token: CSRF_TOKEN,
              jenis_parameter: $('#jenis_parameter_' + row).val(),
              type_parameter: val_type_parameter,
              search: params.term // search term
            };
          },
          processResults: function(response) {
            /* return {
              results: response
            }; */

            return {
              results: $.map(response, function(obj) {
                return {
                  id: obj.id,
                  text: obj.text,
                  harga: obj.harga
                };
              })
            };

            getSubAmount()
          },
          cache: true,
        },
        placeholder: 'Pilih parameter',
        theme: 'classic',
        allowClear: true
      });
    }

    function setDataSatuanParameter(row) {
      var val_jenis_parameter = $('#jenis_parameter_' + row + ' option:selected').val();
      var val_type_parameter = $('#type_parameter_' + row + ' option:selected').val();

      if (val_type_parameter == "P") {
        var val_satuan_parameter = $('#satuan_parameter_' + row + ' option:selected').val();
      } else {
        var val_satuan_parameter = [];

        val_satuan_parameter = $("#satuan_parameter_" + row).val();
      }

      if (val_jenis_parameter !== "" || val_type_parameter !== "" || val_type_parameter !== "") {
        console.log(val_satuan_parameter);

        $.ajax({
          url: "{{ route('elits-permohonan-uji-klinik.count-parameter-dan-harga') }}",
          type: "POST",
          data: {
            _token: CSRF_TOKEN,
            jenis_parameter: val_jenis_parameter,
            type_parameter: val_type_parameter,
            satuan_parameter: val_satuan_parameter
          },
          dataType: "JSON",
          success: function(response) {
            console.log('harga perrow: ' + response[0].harga);

            if (response[0].harga > 0) {
              $('#harga_parameter_' + row).val(response[0].harga);
            } else {
              $('#harga_parameter_' + row).val(0);
            }

            getSubAmount()
          },
          error: function(e) {
            swal("Error!", "System gagal mendapatkan harga parameter!", "error");
          }
        });
      } else {
        $('#harga_parameter_' + row).val(0);
      }

    }

    $(".jenis_parameter").select2({
      ajax: {
        url: "{{ route('getParameterJenisKlinik') }}",
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
      placeholder: 'Pilih jenis parameter',
      theme: 'classic',
      allowClear: true
    });

    function addRow(row) {
      var tableParameterLength = $("#table-parameter tbody .tr_row").length;

      console.log('tableParameterLength ' + tableParameterLength);

      for (x = 0; x < tableParameterLength; x++) {
        var tr = $("#table-parameter tbody tr")[x];
        var count = $(tr).attr('id');

        console.log(count);
        count = Number(count.substring(4));

        console.log(count);
      } // /for

      var count_table_tbody_tr = $("#table-parameter tbody .tr_row").length;
      id_html = count + 1;

      var dom_html = `<tr id="row_${id_html}" class="tr_row">
                        <td style="width: 5%">
                            <h5>${id_html}</h5>
                        </td>

                        <td style="width: 15%">
                            <select class="form-control jenis_parameter" name="jenis_parameter[${id_html}]" id="jenis_parameter_${id_html}" onchange="setDataJenisParameter(${id_html})">
                                <option value=""></option>
                            </select>
                        </td>

                        <td style="width: 15%">
                            <select class="form-control type_parameter" name="type_parameter[${id_html}]" id="type_parameter_${id_html}" onchange="setDataPaketParameter(${id_html})">
                                <option value="P" selected>Paket</option>
                                <option value="C">Custom</option>
                            </select>
                        </td>

                        <td style="width: 35%">
                            <select class="form-control satuan_parameter" name="satuan_parameter[${id_html}][]" id="satuan_parameter_${id_html}" onchange="setDataSatuanParameter(${id_html})">
                                <option value=""></option>
                            </select>
                        </td>

                        <td style="width: 15%">
                            <input type="number" class="form-control harga_parameter" name="harga_parameter[${id_html}]" id="harga_parameter_${id_html}" readonly>
                        </td>

                        <td style="width: 15%">
                            <button type="button" class="btn btn-primary btn-add-row" data-row="${id_html}" onclick="addRow(${id_html})">
                                <i class="fas fa-plus"></i>
                            </button>

                            <button type="button" class="btn btn-danger btn-remove-row" data-row="${id_html}" onclick="removeRow(${id_html})">
                                <i class="fas fa-minus"></i>
                            </button>
                        </td>
                    </tr>`;

      // $(document.getElementById('main-bdy')).append(dom_html);

      if (count_table_tbody_tr >= 1) {
        $("#table-parameter tbody .tr_row:last").after(dom_html);
      } else {
        $("#table-parameter tbody").html(dom_html);
      }

      $(".jenis_parameter").select2({
        ajax: {
          url: "{{ route('getParameterJenisKlinik') }}",
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
        placeholder: 'Pilih jenis parameter',
        theme: 'classic',
        allowClear: true
      });

      setDataJenisParameter(id_html);
      setDataPaketParameter(id_html);
      setDataSatuanParameter(id_html);
    }

    function removeRow(row) {
      var count_table_tbody_tr = $("#table-parameter tbody .tr_row").length;

      if (count_table_tbody_tr > 1) {
        $("#table-parameter tbody .tr_row#row_" + row).remove();
      }
    }

    function getSubAmount() {
      var tableParameterLength = $("#table-parameter tbody .tr_row").length;
      var totalSubAmount = 0;

      for (x = 0; x < tableParameterLength; x++) {
        var tr = $("#table-parameter tbody .tr_row")[x];
        var count = $(tr).attr('id');

        console.log(count);
        count = Number(count.substring(4));

        console.log(count);

        totalSubAmount = Number(totalSubAmount) + Number($("#harga_parameter_" + count).val());
      } // /for

      totalSubAmount = Number(totalSubAmount);

      console.log('totalSubAmount: ' + totalSubAmount);

      $("#subamount_harga_parameter").val(totalSubAmount);
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
