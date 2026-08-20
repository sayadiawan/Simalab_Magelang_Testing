@extends('masterweb::template.admin.layout')
@section('title')
    Master Category Layanan
@endsection

@section('content')

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                                <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/adm-categorylayanan')}}">Category Layanan</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('adm-categorylayanan.update',[$data->id_category_layanan])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="icon">Name Category</label>
                <input type="text" class="form-control" name="nama_layanan" value="{{$data->nama_layanan}}"/>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
                
            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <a href="{{url('/adm-categorylayanan')}}" class="btn btn-light">Kembali</a>
        </form>
        </div>
    </div>
@endsection