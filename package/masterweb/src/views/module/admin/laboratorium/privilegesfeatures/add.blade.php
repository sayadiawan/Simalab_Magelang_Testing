@extends('masterweb::template.admin.layout')

@section('title')
    Role Manager
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
                                <li class="breadcrumb-item"><a href="{{url('/privileges-features')}}">Privileges</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('privileges-features.store')}}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="name">Role</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama..." >
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="name">Level Code</label>
                        <input type="text" class="form-control" id="level" name="level" placeholder="level code.." >
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="name">Deskripsi singkat</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Deskripsi singkat.." >
                    </div>
                </div>
            </div>
            
            <table class="table table-bordered table-stripped">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th width="250" rowspan="2">Menu</th>
                    <th colspan="4" class="text-center">Akses</th>
                </tr>
                <tr>
                    <th>Create</th>
                    <th>Read</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
                @php
                    $no = 1;
                @endphp
                @foreach ($menuadm as $dmn)
                <tr>
                    <td align="center">{{$no++}}</td>
                    <td>{{$dmn->name}}<br>{{$dmn->link}}</td>

                    <td>
                        <input type="checkbox" name="create[{{$dmn->id}}]" checked data-toggle="toggle">
                    </td>
                    <td>
                        <input type="checkbox" name="read[{{$dmn->id}}]" checked data-toggle="toggle">
                    </td>
                    <td>
                        <input type="checkbox" name="update[{{$dmn->id}}]" checked data-toggle="toggle">
                    </td>
                    <td>
                        <input type="checkbox" name="delete[{{$dmn->id}}]" checked data-toggle="toggle">
                    </td>
                </tr>
                    @php
                        $submenuadm = \Smt\Masterweb\Models\AdminMenu::all()->where('upmenu',$dmn->id);
                    @endphp
                    @foreach ($submenuadm as $subdmn)
                    <tr>
                        <td align="center"></td>
                        <td>{{$subdmn->name}}<br>{{$subdmn->link}}</td>
    
                        <td>
                            <input type="checkbox" name="create[{{$subdmn->id}}]" checked data-toggle="toggle">
                        </td>
                        <td>
                            <input type="checkbox" name="read[{{$subdmn->id}}]" checked data-toggle="toggle">
                        </td>
                        <td>
                            <input type="checkbox" name="update[{{$subdmn->id}}]" checked data-toggle="toggle">
                        </td>
                        <td>
                            <input type="checkbox" name="delete[{{$subdmn->id}}]" checked data-toggle="toggle">
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </table>

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

@section('css')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
@endsection