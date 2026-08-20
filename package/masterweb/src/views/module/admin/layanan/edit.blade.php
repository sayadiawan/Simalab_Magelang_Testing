@extends('masterweb::template.admin.layout')

@section('title')
    Layanan
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
                                <li class="breadcrumb-item"><a href="{{url('/admlayanan')}}">Master Layanan</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>edit</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('admlayanan.update', [$layanan->id_layanan])}}" method="POST">
            @csrf
            @method('put')
            <div class="form-group">
                <label for="name">tampilkan pada menu</label>
                <select name="menu_id" id="menu_id" class="form-control">
                    <option value="">pilih menu</option>
                    @foreach ($menupublic as $menu)
                        <option value="{{$menu->id}}" @if ($layanan->menu_id == $menu->id) selected="selected" @endif>{{$menu->name}}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="title">Judul</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Judul" value="{{$layanan->title}}" >
            </div>

            <div class="form-group">
                <label for="link_url">link Url</label>
                <input type="text" class="form-control" id="link_url" name="link_url" placeholder="Link Url" value="{{$layanan->link_url}}">
            </div>

            <div class="form-group">
                <label for="kategor">Kategori</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Pilih Kategori</option>
                    @foreach ($data_cat as $cat)
                        <option value="{{$cat->id_category_layanan}}" <?= ($layanan->kategori == $cat->id_category_layanan) ? "selected" : "" ?>>{{$cat->nama_layanan}}</option>
                    @endforeach
                </select>
            </div>

            <table class="table table-bordered table-striped" id="fitur">
                @php
                $fitur = explode(',', $layanan->fitur);
                @endphp
                <thead>
                    <tr>
                        <th>Fitur</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fitur as $item)
                        <tr>
                            <td><input type="text" name="fitur[]" id="fitur" class="form-control" value="{{$item}}"></td>
                            <td><button type="button" id="btn-remove" class="btn btn-danger"><i class="fas fa-minus"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><center><button type="button" class="btn btn-success" id="btn-add"><i class="fas fa-plus"></i></button></center></td>
                    </tr>
                </tfoot>
            </table>

            <div class="form-group card col-md-12">
                <label for="">Deskripsi</label>
                <textarea name="deskripsi" id="summernote" >{{$layanan->deskripsi}}</textarea>
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" class="dropify" name="image" data-default-file="{{asset('assets/public/images/layanan/'.$layanan->image)}}"/>
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
            <button class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toSeoUrl(url) {
            return url.toString()               // Convert to string
                .normalize('NFD')               // Change diacritics
                .replace(/[\u0300-\u036f]/g,'') // Remove illegal characters
                .replace(/\s+/g,'-')            // Change whitespace to dashes
                .toLowerCase()                  // Change to lowercase
                .replace(/&/g,'-and-')          // Replace ampersand
                .replace(/[^a-z0-9\-]/g,'')     // Remove anything that is not a letter, number or dash
                .replace(/-+/g,'-')             // Remove duplicate dashes
                .replace(/^-*/,'')              // Remove starting dashes
                .replace(/-*$/,'');             // Remove trailing dashes
        }
        $(document).ready(function(){
            $('#title').keyup(function(){
                $('#link_url').val(toSeoUrl($('#title').val()));
            });
        });
        $(document).ready(function(){
            $('#title').keyup(function(){
                $('#link_url').val(toSeoUrl($('#title').val()));
            });
            if ($("#summernote").length) {
                $('#summernote').summernote({
                height: 300,
                tabsize: 2
                });
            }
        });
    </script>

    <script src="{{ asset('assets/admin/js/dropify.js')}}"></script>

    <script>
        $(document).ready(function(){
            $('#btn-add').click(function(){
                var markup = '<tr><td><input type="text" name="fitur[]" id="fitur" class="form-control"></td><td><button type="button" id="btn-remove" class="btn btn-danger"><i class="fas fa-minus"></i></button></td></tr>';
                $('table tbody').append(markup);
            });
            $('#fitur').on('click', '#btn-remove', function(){
                $(this).parent().parent().remove();
            })
        });
    </script>

@endsection