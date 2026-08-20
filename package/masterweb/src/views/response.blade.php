<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>LABKES KAB. MAGELANG | Notification</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/iconfonts/font-awesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/vendors/css/vendor.bundle.addons.css') }}">
    <!-- endinject -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.png') }}" />
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center text-center error-page bg-white">
                <div class="row flex-grow">
                    <div class="col-lg-7 mx-auto ">
                        <div class="row align-items-center d-flex flex-row">
                            <div class="col-lg-6 text-lg-right pr-lg-4">
                                @if ($status == true)
                                    <img src="{{ asset('imagelogo/success.png') }}" class="img-fluid" alt="">
                                @else
                                    <img src="{{ asset('imagelogo/failed.png') }}" class="img-fluid" alt="">
                                @endif
                            </div>

                            <div class="col-lg-6 error-page-divider text-lg-left pl-lg-4">
                                @if ($code == 404)
                                    <h2>404!</h2>

                                    @if (isset($pesan))
                                        {!! $pesan !!}
                                    @else
                                        <h3 class="font-weight-light">Inputan atau parameter yang diinputkan salah.</h3>
                                    @endif
                                @elseif($code == 400)
                                    <h2>400!</h2>

                                    @if (isset($pesan))
                                        {!! $pesan !!}
                                    @else
                                        <h3 class="font-weight-light">Inputan atau parameter yang diinputkan salah.</h3>
                                    @endif
                                @else
                                    @if ($status == true)
                                        <h2>BERHASIL!</h2>

                                        @if (isset($pesan))
                                            {!! $pesan !!}
                                        @else
                                            <h3 class="font-weight-light">Notifikasi <u>{{ $paramname }}</u> untuk
                                                device
                                                <u>{{ $devicename }}</u> akan aktif kembali.
                                            </h3>
                                        @endif
                                    @else
                                        <h2>GAGAL!</h2>

                                        @if (isset($pesan))
                                            {!! $pesan !!}
                                        @else
                                            <h3 class="font-weight-light">Notifikasi <u>{{ $paramname }}</u> untuk
                                                device
                                                <u>{{ $devicename }}</u> tidak aktif.
                                            </h3>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12 text-center mt-xl-2">
                                <a class=" font-weight-medium" href="/">Back to home</a>
                            </div>
                        </div>

                        @php
                            $opt = DB::table('ms_options')->first();
                        @endphp

                        <div class="row mt-5">
                            <div class="col-12 mt-xl-2">
                                <p class=" font-weight-medium text-center"{{ $opt->footer }}. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('assets/admin/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/admin/vendors/js/vendor.bundle.addons.js') }}"></script>
    <!-- endinject -->
    <!-- inject:js -->
    <script src="{{ asset('assets/admin/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/admin/js/misc.js') }}"></script>
    <script src="{{ asset('assets/admin/js/settings.js') }}"></script>
    <script src="{{ asset('assets/admin/js/todolist.js') }}"></script>
    <!-- endinject -->
</body>

</html>
