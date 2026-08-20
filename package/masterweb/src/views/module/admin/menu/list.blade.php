@extends('masterweb::template.admin.layout')

@section('title')
    Admin Menu
@endsection

@section('css')
    
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
                            <li class="breadcrumb-item"><a href="{{url('/menuadm')}}">Admin Menu</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
  <div class="col-12 grid-margin" id="order-menu">
    <div class="card">
      <div class="card-body">
        <div class="d-flex">

          <div class="p-2">
            <button type="button" data-toggle="modal" data-target="#addModal" data-whatever="@fat" class="btn btn-info btn-icon-text">
            <i class="fa fa-plus-circle mr-2"></i> Tambah Data
            </button>
          </div>
        </div>
        @foreach ($data as $menu)
        <div class="main-menu-container">
          <div class="card rounded border mb-2 main-menu" id="{{$menu->id}}">
            <div class="card-body p-3">
              <div class="media">
                <i class="fa fa-sort icon-sm align-self-center mr-3"></i>
                <div class="media-body">
                    <div class="d-flex bd-highlight">
                      <div class="mr-auto p-2 bd-highlight">
                        <h6 class="sub-handle"><i class="{{$menu->icon}}"></i> {{$menu->name}}</h6>
                        <p class="mb-0 text-muted">
                          {{($menu->type == 0)?"link" : "url"}} : {{$menu->link}}
                        </p>
                      </div>
                      <div class="p-2 bd-highlight">
                        <p>
                          <form onsubmit="return confirm('Delete this menu permanently?')" class="d-inline" action="{{url('menuadm/destroy/'.$menu->id)}}" method="POST">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="btn btn-sm btn-primary edit-module" data-id="{{$menu->id}}">Edit</button>
                            <button class="btn btn-sm btn-danger">Delete</button>                       
                          </form>
                        </p>
                      </div>
                    </div>
                </div>                              
              </div> 
            </div>

            @php
                $subdata = \Smt\Masterweb\Models\AdminMenu::all()->where('publish','1')->sortBy('order')->where('upmenu',$menu->id);
            @endphp
            <div class="mr-4 ml-4 py-2 submenu-container">
            @foreach ($subdata as $submenu)
              <div class="card rounded border mb-2 sublist-menu" id="{{$submenu->id}}">
                <div class="card-body p-3">
                    <div class="media">
                      <i class="fa fa-sort icon-sm align-self-center mr-3"></i>
                      <div class="media-body">
                          <div class="d-flex bd-highlight">
                            <div class="mr-auto p-2 bd-highlight">
                              <h6 class="mb-1 sub-handle">{{$submenu->name}}</h6>
                              <p class="mb-0 text-muted">
                                {{($submenu->type == 0)?"link" : "url"}} : {{$submenu->link}}                            
                              </p>
                            </div>
                            <div class="p-2 bd-highlight">
                              <p><form onsubmit="return confirm('Delete this user permanently?')" 
                                class="d-inline" 
                                action="{{url('menuadm/destroy/'.$submenu->id)}}" 
                                method="POST">
    
                                    @csrf
    
                                    <input 
                                    type="hidden" 
                                    name="_method" 
                                    value="DELETE">
                                    <button type="button" class="btn btn-primary btn-sm edit-module" data-id="{{$submenu->id}}">edit</button>
                                    <button class="btn btn-sm btn-danger">delete</button>
                            </form></p>
                            </div>
                          </div>
                      </div>                              
                    </div> 
                </div>
              </div>
            @endforeach
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalLabel">Tambah Menu Admin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="post" action="{{url('menuadm/store')}}">
              @csrf
              <label for="recipient-name" class="col-form-label">Parent:</label>
              <select name="upmenu" class="form-control">
                <option value="0">Induk</option>
                @foreach ($parent as $dparent)
                    <option value="{{$dparent->id}}">{{$dparent->name}}</option>
                @endforeach
              </select>

              <label>Nama</label>
              <input type="text" class="form-control" name="name" value="">

              <label>Icon</label>
              <input type="text" class="form-control" name="icon" value="">

              <label>Link</label>
              <input type="text" class="form-control" name="link" value="">

              <label>Type</label>
              <input type="radio" name="type" value="0" checked>Link | <input type="radio" name="type" value="1">Url
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalLabel">Edit Menu Admin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="post" action="{{url('menuadm/update')}}">
              @csrf
              <label for="recipient-name" class="col-form-label">Parent:</label>
              <select name="upmenu" id="upmenu" class="form-control">
                <option value="0">Induk</option>
                @foreach ($parent as $dparent)
                    <option value="{{$dparent->id}}">{{$dparent->name}}</option>
                @endforeach
              </select>

              <input type="hidden" id="id" class="form-control" name="id" value="">

              <label>Nama</label>
              <input type="text" id="name" class="form-control" name="name" value="">

              <label>Icon</label>
              <input type="text" id="icon" class="form-control" name="icon" value="">

              <label>Link</label>
              <input type="text" id="link" class="form-control" name="link" value="">

              <label>Type</label>
              <input type="radio" name="type_link" value="0" checked>Link | <input type="radio" name="type_link" value="1">Url
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
    var containers = $('.main-menu-container').toArray();
    var sub_container = $('.submenu-container').toArray();

    dragula({
      containers: sub_container,
    })
        .on('drop', function(el) {
          var main = new Array();
          $('#order-menu').find('.main-menu').each(function(){
              var subId = new Array();
              var mainId = $(this).attr('id');
              $(this).find('.sublist-menu').each(function(){
                subId.push($(this).attr('id'));
              });
              main.push([mainId,subId])
          });
          $.ajax({
            url:"{{url('menuadm/sort')}}",
            method:"POST",
            data:{'main':main,'_token':'{{csrf_token()}}'},
            success:function(data)
            {
              // alert('Data berhasil diperbarui');
            }
          });
        })
    dragula({
        containers: containers,
        moves: function (el, container, handle) {
          return handle.classList.contains('handle');
        }
      })
      .on('drop', function(el) {
        var main = new Array();
        $('#order-menu').find('.main-menu').each(function(){
            var subId = new Array();
            var mainId = $(this).attr('id');
            $(this).find('.sublist-menu').each(function(){
              subId.push($(this).attr('id'));
            });
            main.push([mainId,subId])
        });
        $.ajax({
            url:"{{url('menuadm/sort')}}",
            method:"POST",
            data:{'main':main,'_token':'{{csrf_token()}}'},
            success:function(data)
            {
              // alert('Data berhasil diperbarui');
            }
        });
      });

      // edit module
      $(document).on('click','.edit-module',function(){
        var id = $(this).attr('data-id');
        $.ajax({
            url:"{{url('menuadm/data')}}",
            method:"POST",
            data:{'id':id,'_token':'{{csrf_token()}}'},
            success:function(data)
            {
              var obj = JSON.parse(data);
              
              $('#id').val(obj.id);
              $('#upmenu').val(obj.upmenu);
              $('#name').val(obj.name);
              $('#icon').val(obj.icon);
              $('#link').val(obj.link);
              
              $("input[name='type_link']:checked").val(obj.type);
              
              $('#editModal').modal('show')
            }
        });
      });
  </script>
@endsection