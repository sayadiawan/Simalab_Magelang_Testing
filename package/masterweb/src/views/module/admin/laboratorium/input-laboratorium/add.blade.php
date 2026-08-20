

@extends('masterweb::template.admin.layout')
@section('title')
    Laboratorium Management
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-input-laboratorium')}}">Laboratorium Management</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-input-laboratorium.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <label for="kode_laboratorium">Kode Laboratorium</label>
                <input type="text" class="form-control" id="kode_laboratorium" name="kode_laboratorium" placeholder="Kode Laboratorium" required >
            </div>
            <div class="form-group">
                <label for="nama_laboratorium">Nama Laboratorium</label>
                <input type="text" class="form-control" id="nama_laboratorium" name="nama_laboratorium" placeholder="Nama Laboratorium" required >
            </div>


            <div class="form-group">
                <label for="nama_ttd_kepala_laboratorium">Nama Lengkap Kepala Laboratorium</label>
                <input type="text" class="form-control" id="nama_ttd_kepala_laboratorium" name="nama_ttd_kepala_laboratorium" placeholder="Nama Lengkap Kepala Laboratorium" required >
            </div>
            <div class="form-group">
                <label for="nip_kepala_laboratorium">NIP Kepala Laboratorium</label>
                <input type="text" class="form-control" id="nip_kepala_laboratorium" name="nip_kepala_laboratorium" placeholder="NIP Kepala Laboratorium" required >
            </div>
         
            <div class="form-group">
                <label for="koordinator_id">Koordinator</label>
                <select id="type" name="koordinator_id" class="form-control" id="koordinator_id" required>
                    <option value="" selected="selected" disabled>Pilih Koordinator</option>
                    @foreach ($koordinators as $koordinator)
                        <option value="{{$koordinator->id}}">{{$koordinator->name}}</option>
                    @endforeach
                   
                </select>
            </div>


            <div class="form-group" id="fields">
                <div class="form-row align-items-center" id="fields-1">
                    <div class="col-auto" id="field">
                        <label id="label-1" for="name">Progress name</label>
                        <input id="input-1" type="text" class="form-control mb-2 input" name="progress_name[]" id="inlineFormInput" placeholder="Progress name">
                    </div>

                    <div class="col-auto" id="value">
                        <label id="label-value-1" for="name">Link</label>
                        <input id="input-value-1" type="text" class="form-control mb-2 value" name="link_name[]" id="inlineFormInput" placeholder="link name">
                    </div>
                  

                    
                    <div class="col-auto" id="action">  
                        <button type="button" id="tambah" class="btn btn-primary btn-circle active"><i class="fa fa-plus" aria-hidden="true"></i></button>  
                    </div>
                </div>
              
                     
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
         var i =1
    function goBack() {
          window.history.back();
    }

    $("#tambah").click(function() {
            
        addField(i);

    });

    function addField(count) {
          
          $('#tambah').remove();

          var new_field = $('<div class="form-row align-items-center" id="fields-'+(count+1)+'"><div class="col-auto" id="field"><label id="label-'+(count+1)+'" for="name">Progress name </label><input id="input-'+(count+1)+'" name="progress_name[]" type="text" class="form-control mb-2 input" id="inlineFormInput" placeholder="Progress name"></div><div class="col-auto" id="value"><label id="label-value-'+(count+1)+'" for="name">Link</label><input id="input-value-'+(count+1)+'" type="text" class="form-control mb-2 value" name="link_name[]" id="inlineFormInput" placeholder="Link Name"></div>  <button type="button" id="minus-'+(count+1)+'" class="btn btn-primary btn-circle active"><i class="fa fa-minus" aria-hidden="true"></i></button> <button type="button" id="tambah" class="btn btn-primary btn-circle active"><i class="fa fa-plus" aria-hidden="true"></i></button></div></div>');
        
          $("#fields").append(new_field);
          $("#minus-"+(count+1)).click(function() {
              var count_now =  $('#fields').children().last().attr('id');
              var count_before =  $('#fields').children().eq(-2).attr('id');
              count_now=count_now.replace('fields-', '');

              if(count_now==(count+1)){
                 //iya
                 var plusbutton = $('<button type="button" id="tambah" class="btn btn-primary btn-circle active"><i class="fa fa-plus" aria-hidden="true"></i></button>');
                  $('#'+count_before).append(plusbutton);
                  $('#fields-'+(count_now)).remove();
                  $("#tambah").click(function() {
                      addField(i)
                  });
              }else{
                  //tidak
                  $('#fields-'+(count+1)).remove();
              }
             
          });
          i++;
          $("#tambah").click(function() {
              addField(i)
          });
        
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