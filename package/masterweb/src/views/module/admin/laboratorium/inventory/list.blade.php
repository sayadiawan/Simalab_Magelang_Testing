@extends('masterweb::template.admin.layout')

@section('title')
    Inventory Management
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
                    <li class="breadcrumb-item"><a href="{{url('/elits-methods')}}">Laboraturium</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><span>Inventory Management</span></li>
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
            <a href="{{route('elits-inventories.create')}}">

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
            <table id="order-listing" class="table">
              <thead>
                <tr>
                    <th>No</th>
                    <th>Product</th>   
                    <th>Type</th>  
                    <th>Qty</th>                    
                    <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @php
                    $no=1;
                @endphp
                @foreach ($inventories as $inventory)
                  @php
                    $product = \Smt\Masterweb\Models\Product::find($inventory->product_stock);
                  @endphp
                <tr>
                    <td>{{$no++}}</td>
                    <td>{{$product->name_product}}</td>
                    <td>
                      @if ($inventory->type_stock=='1')
                        <button type="button" class="btn btn-success btn-circle btn-xl">+</button>
                      @else
                        <button type="button" class="btn btn-danger btn-circle btn-xl">-</button>
                      @endif
                    </td>
                    <td>{{$inventory->qty_stock}}</td>
                    <td>
                       
                        <form onsubmit="return confirm('Delete this user permanently?')" 
                            class="d-inline" 
                            action="" 
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