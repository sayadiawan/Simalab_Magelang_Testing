@extends('masterweb::template.admin.layout')

@section('title')
    Sosmed
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
                                <li class="breadcrumb-item"><a href="{{url('/admsosmed')}}"> Sosmed</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('admsosmed.update', [$data->id])}}" method="POST">
            @csrf
            @method('put')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title">Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{$data->name}}" >
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="icon">Icon</label>
                        <input type="text" class="form-control" id="icon" name="icon" placeholder="icon" value="{{$data->icon}}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="link">Link</label>
                        <input type="text" class="form-control" id="link" name="link" placeholder="Link" value="{{$data->link}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="Url">Url</label>
                        <input type="text" class="form-control" id="url" name="url" placeholder="url" value="{{$data->url}}">
                    </div>
                </div>
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
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>
@endsection
