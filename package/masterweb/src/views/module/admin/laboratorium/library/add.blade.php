

@extends('masterweb::template.admin.layout')
@section('title')
    Library Management
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-libraries')}}">Library Management</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-libraries.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <label for="name_industry">Judul</label>
                <input type="text" class="form-control" id="title_library" name="title_library" placeholder="Isikan Judul" required>
            </div>

            <div class="form-group">
                <label for="name_industry">Link</label>
                <input type="text" class="form-control" id="link_library" name="link_library" placeholder="Isikan Link" required>
            </div>


            <button type="submit" class="btn btn-primary mr-2" >Simpan</button>
            <button  onclick="goBack()"  class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>


    <script>
    function goBack() {
          window.history.back();
    }
    
    </script>
@endsection