@extends('masterweb::template.admin.layout')

@section('title')
    Favicon
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
                            <li class="breadcrumb-item"><a href="{{url('/favicon')}}">Favicon</a></li>
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
      <div class="d-flex">
        <div class="p-2">
            <a href="{{url('favicon')}}">
                <button type="button" class="btn btn-info btn-icon-text">
                  <i class="fa fa-arrow-left sbtn-icon-append"></i>                             
                    Back
                </button>
            </a>
        </div>
    </div>

      <div class="row">
          
        @if(session('status'))
            <div class="alert alert-success">
                {{session('status')}}
            </div>
        @endif 
        @foreach ($options as $option)
        <div class="col-12">
        <form action="{{url('favicon/'.$option->id)}}" method="POST" enctype="multipart/form-data" class="forms-sample">
            @method('put')
            @csrf
            <div class="form-group">
                <label class="mb-4"><h5>Apakah ingin mengganti Favicon?</h5></label>
                <br>
                
                <div>
                  <img src="{{asset('assets/admin/images/'.$option->favicon)}}" alt="" style="width:70px; height:70px; margin-right:20px; ">
                  <br><br>
                  <input type="file" name="favicon">
                </div>

                <button type="submit" class="btn btn-primary btn-sm mt-3">Save</button>
              @endforeach
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection