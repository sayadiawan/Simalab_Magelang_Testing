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
                    <li class="breadcrumb-item active" aria-current="page"><span>Daftar</span></li>
                </ol>
                </nav>
            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="card">

    
    <div class="card-body">
      <div class="d-md-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center mb-3 mb-md-0">
          <div class="a">
            <div class="col-md-12">
              <label for="">Bulan</label>
              <select name="" id="" class="form-control">
                <option value=""></option>
                @for ($i = 1; $i <= 12; $i++)
                  <option value="{{$i}}" {{isSelected($i,$month)}}>{{fbulan($i)}}</option>
                @endfor
              </select>
            </div>
          </div>
        </div>
        <div class="d-flex align-items-center mb-3 mb-md-0">
            <a href="{{route('stock-opname.create')}}">
              <button type="button" class="btn btn-info btn-icon-text">
                  Tambah Data
                  <i class="fa fa-plus btn-icon-append"></i>                             
              </button>
            </a>
        </div>
      </div>

      <hr>

      <div class="row">
        @if(session('status'))
            <div class="alert alert-success">
                {{session('status')}}
            </div>
        @endif 

        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="smt-table table">
              <thead>
                <tr>
                    <th width="25px">No</th>
                    <th>Kode</th> 
                    <th>Nama Barang</th> 
                    <th>Jenis Barang</th> 
                    <th>Qty Opname</th>                    
                    <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @php
                    $no=1;
                @endphp
                @foreach ($stockopnames as $stockopname)
                  
                <tr>
                    <td>{{$no++}}</td>
                    <td>{{$stockopname->code_stockopname }}</td>
                    <td>{{$stockopname->product->name_product }}</td>
                    <td><span class="badge badge-success">{{smt_reference('TYPE_PRODUCT',$stockopname->product->type_product) }}</span></td>
                    <td>{{$stockopname->qty_stockopname }}</td>
                    <td>
                        <a href="{{route('stock-opname.edit', [$stockopname->id_stockopname])}}">
                            <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                <i class="fas fa-pencil-alt"></i>                                                    
                            </button> 
                        </a>
                        <form onsubmit="return confirm('Apakah anda yakin?')" 
                            class="d-inline" 
                            action="{{route('stock-opname.destroy', [$stockopname->id_stockopname])}}" 
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