@extends('masterweb::template.admin.layout')

@section('title')
    User Management
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
                              <li class="breadcrumb-item"><a href="{{url('/elits-users')}}">Users</a></li>
                              <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
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
            <a href="{{route('elits-users.create')}}">
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
                @if(session('user') && session('user')!=null)
                <script src="{{asset('assets/admin/cdn-local/js/firebase.js')}}"></script>
                <script src= "{{ asset('assets/admin/js/firebase-js/firebase/config.js')}}"></script>
                <script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js')}}"></script>
            
                <script>
                  var user=<?php echo session('user');?>;
                  console.log(user)

                  firebase.database().ref('users/'+user.root_firebase).set({
                      username: user.username,
                      name: user.name,
                      email: user.email,
                      role:"user"
                  }).then(function() {
                      // window.location.href = "./dashboard"
              
                  
                  }).catch(function(error) {
                      // An error happened.
                  });

                  </script>
                @endif 
            </div>
        @endif 
        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table">
              <thead>
                <tr>
                    <th>No</th>
                    <th>Laboratorium</th>
                    <th>Username</th>
                    <th>Hak Akses</th>
                    <th>Nama</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                    $no=1;
                @endphp
                @foreach ($users as $duser)
                
                <tr>
                    <td>{{$no++}}</td>
                    <td>{{$duser->laboratorium->nama_laboratorium ?? ""}}</td>
                    <td>{{$duser->username}}</td>
                    <td>{{$duser->privilege}}</td>
                    <td>{{$duser->name}}</td>
                    <td>
                    <label class="bg-user">
                        <img src="{{ ($duser->photo == NULL) ? asset('assets/admin/images/logo/favicon.png') : asset('/storage/photo/'.$duser->photo)}}" alt="image"/>
                      </label>
                    </td>
                    <td>
                      @if ($duser->publish == 1)
                        <label class="badge badge-info">Aktif</label>
                      @else
                        <label class="badge badge-danger">Tidak Aktif</label>
                      @endif
                    </td>
                    <td>
                        <a href="{{url('elits-users/reset/'.$duser->id)}}">
                            <button type="button" class="btn btn-outline-primary btn-rounded btn-icon">
                            <i class="icon-refresh"></i>                
                            </button> 
                        </a>
                        <a href="{{route('elits-users.edit', [$duser->id])}}">
                            <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                <i class="fas fa-pencil-alt"></i>                                                    
                            </button> 
                        </a>
                        <form onsubmit="return confirm('Delete this user permanently?')" 
                            class="d-inline" 
                            action="{{route('elits-users.destroy', [$duser->id])}}" 
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