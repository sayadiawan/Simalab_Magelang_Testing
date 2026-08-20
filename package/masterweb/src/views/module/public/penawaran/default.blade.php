
        <!-- FEATURES 4 -->
        <div id="about" class="page-section grey-light-bg">
            <div class="container fes1-cont">
                <div class="row">

                    <div class="col-md-5 fes1-img-cont wow fadeInUp mb-20">
                        <img src="https://badr.co.id/web/image/1951/undraw_post_online_dkuk@3x.png" alt="img">
                    </div>

                    <div class="col-md-7 col-xs-12">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="fes1-main-title-cont wow fadeInDown">
                                    <div class="blue1 font-30">
                                        <span class="bold">Let us solve your problem!</span>
                                    </div>
                                    <div class="blue-g"></div>
                                </div>
                            </div>
                        </div>


                        <div class="row">

                            <div class="col-md-8 col-xs-12 col-sm-8">
                                <div class="fes1-box wow fadeIn" data-wow-delay="400ms">
                                    <h3>
                                        Jelaskan secara singkat gambaran umum mengenai proyek Anda.
                                    </h3>
                                    <h3>
                                        Tim kami akan mempelajarinya dan akan menghubungi Anda untuk proses diskusi berikutnya.
                                    </h3>
                                </div>
                                <div class="row">
                                    <h4>
                                        <span class="bold">
                                            Ceritakan tentang Diri Anda
                                        </span>
                                    </h4>
                                    <form id="contact-form" action="{{url('create_offer')}}" method="POST">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <!-- <label>Your name *</label> -->
                                                <input type="text" value="" data-msg-required="Please enter your name" maxlength="100" class="controled" name="nama" id="name" placeholder="Nama" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <!-- <label>Your name *</label> -->
                                                <input type="text" value="" data-msg-required="Perusahaan Anda/Instansi" maxlength="100" class="controled" name="instansi" id="name" placeholder="Perusahaan Anda/Instansi" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <!-- <label>Your name *</label> -->
                                                <input type="text" value="" data-msg-required="" maxlength="100" class="controled" name="email" id="email" placeholder="Email" required>
                                            </div>
                                        </div>
                                </div>
                                <div class="row">
                                    <h4>
                                        <span class="bold">
                                                Ceritakan tentang Proyek Anda
                                            </span>
                                    </h4>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <!-- <label>Your name *</label> -->
                                                <input type="text" value="" data-msg-required="Apa yang ingin Anda kembangkan" maxlength="100" class="controled" name="nama_proyek" id="name" placeholder="Apa yang ingin Anda kembangkan" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <!-- <label>Message *</label> -->
                                                <textarea maxlength="5000" data-msg-required="Silahkan jelaskan lebih detail" rows="3" class="controled" name="detail_proyek" id="message" placeholder="Silahkan jelaskan lebih detail" required></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-10">
                                                <label class="mb-10">Layanan</label>
                                                <div class="form-group">
                                                    <input type="checkbox" id="it" value="it" name="layanan[]" checked>
                                                    <label for="it">IT Consultant</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="web" value="web" name="layanan[]">
                                                    <label for="web">Web Design</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="infor" value="infor" name="layanan[]">
                                                    <label for="infor">information System</label>
                                                </div>
                                                <div class="form-group">
                                                    <input type="checkbox" id="mobile" value="mobil" name="layanan[]">
                                                    <label for="mobile">Mobile Apps</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <label class="mb-10">Deadline Pekerjaan</label>
                                                <input type="date" name="deadline" value="{{date('Y-m-d')}}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <label class="mb-10">Lampirkan Berkas</label>
                                                <input type="file" value="" data-msg-required="Apa yang ingin Anda kembangkan" maxlength="100" class="controled" name="lampiran_berkas" id="name" placeholder="Apa yang ingin Anda kembangkan" >
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-10">
                                                <label>Atau Lampirkan Url</label>
                                                <textarea maxlength="5000" data-msg-required="Masukkan Url" rows="1" class="controled" name="lampiran_url" id="message" placeholder="Masukkan Url"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12 mb-30">
                                                <label class="mb-10">Dari Mana Anda Tahu Seven Media ?</label>
                                                    <select class="select-md input-border w-100" name="info_umum">
                                                        <option value="1">Search Engine</option>
                                                        <option value="2">Mailing Partner</option>
                                                        <option value="3">News Letter</option>
                                                        <option value="4">Facebook</option>
                                                        <option value="5">Twitter</option>
                                                    </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 text-center-xxs tengah">
                                                    <input type="submit" value="Kirim penawaran" class="button medium blue mb-20" data-loading-text="Loading...">
                                                </div>
                                            </div>

                                    </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <script>
            (function() {
                const form = document.querySelector('#contact-form');
                const checkboxes = form.querySelectorAll('input[type=checkbox]');
                const checkboxLength = checkboxes.length;
                const firstCheckbox = checkboxLength > 0 ? checkboxes[0] : null;

                function init() {
                    if (firstCheckbox) {
                        for (let i = 0; i < checkboxLength; i++) {
                            checkboxes[i].addEventListener('change', checkValidity);
                        }

                        checkValidity();
                    }
                }

                function isChecked() {
                    for (let i = 0; i < checkboxLength; i++) {
                        if (checkboxes[i].checked) return true;
                    }

                    return false;
                }

                function checkValidity() {
                    const errorMessage = !isChecked() ? 'At least one checkbox must be selected.' : '';
                    firstCheckbox.setCustomValidity(errorMessage);
                }

                init();
            })();
        </script>