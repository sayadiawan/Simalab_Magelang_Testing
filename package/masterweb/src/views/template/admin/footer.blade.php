@php
    $opt = DB::table('ms_options')->first();
@endphp
<div class="d-sm-flex justify-content-center justify-content-sm-between">
  <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">{{$opt->footer}}. All rights reserved.</span>
</div>