{{-- config --}}
@php
    $columns = $set_columns; // get colom
    $datas = $set_datas;//get tabel
    $fileds = $set_fileds; // get type colom
@endphp
{{-- end config --}}

@extends('masterweb::template.admin.layout')

@section('title')
Master Category Portofolio
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
              <li class="breadcrumb-item"><a href="{{url('/adm-categoryportofolio')}}">Category Portofolio</a></li>
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

    @if (request()->segment(3)=="create")

    {{-- form create --}}
    <form action="/master/SMStore" id="form-tambah" class="forms-sample" method="post">
                
        {{-- make session get table --}}
            @php
                request()->session()->put('table',$table);
            @endphp
        {{-- make session get table --}}

        {{-- get type colom --}}
        @foreach ($fileds as $item)
            
            {{-- unset colom data from type --}}
            @if (in_array($item->getName(),$set_displays))
                @php
                    continue;
                @endphp
            @endif
            {{-- unset colom data from type --}}
            
            <div class="form-group">
                
                {{-- set label colom input --}}
                    <label for=""><b>{{ucwords(str_replace('_',' ',$item->getName()))}}</b></label>
                {{-- set label colom input --}}

                {{-- set input array(type colom, nama colom) --}}
                @php
                    if (isset($relation[$item->getName()])) {
                        $set_relation = $relation;
                        
                    }else{
                        $set_relation = null;
                    }

                    if (isset($set_upload[$item->getName()])) {
                        $set_type = 'upload';

                    }else{
                        $set_type = $item->getType();

                    }
                @endphp
                    {!! SmtHelp::set_input_crud( $set_type,$item->getName(),null,$set_relation) !!}
                {{-- set input --}}
            </div>

          

        @endforeach

    </form>
    
    <button class="btn btn-light btn-sm">Kembali</button>
    <button type="submit" class="btn btn-primary mr-2 btn-sm btn-simpan">Simpan</button>
    {{-- end form create --}}

    @elseif(request()->segment(3)=="edit")

    {{-- form edit --}}
    <form action="/master/SMUpdate/{{request()->segment(4)}}" id="form-tambah" class="forms-sample" method="post">
                
        {{-- make session get table --}}
            @php
                request()->session()->put('table',$table);

                $id = request()->segment(4);

                $set = DB::table($table)->where('id',$id)->first();
                // dd($set);
            @endphp
        {{-- make session get table --}}

        {{-- get type colom --}}
        @foreach ($fileds as $item)
            
            {{-- unset colom data from type --}}
            @if (in_array($item->getName(),$set_displays))
                @php
                    continue;
                @endphp
            @endif
            {{-- unset colom data from type --}}

            @php
                $name = $item->getName();
            @endphp
            
            <div class="form-group">
                
                {{-- set label colom input --}}
                    <label for=""><b>{{ucwords(str_replace('_',' ',$item->getName()))}}</b></label>
                {{-- set label colom input --}}
                @php
                    if (isset($relation[$item->getName()])) {
                        $set_relation = $relation;
                    }
                    if (isset($set_upload[$item->getName()])) {
                        $set_type = 'upload';

                    }else{
                        $set_type = $item->getType();

                    }
                @endphp
                {{-- set input array(type colom, nama colom) --}}
                    {!! SmtHelp::set_input_crud( $set_type,$item->getName(),$set->$name,$set_relation) !!}
                {{-- set input --}}
            
            </div>

        @endforeach

    </form>
    
    <button class="btn btn-light btn-sm">Kembali</button>
    <button type="submit" class="btn btn-primary mr-2 btn-sm btn-simpan">Simpan</button>
    {{-- end form edit --}}

    @else

    {{-- list --}}
    <div class="d-flex">
      <div class="mr-auto p-2">
      </div>
      <div class="p-2">
        <div class="">
            {{-- button add data --}}
            <a href="{{'/master/'.request()->segment(2).'/create'}}">
                <button type="button" class="btn btn-sm btn-info btn-icon-text">
                    Tambah Data
                    <i class="fa fa-plus btn-icon-append"></i>                             
                </button>
            </a>
            {{-- button add data --}}
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
          <table id="order-listing" class="table table-bordered">
            <thead>
              <tr>
                   {{-- get data name colom --}}
                   @foreach ($columns as $key => $column)
                        
                        {{-- unset colom --}}
                        @if (in_array($column,$set_displays))
                            @php
                                continue;
                            @endphp
                        @endif
                        {{-- unset colom --}}
                    
                        {{-- show colom --}}
                        <th>{{ucwords(str_replace('_',' ',$column))}}</th>
                        {{-- show colom --}}

                    @endforeach
                    {{-- get data name colom --}}
                    <th>Actions</th>
              </tr>
            </thead>
            <tbody>
                
                {{-- get data tabel --}}
                @foreach ($datas as $data)
                    <tr>
                        {{-- get data filed --}}
                        @foreach ($columns as $key => $column)
                        
                            {{-- unset data tabel --}}
                            @if (in_array($column,$set_displays))
                                    @php
                                        continue;
                                    @endphp
                            @endif
                            {{-- unset data tabel --}}

                            @php
                                // foreach ($relation as $key => $value) {
                                    // dd($relation[$column]);
                                // }
                            @endphp
                            {{-- get data from tabel set setting get data filed --}}
                            @if (isset($relation[$column]))
                                @php
                                    $value = $relation[$column];
                                    // dd($relation[$column]);

                                    $set_relation = DB::table($value[0])->select($value[2])->where($value[1],$data->$column)->first();
                                    $column_relation = $value[2];

                                    if (empty($set_relation)) {
                                        $get_relation = "-";
                                    } else {
                                        $get_relation = $set_relation->$column_relation;
                                    }
                                @endphp
                                <td>{{$get_relation}}</td>
                            @else
                                <td>{{$data->$column}}</td>
                            @endif
                            {{-- get data from tabel set setting get data filed --}}

                        @endforeach
                        {{-- get data filed --}}

                        <td>
                            
                            <a href="{{'/master/'.request()->segment(2).'/edit/'.$data->id_content}}">
                                <button type="button" class="btn btn-outline-warning btn-rounded btn-icon">
                                    <i class="fas fa-pencil-alt"></i>                                                    
                                </button> 
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-rounded btn-icon btn-hapus" data-id="{{ $data->id_content }}" data-table="{{ $table }}">
                                <i class="fas fa-trash"></i>                                                    
                            </button> 

                        </td>
                    </tr>
                @endforeach
                {{-- get data tabel --}}

            </tbody>
          </table>
        </div>
      </div>
    </div>
    {{-- end list --}}
    
    @endif

  </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('.btn-simpan').click(function () {
            $('#form-tambah').ajaxSubmit({
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: 	function(response){
                        if(response==true){
                            swal({title: "Success !", text: "Berhasil Menyimpan Data", icon: "success"})
                                .then(function(){ 
                                    document.location='/master/{{ request()->segment(2) }}';
                                    // location.reload(true);
                                }
                            );
                        }else{
                            swal("Error!", "Controller Error", "error");
                        }
                    },error: function(){
                        swal("Error!", "Error Ajax", "error");
                    }
            }).submit();
        })
        $('.btn-hapus').click(function () {
            var id = $(this).data('id');
            var table = $(this).data('table');
            swal({
                    title: "Apakah anda yakin?",
                    text: "Untuk menghapus data",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        type: 		'get',
                        url: 		'/master/SMDelete/' +id+'/'+table ,
                        success: 	function(response){
                            
                            if(response==true){
                                swal({title: "Success!", text: "Berhasil Menghapus Data", icon: "success"})
                                    .then(function(){ 
                                    location.reload(true);
                                });
                            }else{
                                swal("Hapus Data Gagal !", {
                                    icon: "warning",
                                });
                            }
                        },
                        error: function(){
                            swal("ERROR", "Hapus Data Gagal.", "error");
                        }
                    });
                } else {
                    swal("Cancelled", "Hapus Data Dibatalkan.", "error");
                }
            });	
        })
    })
</script>
@endsection