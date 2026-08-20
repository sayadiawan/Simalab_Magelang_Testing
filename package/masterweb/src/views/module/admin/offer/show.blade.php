@extends('masterweb::template.admin.layout')

@section('title')
    Offer
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
                                <li class="breadcrumb-item"><a href="{{url('/admoffer')}}">Penawaran</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>detail</span></li>
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
    </div>

      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table id="order-listing" class="table">
                <tr>
                    <th>Nama</th>
                    <td>{{$data->nama}}</td>
                </tr>
                <tr>
                    <th>Instansi</th>
                    <td>{{$data->instansi}}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{$data->email}}</td>
                </tr>
                <tr>
                    <th>Nama Proyek</th>
                    <td>{{$data->nama_proyek}}</td>
                </tr>
                <tr>
                    <th>Detail Proyek</th>
                    <td>{{$data->detail_proyek}}</td>
                </tr>
                <tr>
                    <th>Layanan</th>
                    <td>{{$data->layanan}}</td>
                </tr>
                <tr>
                    <th>Deadline</th>
                    <td>{{$data->deadline}}</td>
                </tr>
                <tr>
                    <th>Lampiran Url</th>
                    <td>{{$data->lampiran_url}}</td>
                </tr>
                <tr>
                    <th>Tau Seven Media dari</th>
                    <td>{{SmtHelp::Info_umum($data->info_umum)}}</td>
                </tr>
                <tr>
                    <th>Lampiran Berkas</th>
                    <td>
                        @if(!empty($data->lampiran_berkas))
                        <img src="{{asset('assets/public/images/'.$data->lampiran_berkas)}}" alt="">
                        @else
                        -
                        @endif
                    </td>
                </tr>
              
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection