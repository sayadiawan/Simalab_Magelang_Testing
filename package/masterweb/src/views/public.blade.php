<!DOCTYPE html>
<html lang="en">
@include('masterweb::template/public/metadata')
@yield('css')

<body>
    <!-- LOADER -->
    {{-- <div id="loader-overflow">
        <img src="https://v3.sevenmediatech.co.id/assets/public/images/logo/LOGO SMT New White.png" class="opc-img" alt="">
        <h3 class="mb-10"><span class="opt-font text-center">www.sevenmediatech.co.id</span></h3>
    </div> --}}
    <div id="wrap" class="boxed ">
        <div class="grey-bg">
            <!-- Grey BG  -->
            @include('masterweb::template.public.header')

            @foreach (unserialize($page->layout) as $column => $module)
            @include('masterweb::module.admin.layoutmodule.column_modules',['column'=> $column,'modules'=>$module])
            @endforeach

            @include('masterweb::template.public.footer')
            <!-- BACK TO TOP -->
            <p id="back-top">
                <a href="#top" title="Back to Top"><span class="icon icon-arrows-up"></span></a>
            </p>

        </div><!-- End BG -->
    </div><!-- End wrap -->

    <!-- JS begin -->
    @include('masterweb::template.public.js')
    @yield('js')
</body>

</html>