@extends('layouts.admin')

@section('title')
    User Management
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">User Management</h4>
        <p class="card-description">
            tambah data
        </p>
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('menu-elits.store')}}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Up Menu</label>
                <select name="level" id="level" class="form-control">
                    <option value="">INDUK</option>
                    @foreach ($admin_menus as $admin_menu)
                        <option value="{{$admin_menu->id}}">{{$admin_menu->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="name">Nama Menu</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="nama menu" >
            </div>

            <div class="form-group">
                <label for="name">Icon</label>
                <input type="text" class="form-control" id="icon" name="icon" placeholder="ex : fa fa-example.." >
            </div>

            <div class="form-group">
                <label for="link">Link/Url</label>
                <input type="text" class="form-control" id="link" name="link" placeholder="link/url" >
            </div>

            <div class="form-group">
                <input type="radio" name="type" id="type" value="0"> link | <input type="radio" name="type" id="type" value="1"> url
            </div>

            <div class="form-group">
                <label for="email">Urutan <br></label>
                
                <input type="number" min="1" class="form-control" id="urutan" name="urutan" placeholder="urutan" >
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