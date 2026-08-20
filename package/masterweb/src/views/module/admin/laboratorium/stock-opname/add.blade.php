

@extends('masterweb::template.admin.layout')
@section('title')
    Stock Opname
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
                    <li class="breadcrumb-item"><a href="{{url('/stock-opname')}}">Stock Opname</a></li>
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
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('stock-opname.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <label for="code_stockopname">Kode Opname</label>
                <input type="text" class="form-control" id="code_stockopname" name="code_stockopname" value="{{date('Ymd').rand(0,99)}}" placeholder="Name Sample Type" required >
            </div>

            <div class="form-group">
                <label for="">Bulan</label>
                <select name="month_stockopname" id="month_stockopname" class="form-control">
                    <option value=""></option>
                    @for ($i = 1; $i <= 12; $i++)
                      <option value="{{sprintf('%02d', $i)}}" {{isSelected($i,date('m'))}}>{{fbulan($i)}}</option>
                    @endfor
                </select>
            </div>

            <div class="form-group">
                <label for="">Tahun</label>
                <input type="text" class="form-control" id="year_stockopname" name="year_stockopname" placeholder="" value="{{date('Y')}}" required >
            </div>

            <div class="form-group">
                <label for="email">Produk</label>
                <select id="type" name="product_stockopname" class="form-control " id="product_stockopname" required>
                    <option value="" selected disabled>Pilih produk</option>
                    @foreach($products as $product)
                        <option value="{{$product->id_product}}" >{{$product->name_product}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="qty_stockopname">Qty +/-</label>
                <input type="text" class="form-control" id="qty_stockopname" name="qty_stockopname" placeholder="Silahkan isi untuk -/+" required >
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