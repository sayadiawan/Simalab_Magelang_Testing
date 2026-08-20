@extends('masterweb::template.admin.layout')
@section('title')
    Unit Management
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-units')}}">Unit Management</a></li>
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
        <h4 class="card-title">Unit Management</h4>
        
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-units.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="name_unit">Nama Unit</label>
                <input type="text" class="form-control" id="name_unit" name="name_unit" value="{{$unit->name_unit}}" placeholder="Nama Unit" required>
            </div>

            <div class="form-group">
                <label for="name_unit">Singkatan Unit</label>
                <input type="text" class="form-control" id="shortname_unit" name="shortname_unit" value="{{$unit->shortname_unit}}" placeholder="Singkatan Unit" required>
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