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
          <a href="{{route('adm-portofolio.create')}}">
            <button type="submit" class="btn btn-info mb-2"><i class="fa fa-plus-circle mr-2"></i>Tambah Data</button>
          </a>

          <button type="submit" class="btn btn-light mb-2"><i class="far fa-file-excel mr-2"></i>Ekspor</button>
          <button type="submit" class="btn btn-light mb-2"><i class="fa fa-print mr-2"></i>Cetak</button>
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
                <th>Images</th>
                <th>Name</th>
                <th>Category</th>
                <th>Technology</th>
                <th>Descipton</th>
                <th>Publish</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
              $no=1;
            //   dd($data);
              @endphp
              @foreach ($data as $data)
              <tr>
                <td>{{$no++}}</td>
                <td><a href="{{asset('assets/public/images/portofolio/'.$data->file_portofolio)}}" target="_blank"><img src="{{asset('assets/public/images/portofolio/'.$data->file_portofolio)}}"></a></td>
                <td><a href=" {{ $data->link_portofolio }} "> {{ Str::ucfirst($data->name_portofolio) }} </a></td>
                <td> {{ $data->category_portofolio->name_category_portofolio }} </td>
                <td> {{ $data->tech_portofolio }} </td>
                <td> {!! substr($data->desc_portofolio, 0,100) !!}... </td>                
                <td><label class="badge badge-info"></label>
                  @if ($data->publish == 1)
                  <a href="{{url('adm-portofolio/publish/'.$data->id_portofolio)}}"><label class="badge badge-info">Aktif</label></a>
                  @else
                  <a href="{{url('adm-portofolio/publish/'.$data->id_portofolio)}}"><label class="badge badge-danger">Tidak Aktif</label></a>
                  @endif
                </td>
                <td>
                  <a href="{{route('adm-portofolio.edit', [$data->id_portofolio])}}">
                    <button type="button" class="btn btn-dark btn-rounded btn-icon" data-toggle="tooltip" data-custom-class="tooltip-dark" data-placement="top" title="" data-original-title="Edit Data">
                      <i class="fas fa-pencil-alt"></i>
                    </button>
                  </a>
                  <form onsubmit="return confirm('Apakah anda yakin ingin menghapus data ini?')" class="d-inline" action="{{route('adm-portofolio.destroy', [$data->id_portofolio])}}" method="POST">

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