@php
$option = \Smt\Masterweb\Models\Option::first();
@endphp

<head>

    <title>{{(request()->segment(1) != NULL) ? SmtHelp::get_linkname()." - " : ""}}{{$option->title}}</title>
    <meta charset="utf-8">
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Seven Media Technology">

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- FAVICONS -->
    <link rel="shortcut icon" href="{{ ($option->favicon == "") ? asset('assets/public/images/favicon/favicon.png') : asset('assets/admin/images/'.$option->favicon)}}">

    <!-- FLEXSLIDER SLIDER CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/public/css/flexslider.css')}}">
     <!-- BOOTSTRAP -->
     <link rel="stylesheet" href="{{ asset('assets/public/css/bootstrap.min.css')}}">
    <!-- GOOGLE FONT (butuh koneksi internet ke fonts.googleapis.com) -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+Pro:wght@200;300;400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+Pro:wght@200;300;400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:500,600,300%7COpen+Sans:400,300,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">

    {{-- FONT AWESOME dari CDN di-nonaktifkan, gunakan font lokal dari icons-fonts.css --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> --}}

    <!-- ICONS ELEGANT FONT & FONT AWESOME & LINEA ICONS (lokal) -->
    <link rel="stylesheet" href="{{ asset('assets/public/css/icons-fonts.css')}}">

    <!-- CSS THEME -->
    
    <link rel="stylesheet" href="{{ asset('assets/public/css/swipper/swipper.min.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/public/css/style.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/public/css/custom.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/public/css/responsive.css')}}">

    <!-- ANIMATE -->
    <link rel='stylesheet' href="{{ asset('assets/public/css/animate.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/public/rs-plugin/css/settings.min.css') }}" media="screen" />
    
    {{-- PROPERTY --}}
    <meta property="og:title" content="{{(request()->segment(1) != NULL) ? SmtHelp::get_linkname()." - " : ""}}{{$option->title}}" />
    <meta property="og:url" content="{{request()->fullUrl()}}" />
    <meta property="og:description" content="{{$option->description}}"> 

    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'id'}, 'google_translate_element');
        }

        function changeLanguageByButtonClick(val) {
            var language = val;
            var selectField = document.querySelector("#google_translate_element select");
            for(var i=0; i < selectField.children.length; i++){
                var option = selectField.children[i];
                // find desired langauge and change the former language of the hidden selection-field 
                if(option.value==language){
                selectField.selectedIndex = i;
                // trigger change event afterwards to make google-lib translate this side
                selectField.dispatchEvent(new Event('change'));
                break;
                }
            }
        }
    </script>
    {{-- Script Google Translate dimatikan agar aplikasi tidak tergantung internet --}}
    {{-- <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> --}}

    <!-- S:fb meta -->
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="{{request()->url()}}" />
    <!-- e:fb meta -->

    <!-- S:tweeter card -->
    <meta name="twitter:card" content="summary_large_image" />
    {{-- <meta name="twitter:site" content="@" /> --}}
    {{-- <meta name="twitter:creator" content="@"> --}}
    <meta name="twitter:title" content="{{$option->keyword}} - {{$option->title}}" />
    <meta name="twitter:description" content="{{$option->title}} - {{$option->keyword}}" />
    
    <!-- E:tweeter card -->
    @php
        $get_artikel = NULL;
    @endphp
    {{-- type artikel --}}
    @if (SmtHelp::get_type() == "2" OR SmtHelp::get_type() == "18")  
        @php
            $menu_id = SmtHelp::get_menuid();
            $linkmenu = SmtHelp::get_linkmenu();
            $link = request()->segment(3);
            $get_artikel = \Smt\Masterweb\Models\Content::where('link_url',$link)->first();
        @endphp
    @endif

    @if ($get_artikel == NULL)
        <meta property="og:image" content="{{ ($option->favicon == "") ? asset('assets/public/images/favicon/favicon.png') : asset('assets/admin/images/'.$option->favicon)}}">
        <meta name="twitter:image" content="{{ ($option->favicon == "") ? asset('assets/public/images/favicon/favicon.png') : asset('assets/admin/images/'.$option->favicon)}}" />
        <meta property="og:image" content="{{ ($option->favicon == "") ? asset('assets/public/images/favicon/favicon.png') : asset('assets/admin/images/'.$option->favicon)}}" />
        <meta name="description" content="{{$option->description}}">
        <meta name="keywords" content="{{$option->keyword}}">
    @else
        <meta property="og:image" content="{{asset('assets/public/images/'.$get_artikel->img_thumbnail)}}">
        <meta name="twitter:image" content="{{asset('assets/public/images/'.$get_artikel->img_thumbnail)}}" />
        <meta property="og:image" content="{{asset('assets/public/images/'.$get_artikel->img_thumbnail)}}" />
        <meta name="description" content="{{strip_tags($get_artikel->deskripsi)}}">
        <meta name="keywords" content="{{$get_artikel->keyword}}">
    @endif

    @yield('css')
</head>