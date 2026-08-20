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
                <li class="breadcrumb-item"><a href="{{ url('/elits-parameter-paket-extra') }}">Parameter Paket Extra
                    Klinik</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Create</span></li>
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
        action="{{ route('elits-parameter-paket-extra.store') }}" method="POST">
        @csrf

        <div class="form-group">
          <label for="nama_parameter_paket_extra">Nama Parameter Paket Extra</label>

          <input type="text" class="form-control" id="nama_parameter_paket_extra" name="nama_parameter_paket_extra"
            placeholder="Nama parameter paket extra.." value="{{ old('nama_parameter_paket_extra') }}">
        </div>

        <div class="form-group">
            <label for="parameter_paket_klinik">Pilih Parameter Paket Klinik:</label>
            <select class="select2-multiple" name="parameter_paket_klinik[]" multiple="multiple" style="width: 100%;" placeholder="Pilih Paket">
                @foreach($parameter_paket as $paket)
                    <option value="{{ $paket->id_parameter_paket_klinik }}">{{ $paket->name_parameter_paket_klinik }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
          <label for="harga_parameter_paket_extra">Harga Parameter Paket (Rupiah)</label>

          <input type="number" class="form-control" id="harga_parameter_paket_extra" name="harga_parameter_paket_extra"
            placeholder="Harga parameter paket extra.." value="{{ old('harga_parameter_paket_extra') }}">
        </div>

        <br>

        <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
        <button type="button" onclick="document.location='{{ url('/elits-parameter-paket-extra') }}'"
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

    $(function() {
      var CSRF_TOKEN = "{{ csrf_token() }}";

      $(document).ready(function() {
          $('.select2-multiple').select2({
              placeholder: "Pilih Paket..",
              allowClear: true,
              theme: "bootstrap4",
          });
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
                  document.location = '/elits-parameter-paket-extra';
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
