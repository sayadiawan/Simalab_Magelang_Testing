
@php
    $column = explode('_',$column)[0];
@endphp
@if ($column == "1")
    @if(isset($modules[0]))
        @foreach ($modules[0] as $index => $module)
            @php
                SmtHelp::takeIt($module);
            @endphp
        @endforeach
    @endif
@elseif($column == "2")
<section>
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @if(isset($modules[0]))
                    @foreach ($modules[0] as $index => $module)
                        @php
                            SmtHelp::takeIt($module);
                        @endphp
                    @endforeach
                @endif
            </div>
        
            <div class="col-md-4">
                @if(isset($modules[1]))
                    @foreach ($modules[1] as $index => $module)
                        @php
                            SmtHelp::takeIt($module);
                        @endphp
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>
@elseif($column == "2b")
<div class="row layoutRow" data-id="2b">
    <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>

    <div class="col-md-4 layoutColumn" data-id="0">
        <div class="main card">
            @if(isset($modules[0]))
                @foreach ($modules[0] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-md-8 layoutColumn" data-id="1">
        <div class="main card">
            @if(isset($modules[1]))
                @foreach ($modules[1] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>
</div>
@elseif($column == "3")
<div class="row layoutRow" data-id="3">
    <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>

    <div class="col-md-4 layoutColumn" data-id="0">
        <div class="main card">
            @if(isset($modules[0]))
                @foreach ($modules[0] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-md-4 layoutColumn" data-id="1">
        <div class="main card">
            @if(isset($modules[1]))
                @foreach ($modules[1] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-md-4 layoutColumn" data-id="2">
        <div class="main card">
            @if(isset($modules[2]))
                @foreach ($modules[2] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
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
            @if(isset($modules[0]))
                @foreach ($modules[0] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-md-3 layoutColumn" data-id="1">
        <div class="main card">
            @if(isset($modules[1]))
                @foreach ($modules[1] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>

    <div class="col-md-3 layoutColumn" data-id="2">
        <div class="main card">
            @if(isset($modules[2]))
                @foreach ($modules[2] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>
    
    <div class="col-md-3 layoutColumn" data-id="3">
        <div class="main card">
            @if(isset($modules[3]))
                @foreach ($modules[3] as $index => $module)
                    @php
                        SmtHelp::takeIt($module);
                    @endphp
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif