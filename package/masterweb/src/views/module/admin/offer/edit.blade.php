@extends('masterweb::template.admin.layout')

@section('title')
    Offer
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">Offer</h4>
        <p class="card-description">
            edit data
        </p>
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('admoffer.update', [$data->id])}}" method="POST">
            @csrf
            @method('patch')

                   
                        <div class="form-group">
                            <label for="name">tampilkan pada menu</label>
                            <select name="menu_id" id="menu_id" class="form-control">
                                <option value="">pilih menu</option>
                                @foreach ($menupublic as $menu)
                                    <option value="{{$menu->id}}" {{($menu->id == $data->menu_id) ? "selected" : ""}}>{{$menu->name}}</option>
                                @endforeach
                            </select>
                        </div>

                   
                        <div class="form-group">
                            <label for="judul">Judul</label>
                            <input type="text" class="form-control" id="judul" name="judul" value="{{$data->judul}}" placeholder="Judul" >
                        </div>
                        
                   
                        <div class="form-group">
                            <label for="foto">Foto</label>
                            <input type="file" class="dropify" name="foto" data-default-file="{{asset('assets/public/images/layanan/'.$data->foto)}}"/>
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