@extends('masterweb::template.admin.layout')
@section('title')
  Data Program
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-program') }}">Data Program</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>update</span></li>
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
        action="{{ route('elits-program.update', $item->id_program) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="name_program">Nama Program</label>

          <input type="text" class="form-control" id="name_program" name="name_program" placeholder="Name program.."
            value="{{ $item->name_program ?? old('name_program') }}" required>
        </div>

        <br>

      </form>
      <button type="submit" class="btn btn-primary mr-2 btn-simpan">Simpan</button>
      <button type="button" onclick="document.location='{{ url('/elits-program') }}'"
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
    $(document).ready(function() {
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
                    document.location = '/elits-program';
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
    })
  </script>
@endsection
