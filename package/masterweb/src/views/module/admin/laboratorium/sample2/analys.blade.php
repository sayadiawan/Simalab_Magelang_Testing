@extends('masterweb::template.admin.layout')
@section('title')
    Analisys Management
@endsection

@section('content')





    <style>

    </style>





    <div class="row">
        <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
            <div class="template-demo">
                <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{url('/elits-samples')}}">Sample Management</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><span>Analys</span></li>
                </ol>
                </nav>
            </div>
            </div>
        </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
        <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- utama -->

                        <div class="col-md-12">
                            @php
                            $datelab_samples=\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $samples->datelab_samples)->format('d/m/Y');
                            @endphp
                           <table class="table table-bordered table-striped">
                            <tbody>
                                <tr><td>No. Sample</td><td>No. Rilis</td><td>Tanggal Diterima di Lab</td><td>Nama PELANGGAN</td><td>Alamat</td></tr>
                                <tr><td> {{$samples->codesample_samples}}</td><td> MASIH PROSES</td><td>{{$datelab_samples}}</td><td> {{$customer->name_customer}}</td><td> {{$customer->address_customer}}</td></tr>
                            </tbody>


                            </table>

                        </div>




                    </div>

                    <br><br>

                    <div class="row">
                    <div class="col-md-12">

                        <div class="container">


                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            @php
                                $no=0;
                            @endphp
                            @foreach ($method as $my_method)


                            <li class="nav-item">
                                @php

                                if($id_method==null){

                                    if($no==0){
                                    @endphp
                                            <a class="nav-link active" id="{{str_slug($my_method->name_method,'-')}}-tab" data-toggle="tab" href="#{{str_slug($my_method->name_method,'-')}}" role="tab" aria-controls="{{str_slug($my_method->name_method,'-')}}" aria-selected="true">{{$my_method->params_method}}</a>

                                    @php
                                    }else{
                                    @endphp
                                        <a class="nav-link" id="{{str_slug($my_method->name_method,'-')}}-tab" data-toggle="tab" href="#{{str_slug($my_method->name_method,'-')}}" role="tab" aria-controls="{{str_slug($my_method->name_method,'-')}}" aria-selected="false">{{$my_method->params_method}}</a>

                                    @php
                                    }
                                    $no++;
                                }else{
                                @endphp

                                @php
                                    if($id_method==$my_method->id_method){
                                    @endphp
                                            <a class="nav-link active" id="{{str_slug($my_method->name_method,'-')}}-tab" data-toggle="tab" href="#{{str_slug($my_method->name_method,'-')}}" role="tab" aria-controls="{{str_slug($my_method->name_method,'-')}}" aria-selected="true">{{$my_method->params_method}}</a>

                                    @php
                                    }else{
                                    @endphp
                                        <a class="nav-link" id="{{str_slug($my_method->name_method,'-')}}-tab" data-toggle="tab" href="#{{str_slug($my_method->name_method,'-')}}" role="tab" aria-controls="{{str_slug($my_method->name_method,'-')}}" aria-selected="false">{{$my_method->params_method}}</a>

                                    @php
                                    }
                                    $no++;
                                }
                                @endphp

                            </li>


                        @endforeach
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @php
                            $no2=0;
                            @endphp
                            @foreach ($method as $my_method)
                                @php

                                if($id_method==null){

                                        if($no2==0){
                                        @endphp
                                        <div class="tab-pane fade active show" id="{{str_slug($my_method->name_method,'-')}}" role="tabpanel" aria-labelledby="{{str_slug($my_method->name_method,'-')}}-tab"></div>
                                        <script>
                                        $(document).ready(function()
                                        {

                                            $("#{{str_slug($my_method->name_method,'-')}}").load("/elits-methods/load/{{$my_method->id_method}}/{{$samples->id_samples}}");


                                        });
                                    </script>
                                        @php
                                        }else{
                                        @endphp
                                        <div class="tab-pane fade" id="{{str_slug($my_method->name_method,'-')}}" role="tabpanel" aria-labelledby="{{str_slug($my_method->name_method,'-')}}-tab"></div>
                                        @php
                                        }
                                        $no2++;

                                    }else{
                                @endphp

                                @php
                                        if($id_method==$my_method->id_method){
                                        @endphp
                                        <div class="tab-pane fade active show" id="{{str_slug($my_method->name_method,'-')}}" role="tabpanel" aria-labelledby="{{str_slug($my_method->name_method,'-')}}-tab"></div>
                                        <script>
                                        $(document).ready(function()
                                        {

                                            $("#{{str_slug($my_method->name_method,'-')}}").load("/elits-methods/load/{{$my_method->id_method}}/{{$samples->id_samples}}");


                                        });
                                        </script>
                                        @php
                                        }else{
                                        @endphp
                                        <div class="tab-pane fade" id="{{str_slug($my_method->name_method,'-')}}" role="tabpanel" aria-labelledby="{{str_slug($my_method->name_method,'-')}}-tab"></div>
                                        @php
                                        }
                                        $no2++;
                                }
                                @endphp
                            <script>
                                $(document).ready(function()
                                {
                                    $("ul.nav-tabs li a#{{str_slug($my_method->name_method,'-')}}-tab").click(function(e)
                                    {
                                        $("#{{str_slug($my_method->name_method,'-')}}").load("/elits-methods/load/{{$my_method->id_method}}/{{$samples->id_samples}}");

                                    });
                                });
                            </script>

                            @endforeach
                        </div>




                        </div>


                        </div>




                    </div>

                    <!-- utama -->
                </div>
                <!-- /.row -->
            </div>
        </div>
    </div>



@endsection

@section('scripts')
    <script>
    </script>
@endsection





