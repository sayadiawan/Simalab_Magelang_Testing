@extends('masterweb::template.admin.layout')

@section('title')
Samples Management
@endsection

@section('content')

<script>
    var role="admin"
</script>

<script src="{{asset('assets/admin/cdn-local/js/firebase.js')}}"></script>
<script src="{{ asset('assets/admin/js/firebase-js/firebase/config.js')}}"></script>
<script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js')}}"></script>


<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
                <div class="template-demo">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('/home')}}"><i
                                        class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{url('/elits-samples')}}">Laboraturium</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>Sample Management</span></li>
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

            <?php
            if(getAction("create")){
        ?>
            <div class="p-2">
                <a href="{{route('elits-samples.create')}}">

                    <button type="button" class="btn btn-info btn-icon-text">
                        Tambah Data
                        <i class="fa fa-plus btn-icon-append"></i>
                    </button>
                </a>
                <a href="{{route('elits-samples.create')}}">

                    <button type="button" class="btn btn-info btn-icon-text">
                        Tambah Data
                        <i class="fa fa-plus btn-icon-append"></i>
                    </button>
                </a>
            </div>

            <?php
            }
        ?>

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
                                <th>No Sampel</th>
                                <th>TGL</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <?php
                        if(getAction("update")||getAction("delete")){
                    ?>
                                <th>Actions</th>
                                <?php
                        }
                    ?>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $no=1;
                            @endphp
                            @foreach ($samples as $samples)
                            @php
                            $user = \Smt\Masterweb\Models\User::find($samples->user_samples );
                            $customer = \Smt\Masterweb\Models\Customer::find($samples->customer_samples);
                            $datelab_samples=\Carbon\Carbon::createFromFormat('Y-m-d H:i:s',
                            $samples->datelab_samples)->format('d/m/Y');
                            @endphp
                            <tr>
                                <td>{{$no++}}</td>
                                <td>{{$samples->codesample_samples}}<br><br>{!!
                                    QrCode::size(100)->generate($samples->codesample_samples); !!}</td>
                                <td>{{$datelab_samples}}</td>
                                <td>{{$customer->name_customer}}</td>
                                <td>
                                    @if($samples->status=='0')
                                    <a href="{{route('elits-deligations', [$samples->id_samples])}}">
                                        <button type="button" class="btn btn-outline-warning">
                                            <i class="fa fa-users" aria-hidden="true"></i>
                                            Proses Delegation
                                        </button>
                                    </a>
                                    @elseif($samples->status=='1')
                                    <a href="{{route('elits-samples.analys', [$samples->id_samples])}}">
                                        <button type="button" class="btn btn-outline-primary">
                                            <i class="fa fa-flask" aria-hidden="true"></i>
                                            Proses Analisa
                                        </button>
                                    </a>
                                    @elseif($samples->status=='2')
                                    <a href="{{route('elits-samples.analys', [$samples->id_samples])}}">
                                        <button type="button" class="btn btn-outline-light">
                                            <i class="fa fa-credit-card" aria-hidden="true"></i>
                                            Proses Invoice
                                        </button>
                                    </a>
                                    @else
                                    <a href="{{route('elits-samples.analys', [$samples->id_samples])}}">
                                        <button type="button" class="btn btn-outline-success">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            Selesai
                                        </button>
                                    </a>

                                    @endif
                                </td>
                                <?php
                        if(getAction("update")||getAction("delete")){
                    ?>
                                <td>
                                    <?php
                          if(getAction("update")){
                      ?>
                                    <a href="{{route('elits-samples.edit', [$samples->id_samples])}}">
                                        <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </a>

                                    <?php
                          }
                      ?>

                                    <?php
                          if( getSpesialAction(Request::segment(1),'verification-sampel','')){
                      ?>
                                    <a href="{{route('elits-samples.edit', [$samples->id_samples])}}">
                                        <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </a>

                                    <?php
                          }
                      ?>

                                    <?php
                          if(getAction("delete")){
                      ?>
                                    <form onsubmit="return confirm('Delete this user permanently?')" class="d-inline"
                                        action="{{route('elits-samples.destroy', [$samples->id_samples])}}"
                                        method="POST">

                                        @csrf

                                        <input type="hidden" name="_method" value="DELETE">

                                        <button type="submit" class="btn btn-outline-danger btn-rounded btn-icon">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </form>
                                    <?php
                          }
                        ?>
                                </td>
                                <?php
                        }
                    ?>

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