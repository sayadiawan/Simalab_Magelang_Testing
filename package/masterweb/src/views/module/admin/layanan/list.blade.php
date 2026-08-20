@extends('masterweb::template.admin.layout')

@section('title')
    Layanan
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
                            <li class="breadcrumb-item"><a href="{{url('/admlayanan')}}">Master Layanan</a></li>
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
            {{-- <div id="datepicker-popup" class="input-group date datepicker">
                <input type="text" class="form-control">
                <span class="input-group-addon input-group-append border-left">
                    <span class="far fa-calendar input-group-text"></span>
                </span>
            </div> --}}
        </div>

        <div class="p-2">
            <a href="{{route('admlayanan.create')}}">
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
                    <th>Menu</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Image</th>
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
                    <td>{{$data->menu['name']}}</td>
                    <td>{{$data->title}}</td>
                    <td>@if ($data->kategori == 0)
                        Personal Blog
                        @elseif ($data->kategori == 1)
                        Toko Online
                        @elseif ($data->kategori == 2)
                        Perusahaan
                        @elseif ($data->kategori == 3)
                        Instansi Pemerintahan
                        @elseif ($data->kategori == 4)
                        Web Sekolah
                        @else
                        Belum ada kategori
                    @endif</td>
                    <td>@if (empty($data->image) || $data->image == null)
                            <a href="{{asset('assets/public/images/defult.png')}}" target="_blank"><img src="{{asset('assets/public/images/default.png')}}"></a>
                        @else
                            <a href="{{asset('assets/public/images/layanan/'.$data->image)}}" target="_blank"><img src="{{asset('assets/public/images/layanan/'.$data->image)}}"></a>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-info btn-rounded btn-icon" data-toggle="tooltip" data-custom-class="tooltip-info" data-placement="top" title="Lihat Data">
                            <i class="fa fa-eye"></i>                                                    
                        </button> 
                        <a href="{{route('admlayanan.edit', [$data->id_layanan])}}">
                            <button type="button" class="btn btn-dark btn-rounded btn-icon" data-toggle="tooltip" data-custom-class="tooltip-dark" data-placement="top" title="Edit Data">
                                <i class="fas fa-pencil-alt"></i>                                                    
                            </button> 
                        </a>
                        <form onsubmit="return confirm('Apakah anda yakin ingin menghapus data ini?')" 
                            class="d-inline" 
                            action="{{route('admlayanan.destroy', [$data->id_layanan])}}" 
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