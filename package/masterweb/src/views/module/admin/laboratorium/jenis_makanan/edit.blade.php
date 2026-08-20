@extends('masterweb::template.admin.layout')
@section('title')
  Jenis Makanan Management
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-jenis-makanan') }}">Jenis Makanan
                    Management</a></li>
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

      {{-- <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-packet.update', [$id])}}"
            method="POST"> --}}
      @csrf
      <input type="hidden" value="PUT" name="_method">

      <div class="form-group">
        <label for="name_packet">Nama Jenis Makanan</label>
        <input type="text" class="form-control" id="name_jenis_makanan" name="name_jenis_makanan"
          value="{{ $jenis_makanan->name_jenis_makanan }}" placeholder="Name Jenis Makanan" required>
      </div>

      <div class="form-group">
        <label for="olahan_jenis_makanan">Olahan Jenis Makanan</label>
        <input type="text" class="form-control" value="{{ $jenis_makanan->olahan_jenis_makanan }}"
          id="olahan_jenis_makanan" name="olahan_jenis_makanan" placeholder="Olahan Jenis Makanan" required>
      </div>

      {{-- <div class="form-group">
                <label for="price_jenis_makanan">Harga Jenis Makanan</label>
                <div class="input-group">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            Rp.
                        </span>
                    </div>
                    <input type="number" class="form-control" id="price_jenis_makanan"
                        value="{{$jenis_makanan->price_jenis_makanan}}" name="price_jenis_makanan" type="number"
                        placeholder="Isikan Harga Jenis Makanan">
                </div>
            </div>
            --}}


      <button type="submit" class="btn btn-primary mr-2" id="submit">Simpan</button>
      <button onclick="goBack()" class="btn btn-light">Kembali</button>
      {{--
        </form> --}}
    </div>
  </div>

  <script>
    function goBack() {
      window.history.back();
    }

    $("#submit").click(function() {
      var name_jenis_makanan = $("#name_jenis_makanan").val()
      // var price_jenis_makanan = $("#price_jenis_makanan").val()
      var olahan_jenis_makanan = $("#olahan_jenis_makanan").val()

      let _token = "{{ csrf_token() }}"

      var url = "{{ route('elits-jenis-makanan.update', ['#']) }}"
      url = url.replace('#', '{{ $id }}')


      $.ajax({
        url: url,
        type: "PUT",
        data: {
          name_jenis_makanan: name_jenis_makanan,
          // price_jenis_makanan:price_jenis_makanan,
          olahan_jenis_makanan: olahan_jenis_makanan,
          id: "{{ $id }}",
          _token: _token
        },
        success: function(response) {

          var url = "{{ route('elits-jenis-makanan.index') }}";
          window.location.href = url;

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseJSON.message);
        }
      });


    });
  </script>
@endsection
