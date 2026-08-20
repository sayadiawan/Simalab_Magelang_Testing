@extends('masterweb::template.admin.layout')
@section('title')
  Data Jenis Makanan
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-jenis-makanan') }}">Data Jenis Makanan</a>
                </li>
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
      <!-- <form enctype="multipart/form-data" class="forms-sample" action="{{ route('elits-packet.store') }}"  method="POST"> -->
      @csrf
      <div class="form-group">
        <label for="name_jenis_makanan">Nama Jenis Makanan</label>
        <input type="text" class="form-control" id="name_jenis_makanan" name="name_jenis_makanan"
          placeholder="Name Jenis Makanan" required>
      </div>

      <div class="form-group">
        <label for="olahan_jenis_makanan">Olahan Jenis Makanan</label>
        <input type="text" class="form-control" id="olahan_jenis_makanan" name="olahan_jenis_makanan"
          placeholder="Olahan Jenis Makanan" required>
      </div>

      {{-- <div class="form-group">
            <label for="price_jenis_makanan">Harga Jenis Makanan</label>
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text">
                        Rp.
                    </span>
                </div>
                <input type="number" class="form-control" id="price_jenis_makanan" name="price_jenis_makanan"
                    type="number" placeholder="Isikan Harga Jenis Makanan">
            </div>
        </div> --}}



      <br>


      <!-- <div class="form-group">
                      <label for="favicon_sampletype">Favicon Sample Type</label>
                      <input type="text" class="form-control" id="favicon_sampletype" name="favicon_sampletype" placeholder="Code Sample Type" required >
                  </div> -->


      <button type="submit" class="btn btn-primary mr-2" id="submit">Simpan</button>
      <button onclick="goBack()" class="btn btn-light">Kembali</button>
      <!-- </form> -->
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



      $.ajax({
        url: "{{ route('elits-jenis-makanan.store') }}",
        type: "POST",
        data: {
          name_jenis_makanan: name_jenis_makanan,
          // price_jenis_makanan:price_jenis_makanan,
          olahan_jenis_makanan: olahan_jenis_makanan,
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


    // function myFunction() {

    //     var kode= document.getElementById("kode-user").value;
    //     var name= document.getElementById("name").value;
    //     var username= document.getElementById("username").value;
    //     var email= document.getElementById("email").value;

    //     var x = document.getElementById("level").selectedIndex;
    //     var level=document.getElementsByTagName("option")[x].value;



    //     if(level=="09405c01-092e-4eb7-a1d7-b511c74f6cda"){

    //         firebase.database().ref('users/'+kode).set({
    //             username: username,
    //             name: name,
    //             email: email,
    //             role:"user"
    //         }).then(function() {
    //             // window.location.href = "./dashboard"


    //         }).catch(function(error) {
    //             // An error happened.
    //         });


    //     }



    // }
  </script>
@endsection
