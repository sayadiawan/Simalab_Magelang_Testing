@extends('masterweb::template.admin.layout')

@section('title')
    Feedback
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
                            <li class="breadcrumb-item"><a href="{{url('/admfeedback')}}">Feedback</a></li>
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
                    <td>{{$data->name}}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{$data->email}}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{$data->phone}}</td>
                </tr>
                <tr>
                    <th>Message</th>
                    <td>{{$data->message}}</td>
                </tr>
                <tr>
                    <th>Tanggal feedback</th>
                    <td>{{SmtHelp::fdate($data->created_at, 'HHDDMMYYYY')}}</td>
                </tr>
              
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection