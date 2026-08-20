@extends('masterweb::template.admin.layout')

@section('title')
Master Category Layanan
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
              <li class="breadcrumb-item"><a href="{{url('/adm-categorylayanan')}}">Category Layanan</a></li>
              <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
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
      
      </div>

      <div class="p-2">
        <div class="">
          <a href="{{route('adm-categorylayanan.create')}}">
            <button type="submit" class="btn btn-info mb-2"><i class="fa fa-plus-circle mr-2"></i>Tambah Data</button>
          </a>
        </div>
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
                <th>Name Category</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
              $no=1;
              @endphp
              @foreach ($data as $data)
              <tr>
                <td>{{$no++}}</td>
                <td> {{ ucfirst($data->nama_layanan) }} </td>
                <td>
                  <a href="{{route('adm-categorylayanan.edit', [$data->id_category_layanan])}}">
                    <button type="button" class="btn btn-dark btn-rounded btn-icon" data-toggle="tooltip" data-custom-class="tooltip-dark" data-placement="top" title="" data-original-title="Edit Data">
                      <i class="fas fa-pencil-alt"></i>
                    </button>
                  </a>
                  <form onsubmit="return confirm('Apakah anda yakin ingin menghapus data ini?')" class="d-inline" action="{{route('adm-categorylayanan.destroy', [$data->id_category_layanan])}}" method="POST">

                    @csrf

                    <input type="hidden" name="_method" value="DELETE">

                    <button type="submit" class="btn btn-danger btn-rounded btn-icon" data-toggle="tooltip" data-custom-class="tooltip-danger" data-placement="top" title="Hapus Data">
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