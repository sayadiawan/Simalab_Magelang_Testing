@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Klinik
@endsection


@section('content')
  <style>
    .select2-container {
      min-width: 10em !important;
    }
  </style>
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
                <li class="breadcrumb-item active" aria-current="page"><span>create permohonan uji paket klinik</span>
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
      <h4>Tambah Permohonan Uji Paket Klinik
      </h4>
    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <form action="{{ route('elits-permohonan-uji-klinik.store-permohonan-uji-parameter') }}" method="POST"
          enctype="multipart/form-data" id="form">

          @csrf

          <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

          <div class="table-responsive">
            <table class="table table-borderless">
              <tr>
                <th width="250px">No. Register</th>
                <td>{{ $item->noregister_permohonan_uji_klinik }}</td>

                <input type="hidden" name="permohonan_uji_klinik" id="permohonan_uji_klinik" value="{{ $id }}"
                  readonly>
              </tr>

              <tr>
                <th width="250px">No. Rekam Medis</th>
                <td>
                  {{ Carbon\Carbon::createFromFormat('Y-m-d', $item->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                </td>
              </tr>

              <tr>
                <th width="250px">Tgl. Register</th>
                <td>{{ $tgl_register }}</td>
              </tr>

              <tr>
                <th width="250px">Nama Pasien</th>
                <td>{{ $item->pasien->nama_pasien }}</td>
              </tr>

              <tr>
                <th width="250px">Umur/Jenis Kelamin</th>
                <td>
                  {{ $item->umurtahun_pasien_permohonan_uji_klinik .
                      ' tahun ' .
                      $item->umurbulan_pasien_permohonan_uji_klinik .
                      ' bulan ' .
                      $item->umurhari_pasien_permohonan_uji_klinik .
                      ' hari' }}
                  / {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
              </tr>
            </table>
          </div>

          <div class="table-responsive">
            <table id="table-parameter" class="table">
              <thead>
                <tr>
                  <th style="width: 5%">No</th>
                  <th style="width: 15%">Paket</th>
                  <th style="width: 15%">Jenis Parameter</th>
                  <th style="width: 25%">Parameter</th>
                  <th style="width: 20%">Harga</th>
                  <th style="width: 15%">Aksi</th>
                </tr>
              </thead>

              <tbody>
                <tr id="row_1" class="tr_row">
                  <td style="width: 5%">
                    <h5>1</h5>
                  </td>

                  <td style="width: 15%">
                    <select class="form-control type_parameter" name="type_parameter[1]" id="type_parameter_1"
                      onchange="setDataPaketParameter(1)">
                      <option value="P" selected>Paket</option>
                      <option value="C">Custom</option>
                    </select>
                  </td>

                  <td style="width: 15%">
                    <select class="form-control jenis_parameter" name="jenis_parameter[1]" id="jenis_parameter_1"
                      onchange="setDataJenisParameter(1)" style="display: none; width:100pt">
                      <option value=""></option>
                    </select>
                  </td>


                  <td style="width: 35%">
                    <select class="form-control satuan_parameter" name="satuan_parameter[1]" id="satuan_parameter_1"
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
        @if (Request::get('complete-step') == true)
          <input type="hidden" name="complete_step" id="complete_step" value="1" readonly>

          <a href="{{ route('elits-permohonan-uji-klinik.bukti-daftar-permohonan-uji-parameter', $item->id_permohonan_uji_klinik) }}"
            class="btn btn-primary mr-2 btn-lanjutkan">Lanjutkan</a>

          <button type="submit" class="btn btn-primary mr-2 btn-simpan" id="btn-simpan-lanjutkan"
            style="display: none">Simpan</button>
        @else
          <input type="hidden" name="complete_step" id="complete_step" value="0" readonly>

          <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>

          <button type="button" class="btn btn-light"
            onclick="document.location='{{ url('/elits-permohonan-uji-klinik') }}'">Kembali</button>
        @endif
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
        $('#jenis_parameter_' + row).css('display', 'none');
        $('#satuan_parameter_' + row).removeAttr('multiple');
        $('#jenis_parameter_' + row).select2('destroy');
        $("#satuan_parameter_" + row).select2({
          ajax: {
            url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                _token: CSRF_TOKEN,
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
        $("#satuan_parameter_" + row).prop("name", "satuan_parameter[" + (row) + "]");
      } else {
        $('#jenis_parameter_' + row).css('display', 'block');
        $('#satuan_parameter_' + row).attr('multiple', 'multiple');

        $('#jenis_parameter_' + row).select2({
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

        $("#satuan_parameter_" + row).prop("name", "satuan_parameter[" + (row) + "][]");
      }



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



    function addRow(row) {
      var tableParameterLength = $("#table-parameter tbody .tr_row").length;


      for (x = 0; x < tableParameterLength; x++) {
        var tr = $("#table-parameter tbody tr")[x];
        var count = $(tr).attr('id');

        count = Number(count.substring(4));

      } // /for

      var count_table_tbody_tr = $("#table-parameter tbody .tr_row").length;
      id_html = count + 1;

      var dom_html = `<tr id="row_${id_html}" class="tr_row">
                        <td style="width: 5%">
                            <h5>${id_html}</h5>
                        </td>

                        <td style="width: 15%">
                            <select class="form-control type_parameter" name="type_parameter[${id_html}]" id="type_parameter_${id_html}" onchange="setDataPaketParameter(${id_html})">
                                <option value="P" selected>Paket</option>
                                <option value="C">Custom</option>
                            </select>
                        </td>
                        <td style="width: 15%">
                            <select class="form-control jenis_parameter" style="width:100pt" name="jenis_parameter[${id_html}]" id="jenis_parameter_${id_html}" onchange="setDataJenisParameter(${id_html})" >
                                <option value=""></option>
                            </select>
                        </td>


                        <td style="width: 35%">
                            <select class="form-control satuan_parameter" name="satuan_parameter[${id_html}]" id="satuan_parameter_${id_html}" onchange="setDataSatuanParameter(${id_html})">
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

      $('#jenis_parameter_' + id_html).select2({
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

      sorting()
      setDataJenisParameter(id_html);
      setDataPaketParameter(id_html);
      setDataSatuanParameter(id_html);
    }

    function removeRow(row) {
      var count_table_tbody_tr = $("#table-parameter tbody .tr_row").length;

      // console.log(count_table_tbody_tr-1);



      if (count_table_tbody_tr > 1) {
        $("#table-parameter tbody .tr_row#row_" + row).remove();

        getSubAmount()
      }
      sorting()
    }

    function sorting() {

      $("#table-parameter tbody .tr_row").each(function(i, element) {
        console.log($(element).find('h5'));
        $(element).find('h5').html((i + 1));
        $(element).find('.type_parameter').prop("id", "type_parameter_" + (i + 1));
        $(element).find('.type_parameter').prop("name", "type_parameter[" + (i + 1) + "]");
        $(element).find('.type_parameter').attr("onchange", "setDataPaketParameter(" + (i + 1) + ")");

        $(element).find('.jenis_parameter').prop("id", "jenis_parameter_" + (i + 1));
        $(element).find('.jenis_parameter').prop("name", "jenis_parameter[" + (i + 1) + "]");
        $(element).find('.jenis_parameter').attr("onchange", "setDataJenisParameter(" + (i + 1) + ")");

        $(element).find('.satuan_parameter').prop("id", "satuan_parameter_" + (i + 1));
        if ($(element).find('.satuan_parameter').attr("multiple") != undefined) {

          $(element).find('.satuan_parameter').prop("name", "satuan_parameter[" + (i + 1) + "][]");
        } else {
          $(element).find('.satuan_parameter').prop("name", "satuan_parameter[" + (i + 1) + "]");
        }
        $(element).find('.satuan_parameter').attr("onchange", "setDataSatuanParameter(" + (i + 1) + ")");

        $(element).find('.harga_parameter').prop("id", "harga_parameter_" + (i + 1));
        $(element).find('.harga_parameter').prop("name", "harga_parameter[" + (i + 1) + "]");


        $(element).find('.btn-add-row').attr("data-row", (i + 1));
        $(element).find('.btn-add-row').attr("onclick", "addRow(" + (i + 1) + ")");


        $(element).find('.btn-remove-row').attr("data-row", (i + 1));
        $(element).find('.btn-remove-row').attr("onclick", "removeRow(" + (i + 1) + ")");

        $(element).prop("id", "row_" + (i + 1));
      })
    }

    function getSubAmount() {
      var tableParameterLength = $("#table-parameter tbody .tr_row").length;
      var totalSubAmount = 0;

      for (x = 0; x < tableParameterLength; x++) {
        var tr = $("#table-parameter tbody .tr_row")[x];
        var count = $(tr).attr('id');

        count = Number(count.substring(4));


        totalSubAmount = Number(totalSubAmount) + Number($("#harga_parameter_" + count).val());
      } // /for

      totalSubAmount = Number(totalSubAmount);


      $("#subamount_harga_parameter").val(totalSubAmount);

      if ($('#subamount_harga_parameter').val() != null && $('#subamount_harga_parameter').val() != 0 && $(
          '#subamount_harga_parameter').val() != '') {
        $('#btn-simpan-lanjutkan').show();
        $('.btn-lanjutkan').hide();
      } else {
        $('#btn-simpan-lanjutkan').hide();
        $('.btn-lanjutkan').show();
      }
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
                  document.location = response.urlNextStep;
                });
            } else {
              var pesan = "";
              var data_pesan = response.pesan;
              const wrapper = document.createElement('div');

              if (typeof(data_pesan) == 'object') {
                jQuery.each(data_pesan, function(key, value) {
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
    })

    $("#satuan_parameter_1").select2({
      ajax: {
        url: "{{ route('elits-permohonan-uji-klinik.get-parameter-dan-harga') }}",
        type: "POST",
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return {
            _token: CSRF_TOKEN,
            type_parameter: "P",
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

          // getSubAmount()
        },
        cache: true,
      },
      placeholder: 'Pilih parameter',
      theme: 'classic',
      allowClear: true
    });
  </script>
@endsection
