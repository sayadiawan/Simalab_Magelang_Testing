@extends('masterweb::template.admin.layout')
@section('title')
    Major Management
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">Major Management</h4>
        
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-majors.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="name_major">Nama Major</label>
                <input type="text" class="form-control" id="name_major" name="name_major" value="{{$major->name_major}}" placeholder="Nama Major" >
            </div>

            
            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <button class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
@endsection