@extends('masterweb::template.admin.layout')
@section('title')
    Industri Management
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-industries')}}">Industry Management</a></li>
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
        <h4 class="card-title">Industri Management</h4>
        <p class="card-description">
            edit data
        </p>
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-industries.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="name_industry">Nama Industri</label>
                <input type="text" class="form-control" id="name_industry" name="name_industry" value="{{$industry->name_industry}}" placeholder="Nama Industri" required >
            </div>

            
            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <button onclick="goBack()" class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
    <script>
    function goBack() {
          window.history.back();
    }
    </script>
@endsection