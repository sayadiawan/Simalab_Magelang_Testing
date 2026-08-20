<div class="page-title-cont page-title-large page-title-img grey-dark-bg pt-250" style="background-image: url({{asset('assets/public/images/pattern/img-page-title.jpg')}})">
    <div class="relative container align-left">
        <div class="row">
            <div class="col-md-8">
                <h1 class="page-title mt-50">{{SmtHelp::get_linkname()}}</h1>
            </div>

            <div class="col-md-4">
                <div class="breadcrumbs">
                    <a href="{{url('')}}">Beranda</a><span class="slash-divider">/</span><a href="{{url(request()->segment(1))}}"> {{ request()->segment(1) }} </a>@if (!empty(request()->segment(3)))
                        <span class="slash-divider">/</span><a href="#"> {{ request()->segment(3) }} </a>@endif
                </div>
            </div>

        </div>
    </div>
</div>