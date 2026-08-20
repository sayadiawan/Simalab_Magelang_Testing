@extends('masterweb::template.admin.layout')

@section('title')
    Contact
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
                    <li class="breadcrumb-item"><a href="{{url('/admcontact')}}">Contact</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><span>Edit</span></li>
                </ol>
                </nav>
            </div>
            </div>
        </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('admcontact.update',[$data->id])}}" method="POST">
            @csrf

            <input type="hidden" value="PATCH" name="_method">

            @if(session('status'))
                <div class="alert alert-success">
                    {{session('status')}}
                </div>
            @endif 
         
            
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="nama" value="{{$data->nama}}">
            </div>

            <div class="form-group">
                <label for="email">Emal</label>
                <input type="email" name="email" id="email" class="form-control" value="{{$data->email}}">
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" name="phone" value="{{$data->phone}}">
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control texteditor">{{$data->alamat}}</textarea>
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
            <button class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
@endsection