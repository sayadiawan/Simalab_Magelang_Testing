@extends('masterweb::template.admin.layout')
@section('title')
    Rate Management
@endsection

@section('content')

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
            <div class="template-demo">
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/elits-rates')}}">Rate Management</a></li>
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
        <h4 class="card-title">Tarif Management</h4>
        
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-rates.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="params_rate">Parameter</label>
                <input type="text" class="form-control" id="params_rate" name="params_rate" value="{{$rate->params_rate}}" placeholder="Parameter" required >
            </div>

            <div class="form-group">
                <label for="major_rate">Major</label>
                <select name="major_rate" class="form-control" id="major_rate" required>
                    <option value="">Pilih Major</option>
                    @foreach ($majors as $major)
                        <option value="{{$major->id_major}}" {{($major->id_major==$rate->major_rate)?"selected":""}}>{{$major->name_major}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="rate_rate">Tarif</label>
                <input type="number" class="form-control" id="rate_rate" name="rate_rate" value="{{$rate->rate_rate}}" placeholder="Tarif" required>
            </div>

            <div class="form-group">
                <label for="purpose_rate">Peruntukan</label>
                <input type="text" class="form-control" id="purpose_rate" name="purpose_rate" value="{{$rate->purpose_rate}}" placeholder="Peruntukan" required>
            </div>


            
            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <button  onclick="goBack()" class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>

    <script>
    function goBack() {
          window.history.back();
    }
    </script>
@endsection