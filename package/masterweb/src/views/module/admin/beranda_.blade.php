@extends('masterweb::template.admin.layout')

@section('title')
Beranda
@endsection

@php
$user = Auth()->user();
@endphp

@section('content')
@php
    $clients = Smt\Masterweb\Models\User::where("level","01f62b38-fce5-43bf-9088-9d4a33a496da")->count();
    if(($auth->level=='a93b0f54-ad04-43cc-a34e-9d414be4c129')||
    ($auth->level=='965371c6-3077-4ea4-adc1-bf162c7a22c4')||
    ($auth->level=='e6684739-b90b-4149-a093-9457474a277b')||
    ($auth->level=='625b6020-1087-4898-b2ea-b598d90b6179')){
      $user = Smt\Masterweb\Models\User::where("level","7d6bc1b7-5115-4724-820d-f04744f61828")->count();
      $device = Smt\Masterweb\Models\Device::count();
    }else if($auth->level=='01f62b38-fce5-43bf-9088-9d4a33a496da'){
      $user = Smt\Masterweb\Models\User::where("level","7d6bc1b7-5115-4724-820d-f04744f61828")->where("client_id",$auth->id)->count();
      $device = Smt\Masterweb\Models\Device::where("client_id",$auth->id)->count();
    }else{
      if($auth->level=='7d6bc1b7-5115-4724-820d-f04744f61828'){
         
          $device= Smt\Masterweb\Models\UserDevice::join('ms_users', 'ms_users.id', '=', 'ms_user_devices.user_id')
          ->join('ms_devices', 'ms_devices.kode_device', '=', 'ms_user_devices.device_id')
          ->where('ms_user_devices.user_id', '=', $auth->id)
          ->count();
      

      }else{
          
          $device = Smt\Masterweb\Models\Device::where("client_id",$auth->client_id)->count();
      }
      
    }
   
    $article = Smt\Masterweb\Models\Content::where('type','0')->count();
    $content = Smt\Masterweb\Models\Content::where('type','1')->count();
    $topten = Smt\Masterweb\Models\Content::orderBy('views', 'desc')->paginate(10);
    $total = Smt\Masterweb\Models\Content::sum('views');
@endphp
{{-- <div class="row">
  <div class="col-12 grid-margin stretch-card">
    <div class="card">
      <div class="">
        <div class="template-demo">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
              <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
              <li class="breadcrumb-item active" aria-current="page"><span>Beranda</span></li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div> --}}

<div class="row grid-margin">
      <div class="col-12">
        <div class="row">
        @if(($auth->level=='a93b0f54-ad04-43cc-a34e-9d414be4c129')||
          ($auth->level=='965371c6-3077-4ea4-adc1-bf162c7a22c4')||
          ($auth->level=='e6684739-b90b-4149-a093-9457474a277b')||
          ($auth->level=='625b6020-1087-4898-b2ea-b598d90b6179'))
          <div class="col-md-4 col-xs-6 col-sm-6  grid-margin stretch-card">
            <div class="card" style="background:#205373">
              <div class="card-body">
                <div class="d-md-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center mb-3 mb-md-0">
                    <button class="btn btn-social-icon btn-white btn-rounded">
                      <i class="fa fa-sitemap"></i>
                    </button>
                    <div class="ml-4">
                      <h5 class="mb-0 mt-2 font-white">Jumlah Client
                      </h5>
                      <h3 class="mt-2 font-white">{{$clients}}</h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif
          @if(($auth->level=='a93b0f54-ad04-43cc-a34e-9d414be4c129')||
          ($auth->level=='965371c6-3077-4ea4-adc1-bf162c7a22c4')||
          ($auth->level=='e6684739-b90b-4149-a093-9457474a277b')||
          ($auth->level=='625b6020-1087-4898-b2ea-b598d90b6179')||
          ($auth->level=='01f62b38-fce5-43bf-9088-9d4a33a496da'))
          <div class="col-md-4 col-xs-6 col-sm-6 grid-margin stretch-card">
            <div class="card" style="background: #25668F;">
              <div class="card-body">
                <div class="d-md-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center mb-3 mb-md-0">
                    <button class="btn btn-social-icon btn-white btn-rounded">
                      <i class="fa fa-user"></i>
                    </button>
                    <div class="ml-4">
                      <h5 class="mb-0 mt-2 font-white">Jumlah User</h5>
                      <h3 class="mt-2 font-white">{{$user}}</h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif
          <div class="col-md-4 col-xs-6 col-sm-6 grid-margin stretch-card">
            <div class="card" style="background: #205373;">
              <div class="card-body">
                <div class="d-md-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center mb-3 mb-md-0">
                    <button class="btn btn-social-icon btn-white btn-rounded">
                    <i class="fas fa-hdd"></i>
                    </button>
                    <div class="ml-4">
                      <h5 class="mb-0 mt-2 font-white">Jumlah Device</h5>
                      <h3 class="mt-2 font-white">{{$device}}</h3>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
         
        </div>
      </div>
</div>
<div class="row grid-margin">
  <div class="card col-md-12">
    <div class="card-body">
      <h4 class="card-title">
        <i class="fas fa-table"></i>
        Peta Sebaran Device
      </h4>
      <div style=" height: 500px;">
          {!! Mapper::render() !!}
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  if ($("#inline-datepicker-example").length) {
    $('#inline-datepicker-example').datepicker({
      enableOnReadonly: true,
      todayHighlight: true,
    });
  }
</script>
@endsection