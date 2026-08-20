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

        <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />

        <div class="form-group">
          <label for="parameter_jenis_klinik">Parameter Jenis Klinik</label>

          <select class="form-control" name="parameter_jenis_klinik" id="parameter_jenis_klinik">
            <option value="{{ $item->parameter_jenis_klinik }}" selected>
              {{ $item->parameterjenisklinik->name_parameter_jenis_klinik }}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="parameter_satuan_klinik">Parameter Satuan Klinik</label>

          <select class="form-control" name="parameter_satuan_klinik[]" id="parameter_satuan_klinik" multiple>
            @foreach ($item_satuan_paket as $isp)
              <option value="{{ $isp->parameter_satuan_klinik }}" selected>
                {{ $isp->parametersatuanklinik->name_parameter_satuan_klinik }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="name_parameter_paket_klinik">Nama Parameter Paket</label>

          <input type="text" class="form-control" id="name_parameter_paket_klinik" name="name_parameter_paket_klinik"
            placeholder="Nama parameter paket klinik.."
            value="{{ $item->name_parameter_paket_klinik ?? old('name_parameter_paket_klinik') }}">
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

    $(function() {
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
        allowClear: true
      });


      $("#parameter_jenis_klinik").change(function(e) {
        $("#parameter_satuan_klinik").val(0).trigger('change');
      })

      $("#parameter_satuan_klinik").select2({
        ajax: {
          url: "{{ route('getParameterSatuanKlinik') }}",
          type: "post",
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              _token: CSRF_TOKEN,
              search: params.term, // search term
              param: $("#parameter_jenis_klinik").val()
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
  </script>
@endsection
