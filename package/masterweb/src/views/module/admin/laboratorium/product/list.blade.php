@extends('masterweb::template.admin.layout')

@section('title')
    Product Management
@endsection

@section('content')

    <script>
              var role="admin"
    </script>

    <script src="{{asset('assets/admin/cdn-local/js/firebase.js')}}"></script>
    <script src= "{{ asset('assets/admin/js/firebase-js/firebase/config.js')}}"></script>
    <script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js')}}"></script>
   

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
            <div class="template-demo">
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/elits-rates')}}">Laboraturium</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><span>Product Management</span></li>
                </ol>
                </nav>
            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="card">

    
    <div class="card-body">
      <div class="d-flex">
        <div class="mr-auto p-2">
            {{-- <div id="datepicker-popup" class="input-group date datepicker">
                <input type="text" class="form-control">
                <span class="input-group-addon input-group-append border-left">
                    <span class="far fa-calendar input-group-text"></span>
                </span>
            </div> --}}
        </div>

        <div class="p-2">
            <a href="{{route('elits-products.create')}}">

                <button type="button" class="btn btn-info btn-icon-text">
                    Tambah Data
                    <i class="fa fa-plus btn-icon-append"></i>                             
                </button>
            </a>
        </div>
    
        
    </div>

      <div class="row">
          
        @if(session('status'))
            <div class="alert alert-success">
                {{session('status')}}
                 
            </div>
        @endif 
        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table smt-table">
              <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>  
                    <th>Nama Barang</th>  
                    <th>Satuan</th>  
                    <th>Tipe</th>  
                    <th>Keterangan</th>  
                    <th>Stock</th>                    
                    <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @php
                    $no=1;
                @endphp
                @foreach ($products as $product)
                @php
                    $unit = \Smt\Masterweb\Models\Unit::find($product->unit_product);
                @endphp
                <tr>
                    <td>{{$no++}}</td>
                    <td>{{$product->code_product}}</td>
                    <td>{{$product->name_product}}</td>
                    <td>{{$unit->shortname_unit}}</td>
                    <td>{{smt_reference('TYPE_PRODUCT',$product->type_product)}}</td>
                    <td>{{$product->desc_product}}</td>
                    <td>{{$product->desc_product}}</td>
                    <td>
                        <a href="{{route('elits-products.edit', [$product->id_product])}}">
                            <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                <i class="fas fa-pencil-alt"></i>                                                    
                            </button> 
                        </a>
                        <form onsubmit="return confirm('Delete this user permanently?')" 
                            class="d-inline" 
                            action="{{route('elits-products.destroy', [$product->id_product])}}" 
                            method="POST">

                                @csrf

                                <input 
                                type="hidden" 
                                name="_method" 
                                value="DELETE">

                                <button type="submit" class="btn btn-outline-danger btn-rounded btn-icon">
                                    <i class="fas fa-trash"></i>                                                    
                                </button> 
                        </form>
                    </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection