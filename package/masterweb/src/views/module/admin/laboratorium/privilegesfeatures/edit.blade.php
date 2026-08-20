@extends('masterweb::template.admin.layout')

@section('title')
    Role Manager
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">Role Manager</h4>
        <p class="card-description">
            tambah data
        </p>
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('privileges-features.update',[$privilege->id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="name">Role</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nama..." value="{{$privilege->name}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="name">Level Code</label>
                        <input type="text" class="form-control" id="level" name="level" placeholder="level code.." value="{{$privilege->level}}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="name">Deskripsi singkat</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Deskripsi singkat.." value="{{$privilege->description}}">
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
                    @php
                        $menu_priv = \Smt\Masterweb\Models\Role::where('privilege_id',$privilege->id)->where('menu_id',$dmn->id)->first();

                        if($menu_priv == NULL)
                        {
                            $create = 1;
                            $read = 1;
                            $update = 1;
                            $delete = 1;
                        }else{
                            $create = $menu_priv->create; 
                            $read = $menu_priv->read;
                            $update = $menu_priv->update; 
                            $delete = $menu_priv->delete; 
                        }
                    @endphp
                <tr>
                    <td align="center">{{$no++}}</td>
                    <td>{{$dmn->name}}<br>{{$dmn->link}}</td>

                    <td>
                        <input type="checkbox" name="create[{{$dmn->id}}]" {{($create == "1") ? "checked" : ""}} data-toggle="toggle">
                    </td>
                    <td>
                        <input type="checkbox" name="read[{{$dmn->id}}]" {{($read == "1") ? "checked" : ""}} data-toggle="toggle">
                    </td>
                    <td>
                        <input type="checkbox" name="update[{{$dmn->id}}]" {{($update == "1") ? "checked" : ""}} data-toggle="toggle">
                    </td>
                    <td>
                        <input type="checkbox" name="delete[{{$dmn->id}}]" {{($delete == "1") ? "checked" : ""}} data-toggle="toggle">
                    </td>
                </tr>
                    @php
                        $submenuadm = \Smt\Masterweb\Models\AdminMenu::all()->where('upmenu',$dmn->id);
                    @endphp
                    @foreach ($submenuadm as $subdmn)
                        @php
                            $menu_priv = \Smt\Masterweb\Models\Role::where('privilege_id',$privilege->id)->where('menu_id',$subdmn->id)->first();

                            if($menu_priv == NULL)
                            {
                                $create = 1;
                                $read = 1;
                                $update = 1;
                                $delete = 1;
                            }else{
                                $create = $menu_priv->create; 
                                $read = $menu_priv->read;
                                $update = $menu_priv->update; 
                                $delete = $menu_priv->delete; 
                            }
                        @endphp
                    <tr>
                        <td align="center"></td>
                        <td>{{$subdmn->name}}<br>{{$subdmn->link}}</td>
    
                        <td>
                            <input type="checkbox" name="create[{{$subdmn->id}}]" {{($create == "1") ? "checked" : ""}} data-toggle="toggle">
                        </td>
                        <td>
                            <input type="checkbox" name="read[{{$subdmn->id}}]" {{($read == "1") ? "checked" : ""}} data-toggle="toggle">
                        </td>
                        <td>
                            <input type="checkbox" name="update[{{$subdmn->id}}]" {{($update == "1") ? "checked" : ""}} data-toggle="toggle">
                        </td>
                        <td>
                            <input type="checkbox" name="delete[{{$subdmn->id}}]" {{($delete == "1") ? "checked" : ""}} data-toggle="toggle">
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