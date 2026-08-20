@extends('masterweb::template.admin.layout')

@section('title')
    Offer
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">Offer</h4>
        <p class="card-description">
            tambah data
        </p>
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('admoffer.store')}}" method="POST">
            @csrf

                   
                        <div class="form-group">
                            <label for="name">tampilkan pada menu</label>
                            <select name="menu_id" id="menu_id" class="form-control">
                                <option value="">pilih menu</option>
                                @foreach ($menupublic as $menu)
                                    <option value="{{$menu->id}}">{{$menu->name}}</option>
                                @endforeach
                            </select>
                        </div>

                   
                        <div class="form-group">
                            <label for="judul">Judul</label>
                            <input type="text" class="form-control" id="judul" name="judul" placeholder="Judul" >
                        </div>
                        
                   
                        <div class="form-group">
                            <label for="foto">Foto</label>
                            <input type="file" class="dropify" name="foto" />
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
                        <a href="{{route('admoffer.index')}}" class="btn btn-light">Kembali</a>
        </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/dropify.js')}}"></script>
@endsection