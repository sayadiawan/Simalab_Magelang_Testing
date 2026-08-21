<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Diawan</title>

    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="robots" content="index, follow">
    <meta name="keywords" content="Diawan">
    <meta name="description" content="Diawan">
    <meta name="author" content="Diawan.io">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- FAVICONS -->
    <link rel="shortcut icon" href="{{ asset('assets/public/images/favicon.png') }}">

    <!-- CSS -->
    <!-- REVOSLIDER CSS SETTINGS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('araset') }}/rs-plugin/css/settings.min.css" media="screen" />
    <!--  BOOTSTRAP -->
    <link rel="stylesheet" href="{{ asset('araset') }}/css/bootstrap.min.css">
    <!--  GOOGLE FONT -->
    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700%7COpen+Sans:400,300,700' rel='stylesheet'
        type='text/css'>
    <!-- ICONS ELEGANT FONT & FONT AWESOME & LINEA ICONS  -->
    <link rel="stylesheet" href="{{ asset('araset') }}/css/icons-fonts.css">
    <!--  CSS THEME -->
    <link rel="stylesheet" href="{{ asset('araset') }}/css/style.css">
    <!-- ANIMATE -->
    <link rel='stylesheet' href="{{ asset('araset') }}/css/animate.min.css">


    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-127182076-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-127182076-1');
    </script>
    <!-- Facebook Pixel Code -->
    <script>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1865845287066527');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1865845287066527&ev=PageView&noscript=1" /></noscript>
    <!-- End Facebook Pixel Code -->


</head>

