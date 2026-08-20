@php
//get data option website
$option = \Smt\Masterweb\Models\Option::first();

//function sub menu
function re_menu($id)
{
$subnav = \Smt\Masterweb\Models\Menu::all()->where('publish','1')->sortBy('order')->where('upmenu',$id);
if (count($subnav)>0) {
@endphp
<ul class="sub">
    <?php foreach ($subnav as $sub_nav) { ?>
        <li><a href="/{{ $sub_nav->link }}" title="{{ $sub_nav->name }}">{{ strtoupper($sub_nav->name) }}</a></li>
    <?php } ?>
</ul>
@php

}
}
@endphp
<!-- <div class="text-right">
    <div id="google_translate_element" style="display: none"></div>
    <button onclick="changeLanguageByButtonClick('en')">ENG</button>
    <button onclick="changeLanguageByButtonClick('id')">ID</button>
</div> -->

<header id="nav" class="header header-1 no-transparent mobile-no-transparent affix-top">
    <!-- TOP BAR -->
    {{-- <div class="top-bar">
        <div class="container-m-30 clearfix">
          
          <!-- LEFT SECTION -->
          <ul class="top-bar-section left">
            <li><a href="#" title="Facebook"><i class="fa fa-facebook"></i></a></li>
            <li><a href="#" title="Twitter"><i class="fa fa-twitter"></i></a></li>
            <li><a href="#" title="Linked"><i class="fa fa-linkedin"></i></a></li>
            <li><a href="#" title="Pinterest"><i class="fa fa-pinterest"></i></a></li>
            <li><a href="#" title="Email"><i class="fa fa-envelope"></i></a></li>
          </ul>
          
          <!-- RIGHT SECTION -->
          <ul class="top-bar-section right ">
            <li>
            <a href="#"><img class="img-bendera" src="{{ asset('assets/public/images/country/b-indonesia.png')}}"></a>
    </li>
    <li>
        <a href="#"><img class="img-bendera" src="{{ asset('assets/public/images/country/b-inggris.png')}}"></a>
    </li>

    </div>
    </div> --}}

    <div class="header-wrapper">
        <div class="container-m-30 clearfix">
            <div class="logo-row">

                <!-- LOGO -->
                <div class="logo-container-2">
                    <div class="logo-2">
                        <a href="/beranda" class="clearfix">
                            <img src="{{ ($option->logo == NULL) ? asset('assets/public/images/logo/favicon.png') : asset('assets/public/images/logo/'.$option->logo)}}" class="logo-img" alt="Logo">
                        </a>
                    </div>
                </div>
                <!-- BUTTON -->
                <div class="menu-btn-respons-container">
                    <button type="button" class="navbar-toggle btn-navbar collapsed" data-toggle="collapse" data-target="#main-menu .navbar-collapse">
                        <span aria-hidden="true" class="icon_menu hamb-mob-icon"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN MENU CONTAINER -->
        <div class="main-menu-container">

            <div class="">

                <!-- MAIN MENU -->
                <div id="main-menu" class="font-poppins">
                    <div class="navbar navbar-default" role="navigation">

                        <!-- MAIN MENU LIST -->
                        <nav class="collapse collapsing navbar-collapse nav-center">
                            <ul class="nav navbar-nav">

                                @php
                                //get data navigation
                                $nav = \Smt\Masterweb\Models\Menu::all()->where('publish','1')->sortBy('order')->where('upmenu','0');

                                foreach ($nav as $nav_menu) {

                                $subnav = \Smt\Masterweb\Models\Menu::all()->where('publish','1')->sortBy('order')->where('upmenu',$nav_menu->id);
                                if (count($subnav)>0) {
                                $sbb = "parent";
                                } else {
                                $sbb = "";
                                }

                                @endphp
                                <li class="<?= $sbb; ?><?= Request::segment(1) == $nav_menu->link ? "current" : NULL ?>">
                                    <a href="/{{ $nav_menu->link }}" class="main-menu-title" title="{{ $nav_menu->name }}"><span id="{{ $nav_menu->link }}">{{ strtoupper($nav_menu->name) }}</span></a>
                                    @php
                                    //calll function sub menu
                                    re_menu($nav_menu->id)
                                    @endphp
                                </li>
                                @php

                                }
                                @endphp

                            </ul>

                        </nav>

                    </div>
                </div>
                <!-- END main-menu -->
                
                <ul class="cd-header-buttons">
                    <li><a class="cd-search-trigger" href="#cd-search"><span></span></a></li>
                    <li>
                        <div class="dropdown">
                          <a class="main-menu-title dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-globe" data-toggle="tooltip" data-placement="bottom" title=""></span>
                          </a>
                          <div id="google_translate_element" style="display: none"></div>
                          <div class="dropdown-menu text-center" aria-labelledby="dropdownMenuButton" style="padding:10px;">
                            <a class="dropdown-item" onclick="changeLanguageByButtonClick('id')"><img src="{{asset('id.png')}}"> Indonesia</a> <br>
                            <a class="dropdown-item" onclick="changeLanguageByButtonClick('en')"><img src="{{asset('us.png')}}"> English</a>
                          </div>
                        </div>
                    </li>
                    <li>
                        <a href="/penawaran" class="main-menu-title" title="Buat Penawaran"><span id="penawaran">BUAT PENAWARAN</span></a>
                    </li>
                </ul> <!-- cd-header-buttons -->
                @php
                $linkSearch = SmtHelp::get_linkmenu(29); 
                @endphp
                <div id="cd-search" class="cd-search">
						<form class="form-search" id="searchForm" action="{{url($linkSearch)}}" method="get">
							<input type="text" value="" name="q" id="q" placeholder="Cari disini...">
						</form>
					</div>
            </div>
            <!-- END container-m-30 -->

        </div>

    </div>
    <!-- END header-wrapper -->

</header>
{{-- <div class="mb-60"></div> --}}
<br><br><br><br>