@extends('masterweb::template.admin.layout')

@section('title')
    Library Management
@endsection

@section('content')

    <script>
              var role="admin"
    </script>

    <script src="{{asset('assets/admin/cdn-local/js/firebase.js')}}"></script>
    <script src= "{{ asset('assets/admin/js/firebase-js/firebase/config.js')}}"></script>
    <script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js')}}"></script>
   

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
            <div class="template-demo">
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/elits-industries')}}">Laboraturium</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><span>Library Management</span></li>
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
        <div class="mr-auto p-2">
            {{-- <div id="datepicker-popup" class="input-group date datepicker">
                <input type="text" class="form-control">
                <span class="input-group-addon input-group-append border-left">
                    <span class="far fa-calendar input-group-text"></span>
                </span>
            </div> --}}
        </div>

        <div class="p-2">
            <a href="{{route('elits-libraries.create')}}">

                <button type="button" class="btn btn-info btn-icon-text">
                    Tambah Data
                    <i class="fa fa-plus btn-icon-append"></i>                             
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
        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table">
              <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>                    
                    <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                    $no=1;
                @endphp
                @foreach ($libraries as $library)
                  
                <tr>
                    <td>{{$no++}}</td>
                    <td> <a href="{{$library->link_library}}">{{$library->title_library}}</a></td>
                    <td>
                        <a href="{{route('elits-libraries.edit', [$library->id_library])}}">
                            <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                <i class="fas fa-pencil-alt"></i>                                                    
                            </button> 
                        </a>
                        <form onsubmit="return confirm('Delete this user permanently?')" 
                            class="d-inline" 
                            action="{{route('elits-libraries.destroy', [$library->id_library])}}" 
                            method="POST">

                                @csrf

                                <input 
                                type="hidden" 
                                name="_method" 
                                value="DELETE">

                                <button type="submit" class="btn btn-outline-danger btn-rounded btn-icon">
                                    <i class="fas fa-trash"></i>                                                    
                                </button> 
                        </form>
                    </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection