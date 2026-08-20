@extends('masterweb::template.admin.layout')

@section('title')
Permintaan Pemeriksaan Klinik
@endsection

@section('content')



<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
                <div class="template-demo">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('/home')}}"><i
                                        class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{url('/elits-permohonan-uji-klinik')}}">Laboraturium</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>Permohonan Uji
                                    Management</span></li>
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
                <a href="{{route('elits-permohonan-uji-klinik.create')}}">

                    <button type="button" class="btn btn-info btn-icon-text" onclick="localStorage.clear();">
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
                    <table id='empTable' class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Sampel</th>
                                <th>TGL</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
    $(document).ready(function(){

      // DataTable
      $('#empTable').DataTable({
         processing: true,
         serverSide: true,
         ajax: "{{route('elits-permohonan-uji-klinik.getSamplePagination')}}",
         columns: [
            { data: 'number'},
            { data: 'code_permohonan_uji' },
            { data: 'date_create' },
            { data: 'customer_permohonan_uji' },
          
            
            { data: 'status' },
          
         ]
      });

    });
</script>
@endsection