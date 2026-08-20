@php
    $column = explode('_',$column)[0];
@endphp
@if ($column == "1")
<div class="row layoutRow" data-id="1">
    <div class="col-md-12 text-right">
        <span><i class="fa fa-bars handle"></i></span>
    </div>
    <div class="col-md-12 layoutColumn" data-id="0">
        <div class="main card">
            @foreach ($modules[0] as $index => $module)
                {{-- jika HTML --}}
                @if (isset($module[0]) AND $module[0] == "html")
                    <div class="card rounded border mb-2">
                        <div class="card-body p-3">
                            <div class="media">
                            <i class="fa fa-news icon-sm text-primary align-self-center mr-3"></i>
                            <div class="media-body">
                                <h6 class="mb-1">{{"HTML CODE"}}</h6>
                                <p class="mb-0 text-muted">
                                    <textarea name="html_code" class="form-control moduleId" data-id="{{"html"}}" id="" cols="30" rows="3">{{$module[1]}}</textarea>                   
                                </p>
                            </div>                              
                            </div> 
                        </div>
                    </div>
                @else
                    @php
                        SmtHelp::getModule($module)
                    @endphp
                @endif
            @endforeach
        </div>
    </div>
</div>
@elseif($column == "2")
<div class="row layoutRow" data-id="2">
    <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>
    <div class="col-md-8 layoutColumn" data-id="0">
        <div class="main card">
            @foreach ($modules[0] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>

    <div class="col-md-4 layoutColumn" data-id="1">
        <div class="main card">
            @foreach ($modules[1] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>
</div>
@elseif($column == "2b")
<div class="row layoutRow" data-id="2b">
    <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>

    <div class="col-md-4 layoutColumn" data-id="0">
        <div class="main card">
            @foreach ($modules[0] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>

    <div class="col-md-8 layoutColumn" data-id="1">
        <div class="main card">
            @foreach ($modules[1] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>
</div>
@elseif($column == "3")
<div class="row layoutRow" data-id="3">
    <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>

    <div class="col-md-4 layoutColumn" data-id="0">
        <div class="main card">
            @foreach ($modules[0] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>

    <div class="col-md-4 layoutColumn" data-id="1">
        <div class="main card">
            @foreach ($modules[1] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>

    <div class="col-md-4 layoutColumn" data-id="2">
        <div class="main card">
            @foreach ($modules[2] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>
</div>
@elseif($column == "4")
<div class="row layoutRow" data-id="4">
    <div class="col-md-12 text-right">
        <span><i class="fa fa-bars handle"></i></span>
        <button class="btn-remove-container"> <i class="fa fa-trash"></i> </button>
    </div>

    <div class="col-md-3 layoutColumn" data-id="0">
        <div class="main card">
            @foreach ($modules[0] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>

    <div class="col-md-3 layoutColumn" data-id="1">
        <div class="main card">
            @foreach ($modules[1] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>

    <div class="col-md-3 layoutColumn" data-id="2">
        <div class="main card">
            @foreach ($modules[2] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>
    
    <div class="col-md-3 layoutColumn" data-id="3">
        <div class="main card">
            @foreach ($modules[3] as $index => $module)
                @php
                    SmtHelp::getModule($module)
                @endphp
            @endforeach
        </div>
    </div>
</div>
@endif