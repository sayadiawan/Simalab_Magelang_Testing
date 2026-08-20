@extends('masterweb::template.admin.layout')
@section('title')
    User Management
@endsection

@section('content')

    <
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                                <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/elits-users')}}">Users</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-users.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <input type="hidden" class="form-control" id="root_firebase" name="root_firebase" value="{!! Ramsey\Uuid\Uuid::uuid4();!!}" >
           
                <label for="name">Nama Lengkap</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap" >
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" >
            </div>

            <div class="form-group">
                <label for="email">Alamat email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" >
            </div>

            <div class="form-group">
                <label for="email">Hak Akses</label>
                <select name="level" id="level" class="form-control">
                    @foreach ($privileges as $privilege)
                        @if($privilege->id!="7d6bc1b7-5115-4724-820d-f04744f61828")
                            <option value="{{$privilege->id}}">{{$privilege->name}}</option>
                        @endif 
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="email">Photo</label>
                <input type="file" name="photo" id="photo" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary mr-2" >Simpan</button>
            <button class="btn btn-light" onclick="goBack()">Kembali</button>
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