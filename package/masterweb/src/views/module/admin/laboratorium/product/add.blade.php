

@extends('masterweb::template.admin.layout')
@section('title')
    Data Barang
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-products')}}">Product Management</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-products.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <label for="code_product">Kode Barang</label>
                <input type="text" class="form-control" id="code_product" name="code_product" placeholder="Kode Barang" required autofocus>
            </div>

            <div class="form-group">
                <label for="name_product">Nama Barang</label>
                <input type="text" class="form-control" id="name_product" name="name_product" placeholder="Nama Barang" required >
            </div>

            <div class="form-group">
                <label for="unit_product">Unit</label>
                <select name="unit_product" class="form-control smt-select2" id="unit_product" required>
                    <option value="">Pilih Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{$unit->id_unit}}">{{$unit->shortname_unit}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="type_product">Tipe</label>
                <select name="type_product" class="form-control smt-select2" id="type_product" required>
                    <option value="">Pilih Tipe</option>
                    @php
                    $types = smt_reference('TYPE_PRODUCT');
                    @endphp
                    @foreach ($types as $id_type => $type)
                        <option value="{{$id_type}}">{{$type}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="desc_product">Keterangan</label>
                <input type="text" class="form-control" id="desc_product" name="desc_product" placeholder="Keterangan barang" required >
            </div>


            <button type="submit" class="btn btn-primary mr-2" >Simpan</button>
            <button  onclick="goBack()"  class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>


    <script>
    function goBack() {
          window.history.back();
    }
    </script>
@endsection

@section('scripts')
<script>
$('.smt-select2').select2();
</script>
@endsection