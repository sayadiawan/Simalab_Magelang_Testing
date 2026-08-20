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
            </div>

            <?php
            }
        ?>

            <div class="p-2">
                <button type="button" class="btn btn-primary mr-1" data-toggle="modal" data-target="#importExcel">
                    IMPORT EXCEL
                </button>

                <!-- Import Excel -->
                <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="post" action="/elits-excel/formImports" enctype="multipart/form-data">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                                </div>
                                <div class="modal-body">

                                    {{ csrf_field() }}

                                    <div class="form-group">

                                        <div class="row">

                                            <div class="col-md-12">
                                                <label for="method">Pilih Method</label>
                                                <select class="form-select form-control" id="method" name="method"
                                                    aria-label="Pilih Method">
                                                    @foreach($method as $method)
                                                    <option value="{{$method->id_method}}">{{$method->params_method}}
                                                    </option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>
                                    </div>

                                    <label>Pilih file excel</label>
                                    <div class="form-group">

                                        <input type="file" name="file" required="required">
                                    </div>

                                </div>
                                <div id="test">
                                </div>
                                <div class="modal-footer ">
                                    <button type="button" id="download" class="btn btn-primary mr-auto">Download
                                        Format</button>


                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Import</button>
                                </div>
                            </div>
                        </form>

                    </div>
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
         ajax: "{{route('elits-analys.getSamplePagination')}}",
         columns: [
            { data: 'number'},
            { data: 'codesample_samples' },
            { data: 'datelab_samples' },
            { data: 'customer_samples' },
          
            
            { data: 'status' },
          
         ]
      });

      $("#download").click(function(){

        var id_method = $('#method').find(":selected").val()
        console.log(id_method)
        var url = "{{route('elits-excel.downloadFormImports', ':id')}}";
       
        url =url.replace(":id" , id_method);
        console.log(url);

        window.location = url;
      
      })



    });
</script>
@endsection