<body>

    <!-- LOADER -->
    <div id="loader-overflow">
        <div id="loader3">Please enable JS</div>
    </div>

    <div id="wrap" class="boxed ">
        <div class="grey-bg"> <!-- Grey BG  -->
            <!-- HEADER 1 BLACK MOBILE-NO-TRANSPARENT -->
            <header id="nav" class="header header-1 black-header mobile-no-transparent">
                <div class="header-wrapper">
                    <div class="container clearfix">
                        <div class="logo-row">

                            <!-- LOGO -->
                            <div class="logo-container-2">
                                <div class="logo-2">
                                    <a href="http://diawan.io" class="clearfix">
                                        <img src="{{ asset('assets/admin/images/logo/diawan.png') }}" title="Diawan"
                                            alt="Diawan" class="logo-img">
                                    </a>
                                </div>
                            </div>
                            <!-- BUTTON -->
                            <div class="menu-btn-respons-container">
                                <button type="button" class="navbar-toggle btn-navbar collapsed" data-toggle="collapse"
                                    data-target="#main-menu .navbar-collapse">
                                    <span aria-hidden="true" class="icon_menu hamb-mob-icon"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- MAIN MENU CONTAINER -->
                    <div class="main-menu-container">

                        <div class="container-m-30 clearfix">

                            <!-- MAIN MENU -->
                            <div id="main-menu">
                                <div class="navbar navbar-default" role="navigation">

                                    <!-- MAIN MENU LIST -->
                                    <nav class="collapse collapsing navbar-collapse nav-center">
                                        <ul id="nav-onepage" class="nav navbar-nav">

                                            <!-- MENU ITEM -->
                                            <li class="current">
                                                <a href="#index-link">
                                                    <div class="main-menu-title">BERANDA</div>
                                                </a>
                                            </li>

                                            <!-- MENU ITEM -->
                                            <li>
                                                <a href="#fitur-link">
                                                    <div class="main-menu-title">FITUR</div>
                                                </a>
                                            </li>

                                            <!-- MENU ITEM -->
                                            <li>
                                                <a href="#kontak-link">
                                                    <div class="main-menu-title">KONTAK KAMI</div>
                                                </a>
                                            </li>



                                        </ul>

                                    </nav>

                                </div>
                            </div>
                            <!-- END main-menu -->

                        </div>
                        <!-- END container-m-30 -->

                    </div>
                    <ul class="cd-header-buttons">
                        <li><a href="{{ asset('login') }}" class="button small teal"><i class="fa fa-lock"></i>LOGIN</a>
                        </li>
                        <li><a href="{{ asset('register') }}" class="button small yellow"><i class="fa fa-user"></i>FREE
                                TRIAL</a></li>
                    </ul>
            </header>

            <div class="auth">
                <center>
                    <a href="{{ asset('login') }}" class="button small teal"><i class="fa fa-lock"></i>MASUK</a>
                    <a href="{{ asset('register') }}" class="button small yellow"><i class="fa fa-user"></i>DAFTAR</a>
                </center>
            </div>
        </div>
    </div>

    <!-- Kontak -->
    @php
        $data = Smt\Masterweb\Models\Contact::first();
    @endphp
    <div class="page-section pt-100">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-sm-7 ">
                    <h2 class="">
                        <span class="font-light blue1 text-center">Buat akun uji coba pertamamu!</span>
                    </h2>
                    <div class="garis-kontak"></div>
                    <p class="">
                        Jika Anda memiliki pertanyaan atau permasalahan tentang project, pembuatan sistem, atau segala
                        hal terkait teknologi informasi, bisa kontak kami langsung atau bisa datang langsun ke alamat
                        kami dibawah ini :
                    </p>
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="cis-cont">
                                <div class="cis-icon">
                                    <div class="icon icon-basic-geolocalize-05"></div>
                                </div>
                                <div class="cis-text">
                                    <p class="font-kontak">{{ strip_tags($data->alamat) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12">
                            <div class="cis-cont">
                                <div class="cis-icon">
                                    <div class="icon icon-basic-ipod"></div>
                                </div>
                                <div class="cis-text">
                                    <p class="font-kontak mt-10">{{ $data->phone }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12">
                            <div class="cis-cont">
                                <div class="cis-icon">
                                    <div class="icon icon-basic-paperplane"></div>
                                </div>
                                <div class="cis-text">
                                    <p class="font-kontak mt-10">{{ $data->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-sm-5 mb-50 k-card">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="contact-form" class="form-kontak" action="{{ url('register_process') }}"
                        method="POST">
                        <h3>
                            <span class="font-light blue1">Lengkapi formulir dibawah ini</span>
                        </h3>
                        <div class="garis-kontak"></div>
                        @csrf
                        <input type="hidden" class="form-control" id="root_firebase" name="root_firebase"
                            value="{!! Ramsey\Uuid\Uuid::uuid4() !!}">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- <label>Your name *</label> -->
                                <input type="text" value="" data-msg-required="Please enter your name"
                                    maxlength="100" class="controled" name="username" id="username"
                                    placeholder="Username" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <!-- <label>Your name *</label> -->
                                <input type="text" value="" data-msg-required="Please enter your name"
                                    maxlength="100" class="controled" name="name" id="name"
                                    placeholder="Nama Lengkap" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <!-- <label>Your name *</label> -->
                                <input type="text" value="" data-msg-required="Please enter your name"
                                    maxlength="100" class="controled" name="instansi" id="instansi"
                                    placeholder="Nama Instansi" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <!-- <label>Your email address *</label> -->
                                <input type="email" value=""
                                    data-msg-required="Please enter your email address"
                                    data-msg-email="Please enter a valid email address" maxlength="100"
                                    class="controled" name="email" id="email" placeholder="Alamat Email"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <!-- <label>Your email address *</label> -->
                                <input type="number" value="" class="controled" name="phone"
                                    id="phone" placeholder="Nomor Handphone" required>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="form-group text-center">
                                <div class="g-recaptcha" name="g-recaptcha-response"
                                    data-sitekey="{{ config('app.recaptcha_site_key') }}"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center-xxs tengah">
                                <input type="submit" value="DAFTAR SEKARANG" class="button medium blue mb-20"
                                    data-loading-text="Loading...">
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- FOOTER 2 BLACK -->
    <footer id="kontak-link" class="page-section pt-80 pb-50 footer2-black">
        <div class="container">
            <div class="row">

                <div class="col-md-4 col-sm-4 widget">
                    <div class="logo-footer-cont">
                        <a href="" class="a-text">
                            <h3 class="a-text">DIAWAN.IO</h3>
                        </a>
                    </div>
                    <div class="footer-2-text-cont">
                        <address>
                            Jalan Stonen Timur No. 7A, Gajah Mungkur<br>
                            —, Jawa Tengah 50233
                        </address>
                    </div>
                    <div class="footer-2-text-cont">


                        <i class="icon_chat"></i><a class="a-text" target="_blank" href="">
                            &nbsp;+62857-4774-7725 (Marketing)</a><br>
                        <i class="icon_chat"></i><a class="a-text" target="_blank" href="">
                            &nbsp;+62895-4142-55244 (Support)</a>
                    </div>
                    <div class="footer-2-text-cont">
                        <i class="fa fa-envelope-square"></i> &nbsp;<a class="a-text"
                            href="mailto:support@diawan.io">support@diawan.io</a>
                    </div>
                </div>


                <div class="col-md-4 col-sm-4 widget">
                    <h4>Bantuan</h4>
                    <ul class="links-list a-text-cont">
                        <li><a href="{{ asset('bantuan/panduan') }}">Panduan</a></li>
                        <li><a href="{{ asset('bantuan/faq') }}">F.A.Q</a></li>
                        <li><a href="{{ asset('bantuan/pembayaran') }}">Pembayaran</a></li>
                        <li><a href="{{ asset('bantuan/ketentuan') }}">Syarat dan Ketentuan</a></li>
                    </ul>
                </div>

                <div class="col-md-4 col-sm-4 widget">
                    <h4>Blog</h4>

                </div>
            </div>

            <div class="footer-2-copy-cont clearfix">
                <!-- Social Links -->
                <div class="footer-2-soc-a right">
                    <a href="http://facebook.com/diawan.io" title="Facebook" target="_blank"><i
                            class="fa fa-facebook-square"></i></a>
                    <a href="https://twitter.com/diawanio" title="Twitter" target="_blank"><i
                            class="fa fa-twitter-square"></i></a>
                    <a href="https://instagram.com/diawan.io" title="Instagram" target="_blank"><i
                            class="fa fa-instagram"></i></a>
                    <a href="https://www.youtube.com" title="Youtube" target="_blank"><i
                            class="fa fa-youtube-square"></i></a>
                </div>

                <!-- Copyright -->
                <div class="left">
                    <a class="footer-2-copy" href="http://www.diawan.io" target="_blank">&copy; DIAWAN 2020</a>
                </div>


            </div>

        </div>
    </footer>


    <style>
        .helpme {
            position: fixed;
            bottom: 0;
            right: 0;
            /* extra styling */
            /* margin:.5em; */
            margin-bottom: 50px;
            margin-right: 10px;
            z-index: 1000;
        }

        .btn-wa {
            border-radius: 50%;
            height: 60px;
            width: 60px;
            background-color: #4DC247;
            color: #ffffff;
            border: none;
            /* padding: 20px; */
            text-align: center;
            text-decoration: none;
            display: inline-block;

        }

        .helpme:hover {
            border-color: #fff;
            color: #fff;
            -webkit-animation-duration: 1s;
            animation-duration: 1s;
            -webkit-animation-fill-mode: both;
            animation-fill-mode: both;
            -webkit-animation-timing-function: ease-in-out;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
            -webkit-animation-iteration-count: infinite;
            /* animation: shakeThatBooty 0.3s linear 1;
          -webkit-animation: shakeThatBooty 0.3s linear 1;
          -moz-animation: shakeThatBooty 0.3s linear 1;
          -o-animation: shakeThatBooty 0.3s linear 1;
          -ms-animation: shakeThatBooty 0.3s linear 1; */

            /* cursor: pointer; */
            animation-name: bounce;
            -moz-animation-name: bounce;
        }

        @keyframes bounce {

            0%,
            100%,
            20%,
            50%,
            80% {
                -webkit-transform: translateY(0);
                -ms-transform: translateY(0);
                transform: translateY(0)
            }

            40% {
                -webkit-transform: translateY(-30px);
                -ms-transform: translateY(-30px);
                transform: translateY(-30px)
            }

            60% {
                -webkit-transform: translateY(-15px);
                -ms-transform: translateY(-15px);
                transform: translateY(-15px)
            }
        }
    </style>

    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
    <p id="back-top">
        <a href="#top" title="Back to Top"><span class="icon icon-arrows-up"></span></a>
    </p>

    </div><!-- End BG -->
    </div><!-- End wrap -->

    <!-- JS begin -->

    <!-- jQuery  -->
    <script type="text/javascript" src="{{ asset('araset') }}/js/jquery-1.11.2.min.js"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="{{ asset('araset') }}/js/bootstrap.min.js"></script>

    <!-- MAGNIFIC POPUP -->
    <script src='{{ asset('araset') }}/js/jquery.magnific-popup.min.js'></script>

    <!-- PORTFOLIO SCRIPTS -->
    <script type="text/javascript" src="{{ asset('araset') }}/js/isotope.pkgd.min.js"></script>
    <script type="text/javascript" src="{{ asset('araset') }}/js/imagesloaded.pkgd.min.js"></script>
    <script type="text/javascript" src="{{ asset('araset') }}/js/masonry.pkgd.min.js"></script>

    <!-- COUNTER -->
    <!--<script type="text/javascript" src="{{ asset('araset') }}/js/jquery.countTo.js"></script>-->

    <!-- APPEAR -->
    <script type="text/javascript" src="{{ asset('araset') }}/js/jquery.appear.js"></script>


    <script type="text/javascript" src="{{ asset('araset') }}/js/owl.carousel.min.js"></script>

    <!-- ONE PAGE NAV -->
    <script src="{{ asset('araset') }}/js/jquery.nav.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //ONE PAGE NAV	---------------------------------------------------------------------------
            var top_offset = $('header').height() - 1; // get height of fixed navbar

            $('#nav-onepage').onePageNav({
                currentClass: 'current',
                changeHash: false,
                scrollSpeed: 700,
                scrollOffset: top_offset,
                scrollThreshold: 0.5,
                filter: '',
                easing: 'swing',
                begin: function() {
                    //I get fired when the animation is starting
                },
                end: function() {
                    //I get fired when the animation is ending
                },
                scrollChange: function($currentListItem) {
                    //I get fired when you enter a section and I pass the list item of the section
                }
            });

        }); //END document.ready 
    </script>

    <!-- MAIN SCRIPT -->
    <script src="{{ asset('araset') }}/js/main.js"></script>

    <!-- REVOSLIDER SCRIPTS  -->
    <!-- SLIDER REVOLUTION 4.x SCRIPTS  -->
    <script type="text/javascript" src="{{ asset('araset') }}/rs-plugin/js/jquery.themepunch.tools.min.js"></script>
    <script type="text/javascript" src="{{ asset('araset') }}/rs-plugin/js/jquery.themepunch.revolution-parallax.min.js">
    </script>

    <!-- SLIDER REVOLUTION INIT  -->
    <script type="text/javascript">
        jQuery(document).ready(function() {
            if ((navigator.appVersion.indexOf("Win") != -1) && (ieDetect == false)) {
                jQuery('#rs-fullwidth').revolution({
                    dottedOverlay: "none",
                    delay: 16000,
                    startwidth: 1170,
                    startheight: 665,
                    hideThumbs: 200,

                    thumbWidth: 100,
                    thumbHeight: 50,
                    thumbAmount: 5,

                    //fullScreenAlignForce: "off",

                    navigationType: "none",
                    navigationArrows: "solo",
                    navigationStyle: "preview4",

                    hideTimerBar: "on",

                    touchenabled: "on",
                    onHoverStop: "on",

                    swipe_velocity: 0.7,
                    swipe_min_touches: 1,
                    swipe_max_touches: 1,
                    drag_block_vertical: false,

                    parallax: "scroll",
                    parallaxBgFreeze: "on",
                    parallaxLevels: [45, 40, 35, 50],
                    parallaxDisableOnMobile: "on",

                    keyboardNavigation: "off",

                    navigationHAlign: "center",
                    navigationVAlign: "bottom",
                    navigationHOffset: 0,
                    navigationVOffset: 20,

                    soloArrowLeftHalign: "left",
                    soloArrowLeftValign: "center",
                    soloArrowLeftHOffset: 20,
                    soloArrowLeftVOffset: 0,

                    soloArrowRightHalign: "right",
                    soloArrowRightValign: "center",
                    soloArrowRightHOffset: 20,
                    soloArrowRightVOffset: 0,

                    shadow: 0,
                    fullWidth: "on",
                    fullScreen: "off",

                    spinner: "spinner4",

                    stopLoop: "off",
                    stopAfterLoops: -1,
                    stopAtSlide: -1,

                    shuffle: "off",

                    autoHeight: "off",
                    forceFullWidth: "off",

                    hideThumbsOnMobile: "off",
                    hideNavDelayOnMobile: 1500,
                    hideBulletsOnMobile: "off",
                    hideArrowsOnMobile: "off",
                    hideThumbsUnderResolution: 0,

                    hideSliderAtLimit: 0,
                    hideCaptionAtLimit: 0,
                    hideAllCaptionAtLilmit: 0,
                    startWithSlide: 0,
                    //fullScreenOffsetContainer: ""	
                });
            } else {
                jQuery('#rs-fullwidth').revolution({
                    dottedOverlay: "none",
                    delay: 16000,
                    startwidth: 1170,
                    startheight: 760,
                    hideThumbs: 200,

                    thumbWidth: 100,
                    thumbHeight: 50,
                    thumbAmount: 5,

                    navigationType: "none",
                    navigationArrows: "solo",
                    navigationStyle: "preview4",

                    hideTimerBar: "on",

                    touchenabled: "on",
                    onHoverStop: "on",

                    swipe_velocity: 0.7,
                    swipe_min_touches: 1,
                    swipe_max_touches: 1,
                    drag_block_vertical: false,

                    parallax: "mouse",
                    parallaxBgFreeze: "on",
                    parallaxLevels: [0],
                    parallaxDisableOnMobile: "on",

                    keyboardNavigation: "off",

                    navigationHAlign: "center",
                    navigationVAlign: "bottom",
                    navigationHOffset: 0,
                    navigationVOffset: 20,

                    soloArrowLeftHalign: "left",
                    soloArrowLeftValign: "center",
                    soloArrowLeftHOffset: 20,
                    soloArrowLeftVOffset: 0,

                    soloArrowRightHalign: "right",
                    soloArrowRightValign: "center",
                    soloArrowRightHOffset: 20,
                    soloArrowRightVOffset: 0,

                    shadow: 0,
                    fullWidth: "on",
                    fullScreen: "off",

                    spinner: "spinner4",

                    stopLoop: "off",
                    stopAfterLoops: -1,
                    stopAtSlide: -1,

                    shuffle: "off",

                    autoHeight: "off",
                    forceFullWidth: "off",

                    hideThumbsOnMobile: "off",
                    hideNavDelayOnMobile: 1500,
                    hideBulletsOnMobile: "off",
                    hideArrowsOnMobile: "off",
                    hideThumbsUnderResolution: 0,

                    hideSliderAtLimit: 0,
                    hideCaptionAtLimit: 0,
                    hideAllCaptionAtLilmit: 0,
                    startWithSlide: 0,

                });
            }
        }); //ready

        $(".fullwidth-slider").owlCarousel({
            slideSpeed: 350,
            singleItem: true,
            // autoHeight: true,
            navigation: true,
            navigationText: ["<span class='icon icon-arrows-left'></span>",
                "<i class='icon icon-arrows-right'></span>"
            ]
        });
    </script>

    <!-- JS end -->

</body>

</html>
