@extends('masterweb::template.admin.layout')

@section('title')
    Master Portofolio
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
                                <li class="breadcrumb-item"><a href="{{url('/adm-portofolio')}}">Master Portofolio</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('adm-portofolio.update',[$get_data->id_portofolio])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="">Name</label>
                <input type="text" class="form-control" value=" {{ $get_data->name_portofolio }} " name="name_portofolio" id="name_portofolio">
            </div>

            <div class="form-group">
                <label for="">Category</label>
                <select name="catport_portofolio" class="form-control" id="catport_portofolio">
                    <option value="">Pilih Category</option>
                    @foreach ($data_cat as $item)
                        <option value="{{$item->id_category_portofolio}}" {{ SmtHelp::isSelected($item->id_category_portofolio,$get_data->catport_portofolio) }}> {{ $item->name_category_portofolio }} </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="">Technology</label>
                <input type="text" class="form-control" placeholder="ex : PHP, CSS" value=" {{ $get_data->tech_portofolio }} " name="tech_portofolio" id="tech_portofolio">
            </div>

            <div class="form-group">
                <label for="">Client</label>
                <input type="text" class="form-control" value=" {{ $get_data->client_portofolio }} " name="client_portofolio" id="client_portofolio">
            </div>

            <div class="form-group">
                <label for="">Link</label>
                <input type="text" class="form-control" value=" {{ $get_data->link_portofolio }} " name="link_portofolio" id="link_portofolio">
            </div>

            <div class="form-group">
                <label for="">Description</label>
                <textarea name="desc_portofolio" id="desc_portofolio" class="form-control texteditor" cols="30" rows="10"> {{ $get_data->desc_portofolio }} </textarea>
            </div>

            <div class="form-group">
                <label for="icon">Gambar Portofolio</label>
                <input type="file" class="dropify" name="file_portofolio" data-default-file="{{asset('assets/public/images/portofolio/'.$get_data->file_portofolio)}}"/>
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
    <script src="{{ asset('assets/admin/js/dropify.js')}}"></script>
@endsection