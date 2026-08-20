<!DOCTYPE html>
<html lang="en">



@include('masterweb::template.admin.metadata')

<body>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif
        }

        .container {
            margin: 50px auto;
        }


        .box-1 img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .box-2 {
            padding: 10px;
        }

        .box-1,
        .box-2 {
            width: 50%;
        }

        .h-1 {
            font-size: 24px;
            font-weight: 700;
        }

        .text-muted {
            font-size: 14px;
        }

        .container .box {
            width: 200px;
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px solid transparent;
            text-decoration: none;
            color: #615f5fdd;
        }

        .box:active,
        .box:visited {
            border: 2px solid #287373;
        }

        .box:hover {
            border: 2px solid #287373;
        }

        .btn.btn-primary {
            background-color: transparent;
            color: #287373;
            border: 0px;
            padding: 0;
            font-size: 14px;
        }

        .btn.btn-primary .fas.fa-chevron-right {
            font-size: 12px;
        }

        .footer .p-color {
            color: #287373;
        }

        .footer.text-muted {
            font-size: 10px;
        }

        .fas.fa-times {
            position: absolute;
            top: 20px;
            right: 20px;
            height: 20px;
            width: 20px;
            background-color: #f3cff379;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fas.fa-times:hover {
            color: #ff0000;
        }

        @media (max-width:767px) {
            body {
                padding: 10px;
            }

            .body {
                width: 100%;
                height: 100%;
            }

            .box-1 {
                width: 100%;
            }

            .box-2 {
                width: 100%;
                height: 440px;
            }
        }
    </style>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row w-100">
                    {{--
                    <div class="container-lg col-md-12" style="max-width:100%">
                        <div class="row nopadding">
                            <div class="col-md-6 large" style="--gohub-gutter-x:0;  ">
                                <button type="button" class="btn-close form-login-cancel" data-bs-dismiss="modal"
                                    aria-label="Close"
                                    style="right:1rem !important; top:1rem !important; position:absolute;"></button>

                                <img class="bg_login nopadding"
                                    src="https://diawan.io/imagelogo/credential-blank-bg.png">

                                <div class="ps-5 mx-auto" style="top: 30%; position:absolute;">
                                    <p class="h4 text-white">please login to verify access</p>

                                    <small class="text-white">By joining us, you agree to the <a
                                            href="https://diawan.io/public-read-term-agreement"
                                            style="text-decoration: none" target="__blank">Terms of Service and Privacy
                                            Policy,
                                            including the Use of Cookies</a>.</small>
                                </div>
                            </div>
                            <div class="col-md-6">

                            </div>
                        </div>
                    </div> --}}

                    <div class="container">
                        <div class="body d-md-flex align-items-center justify-content-between">
                            <div class="box-1 mt-md-0 mt-5 center"
                                style="width:100%; 
                                align-content:center;
                            text-align: center;
                                ">
                                <img src="{{ asset('assets/admin/images/logo/login_logo.png') }}"
                                    style="vertical-align: middle; margin:auto; height: 100%; margin-bottom: 50px;"
                                    alt="Logo Admin">
                                {{-- <img src="{{ asset('assets/admin/images/login.png') }}"
                                    style="vertical-align: middle; margin:auto; width: 400px;" alt="Logo Admin"> --}}
                            </div>
                            <div class=" box-2 d-flex flex-column h-100" style=" min-height:600px; width:100%;">
                                <div class="mt-5" style="  margin-inline-start: 20px;">
                                    <img src="{{ asset('assets/admin/images/logo/logo-silaboy-mini.png') }}"
                                        style="vertical-align: middle; margin:auto; max-height:100px;" width="65px"
                                        alt="Logo ">

                                    <p class="mb-1 h-1" style="color:#000000; font-size: 50px;">LOGIN</p>
                                    <form class="pt-3" method="POST" action="{{ route('login') }}" autocomplete="off"
                                        novalidate id="loginFormMain">
                                        @csrf
                                        <!-- Dummy fields to trick Firefox autofill -->
                                        <input type="text" name="fakeusernameremembered"
                                            style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
                                        <input type="password" name="fakepasswordremembered"
                                            style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
                                        <!-- Honeypot -->
                                        <input type="text" name="hp_field" style="display:none" tabindex="-1"
                                            autocomplete="off">

                                        <div class="form-group">
                                            <input type="text" name="username"
                                                class="form-control form-control-lg {{ $errors->has('username') ? ' is-invalid' : '' }}"
                                                id="username" placeholder="Username" value="{{ old('username') }}"
                                                autocomplete="nope" readonly onfocus="this.removeAttribute('readonly');"
                                                style="background-color: white;">

                                            @if ($errors->has('username'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('username') }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <input type="password" name="password"
                                                class="form-control form-control-lg {{ $errors->has('password') ? ' is-invalid' : '' }}"
                                                id="password" placeholder="Password" value="{{ old('password') }}"
                                                autocomplete="new-password" readonly
                                                onfocus="this.removeAttribute('readonly');"
                                                style="background-color: white;">

                                            @if ($errors->has('password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('password') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label for="captcha" class="sr-only">Kode Keamanan</label>
                                            <div style="display:flex;align-items:center;gap:10px;">
                                                <img src="{{ route('captcha.generate') }}?t={{ microtime(true) }}"
                                                    alt="CAPTCHA" id="captchaImgAdmin"
                                                    style="height:64px; border-radius:8px; background:#eef2ff; padding:4px; cursor:pointer;"
                                                    title="Klik untuk refresh">
                                                <button type="button"
                                                    onclick="document.getElementById('captchaImgAdmin').src='{{ route('captcha.generate') }}?t='+(Date.now())"
                                                    class="btn btn-primary" style="padding:8px 12px;">Refresh</button>
                                            </div>
                                            <input type="text" class="form-control form-control-lg" id="captcha"
                                                name="captcha" placeholder="Masukkan kode pada gambar" required
                                                autocomplete="off" style="margin-top:8px;"
                                                onblur="this.value=this.value.trim().toUpperCase();">
                                        </div>
                                        <div class="form-group form-check">
                                            <input type="checkbox" class="form-check-input" id="show-password"
                                                onclick="togglePassword()">
                                            <label class="form-check-label" for="show-password"
                                                style="font-size: 0.9rem;">Lihat Password</label>
                                        </div>


                                        {{-- <div class="form-group text-center">
                  <div class="g-recaptcha" name="g-recaptcha-response"
                    data-sitekey="{{ config('app.recaptcha_site_key') }}"></div>
                  @if ($errors->has('g-recaptcha-response'))
                    <span class="help-block text-danger">
                      <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                    </span>
                  @endif
                </div> --}}

                                        <div class="mt-3">
                                            <button type="submit"
                                                class="btn btn-block btn-info btn-lg font-weight-medium auth-form-btn mr-1"
                                                style="background: #046E6E">
                                                Masuk <i class="icon-login mr-2"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                {{-- <span class="fas fa-times"></span> --}}
                            </div>
                        </div>




                    </div>
                </div>
                <!-- content-wrapper ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>
        <!-- container-scroller -->

        <script>
            function togglePassword() {
                var passwordInput = document.getElementById('password');
                var showPasswordCheckbox = document.getElementById('show-password');

                if (showPasswordCheckbox.checked) {
                    passwordInput.type = 'text';
                } else {
                    passwordInput.type = 'password';
                }
            }

            // Additional Firefox autofill prevention
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('loginFormMain');
                if (form) {
                    setTimeout(function() {
                        var inputs = form.querySelectorAll('input[type="text"], input[type="password"]');
                        inputs.forEach(function(input) {
                            if (input.name !== 'fakeusernameremembered' && input.name !==
                                'fakepasswordremembered') {
                                input.setAttribute('autocomplete', 'nope');
                                input.setAttribute('data-lpignore', 'true');
                                input.setAttribute('data-form-type', 'other');
                            }
                        });
                    }, 100);
                }
            });
        </script>

</body>

</html>
