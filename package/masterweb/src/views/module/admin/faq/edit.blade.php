@extends('masterweb::template.admin.layout')
@section('title')
    Master FAQ
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
                                <li class="breadcrumb-item"><a href="{{url('/adm-faq')}}">Master FAQ</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('adm-faq.update',[$data->id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">
            
            <div class="form-group">
                <label for="name">Question</label>
                <input type="text" class="form-control" id="question" name="question" placeholder="Question" value="{{$data->question}}">
            </div>
            
            <div class="form-group">
                <label for="name">Answer</label>
                <textarea name="answer" class="form-control">{{$data->answer}}</textarea>
            </div>

            <div class="form-group">
                <label for="name">Urutan</label>
                <input type="text" class="form-control" id="ordered" name="ordered" placeholder="Urutan" value="{{$data->ordered}}">
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