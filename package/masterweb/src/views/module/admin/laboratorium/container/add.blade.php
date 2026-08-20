

@extends('masterweb::template.admin.layout')
@section('title')
    Sample Type Management
@endsection

@section('content')

   

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
            <div class="template-demo">
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/elits-containers')}}">Container Management</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-containers.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <label for="name_container">Nama Wadah</label>
                <input type="text" class="form-control" id="name_container" name="name_container" placeholder="Name Wadah" required >
            </div>
            

            <br>


            <!-- <div class="form-group">
                <label for="favicon_sampletype">Favicon Sample Type</label>
                <input type="text" class="form-control" id="favicon_sampletype" name="favicon_sampletype" placeholder="Code Sample Type" required >
            </div> -->


            <button type="submit" class="btn btn-primary mr-2" >Simpan</button>
            <button  onclick="goBack()"  class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>


    <script>
    function goBack() {
          window.history.back();
    }
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