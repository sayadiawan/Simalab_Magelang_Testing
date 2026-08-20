@extends('masterweb::template.admin.layout')
@section('title')
  Parameter Paket Klinik
@endsection

@section('content')
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ url('/home') }}">
                    <i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a>
                </li>

                <li class="breadcrumb-item">
                  <a href="{{ url('/elits-parameter-paket-klinik') }}">Parameter Paket
                    Klinik</a>

                </li>
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
        action="{{ route('elits-parameter-paket-klinik.update', $item->id_parameter_paket_klinik) }}" method="POST">

        @csrf
        @method('PUT')


        <div class="form-group">
          <label for="name_parameter_paket_klinik">Nama Parameter Paket</label>

          <input type="text" class="form-control" id="name_parameter_paket_klinik" name="name_parameter_paket_klinik"
            placeholder="Nama parameter paket klinik.."
            value="{{ $item->name_parameter_paket_klinik ?? old('name_parameter_paket_klinik') }}">
        </div>


        <div class="parameter_jenis">
          @php
            $no = 1;
          @endphp
          @foreach ($item->parameterpaketjenisklinik as $parameterpaketjenisklinik)
            <div class="form-group parameter_jenis_card" id="parameter_jenis_card_1">
              <div class="card-body ">
                <button type="button" class="close" onclick="minus(1)" id="minus_1" data-dismiss="modal"
                  aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>

                <h5 class="card-title">
                  <center>Parameter Jenis Klinik {{ $no }}</center>
                </h5>

                <div class="form-group">
                  <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

                  <select class="form-control" name="parameter_jenis_klinik[{{ $no - 1 }}]"
                    id="parameter_jenis_klinik_{{ $no }}">
                    <option value=""></option>
                    <option value="{{ $parameterpaketjenisklinik->parameterjenisklinik->id_parameter_jenis_klinik }}"
                      selected>{{ $parameterpaketjenisklinik->parameterjenisklinik->name_parameter_jenis_klinik }}
                    </option>
                  </select>
                </div>

                <div class="form-group parameter_satuan_klinik">
                  <label for="parameter_satuan_klinik">Parameter Satuan Klinik</label>

                  <select class="form-control" name="parameter_satuan_klinik[{{ $no - 1 }}][]"
                    id="parameter_satuan_klinik_{{ $no }}" multiple>
                    <option value=""></option>
                    @foreach ($parameterpaketjenisklinik->parametersatuanpaketklinik as $parametersatuanpaketklinik)
                      <option
                        value="{{ $parametersatuanpaketklinik->parametersatuanklinik->id_parameter_satuan_klinik }}"
                        selected>{{ $parametersatuanpaketklinik->parametersatuanklinik->name_parameter_satuan_klinik }}
                      </option>
                    @endforeach

                  </select>
                </div>
                @if ($no == count($item->parameterpaketjenisklinik))
                  <button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i
                      class="fas fa-plus"></i>
                    Parameter Jenis Klinik</button>
                @endif
              </div>
            </div>

            <script>
              $(function() {



                var CSRF_TOKEN = $('#csrf-token').val();

                $("#parameter_jenis_klinik_{{ $no }}").select2({
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
                  allowClear: true
                });


                $("#parameter_jenis_klinik_{{ $no }}").change(function(e) {
                  $("#parameter_satuan_klinik_{{ $no }}").val(0).trigger('change');
                  $("#parameter_satuan_klinik_{{ $no }}").select2({
                    ajax: {
                      url: "{{ route('getParameterSatuanKlinik') }}",
                      type: "post",
                      dataType: 'json',
                      delay: 250,
                      data: function(params) {
                        return {
                          _token: CSRF_TOKEN,
                          search: params.term, // search term
                          param: $("#parameter_jenis_klinik_{{ $no }}").val()
                        };
                      },
                      processResults: function(response) {
                        return {
                          results: response
                        };
                      },
                      cache: true
                    },
                    multiple: true,
                    allowClear: true,
                    theme: "classic"
                  });

                  $("#parameter_satuan_klinik_{{ $no }}").on("select2:select", function(evt) {
                    var element = evt.params.data.element;
                    var $element = $(element);

                    $element.detach();
                    $(this).append($element);
                    $(this).trigger("change");
                  });
                })



                $("#parameter_satuan_klinik_{{ $no }}").select2({
                  ajax: {
                    url: "{{ route('getParameterSatuanKlinik') }}",
                    type: "post",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                      return {
                        _token: CSRF_TOKEN,
                        search: params.term, // search term
                        param: $("#parameter_jenis_klinik_{{ $no }}").val()
                      };
                    },
                    processResults: function(response) {
                      return {
                        results: response
                      };
                    },
                    cache: true
                  },
                  multiple: true,
                  allowClear: true,
                  theme: "classic"
                });

                $("#minus_{{ $no }}").click(function() {

                  sorting()
                });

                var selectedValues = new Array();
                @php
                  $i = 0;
                @endphp
                @foreach ($parameterpaketjenisklinik->parametersatuanpaketklinik as $parametersatuanpaketklinik)

                  selectedValues[parseInt("{{ $i }}")] =
                    "{{ $parametersatuanpaketklinik->parametersatuanklinik->id_parameter_satuan_klinik }}";
                  @php
                    $i++;
                  @endphp
                @endforeach
                $("#parameter_satuan_klinik_{{ $no }}").val(selectedValues).change();

                $("#parameter_satuan_klinik_{{ $no }}").on("select2:select", function(evt) {
                  var element = evt.params.data.element;
                  var $element = $(element);

                  $element.detach();
                  $(this).append($element);
                  $(this).trigger("change");
                });
              })
            </script>
            @php
              $no++;
            @endphp
          @endforeach
        </div>



        <div class="form-group">
          <label for="harga_parameter_paket_klinik">Harga Parameter Paket (Rupiah)</label>

          <input type="number" class="form-control" id="harga_parameter_paket_klinik" name="harga_parameter_paket_klinik"
            placeholder="Harga parameter paket klinik.."
            value="{{ $item->harga_parameter_paket_klinik ?? old('harga_parameter_paket_klinik') }}">
        </div>
        <br>

      </form>
      <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
      <button type="button" onclick="document.location='{{ url('/elits-parameter-paket-klinik') }}'"
        class="btn btn-light">Kembali</button>
    </div>
  </div>

  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    function goBack() {
      window.history.back();
    }

    // $(document).ready(function() {
    $(function() {




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
                  document.location = '/elits-parameter-paket-klinik';
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
    var no = parseInt("{{ count($item->parameterpaketjenisklinik) }}");

    function minus(no) {
      var count = $(".parameter_jenis .parameter_jenis_card").children().length;
      if (count > 1) {
        $('#parameter_jenis_card_' + no).remove();
        sorting()
        if (no == count) {
          $('#parameter_jenis_card_' + (count - 1) + ' .card-body').append(
            '<button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i> Sub Baku Mutu</button>'
          )
          $("#tambah").click(function() {
            tambah(no + 1)
            sorting()
          });
        }
      }

    }

    function tambah(no) {
      $('#tambah').remove();

      var new_field = $(`

            <div class="form-group parameter_jenis_card" id="parameter_jenis_card_` + no + `">
              <div class="card-body ">
                <button type="button" class="close" onclick="minus(` + no + `)" id="minus_` + no + `" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
                <h5 class="card-title">
                  <center>Parameter Jenis Klinik ` + no + `</center>
                </h5>
                <div class="form-group">
                  <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

                  <select class="form-control" name="parameter_jenis_klinik[` + (no - 1) +
        `]" id="parameter_jenis_klinik_` + no + `">
                    <option value=""></option>
                  </select>
                </div>

                <div class="form-group parameter_satuan_klinik">
                  <label for="parameter_satuan_klinik">Parameter Satuan Klinik</label>

                  <select class="form-control" name="parameter_satuan_klinik[` + (no - 1) +
        `][]" id="parameter_satuan_klinik_` + no + `" multiple>
                    <option value=""></option>
                  </select>
                </div>

                <button type="button" id="tambah" class="tambah btn btn-primary btn-lg btn-block"><i class="fas fa-plus"></i>
                  Parameter Jenis Klinik</button>
              </div>
            </div>`);
      $(".parameter_jenis").append(new_field);
      $(function() {
        var CSRF_TOKEN = $('#csrf-token').val();
        // $("#minus_"+(no+1)).click(function() {
        // minus(no+1)
        // sorting()
        // });
        $("#parameter_jenis_klinik_" + no).select2({
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
          allowClear: true
        });
        $("#parameter_jenis_klinik_" + no).change(function(e) {
          $("#parameter_satuan_klinik_" + no).val(0).trigger('change');

          $("#parameter_satuan_klinik_" + no).select2({
            ajax: {
              url: "{{ route('getParameterSatuanKlinik') }}",
              type: "post",
              dataType: 'json',
              delay: 250,
              data: function(params) {
                return {
                  _token: CSRF_TOKEN,
                  search: params.term, // search term
                  param: $("#parameter_jenis_klinik_" + no).val()
                };
                var element = params.data.element;
                var $element = $(element);

                $element.detach();
              },
              processResults: function(response) {
                return {
                  results: response
                };
              },
              cache: true
            },
            multiple: true,
            theme: "classic"
          }).on("select2:select", function(evt) {
            var elm = evt.params.data.element;
            console.log(evt);
            // $elm = $(elm);
            // $t = $(this);
            // $t.append($elm);
            // $t.trigger('change.select2');
            // var element = evt.params.data.element;
            // var data = evt.params.data;
            // var save_data = [];
            // // var $element = $(element);
            // // console.log($(this).select2('data'));
            // // var new_data = $.grep($(this).select2('data'), function(value) {
            // //   if (value['id'] == data.id) {
            // //     save_data = value;
            // //   }
            // //   return value['id'] != data.id;
            // // });
            // $.each($(this).select2('data'), function(i, item) {

            //   if (data.id != item.id) {
            //     // $("#parameter_satuan_klinik_" + no).append($('<option>', {
            //     //   value: item.id,
            //     //   text: item.text
            //     // }));
            //     save_data.push(item.id);
            //     $("#parameter_satuan_klinik_" + no).val(save_data);
            //     $("#parameter_satuan_klinik_" + no).trigger("change");
            //   }
            // });

            // save_data.push(data.id);
            // console.log(save_data);
            // $("#parameter_satuan_klinik_" + no).val(save_data);
            // $("#parameter_satuan_klinik_" + no).trigger("change");
            // save_data.push(data.id)
            // // console.log(save_data);
            // // new_data.push(save_data);
            // $(this).val(save_data);
            // // console.log($(this).select2('data'));


            // $(this).detach();
            // console.log($element);
            // $(this).append($element);

          }).on("select2:unselect", function(evt) {
            // var element = evt.params.data.element;
            var data = evt.params.data;
            // var $element = $(element);
            console.log(data);


            // $element.detach();
            // console.log($element);
            // $(this).append($element);
            // $(this).trigger("change");
          });
        })





        $("#tambah").click(function() {
          tambah(no + 1)
          sorting()
        });
        sorting()

      })




    }




    function sorting() {

      $(".parameter_jenis .parameter_jenis_card").each(function(i, element) {
        // $(element).find('.card-title');
        // console.log( $(element).find('.card-title'))
        $(element).find('.card-title').html("<center>Parameter Jenis Klinik " + (i + 1) + "</center>");
        $(element).find('.close').prop("id", "minus_" + (i + 1));
        $(element).find('.close').attr("onclick", "minus(" + (i + 1) + ")");
        $(element).prop("id", "parameter_jenis_card_" + (i + 1));
        $(element).find('#parameter_jenis_klinik_' + (i + 1)).prop("name", "parameter_jenis_klinik[" + (i) + "]");
        $(element).find('#parameter_satuan_klinik_' + (i + 1)).prop("name", "parameter_satuan_klinik[" + (i) + "][]");
        // $("#minus_"+(i+1)).click(function() {
        // minus(i+1)
        // sorting()
        // });


        // $('.card-title').text();
      }).on("select2:select", function(evt) {
        var element = evt.params.data.element;
        var $element = $(element);

        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
      });
    }

    $("#tambah").click(function() {
      tambah(no + 1)
    });

    // })
  </script>
@endsection
