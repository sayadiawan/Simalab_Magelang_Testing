@extends('masterweb::template.admin.layout')

@section('title')
    Layout Module
@endsection

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="">
                <div class="template-demo">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                            <li class="breadcrumb-item"><a href="#"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                            <li class="breadcrumb-item"><a href="#">Layout Module</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><span>List</span></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-8 grid-margin">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Module and Layout Management</h4>
                <div class="row">
                    
                </div>
                <form action="">
                <div class="form-group row">
                    <div class="input-group col-md-4">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-light" type="button">Action</button>
                            </div>
                            <select name="action" id="action" onchange="submit()" class="form-control">
                                @php
                                    $actions = explode(',',$type->action);
                                @endphp
                                @foreach ($actions as $action)
                                <option value="{{$action}}" {{SmtHelp::isSelected($action,request()->action)}}>{{$action}}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    <div class="input-group col-md-6">
                        {{-- <input type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username"> --}}
                        <select name="" id="column-data" class="form-control">
                            <option value="1">1 column</option>
                            <option value="2">2 column - 8 & 4</option>
                            <option value="2b">2 column - 4 & 8</option>
                            <option value="3">3 column</option>
                            <option value="4">4 column</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-sm btn-primary" id="btn-column" type="button"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-md-2 text-right">
                        <button class="btn btn-save btn-primary" type="button" onclick="confirm('Simpan perubahan?')">SAVE</button>
                    </div>
                </div>

                <div id="container">
                    <div class="columnCard">
                        {{-- IF EXITS --}}
                        @if ($LayoutModule != NULL)
                        @foreach (unserialize($LayoutModule['layout']) as $key => $lym)
                            @php
                                echo SmtHelp::GetLayoutModule($key,$lym);
                            @endphp
                        @endforeach
                        @endif
                        {{-- END IF EXITS --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
     <div class="col-4 grid-margin mo-fixed">
        <div class="card">
            <div class="card-body scrool-list">
                <div id="listModule">
                    <h4 class="card-title">List Module</h4>
                    @foreach ($module as $mdl)
                    <div class="card rounded border mb-2 grabbable">
                        <div class="card-body p-3 moduleId" data-id="{{$mdl->id}}">
                            <div class="media">
                            <button class="btn btn-config btn-default btn-sm text-primary align-self-center mr-3" data-id="{{$mdl->id}}" type="button"><i class="fa fa-cog icon-sm"></i></button>
                            <div class="media-body">
                                <h6 class="mb-1">{{$mdl->name}}</h6>
                                <p class="mb-0 text-muted">
                                    {{$mdl->module}}                   
                                </p>
                                <input type="text" name="config" class="config" value="">
                                <div class="collapse">
                                    <div class="card card-body">
                                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.
                                    </div>
                                </div>
                            </div>                              
                            </div> 
                        </div>
                    </div>
                    @endforeach
                    <div class="card rounded border mb-2">
                        <div class="card-body p-3">
                            <div class="media">
                            <i class="fa fa-news icon-sm text-primary align-self-center mr-3"></i>
                            <div class="media-body">
                                <h6 class="mb-1">{{"HTML CODE"}}</h6>
                                <p class="mb-0 text-muted">
                                    <textarea name="html_code" class="form-control moduleId" data-id="{{"html"}}" id="" cols="30" rows="3"></textarea>                   
                                </p>
                            </div>                              
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
    <script>
        $(document).ready(()=>{
            $('body').addClass('sidebar-icon-only');

            var mainContainer = $('.main').toArray();
            var columnContainer = $('.columnCard').toArray();

            var columnDrake = dragula({
                containers:columnContainer,
                removeOnSpill:true,
                moves: function (el, container, handle) {
                    return handle.classList.contains('handle');
                }
            });

            $(document).on('click', '.btn-config', function () {
                var id = $(this).data("id");

                $('#exampleModal').modal('show')

                $.ajax({
                    url:"{{url('adm-layout/get_option_view')}}",
                    method:"GET",
                    data:{'moduleId':id,'_token':'{{csrf_token()}}'},
                    success:function(data)
                    {
                        $('#modal-body').html(data);
                        $(this).closest('.moduleId').find('.config').val('oke');
                        console.log($(this).closest('.moduleId').find('.config'));
                    }
                });
            });

            // btn-simpan action
            $('#btn-column').on('click',()=>{
                //get value select column
                var columnData = $('#column-data').val();

                $.ajax({
                    url:"{{url('adm-layout/getColumn')}}",
                    method:"GET",
                    data:{'columnData':columnData,'_token':'{{csrf_token()}}'},
                    success:function(data)
                    {
                        $('.columnCard').append(data);
                        drake.containers.push($('.main').get(0));
                        drake.containers.push($('.main').get(1));
                        drake.containers.push($('.main').get(2));
                        drake.containers.push($('.main').get(3));
                        
                    }
                });
            });

            $(document).on('click', '.btn-save', function () {
                var id = $(this).data("id");
                let layoutData = [];
                $('.layoutRow').each(function(i, row) {
                    var idRow = ($(row).data('id')+"_"+i).toString();
                    var layoutColumn = [];
                    $(row).find(".layoutColumn").each(function(z, column) {
                        var moduleData = [];
                        var columnId = $(column).data('id');
                        $(column).find(".moduleId").each(function(j, module) {
                            var moduleId = $(module).data('id');
                            
                            if(moduleId == "html")
                            {
                                var html_val = $(module).val();
                                var html_code = [moduleId,html_val];
                                moduleData.push(html_code);
                                // console.log(html_code);
                            }else{
                                moduleData.push(moduleId);
                            }
                        });
                        layoutColumn[z] = moduleData;
                    });
                    layoutData[idRow] = layoutColumn;
                });

                layoutData = Object.assign({},layoutData);
                console.log(layoutData);
                const typePage = "{{request()->segment(3)}}";
                const actionPage = $('#action').val() ;
                $.ajax({
                    url:"{{url('adm-layout/store')}}",
                    method:"POST",
                    data:{'layoutData':layoutData,'type':typePage,'action':actionPage,'_token':'{{csrf_token()}}'},
                    dataType: 'JSON',
                    success:function(data)
                    {
                        alert('Data berhasil disimpan');
                    }
                });
            });

            var counter = 0;

            var drake = dragula({
                containers : mainContainer,
                copy: function (el, source) {
                    return source === document.getElementById('listModule')
                },
                accepts: function (el, target) {
                    return target !== document.getElementById('listModule')
                },
                revertOnSpill: true,
                removeOnSpill: true,
            });

            $('#add_section').on('click', function() {       
                var elem = '<div class="main card"></div>';       
                $('#container').append(elem);
                console.log(drake.containers.push($('.main').last().get(0)));
            })
            $('#add_item').on('click', function() {       
                var elem = '<div class="item">' + (counter++) + '</div>';       
                $('.main').last().append(elem);
            })
            // drake.containers.push($('.main').get(0));
            // drake.containers.push($('.main').get(1));
            // drake.containers.push($('.main').toArray());
            drake.containers.push(document.getElementById('listModule'));
        });
    </script>
@endsection

@section('css')
    <style id="styles" type="text/css">
        .grabbable {
            cursor: move; /* fallback if grab cursor is unsupported */
            cursor: grab;
            cursor: -moz-grab;
            cursor: -webkit-grab;
        }

        /* (Optional) Apply a "closed-hand" cursor during drag operation. */
        .grabbable:active {
            cursor: grabbing;
            cursor: -moz-grabbing;
            cursor: -webkit-grabbing;
        }
        .main{
            padding: 15px;
            margin:5px 0;
        }
        .columnCard > .row{
            background: #f0f0f0;
            margin-bottom: 5px;
            padding: 10px;
        }
    </style>
@endsection