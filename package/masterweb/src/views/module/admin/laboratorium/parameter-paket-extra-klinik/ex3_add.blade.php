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
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-paket-klinik') }}">Parameter Paket
                    Klinik</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
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
        action="{{ route('elits-parameter-paket-klinik.store') }}" method="POST">
        @csrf

        <div class="form-group">
          <label for="name_parameter_paket_klinik">Nama Parameter Paket</label>

          <input type="text" class="form-control" id="name_parameter_paket_klinik" name="name_parameter_paket_klinik"
            placeholder="Nama parameter paket klinik.." value="{{ old('name_parameter_paket_klinik') }}">
        </div>

        {{-- start add field parameter --}}
        <div class="row grid-margin" id="parameter">

          <div class="col-md-12">
            <div class="card">
              <div class="card-header">Setup Parameter Jenis</div>

              <div class="card-body">
                <div class="col-md-12 set-grid-parameter">

                  {{-- fleksibel --}}
                  <div class="card card-main mb-2">
                    <div class="card-body">
                      <div class="row grid-margin">
                        <div class="col-md-10 parameter_jenis_klinik">
                          <select class="form-control" name="parameter_jenis_klinik[1]" id="parameter_jenis_klinik_1"
                            style="width: 100%">
                            <option value=""></option>
                          </select>
                        </div>

                        <div class="col-md-2">
                          <button type="button" class="btn float-right btn-danger btn-sm btn-remove-parameter">Hapus
                            Jenis
                            Parameter</button>
                        </div>
                      </div>

                      {{-- display opptional --}}
                      <div class="row" id="display-parameter-satuan-paket-1">
                        <div class="col-md-12">
                          <div id="form-detail-parameter-satuan-paket-1" class="mb-3">
                            <div class="table-responsive">
                              <table class="table" style="width: 100%">
                                <thead>
                                  <tr>
                                    <th style="width:50%;">Nama Parameter</th>
                                    <th style="width:30%;">Urutan</th>
                                    <th style="width:20%;">Aksi</th>
                                  </tr>
                                </thead>

                                <tbody id="parameter-satuan-paket">

                                </tbody>
                              </table>
                            </div>
                          </div>

                          <div class="row">
                            <button type="button" class="btn mt-2 mx-auto btn-add-parameter-satuan-paket btn-info btn-sm"
                              data-parameter-satuan-paket-param="0">Tambah Parameter Satuan</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {{-- end of fleksibel --}}

                </div>

                <div class="col-md-12">
                  <div class="row">
                    <button type="button" class="btn btn-success mx-auto btn-sm btn-add-parameter" data-param="1">Tambah
                      Jenis Parameter</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        {{-- end add field parameter --}}

        <div class="form-group mb-3">
          <label for="harga_parameter_paket_klinik">Harga Parameter Paket (Rupiah)</label>

          <input type="number" class="form-control" id="harga_parameter_paket_klinik" name="harga_parameter_paket_klinik"
            placeholder="Harga parameter paket klinik.." value="{{ old('harga_parameter_paket_klinik') }}">
        </div>

        <br>

        <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
        <button type="button" onclick="document.location='{{ url('/elits-parameter-paket-klinik') }}'"
          class="btn btn-light">Kembali</button>
      </form>
    </div>
  </div>

  <script src="{{asset('assets/admin/cdn-local/js/sweetalert.min.js')}}"></script>

  <script src="{{asset('assets/admin/cdn-local/js/jquery.form.min.js')}}"
    integrity="sha384-qlmct0AOBiA2VPZkMY3+2WqkHtIQ9lSdAsAn5RUJD/3vA5MKDgSGcdmIv4ycVxyn" crossorigin="anonymous"></script>

  <script>
    function goBack() {
      window.history.back();
    }

    $(document).ready(function() {
      var CSRF_TOKEN = "{{ csrf_token() }}";

      $("#parameter_jenis_klinik_1").select2({
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
        placeholder: "Parameter Jenis Klinik"
      });

      var id_html = 1;

      // logic fleksibel kolom
      $("body").on('click', '.btn-add-parameter', function() {
        var count_html = $(this).data('param');

        var html = `
        <div class="card card-main mb-2">
          <div class="card-body">
            <div class="row grid-margin">
              <div class="col-md-10 parameter_jenis_klinik">
                <select class="form-control" name="parameter_jenis_klinik[${count_html}]" id="parameter_jenis_klinik_${count_html}"
                  style="width: 100%">
                  <option value=""></option>
                </select>
              </div>

              <div class="col-md-2">
                <button type="button" class="btn float-right btn-danger btn-sm btn-remove-parameter">Hapus Jenis Parameter</button>
              </div>
            </div>

            {{-- display opptional --}}
            <div class="row" id="display-parameter-satuan-paket-${count_html}">
              <div class="col-md-12">
                <div id="form-detail-parameter-satuan-paket-${count_html}" class="mb-3">
                  <div class="table-responsive">
                    <table class="table" style="width: 100%">
                      <thead>
                        <tr>
                          <th style="width:50%;">Nama Parameter</th>
                          <th style="width:30%;">Urutan</th>
                          <th style="width:20%;">Aksi</th>
                        </tr>
                      </thead>

                      <tbody id="parameter-satuan-paket">

                      </tbody>
                    </table>
                  </div>
                </div>

                <div class="row">
                  <button type="button" class="btn mt-2 mx-auto btn-add-parameter-satuan-paket btn-info btn-sm"
                    data-parameter-satuan-paket-param="${count_html}">Tambah Parameter Satuan</button>
                </div>
              </div>
            </div>
          </div>
        </div>`;

        $('.set-grid-parameter').append(html);
        $(this).data('param', id_html++);

        $("#parameter_jenis_klinik_" + count_html).select2({
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
          placeholder: "Parameter Jenis Klinik"
        });

        $("#parameter_jenis_klinik_" + count_html).change(function(e) {
          $(".select2_parameter_satuan_klinik_" + count_html).val(0).trigger('change');

          $(".select2_parameter_satuan_klinik_" + count_html).select2({
            ajax: {
              url: "{{ route('getParameterSatuanKlinik') }}",
              type: "post",
              dataType: 'json',
              delay: 250,
              data: function(params) {
                return {
                  _token: CSRF_TOKEN,
                  search: params.term, // search term
                  param: $("#parameter_jenis_klinik_" + count_html).val()
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
            theme: "classic",
            placeholder: "Parameter Satuan Klinik"
          });
        })
      });

      var param = 0;
      var no_urut = 1;

      $("body").on('click', '.btn-add-parameter-satuan-paket', function() {
        var obj = $(this);
        var param = $(this).data('parameter-satuan-paket-param');
        param++;

        var html =
          `<tr>
              <td>
                <select class="form-control select2_parameter_satuan_klinik_${id_html}" name="parameter_satuan_klinik[${id_html}][${param}]" id="parameter_satuan_klinik_${param}" style="width: 100%">
                  <option value=""></option>
                </select>
              </td>

              <td>
                <input type="number" class="form-control" name="sorting_parameter_satuan_paket_klinik[${id_html}][${param}]" id="sorting_parameter_satuan_paket_klinik_${param}" value="` +
          (no_urut++) + `">
              </td>

              <td>
                  <button type="button" class="btn btn-sm btn-danger btn-remove-parameter-satuan-paket">Hapus Parameter Satuan</button>
              </td>
          </tr>`;


        obj.parent().parent().parent().parent().find('#parameter-satuan-paket').append(html);

        $(".select2_parameter_satuan_klinik_" + id_html).select2({
          ajax: {
            url: "{{ route('getParameterSatuanKlinik') }}",
            type: "post",
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                _token: CSRF_TOKEN,
                search: params.term, // search term
                param: $("#parameter_jenis_klinik_" + id_html).val()
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
          theme: "classic",
          placeholder: "Parameter Satuan Klinik"
        });
      })

      $('body').on('click', '.btn-remove-parameter', function() {
        if ($('.card-main').length > 1) {
          $(this).parent().parent().parent().parent().remove();
        } else {
          swal({
            title: "Perhatian!",
            text: "Anda harus menyisakan setidaknya satu parameter untuk paket Anda!",
            icon: "warning",
            button: "Mengerti!",
          });
        }
      })

      $('body').on('click', '.btn-remove-parameter-satuan-paket', function() {
        $(this).parent().parent().remove();
      });

      $('.btn-simpan').on('click', function() {
        $('#form').ajaxForm({
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
    });
  </script>
@endsection
