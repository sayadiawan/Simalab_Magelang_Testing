<!-- Kontak -->
@php
    $data = Smt\Masterweb\Models\Contact::first();
@endphp
<div class="page-section pt-100">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 col-sm-7 ">
                        <h2 class="">
                            <span class="font-light  blue1 text-center">Konsultasi dengan Kami </span>
                        </h2>
                        <div class="garis-kontak"></div>
                        <p class="">
                            Jika Anda memiliki pertanyaan atau permasalahan tentang project, pembuatan sistem, atau segala hal terkait teknologi informasi, bisa kontak kami langsung atau bisa datang langsun ke alamat kami dibawah ini :
                        </p>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="cis-cont">
                                    <div class="cis-icon">
                                        <div class="icon icon-basic-geolocalize-05"></div>
                                    </div>
                                    <div class="cis-text">
                                        <p class="font-kontak">{{strip_tags($data->alamat)}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="cis-cont">
                                    <div class="cis-icon">
                                        <div class="icon icon-basic-ipod"></div>
                                    </div>
                                    <div class="cis-text">
                                        <p class="font-kontak mt-10">{{$data->phone}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="cis-cont">
                                    <div class="cis-icon">
                                        <div class="icon icon-basic-paperplane"></div>
                                    </div>
                                    <div class="cis-text">
                                        <p class="font-kontak mt-10">{{$data->email}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-sm-5 mb-50 k-card" style="background-image: url({{ asset('assets/public/images/kontak/hp2.png')}});">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif
                        
                        <form id="contact-form" class="form-kontak" action="{{url('create_contact')}}" method="POST">
                            <h3>
                                <span class="font-light blue1">Kirim Pesan Anda
                               </span>
                            </h3>
                            <div class="garis-kontak"></div>
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- <label>Your name *</label> -->
                                    <input type="text" value="" data-msg-required="Please enter your name" maxlength="100" class="controled" name="name" id="name" placeholder="NAME" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <!-- <label>Your email address *</label> -->
                                    <input type="email" value="" data-msg-required="Please enter your email address" data-msg-email="Please enter a valid email address" maxlength="100" class="controled" name="email" id="email" placeholder="EMAIL" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- <label>Your email address *</label> -->
                                    <input type="number" value="" data-msg-required="Please enter your telefon " data-msg-email="Please enter a valid telefon" maxlength="100" class="controled" name="Telefon" id="Telefon" placeholder="Telefon" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-40">
                                    <!-- <label>Message *</label> -->
                                    <textarea maxlength="5000" data-msg-required="Please enter your message" rows="3" class="controled" name="message" id="message" placeholder="MESSAGE" required></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-center-xxs tengah">
                                    <input type="submit" value="SEND MESSAGE" class="button medium blue mb-20" data-loading-text="Loading...">
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- maps -->
        <div class="page-section">
                <div class="container-fluid">
                    <div class="row">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d17273.739448212888!2d110.39660106782577!3d-7.0066748593441845!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708b0d51a81815%3A0x4f24941a09e8f1f9!2sSeven+Media+Technology!5e0!3m2!1sid!2sid!4v1471600720299"
                            width="100%" height="400" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>

        