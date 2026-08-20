@php
$data = Smt\Masterweb\Models\Slideshow::all();
@endphp

<div class="relative">
    <div class="rs-fullscr-container">

        <div id="rs-fullwidth" class="tp-banner dark-bg">
            <ul>
                @foreach ($data as $slideshow)
                    
                @endforeach
                <!-- SLIDE 1 -->
                <li data-transition="zoomout" data-slotamount="1" data-masterspeed="1500" data-thumb="images/revo-slider/terka-thumb.jpg" data-saveperformance="on" data-title="HASWELL">
                    <!-- MAIN IMAGE -->

                    <img src="{{asset('assets/public/images/slideshow/'.$slideshow->images)}}" alt="slidebg1" data-lazyload="{{asset('assets/public/images/slideshow/'.$slideshow->images)}}" data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">

                    <!-- LAYERS -->

                    <!--PARALLAX & OPACITY container -->
                    <div class="rs-parallaxlevel-4 opacity-scroll2">
                        <!-- LAYER NR. 1 -->
                        <div class="tp-caption font-white light-73-wide sfb tp-resizeme" data-x="center" data-hoffset="0" data-y="center" data-voffset="-50" data-speed="500" data-start="850" data-easing="Power1.easeInOut" data-splitin="none" data-splitout="none" data-elementdelay="0.1"
                            data-endelementdelay="0.1" style="z-index: 7; max-width: auto; max-height: auto; white-space: nowrap;">{{$slideshow->deskripsi}}</span>
                        </div>

                        <!-- LAYER NR. 2 -->
                        <div class="tp-caption font-white norm-16-wide tp-left sfb tp-resizeme hide-0-736" data-x="center" data-hoffset="0" data-y="center" data-voffset="25" data-speed="900" data-start="1500" data-easing="Power3.easeInOut" data-splitin="none" data-splitout="none"
                            data-elementdelay="0.1" data-endelementdelay="0.1" style="z-index: 9; max-width: auto; max-height: auto; white-space: nowrap;">Konsultan TI<span class="slash-divider-10">/</span>Pembuatan Website<span class="slash-divider-10">/</span>Implementasi Sistem Informasi<span class="slash-divider-10">/</span>Pembuatan Aplikasi Mobile
                        </div>

                        <!-- LAYER NR. 3 -->
                        <div class="tp-caption center-0-478 sfb" data-x="center" data-hoffset="0" data-y="center" data-voffset="115" data-speed="900" data-start="1350" data-easing="Power3.easeInOut" data-splitin="none" data-splitout="none" data-elementdelay="0.1" data-endelementdelay="0.1"
                            style="z-index: 9; max-width: auto; max-height: auto; white-space: nowrap;"><a class="button large thin hover-dark tp-button white" href="{{$slideshow->url}}"><i class="fa fa-whatsapp mr-5"></i>HUBUNGI KAMI</a>
                        </div>

                    </div>
                </li>

            </ul>

        </div>

    </div>

</div>

<div class="page-section service-css mb-100">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 ">
                <div class="box-container">
                    <div class="box-item">
                        <div class="flip-box">
                            <div class="flip-box-front text-center box-service">
                                <div class="inner2 color-white">
                                    <div class="fes6-box ">
                                        <div class="icon blue1 icon-basic-sheet-pencil mb-10"></div>
                                        <h3>
                                            <span class="bold blue1">Konsultan TI</span>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="flip-box-back text-center box-service">
                                <div class="inner color-white">
                                    <h3>
                                        <span class="bold color-white">Konsultan TI</span>
                                    </h3>
                                    <div>
                                        Kami memberikan solusi terbaik untuk masalah Information Technology (IT) Anda, bahkan tanpa harus menggunakan layanan kami.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 ">
                <div class="box-container">
                    <div class="box-item">
                        <div class="flip-box">
                            <div class="flip-box-front text-center box-service">
                                <div class="inner2 color-white">
                                    <div class="fes6-box">
                                        <div class="icon blue1 icon-basic-laptop mb-10"></div>
                                        <h3>
                                            <span class="bold blue1">Pembuatan Website</span>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="flip-box-back text-center box-service">
                                <div class="inner color-white">
                                    <h3>
                                        <span class="bold color-white">Pembuatan Website</span>
                                    </h3>
                                    <div>
                                        Website adalah bagian dari Strategi Bisnis yang merupakan salah satu aspek penting dalam keberhasilan di pasar online.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 ">
                <div class="box-container">
                    <div class="box-item">
                        <div class="flip-box">
                            <div class="flip-box-front text-center box-service">
                                <div class="inner2 color-white">
                                    <div class="fes6-box">
                                        <div class="icon blue1 icon-basic-share mb-10"></div>
                                        <h3>
                                            <span class="bold blue1">Implementasi Sistem Informasi
                                            </span>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="flip-box-back text-center box-service">
                                <div class="inner color-white">
                                    <h3>
                                        <span class="bold color-white">Implementasi Sistem Informasi 
                                    </span>
                                    </h3>
                                    <div>
                                        Kami membantu implementasi teknologi informasi melalui Aplikasi Website, Aplikasi Android, Jaringan, dan Dedicated Server sesuai dengan kebutuhan Perusahaan Anda.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 ">
                <div class="box-container">
                    <div class="box-item">
                        <div class="flip-box">
                            <div class="flip-box-front text-center box-service">
                                <div class="inner2 color-white">
                                    <div class="fes6-box">
                                        <div class="icon blue1 icon-basic-smartphone mb-10"></div>
                                        <h3>
                                            <span class="bold blue1">Pembuatan Aplikasi Mobile
                                            </span>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="flip-box-back text-center box-service">
                                <div class="inner color-white">
                                    <h3>
                                        <span class="bold color-white">Pembuatan Aplikasi Mobile
                                    </span>
                                    </h3>
                                    <div>
                                        Kami membantu Anda merealisasikan Aplikasi Gawai (Mobile Apps) impian Anda. Dengan menggunakan Teknologi Android Java dan Android Kotlin.    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>