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
                                    <a href="#" class="clearfix">
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
                    </ul> <!-- cd-header-buttons -->
                    <!-- 					<div class="">
                        <a href="#kontak-link"><div class="main-menu-title">MASUK</div></a>

     </div>
 --> <!-- END main-menu-container -->
            </header>

            <div class="auth">
                <center>
                    <a href="{{ asset('login') }}" class="button small teal"><i class="fa fa-lock"></i>MASUK</a>
                    <a href="{{ asset('register') }}" class="button small yellow"><i class="fa fa-user"></i>DAFTAR</a>
                </center>
            </div>

            <!-- REVO SLIDER FULLSCREEN 1 -->
            <div id="index-link" class="relative">
                <div class="rs-fullscr-container">

                    <div id="rs-fullwidth" class="tp-banner dark-bg">
                        <ul>
                            <li data-transition="zoomout" data-slotamount="1" data-masterspeed="1500"
                                data-thumb="images/revo-slider/thumb-muda.jpg" data-saveperformance="on"
                                data-title="HASWELL">
                                <!-- MAIN IMAGE -->

                                <img src="{{ asset('araset') }}/images/revo-slider/muda.jpg" alt="slidebg1"
                                    data-lazyload="{{ asset('araset') }}/images/revo-slider/muda.jpg"
                                    data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">

                                <!-- LAYERS -->

                                <!--PARALLAX & OPACITY container -->
                                <div class="rs-parallaxlevel-4 opacity-scroll2">
                                    <!-- LAYER NR. 1 -->
                                    <div class="tp-caption font-white light-60 sfb tp-resizeme" data-x="center"
                                        data-hoffset="0" data-y="center" data-voffset="-125" data-speed="500"
                                        data-start="850" data-easing="Power1.easeInOut" data-splitin="none"
                                        data-splitout="none" data-elementdelay="0.1" data-endelementdelay="0.1"
                                        style="z-index: 7; max-width: auto; max-height: auto; white-space: nowrap;">
                                        <span class="bold">Diawan</span>
                                    </div>

                                    <!-- LAYER NR. 2 -->
                                    <div class="tp-caption font-white light-60 tp-left sfb tp-resizeme hide-0-736"
                                        data-x="center" data-hoffset="0" data-y="center" data-voffset="-40"
                                        data-speed="900" data-start="1500" data-easing="Power3.easeInOut"
                                        data-splitin="none" data-splitout="none" data-elementdelay="0.1"
                                        data-endelementdelay="0.1"
                                        style="z-index: 9; max-width: auto; max-height: auto; white-space: nowrap;">
                                        <span class="bold">Manage</span> Your IOT Devices
                                    </div>

                                    <!-- LAYER NR. 3 -->
                                    <div class="tp-caption center-0-478 tp-left sfb" data-x="center" data-hoffset="0"
                                        data-y="center" data-voffset="50" data-speed="900" data-start="1350"
                                        data-easing="Power3.easeInOut" data-splitin="none" data-splitout="none"
                                        data-elementdelay="0.1" data-endelementdelay="0.1"
                                        style="z-index: 9; max-width: auto; max-height: auto; white-space: nowrap;">
                                        <a class="button medium hover-dark tp-button yellow"
                                            href="{{ asset('register') }}"><b>FREE </b> TRIAL NOW</a>
                                    </div>

                                </div>
                            </li>






                            <!-- SLIDE 3 -->
                            <li data-transition="zoomout" data-slotamount="1" data-masterspeed="1500"
                                data-thumb="{{ asset('araset') }}/images/revo-slider/thumb-muda.jpg"
                                data-saveperformance="on" data-title="LEADS TO REVENEU">

                                <!-- MAIN IMAGE -->
                                <!-- 								<div  style="background: linear-gradient(to bottom, rgba(100,142,78,1), rgba(100,142,78,0)); z-index: 9999999;">
 --> <img src="{{ asset('araset') }}/images/revo-slider/muda.jpg" alt="citybg"
                                    data-lazyload="{{ asset('araset') }}/images/revo-slider/muda.jpg"
                                    data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">
                                <!-- 								</div>
 --> <!-- LAYERS -->

                                <!--PARALLAX & OPACITY container -->
                                <div class="rs-parallaxlevel-4 opacity-scroll2">
                                    <!-- LAYER NR. 1 -->

                                    <div class="tp-caption font-white light-60 sfb tp-resizeme" data-x="center"
                                        data-hoffset="0" data-y="center" data-voffset="-120" data-speed="500"
                                        data-start="850" data-easing="Power1.easeInOut" data-splitin="none"
                                        data-splitout="none" data-elementdelay="0.1" data-endelementdelay="0.1"
                                        style="z-index: 7; max-width: auto; max-height: auto; white-space: nowrap; ">
                                        Monitoring<span class="bold"> Reporting</span> Controlling
                                    </div>

                                    <!-- LAYER NR. 2 -->
                                    <div class="tp-caption font-white light-60 tp-left sfb tp-resizeme hide-0-736"
                                        data-x="center" data-hoffset="0" data-y="center" data-voffset="-50"
                                        data-speed="900" data-start="1500" data-easing="Power3.easeInOut"
                                        data-splitin="none" data-splitout="none" data-elementdelay="0.1"
                                        data-endelementdelay="0.1"
                                        style="z-index: 9; max-width: auto; max-height: auto; white-space: nowrap; ">
                                        <span class="bold"></span> Your Devices
                                    </div>

                                    <!-- LAYER NR. 3 -->



                                </div>

                            </li>

                        </ul>

                    </div>

                </div>

            </div>
        </div>




        <!-- FEATURES 5 & TESTIMONIALS 1 -->
        <div id="about-link" class="page-section pt-80 pb-80">
            <div class="container">


                <div class="row">

                    <div class="row text-center pt-20">
                        <h1><span class="font-light">Fitur Utama</span></h1>

                        <div class="col-md-4 col-sm-4 pb-50">
                            <div class="text-center wow fadeIn" data-wow-delay="50ms">
                                <div class="icon icon-ecommerce-graph-increase" style="font-size:48px;"></div>
                                <h3> <span class="text-bold">Monitoring</span> </h3>
                                <p>
                                    Anda dapat melihat, memantau, device anda secara mudah melalui diawan.

                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 pb-50">
                            <div class="text-center wow fadeIn" data-wow-delay="200ms">
                                <div class="icon icon-basic-archive-full" style="font-size:48px;"></div>
                                <h3><span class="text-bold">Reporting</span> </h3>
                                <p>Anda dapat mengunduh data laporan sesuai dengan keinginan Anda, kapanpun Anda
                                    butuhkan.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 pb-50">
                            <div class="text-center wow fadeIn" data-wow-delay="400ms">
                                <div class="icon icon-basic-target" style="font-size:48px;"></div>
                                <h3><span class="text-bold">Controlling</span></h3>
                                <p>Anda dapat mengatur device IOT anda melalui diawan. Mengaktifkan, mematikan, hingga
                                    merubah pengaturan device, hanya dalam 1 langkah.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- FEATURES 7 -->
    <div id="fitur-link" class="page-section grey-light-bg clearfix">
        <div class="fes7-img-cont col-md-5">
            <div class="fes7-img"
                style="background-image: url({{ asset('araset') }}/images/revo-slider/fiturawan.jpg)"></div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-6 fes7-text-cont p-80-cont">
                    <h1><span class="font-light">Fitur Diawan</span></h1>
                    <div class="row">

                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-basic-webpage-img-txt"></div>
                                </div>
                                <h3>Realtime Data</h3>
                                <p>Data yang disajikan akan terupdate secara real-time (per detik)</p>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="200ms">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-arrows-move"></div>
                                </div>
                                <h3>Data Editing</h3>
                                <p>Data dapat diatur / disesuaikan dengan kebutuhan (offset management)</p>
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="400ms">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-basic-map"></div>
                                </div>
                                <h3>Device Map Interactive</h3>
                                <p>Dilengkapi dengan peta interaktif, sehingga lokasi alat dapat dilihat dengan mudah.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="400ms">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-basic-share"></div>
                                </div>
                                <h3>Collaboration</h3>
                                <p>Anda dapat mengajak partner Anda untuk berkolaborasi, sehingga alat dapat dikelola
                                    secara bersama-sama.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="400ms">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-basic-world"></div>
                                </div>
                                <h3>Dashboard Management</h3>
                                <p>Anda dapat mengatur data yang akan ditampilkan, sesuai dengan kebutuhan Anda.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="400ms">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-basic-webpage-multiple"></div>
                                </div>
                                <h3>Export Data</h3>
                                <p>Data dapat diunduh dan dieksport ke dalam bentuk xls,csv maupun pdf.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="600ms">
                                <div class="fes7-box-icon">
                                    <div class="icon icon-arrows-button-off"></div>
                                </div>
                                <h3>Controlling Device</h3>
                                <p>Anda dapat mengatur alat anda seperti menyalakan dan mematikan alat.</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="fes7-box wow fadeIn" data-wow-delay="400ms">
                                <div class="fes7-box-icon">
                                    <span aria-hidden="true" class="icon_group"></span>
                                </div>
                                <h3>Full Support</h3>
                                <p>Jangan takut untuk mencoba, kami siap membantu Anda 24 jam dalam 7 hari. </p>
                            </div>
                        </div>


                    </div>
                </div>
            </div><!--end of row-->
        </div>
    </div>




    <div class="page-section nl-cont" style="background:#225776; color:#fff !important;">
        <div class="container">
            <form action="#" method="get" target="_blank">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-3">
                        <h3 style="margin: 10px 0; color:#fff;">GET SPECIAL OFFER</h3>
                    </div>
                    <div class="col-md-3">
                        <input type="submit" value="CONTACT US" name="daftar" onclick="submit()"
                            id="mc-embedded-subscribe" class="button medium ">
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </form>
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
                            Magelang, Jawa Tengah 50233
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
