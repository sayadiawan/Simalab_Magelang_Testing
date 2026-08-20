@extends('masterweb::template.admin.layout')
@section('title')
  Edit Permohonan Uji Klinik Parameter
@endsection


@section('content')

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
              <li class="breadcrumb-item">
                <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                  Beranda</a>
              </li>

              <li class="breadcrumb-item">
                <a href="{{ url('/elits-permohonan-uji-klinik') }}">Permohonan Uji Klinik
                  Management</a>
              </li>

              <li class="breadcrumb-item">
                <a
                  href="{{ route('elits-permohonan-uji-klinik.permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}">Permohonan
                  Uji
                  Klinik Parameter
                  Management</a>
              </li>

              <li class="breadcrumb-item active" aria-current="page">
                <span>edit</span>
              </li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Edit Permohonan Uji Klinik Parameter
      </h4>
    </div>

    <div class="card-body">
      <form
        action="{{ route('elits-permohonan-uji-klinik.update-permohonan-uji-klinik-parameter', [$id_permohonan_uji_klinik, $id_permohonan_uji_paket_klinik]) }}"
        method="POST" enctype="multipart/form-data" id="form">

        @csrf

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

        <div class="form-group">
          <label for="parameter_jenis_klinik"> Jenis Parameter<span style="color: red">*</span></label>
          <select class="form-control" name="parameter_jenis_klinik" id="parameter_jenis_klinik">
            <option value="{{ $item_paket->parameter_jenis_klinik }}" selected>
              {{ $item_paket->parameterjenisklinik->name_parameter_jenis_klinik }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="type_permohonan_uji_paket_klinik">Pilih Paket<span style="color: red">*</span></label>

          <select class="form-control" name="type_permohonan_uji_paket_klinik" id="type_permohonan_uji_paket_klinik">
            <option value="">-Pilih Paket-</option>
            <option value="C" {{ $item_paket->type_permohonan_uji_paket_klinik == 'C' ? 'selected' : '' }}>Custom
            </option>
            <option value="P" {{ $item_paket->type_permohonan_uji_paket_klinik == 'P' ? 'selected' : '' }}>Paket
            </option>
          </select>
        </div>

        {{-- dinamik display set --}}
        @php
          if ($item_paket->type_permohonan_uji_paket_klinik == 'C') {
              $display_custom = 'style="display: block"';
              $display_paket = 'style="display: none"';
          } else {
              $display_custom = 'style="display: none"';
              $display_paket = 'style="display: block"';
          }
        @endphp

        <div class="card mb-3 mt-3" id="display-custom-detail-parameter" {!! $display_custom !!}>
          <div class="card-body">

            <div class="form-inline" id="content-custom-detail-parameter">

              @if ($item_paket->parameter_paket_klinik == null)
                <div class="row">
                  @php
                    $arr_parameter_paket = [];
                    
                    foreach ($data_parameter_paket as $key => $val) {
                        array_push($arr_parameter_paket, $val->parameter_satuan_klinik);
                    }
                  @endphp

                  @foreach ($data_parameter_satuan as $key => $val)
                    @php
                      if (in_array($val->id_parameter_satuan_klinik, $arr_parameter_paket)) {
                          $checked = 'checked';
                      } else {
                          $checked = '';
                      }
                    @endphp

                    <div class="col-md-3">
                      <div class="form-check mr-5 justify-content-start">
                        <label class="form-check-label">
                          <input type="checkbox" class="form-check-input parameter-custom-klinik"
                            name="parameter_custom_klinik[{{ $key }}]"
                            id="parameter_custom_klinik_{{ $key }}"
                            value="{{ $val->id_parameter_satuan_klinik }}"
                            data-harga="{{ $val->harga_satuan_parameter_satuan_klinik }}"
                            onclick="updateParameterCustom(this);" data-index="{{ $key }}"
                            {{ $checked }}>{{ $val->name_parameter_satuan_klinik }}
                          <i class="input-helper"></i></label>
                      </div>
                    </div>
                  @endforeach

                </div>
              @endif

            </div>

          </div>
        </div>

        <div id="display-paket-detail-parameter" {!! $display_paket !!}>
          <div class="form-group">
            <label for="parameter_paket_klinik">Pilih Parameter Paket</label>

            @if ($item_paket->parameter_paket_klinik != null)
              <select class="form-control parameter-paket-klinik" name="parameter_paket_klinik"
                id="parameter_paket_klinik">
                <option value="{{ $item_paket->parameter_paket_klinik }}" selected>
                  {{ $item_paket->parameterpaketklinik->name_parameter_paket_klinik }}</option>
              </select>
            @else
              <select class="form-control parameter-paket-klinik" name="parameter_paket_klinik"
                id="parameter_paket_klinik">
                <option value=""></option>
              </select>
            @endif

          </div>
        </div>

        <div class="form-group">
          <label for="harga_permohonan_uji_paket_klinik"> Harga Total (Rp.)</label>

          <input type="text" class="form-control" name="harga_permohonan_uji_paket_klinik"
            id="harga_permohonan_uji_paket_klinik" value="{{ $item_paket->harga_permohonan_uji_paket_klinik }}" readonly>
        </div>

      </form>
      <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
      {{-- <a href="javascript:void(0)" class="btn btn-primary btn-simpan">Simpan</a> --}}
      <button type="button" class="btn btn-light"
        onclick="document.location='{{ route('elits-permohonan-uji-klinik.permohonan-uji-klinik-parameter', $id_permohonan_uji_klinik) }}'">Kembali</button>
    </div>
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

      $("#parameter_jenis_klinik").select2({
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
          cache: true
        },
        allowClear: true,
        placeholder: 'Pilih jenis parameter'
      });

      /* $('#display-custom-detail-parameter').hide();
      $('#display-paket-detail-parameter').hide(); */

      $('#parameter_jenis_klinik, #type_permohonan_uji_paket_klinik').change(function(e) {
        e.preventDefault();

        $(this).closest('form').find("input").val("");

        if ($('#type_permohonan_uji_paket_klinik').val() == 'C') {
          $('#display-paket-detail-parameter').hide();
          // $('#display-custom-detail-parameter').show();
          $(this).closest('form').find("input").val("");

          $.ajax({
            type: "post",
            url: "{{ route('elits-permohonan-uji-klinik.get-parameter-custom-permohonan-uji-klinik-parameter') }}",
            data: {
              _token: CSRF_TOKEN,
              parameter_type: $('#parameter_jenis_klinik').val(),
              type_paket: $('#type_permohonan_uji_paket_klinik').val()
            },
            dataType: "json",
            success: function(response) {
              // console.log(response);

              $('#display-custom-detail-parameter').show();
              var html = '<div class="row">';

              for (let i = 0; i < response.length; i++) {
                html += '<div class="col-md-3">';
                html += '<div class="form-check mr-5 justify-content-start">';
                html += '<label class="form-check-label">';
                html +=
                  '<input type="checkbox" class="form-check-input parameter-custom-klinik" name="parameter_custom_klinik[' +
                  i +
                  ']" id="parameter_custom_klinik_' +
                  i + '" value="' + response[i].id + '" data-harga="' + response[i].harga +
                  '" onclick="updateParameterCustom(this);" data-index="' + i + '">' + response[
                    i].text;
                html += '<i class="input-helper"></i></label>';
                html += '</div>';
                html += '</div>';

                /* html += '<input type="checkbox" id="parameter_custom_klinik_' +
                  i + '" name="parameter_custom_klinik[' +
                  i +
                  ']" value="Bike">';
                html += '<label for="checkbox"> ' + response[i].text + '</label>'; */
              }

              html += '</div>';

              $('#content-custom-detail-parameter').html(html);
            },
            error: function() {
              swal("Error!", "Tidak berhasil mendapatkan data parameter!", "error");
            }
          });
        }

        if ($('#type_permohonan_uji_paket_klinik').val() == 'P') {
          $("#parameter_paket_klinik").val('').trigger('change');
          $('#display-paket-detail-parameter').show();
          $('#display-custom-detail-parameter').hide();

          $("#parameter_paket_klinik").select2({
            ajax: {
              url: "{{ route('elits-permohonan-uji-klinik.get-parameter-paket-permohonan-uji-klinik-parameter') }}",
              type: "post",
              dataType: 'json',
              delay: 250,
              data: function(params) {
                return {
                  _token: CSRF_TOKEN,
                  parameter_type: $('#parameter_jenis_klinik').val(),
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
              },
              cache: true
            },
            allowClear: true,
            placeholder: 'Pilih parameter paket'
          }).on('select2:select', function(e) {
            var data = e.params.data;
            $(this).children('[value="' + data['id'] + '"]').attr({
              'data-harga': data["harga"], //dynamic value from data array
            });
          }).val(0).trigger('change');

          $('#parameter_paket_klinik').on('select2:select', function(e) {
            var data = e.params.data.harga;
            console.log(data);
            $('#harga_permohonan_uji_paket_klinik').val(data);
          });
        }
      });

      $('.btn-simpan').on('click', function() {
        $('#form').ajaxSubmit({
          headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
          },
          success: function(response) {
            if (response.status == true) {
              swal({
                  title: "Success!",
                  text: response.pesan,
                  icon: "success"
                })
                .then(function() {
                  document.location =
                    '/elits-permohonan-uji-klinik/permohonan-uji-klinik-parameter/' +
                    response
                    .id_permohonan_uji_klinik;
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

        /* $.ajax({
          type: "POST",
          url: $('#form').attr('action'),
          data: $('#form').serialize(),
          dataType: "JSON",
          success: function(response) {
            if (response.status == true) {
              swal({
                  title: "Success!",
                  text: response.pesan,
                  icon: "success"
                })
                .then(function() {
                  document.location =
                    '/elits-permohonan-uji-klinik/permohonan-uji-klinik-parameter/' +
                    response
                    .id_permohonan_uji_klinik;
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
                  title: "Gagal!",
                  content: wrapper,
                  icon: "warning"
                });
              } else {
                swal({
                  title: "Gagal!",
                  text: response.pesan,
                  icon: "warning"
                });
              }
            }
          },
          error: function() {
            swal("Error!", "System gagal menyimpan!", "error");
          }
        }); */
      })
    });

    function updateParameterCustom(obj) {
      // console.log(obj);
      var index = $(obj).attr('data-index');
      var id = $(obj).attr('id');
      var name = $(obj).attr('name');
      var value = $(obj).attr('value');
      var harga = parseInt($(obj).attr('data-harga'));
      var IsChecked = $(obj).is(':checked');

      var harga_paket_parameter = 0;

      $('.parameter-custom-klinik').each(function(i, e) {

        if (parseInt($(this).val()) != 0) {
          if (IsChecked == true) {
            $("#parameter_custom_klinik_" + index).attr('checked', 'checked');
          } else {
            $("#parameter_custom_klinik_" + index).removeAttr('checked');
          }
        }

        if ($(this).is(':checked')) {
          harga_paket_parameter += $(this).data('harga');
        }
      });

      $('#harga_permohonan_uji_paket_klinik').val(harga_paket_parameter);
    }
  </script>
@endsection
