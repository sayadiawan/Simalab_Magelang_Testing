@extends('masterweb::template.admin.layout')

@section('title')
    Password
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
                                <li class="breadcrumb-item"><a href="{{url('/adm-password')}}">Password</a></li>
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
        <h4 class="card-title">Password</h4>
        <p class="card-description">
            edit password
        </p>
        @if(session('status'))
            <div class="alert alert-success">
                {{session('status')}}
            </div>
        @endif 
        
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('user.adm-password.update')}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group row">
                <label for="current_password" class="col-md-3 col-form-label text-md-left">{{ __('Password Lama') }}</label>
            
                <div class="col-md-6">
                    <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required autocomplete="current_password">
            
                    @error('current_password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="password" class="col-md-3 col-form-label text-md-left">{{ __('Password Baru') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="password-confirm" class="col-md-3 col-form-label text-md-left">{{ __('Konfirmasi Password') }}</label>

                <div class="col-md-6">
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>
            

            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <button class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
@endsection