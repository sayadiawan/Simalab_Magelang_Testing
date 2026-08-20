

@extends('masterweb::template.admin.layout')
@section('title')
    User Management
@endsection

@section('content')

    <script src="{{asset('assets/admin/cdn-local/js/firebase.js')}}"></script>
    <script src= "{{ asset('assets/admin/js/firebase-js/firebase/config.js')}}"></script>
    <script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js')}}"></script>
 

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
            <div class="template-demo">
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/')}}">Import Excel Management</a></li>
                 
                </ol>
                </nav>
            </div>
            </div>
        </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <button type="button" class="btn btn-primary mr-5" data-toggle="modal" data-target="#importExcel">
                IMPORT EXCEL
            </button>
    
            <!-- Import Excel -->
            <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form method="post" action="/elits-excel/formImports" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                            </div>
                            <div class="modal-body">
    
                                {{ csrf_field() }}
    
                                <label>Pilih file excel</label>
                                <div class="form-group">
                                    <input type="file" name="file" required="required">
                                </div>
    
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Import</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
 
        </div>
    </div>


    <script>
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