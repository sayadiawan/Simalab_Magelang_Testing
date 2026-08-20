@extends('masterweb::template.admin.layout')

@section('title')
Privileges Menu Manager
@endsection

@section('content')

<div class="row">
  <div class="col-12 grid-margin stretch-card">
    <div class="card">
      <div class="">
        <div class="template-demo">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
              <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i>
                  Beranda</a></li>
              <li class="breadcrumb-item"><a href="{{url('/privileges-features')}}">PrivilegesMenu</a></li>
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
        <button data-toggle="modal" data-target="#addModal" type="button" class="btn btn-info btn-icon-text">
          Tambah Data
          <i class="fa fa-plus btn-icon-append"></i>
        </button>
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
                <th width="15">No</th>
                <th width="100">Name</th>
                <th>Upmenu</th>
                <th>Sublink</th>
                <th width="200">Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
              $no=1;
              @endphp
              @foreach ($data as $data)
              <tr>
                <td>{{$no++}}</td>
                <td>{{$data->name_privilege_features}}</td>
                @php
                $menu =\Smt\Masterweb\Models\AdminMenu::where("id",$data->menu_id)->first();
                @endphp

                <td>{{isset($menu->link)?$menu->link:''}}</td>
                <td>{{isset($data->sub_link)?$data->sub_link:''}}</td>
                <td>

                  <form onsubmit="return confirm('Apakah anda yakin ingin menghapus data ini?')" class="d-inline"
                    action="{{route('privileges-features.destroy', [$data->id_privilege_features])}}" method="POST">

                    @csrf

                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" data-id="{{$data->id_privilege_features}}"
                      class="btn btn-outline-success btn-rounded btn-icon edit-module">
                      <i class="fas fa-pencil-alt"></i>
                    </button>
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




<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true"
  style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel">Tambah Menu Privelege</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" action="{{url('privileges-features/store')}}">
          @csrf
          <label for="recipient-name" class="col-form-label">Parent:</label>
          <select name="upmenu" id="upmenu" class="form-control">
            <option value="0">Induk</option>
            @foreach ($parent as $dparent)
            <option value="{{$dparent->id}}">{{$dparent->name}}</option>
            @endforeach
          </select>

          <label>Nama</label>
          <input type="text" id="name" class="form-control" name="name" value="">

          <label>Sub-Link</label>
          <input type="text" id="link" class="form-control" name="link" value="">

          <br>
          <input type="checkbox" name="bydevice" id="bydevice" checked data-toggle="toggle" data-on="ByDevice"
            data-off="No" data-width="120">



      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Simpan</button>
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true"
  style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ModalLabel">Edit Menu Privelege</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" action="{{url('privileges-features/update')}}">
          @csrf
          <label for="recipient-name" class="col-form-label">Parent:</label>
          <select name="upmenu" id="upmenu-edit" class="form-control">
            <option value="0">Induk</option>
            @foreach ($parent as $dparent)
            <option value="{{$dparent->id}}">{{$dparent->name}}</option>
            @endforeach
          </select>


          <input type="hidden" id="id-edit" class="form-control" name="id" value="">

          <label>Nama</label>
          <input type="text" id="name-edit" class="form-control" name="name" value="">

          <label>Sub-Link</label>
          <input type="text" id="link-edit" class="form-control" name="link" value="">

          <br>
          <input type="checkbox" name="bydevice" id="bydevice" checked data-toggle="toggle" data-on="ByDevice"
            data-off="No" data-width="120">


      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Simpan</button>
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
      </form>
    </div>
  </div>
</div>








@endsection


@section('scripts')
<script>
  // edit module
  $(document).on('click','.edit-module',function(){
        var id = $(this).attr('data-id');
        $.ajax({
            url:"{{url('privileges-features/data')}}",
            method:"POST",
            data:{'id':id,'_token':'{{csrf_token()}}'},
            success:function(data)
            {
              var obj = JSON.parse(data);
              
              $('#id-edit').val(obj.id_privilege_features);
              $('#upmenu-edit').val(obj.menu_id);
              $('#name-edit').val(obj.name_privilege_features);
              $('#link-edit').val(obj.sub_link );

              console.log(obj.bydevice)
              if(obj.bydevice!=0){
                
                $('.toggle').addClass( "btn-primary" ).removeClass( "btn-light off" );
              }else{
                $('.toggle').removeClass( "btn-primary" ).addClass( "btn-light off" );
              }

            
            
              
              $('#editModal').modal('show')
            }
        });
      });
</script>
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

@endsection


@section('css')

<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
  rel="stylesheet">
@endsection