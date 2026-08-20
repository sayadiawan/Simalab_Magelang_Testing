@extends('masterweb::template.admin.layout')

@section('title')
    Content & Article
@endsection

@section('content')
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('admcontent.update',[$content->id_content])}}" method="POST">
            @csrf
            <div class="row">
                <div class="card col-md-8">
                    <textarea name="content" id="summernote" >{{$content->content}}</textarea>
                </div>
                <div class="card col-md-4">
                    <div class="card-body">
                        <h4 class="card-title">Content & Article</h4>
                        <input type="hidden" value="PATCH" name="_method">

                        <div class="form-group">
                            <label for="name">tampilkan pada menu</label>
                            <select name="menu_id" id="menu_id" class="form-control">
                                <option value="">pilih menu</option>
                                @foreach ($menupublic as $menu)
                                    <option value="{{$menu->id}}" {{($content->menu_id == $menu->id) ? "selected" : ""}}>{{$menu->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Tipe</label>
                            <select name="type" id="type" class="form-control">
                                <option value="1" {{($content->type == "1") ? "selected" : ""}}>Full</option>
                                <option value="0" {{($content->type == "0") ? "selected" : ""}}>List</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Judul" value="{{$content->title}}">
                        </div>

                        <div class="form-group">
                            <label for="link_url">Link</label>
                            <input type="text" class="form-control" id="link_url" name="link_url" placeholder="Link" value="{{$content->link_url}}">
                        </div>

                        <div class="form-group">
                            <label for="thumbnail">Thumbnail</label>
                            <input type="file" class="dropify" name="img_thumbnail" data-default-file="{{asset('assets/public/images/'.$content->img_thumbnail)}}"/>
                        </div>

                        <div class="form-group">
                            <label for="author">Keyword</label>
                            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="keyword, pisahkan dengan koma.." value="{{$content->keyword}}">
                        </div>

                        <div class="form-group">
                            <label for="author">Deskripsi</label>
                            <input type="text" class="form-control" id="deskripsi" name="deskripsi" placeholder="" value="{{$content->deskripsi}}">
                        </div>

                        <div class="form-group">
                            <label for="author">Penulis</label>
                            <input type="text" class="form-control" id="author" name="author" placeholder="Penulis" value="{{$content->author}}">
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
                    </div>
                </div>
            </div>
        </form>
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
            $('body').addClass('sidebar-icon-only');
            $('#title').keyup(function(){
                $('#link_url').val(toSeoUrl($('#title').val()));
            });

            if ($("#summernote").length) {
                $('#summernote').summernote({
                height: 800,
                tabsize: 2
                });
            }
        });
    </script>

    <script src="{{ asset('assets/admin/js/dropify.js')}}"></script>
    
@endsection