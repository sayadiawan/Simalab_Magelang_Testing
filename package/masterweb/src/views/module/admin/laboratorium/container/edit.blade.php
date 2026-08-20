@extends('masterweb::template.admin.layout')
@section('title')
Wadah Management
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-containers')}}">Wadah Management</a></li>
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
        
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-containers.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="name_container">Nama Wadah</label>
                <input type="text" class="form-control" id="name_container" name="name_container" value="{{$container->name_container}}" placeholder="Nama Wadah" required >
            </div>
            
            
            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
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