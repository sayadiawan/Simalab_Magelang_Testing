@php
$data = Smt\Masterweb\Models\Testimoni::all();
@endphp
<div class="page-section p-110-cont" style="background-image: url('{{asset('assets/public/images/pattern/bg-testi.jpg')}}');background-repeat: no-repeat !important;background-size: cover !important;background-position: center center !important;">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 blog-main-posts">
                <h2 class="section-title-2 text-center pr-0"><span class="bold font-white">TESTIMONI</span></h2>
                <div class="garis-putih"></div>
                <div class="testim">
                    <!--         <div class="testim-cover"> -->
                    <div class="wrap">

                        <span id="right-arrow" class="arrow right fa fa-chevron-right"></span>
                        <span id="left-arrow" class="arrow left fa fa-chevron-left "></span>
                        <ul id="testim-dots" class="dots">
                        @foreach($data as $item)
                            <li class="dot"></li>
                        @endforeach
                        </ul>
                        <div id="testim-content" class="cont">

                            @foreach($data as $item)
                                <div class="active">
                                    <div class="img"><img src="{{asset('assets/public/images/testimoni/'.$item->file_testimoni)}}" alt="{{ $item->name_testimoni }}"></div>
                                    <h2>{{ $item->name_testimoni }}</h2>
                                    <p>{!! $item->description_testimoni !!}</p>
                                </div>
                            @endforeach

                        </div>

                    </div>
                    <!--         </div> -->
                </div>

            </div>
        </div>

    </div>
</div>

