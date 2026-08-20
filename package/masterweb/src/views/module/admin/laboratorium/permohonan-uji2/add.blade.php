@extends('masterweb::template.admin.layout')
@section('title')
  Permohonan Uji Management
@endsection

@section('css')
  <style>
    * {
      margin: 0;
      padding: 0
    }

    html {
      height: 100%
    }



    #msform {
      text-align: center;
      position: relative;
      margin-top: 20px
    }

    #msform fieldset .form-card {
      background: white;
      border: 0 none;
      border-radius: 0px;
      box-shadow: 0 2px 2px 2px rgba(0, 0, 0, 0.2);
      padding: 20px 40px 30px 40px;
      box-sizing: border-box;

      width: 94%;
      margin: 0 3% 20px 3%;
      position: relative
    }

    #msform fieldset {
      background: white;
      border: 0 none;
      border-radius: 0.5rem;
      box-sizing: border-box;
      width: 100%;
      margin: 0;
      padding-bottom: 20px;
      position: relative
    }

    #msform fieldset:not(:first-of-type) {
      display: none
    }

    #msform fieldset .form-card {
      text-align: left;
      color: #9E9E9E
    }


    #msform textarea {
      padding: 0px 8px 4px 8px;
      border: none;
      border-bottom: 1px solid #ccc;
      border-radius: 0px;
      margin-bottom: 25px;
      margin-top: 2px;
      width: 100%;
      box-sizing: border-box;
      font-family: montserrat;
      color: #2C3E50;
      font-size: 16px;
      letter-spacing: 1px
    }

    #msform input:focus,
    #msform textarea:focus {
      -moz-box-shadow: none !important;
      -webkit-box-shadow: none !important;
      box-shadow: none !important;
      border: none;
      font-weight: bold;
      border-bottom: 2px solid skyblue;
      outline-width: 0
    }

    #msform .action-button {
      width: 100px;
      background: skyblue;
      font-weight: bold;
      color: white;
      border: 0 none;
      border-radius: 0px;
      cursor: pointer;
      padding: 10px 5px;
      margin: 10px 5px
    }

    #msform .action-button:hover,
    #msform .action-button:focus {
      box-shadow: 0 0 0 2px white, 0 0 0 3px skyblue
    }

    #msform .action-button-previous {
      width: 100px;
      background: #616161;
      font-weight: bold;
      color: white;
      border: 0 none;
      border-radius: 0px;
      cursor: pointer;
      padding: 10px 5px;
      margin: 10px 5px
    }

    #msform .action-button-previous:hover,
    #msform .action-button-previous:focus {
      box-shadow: 0 0 0 2px white, 0 0 0 3px #616161
    }

    select.list-dt {
      border: none;
      outline: 0;
      border-bottom: 1px solid #ccc;
      padding: 2px 5px 3px 5px;
      margin: 2px
    }

    select.list-dt:focus {
      border-bottom: 2px solid skyblue
    }

    .card {
      z-index: 0;
      border: none;
      border-radius: 0.5rem;
      position: relative
    }

    .fs-title {
      font-size: 25px;
      color: #2C3E50;
      margin-bottom: 10px;
      font-weight: bold;
      text-align: left
    }

    #progressbar {
      margin-bottom: 30px;
      overflow: hidden;
      color: lightgrey
    }

    #progressbar .active {
      color: #000000
    }

    #progressbar li {
      list-style-type: none;
      font-size: 15px;
      width: 33.333333333%;
      float: left;
      position: relative
    }

    #progressbar #account:before {
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      content: "\f073";
    }

    #progressbar #personal:before {
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      content: "\f328";
    }

    #progressbar #payment:before {
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      content: "\f3c5";

    }

    #progressbar #confirm:before {
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      content: "\f00c";

    }

    #progressbar li:before {
      width: 50px;
      height: 50px;
      line-height: 45px;
      display: block;
      font-size: 18px;
      color: #ffffff;
      background: lightgray;
      border-radius: 50%;
      margin: 0 auto 10px auto;
      padding: 2px
    }

    #progressbar li:after {
      content: '';
      width: 100%;
      height: 2px;
      background: lightgray;
      position: absolute;
      left: 0;
      top: 25px;
      z-index: -1
    }

    #progressbar li.active:before,
    #progressbar li.active:after {
      background: #0C4061
    }

    .radio-group {
      position: relative;
      margin-bottom: 25px
    }

    .radio {
      display: inline-block;
      width: 204;
      height: 104;
      border-radius: 0;
      background: lightblue;
      box-shadow: 0 2px 2px 2px rgba(0, 0, 0, 0.2);
      box-sizing: border-box;
      cursor: pointer;
      margin: 8px 2px
    }

    .radio:hover {
      box-shadow: 2px 2px 2px 2px rgba(0, 0, 0, 0.3)
    }

    .radio.selected {
      box-shadow: 1px 1px 2px 2px rgba(0, 0, 0, 0.1)
    }

    .fit-image {
      width: 100%;
      object-fit: cover
    }

    .wrimagecard {
      margin-top: 0;
      margin-bottom: 1.5rem;
      text-align: left;
      position: relative;
      background: #fff;
      box-shadow: 12px 15px 20px 0px rgba(46, 61, 73, 0.15);
      border-radius: 4px;
      transition: all 0.3s ease;
    }

    .wrimagecard .fa {
      position: relative;
      font-size: 70px;
    }

    .wrimagecard-topimage_header {
      padding: 20px;
    }

    a.wrimagecard:hover,
    .wrimagecard-topimage:hover {
      box-shadow: 2px 4px 8px 0px rgba(46, 61, 73, 0.2);
    }

    .wrimagecard-topimage a {
      width: 100%;
      height: 100%;
      display: block;
    }

    .wrimagecard-topimage_title {
      padding: 20px 24px;
      height: 80px;
      padding-bottom: 0.75rem;
      position: relative;
    }

    .wrimagecard-topimage a {
      border-bottom: none;
      text-decoration: none;
      color: #525c65;
      transition: color 0.3s ease;
    }

    i.checked {
      display: inline-block;
      border-radius: 200px;
      box-shadow: 0px 0px 2px #25668f;
      padding: 0.5em 0.6em;

    }
  </style>
@endsection

@section('content')
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCvkw3POs-1ZAeDNh83LazabKECKU8i024&libraries=places"></script>


  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji') }}">Permohonan Uji
                    Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h4>Tambah Permohonan Uji</h4>

    </div>

    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <input type="hidden" name="code_permohonan_uji" id="code_permohonan_uji" value="{{ $code }}">
        <div class="form-group">
          <label for="customer_samples">Nama pelanggan/perusahaan:</label>
          <select id="customerAttributes" class="js-customer-basic-multiple js-states form-control" style="width: 100%"
            required>
          </select>
        </div>


        <div class="form-group">
          <label for="name_personil"> Nama personil penghubung:</label>
          <div class="input-group date">
            <input type="text" class="form-control " name="name_personil" id="name_personil"
              placeholder="Nama personil penghubung">

          </div>
        </div>

        <div class="form-group">
          <label for="customer_samples">Pengambilan sampel dilakukan oleh:</label>
          <div class="form-row align-items-center col-md-12" id="fields-relay-1">
            {{-- <div class="col-md-2" id="condition">
                        <input type="checkbox" id="issend_samples" class="issend_samples" name="issend_samples"
                            data-toggle="toggle"> DIANTAR

                    </div>
                    <div class="col-md-10" id="name_sampling_field">
                        <label id="label-sender_samples" for="sender_samples">Petugas Sampling</label>
                        <select id="sender_samples" class="js-customer-basic-multiple js-states form-control" required>
                        </select>
                    </div> --}}

            <div class="col-md-6" id="condition">
              <input type="radio" id="lab" name="issend_samples" value="on"> Laboratorium
            </div>
            <div class="col-md-6" id="condition">
              <input type="radio" id="pelanggan" name="issend_samples" value="off" checked> Pelanggan
            </div>
          </div>
        </div>

        <div class="form-group table-sample">
          <label for="date_get_sample">Tanggal Diterima Sampling</label>
          <div class="input-group date">
            <input type="text" class="form-control date_get_sample" name="date_get_sample" id="date_get_sample"
              placeholder="Isikan Tanggal Diterima Sampling" data-date-format="dd/mm/yyyy" required>
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </span>
            </div>
          </div>
        </div>

        <div class="form-group sampling" style="display:none;">
          <label for="datesampling_samples">Tanggal Sampling</label>
          <div class="input-group date">
            <input type="text" class="form-control datesampling_samples" name="datesampling_samples"
              id="datesampling_samples" placeholder="Isikan Tanggal Sampling" data-date-format="dd/mm/yyyy" required>
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </span>
            </div>
          </div>
        </div>



        <!-- <div class="form-group">
          <label for="datelab_samples">Tanggal perkiraan selesai pengujian</label>
          <div class="input-group date">
            <input type="text" class="form-control date_done_estimation" name="date_done_estimation"
              id="date_done_estimation" placeholder="Tanggal perkiraan selesai pengujian" data-date-format="dd/mm/yyyy"
              required>
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </span>
            </div>
          </div>
        </div> -->
      </li>

      <li class="list-group-item table-sample">

        <br>
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <button type="button" id="add-sample" class="btn btn-primary" data-toggle="modal"
                data-target="#bd-example-modal-lg1">Tambah Sample</button>

              <table id='empTable' class="table">

              </table>
            </div>
          </div>
        </div>
      </li>

      <li class="list-group-item sampling" style="display:none;">
        <br>
        <div class="form-group">

          <label for="name_customer">Jenis Sarana</label>
          <select name="packet_samples_sampling" id="packet_samples_sampling" class="form-control"
            onchange='CheckPacketSampling(this.value);' style="margin-bottom:4px; width: 100%;">
            <option value="" selected disabled>Pilih Jenis Sarana</option>
            @foreach ($packets as $packet)
              <option value="{{ $packet->id_packet }}" data-value="{{ $packet->price_packet }}">
                {{ $packet->name_packet }}</option>
            @endforeach
            <!-- <option value="0">Lainnya</option> -->
          </select>
          <select id="methodAttributes_sampling" class="js-example-basic-multiple methodAttributes"
            style="display:none; width: 100%" multiple="multiple">

          </select>
        </div>


      </li>

      <li class="list-group-item table-sample">

        <br>

        <div class="form-group">
          <label for="condition_samples">Kondisi sampel saat diterima termasuk abnormalitas atau penyimpangan dari
            kondisi normal</label>
          <div class="input-group date">
            <select name="condition_samples" id="condition_samples" class=" form-control"
              onchange='CheckCondition(this.value);' style="margin-bottom:4px; width: 100%">
              <option value="" selected disabled>Pilih Kondisi Sample</option>
              <option value="1">tidak diawetkan di lapangan</option>
              <option value="2">wadah sampel tidak sesuai </option>
              <option value="3">sampel kedaluarsa (melampaui holding time)</option>
              <option value="0">Lainnya</option>

            </select>
          </div>
          <input type="text" class="form-control" style="display:none;" name="condition_samples_others"
            id="condition_samples_others" placeholder="Isikan kondisi Sample Lain">

        </div>
      </li>




      <li class="list-group-item">

        <br>

        <div class="form-group total_price_sample table-sample">
          <label for="condition_samples">Jumlah Sampel</label>

          <input type="text" class="form-control" name="count_sample" id="count_sample" type="number"
            value="0" placeholder="Jumlah Sample">

        </div>

        <div class="form-group">
          <label for="condition_samples">Harga</label>
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text">
                Rp.
              </span>
            </div>
            <input type="text" class="form-control" name="total_price_sample" id="total_price_sample"
              type="number" value="0" placeholder="Total Harga">
          </div>
        </div>
        <div class="form-group">
          <label for="condition_samples">Uang Muka</label>
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text">
                Rp.
              </span>
            </div>
            <input type="text" class="form-control" name="down_payment_sample" id="down_payment_sample"
              type="number" value="0" placeholder="Isikan Uang Muka">
          </div>
        </div>
        <div class="form-group">
          <label for="condition_samples">Sisa</label>
          <div class="input-group">
            <div class="input-group-append">
              <span class="input-group-text">
                Rp.
              </span>
            </div>
            <input type="text" class="form-control" name="underpayment_sample" id="underpayment_sample"
              type="number" value="0" placeholder="Sisa" readonly>
          </div>
        </div>

        <div class="form-group">
          <label for="payment_samples">Pembayaran</label>
          <div class="input-group date">
            <select name="payment_samples" id="payment_samples" class=" form-control"
              onchange='CheckPayment(this.value);' style="margin-bottom:4px; width: 100%">
              <option value="" selected disabled>Pilih Pembayaran</option>
              <option value="1">Tunai</option>
              <option value="2">Transfer </option>
              <option value="0">Lainnya</option>

            </select>
          </div>
          <input type="text" class="form-control" style="display:none;" name="payment_samples_others"
            id="payment_samples_others" placeholder="Pembayaran Lain">

        </div>

        <div class="form-group">
          <label for="condition_samples">Catatan</label>
          <div class="input-group">
            <textarea name="note_sample" id="note_sample" class="form-control"></textarea>
          </div>
        </div>


        <div class="form-group">

          <input class="form-check-input form-control" type="checkbox" value="true"
            id="subcontract_samples">Subkontrak pengujian :
          <input type="text" class="form-control" style="display:none;" name="subcontract_name_samples"
            id="subcontract_name_samples" placeholder="Nama Lab Subkontrak">

        </div>


        <button type="submit" id="submitAll" class="btn btn-primary mr-2">Simpan</button>
        <button type="button" class="btn btn-light" onclick="goBack()">Kembali</button>
        <script>
          function goBack() {
            window.history.back();
          }
        </script>




      </li>
    </ul>


  </div>

  <div class="addmodal">

    <div id="sample-modal" class="modal mymodal fade bd-example-modal-lg1" tabindex="-1" role="dialog"
      aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="card">
              <div class="card-body">

                <div class="row justify-content-center mt-0">
                  <div class="col-12">
                    <div class="card">
                      <h2><strong>Masukkan Data Sample</strong></h2>
                      <p>Isi Setiap Form Sample </p>

                      <div class="row">
                        <div class="col-md-12 mx-0">
                          <form id="msform">
                            <ul id="progressbar">
                              <li class="active" id="account"><strong>Jenis Sample</strong>
                              </li>
                              <li id="personal"><strong>Detail Sample</strong></li>
                              {{-- <li id="payment"><strong>Titik Sampling</strong></li> --}}
                              <li id="confirm"><strong>Selesai</strong></li>
                            </ul>
                            <fieldset class="first">
                              <div class="form-card">
                                <h2 class="fs-title">Pilih Jenis Sample</h2>
                                <div class="container-fluid">
                                  <div class="row">
                                    @foreach ($sampletypes as $sampletype)
                                      <div class="col-md-3 col-sm-4 next" data-id="{{ $sampletype->id_sample_type }}"
                                        data-type="{{ $sampletype->typesample_type }}"
                                        data-code="{{ $sampletype->code_sample_type }}">
                                        <div class="wrimagecard wrimagecard-topimage">

                                          <div class="wrimagecard-topimage_header">
                                            <img
                                              src="{{ $sampletype->photo == null ? asset('/assets/admin/images/logo/ELITS.png') : asset('/storage/sample-type/' . $sampletype->photo) }}"
                                              class="card-img-top" height="120px" width="180px" alt="...">
                                          </div>
                                          <div class="wrimagecard-topimage_title">
                                            <center>
                                              <h5 style="color:black;">
                                                {{ $sampletype->name_sample_type }}
                                              </h5>
                                            </center>
                                          </div>
                                        </div>
                                      </div>
                                    @endforeach





                                  </div>

                                </div>
                              </div>

                            </fieldset>
                            <fieldset>

                              <div class="form-card">

                                <div class="card">
                                  <div class="card-body">
                                    @csrf
                                    <div class="form-group">
                                      <label for="code_samples">Kode</label>
                                      <input type="text" class="form-control" id="code_samples" name="code_samples"
                                        value='-' placeholder="Isikan Kode" readonly>
                                    </div>

                                    <div class="form-group">
                                      <label for="code_samples_user">Kode Sampel
                                        Pelanggan (jika ada)</label>
                                      <input type="text" class="form-control" id="code_samples_user"
                                        name="code_samples_user" placeholder="Isikan Kode Sample Pelanggan">
                                    </div>

                                    {{-- <div class="form-group">
                                                                        <label for="customer_samples">Customer</label>
                                                                        <select id="customerAttributes"
                                                                            class="js-customer-basic-multiple js-states form-control"
                                                                            required>
                                                                        </select>
                                                                    </div> --}}

                                    <div class="form-group">

                                      <label for="name_customer">Jenis Sarana</label>
                                      <select name="packet_samples" id="packet_samples" class=" form-control"
                                        onchange='CheckPacket(this.value);' style="margin-bottom:4px; width: 100%;"
                                        disabled="true">
                                        <option value="" selected disabled>Pilih
                                          Jenis Sarana</option>
                                        @foreach ($packets as $packet)
                                          <option value="{{ $packet->id_packet }}"
                                            data-value="{{ $packet->price_packet }}">
                                            {{ $packet->name_packet }}</option>
                                        @endforeach
                                        <option value="0">Lainnya</option>
                                      </select>
                                      <select id="methodAttributes_selected"
                                        class="js-example-basic-multiple methodAttributes_selected"
                                        style="display:none; width: 100%" multiple="multiple" required>

                                      </select>


                                      <select id="methodAttributes" class="js-example-basic-multiple methodAttributes"
                                        style="display:none; width: 100%;" multiple="multiple" required>
                                      </select>
                                    </div>




                                    <div class="form-group">
                                      <label for="wadah_samples">Wadah</label>
                                      <select name="wadah_samples" id="wadah_samples" class=" form-control hidden"
                                        onchange='CheckWadah(this.value);' style="margin-bottom:4px; width: 100%">
                                        <option value="" selected disabled>Pilih
                                          Wadah</option>
                                        @foreach ($containers as $container)
                                          <option value="{{ $container->id_container }}">
                                            {{ $container->name_container }}</option>
                                        @endforeach
                                        <option value="0">Lainnya</option>
                                      </select>
                                      <input type="text" class="form-control" id="wadah_samples_others"
                                        style='display:none;' id="wadah_samples" name="wadah_samples"
                                        placeholder="Isikan Wadah" required>
                                    </div>


                                    <div class="form-group">
                                      <div class="form-row align-items-center col-md-12" id="fields-relay-1">
                                        <div class="col-md-6" id="condition">
                                          <label for="weight_samples">Berat/Volume</label>
                                          <input type="number" class="form-control" step="0.01"
                                            id="weight_samples" name="amount_samples" placeholder="Isikan Berat/Volume"
                                            required>
                                        </div>
                                        <div class="col-md-6" id="name_sampling_field">
                                          <br>
                                          <select id="unitAttributes"
                                            class="js-unit-basic-multiple js-states form-control" style="width: 100%"
                                            required>

                                          </select>

                                        </div>
                                      </div>
                                    </div>


                                    <div class="form-group">
                                      <label for="datelab_samples">Tanggal Masuk
                                        Lab</label>
                                      <div class="input-group date">
                                        <input type="text" class="form-control datelab_samples"
                                          name="datelab_samples" id="datelab_samples"
                                          placeholder="Isikan Tanggal Masuk Lab" data-date-format="dd/mm/yyyy" required>
                                        <div class="input-group-append">
                                          <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                          </span>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="form-group">
                                      <label for="cost_samples">Harga</label>
                                      <div class="input-group">
                                        <div class="input-group-append">
                                          <span class="input-group-text">
                                            Rp.
                                          </span>
                                        </div>
                                        <input type="number" class="form-control" id="cost_samples"
                                          name="cost_samples" value="0" placeholder="Isikan Harga" readonly
                                          required>
                                      </div>
                                    </div>

                                  </div>
                                </div>
                              </div>

                              {{-- <button
                                                            class="previous action-button-previous">Kembali</button>
                                                        <button type="submit"
                                                            class="next action-button btn btn-primary mr-2"
                                                            id="submit">Simpan</button> --}}
                              <input type="button" name="previous" class="previous btn btn-light"
                                value="Sebelumnya" />
                              {{-- <input type="button" name="next"
                                                            class="next btn btn-primary mr-2" value="Lanjut" /> --}}
                              <button class="next btn btn-primary mr-2 submit" id="submit">Simpan</button>



                            </fieldset>
                            {{-- <fieldset>
                                                        <div class="form-card">
                                                            {{-- <h2 class="fs-title">Input</h2> --}}
                            {{-- <div class="container-fluid">
                                                                <div class="row">

                                                                    <div class="col-lg-12">

                                                                        <div id="pac-input-div" class="col-md-7 mb-3">
                                                                            <input id="pac-input" class="form-control"
                                                                                type="text"
                                                                                placeholder="Pencarian Lokasi"
                                                                                autofocus>
                                                                        </div>

                                                                        <div class="col">
                                                                        </div>
                                                                        <div style=" height: 500px;">
                                                                            {!! Mapper::render() !!}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-12">
                                                                        <div class="form-row">
                                                                            <div class="col">
                                                                                <label for="name">Lat</label>
                                                                                <input type="text" id="lat" name="lat"
                                                                                    class="form-control"
                                                                                    placeholder="Lat">
                                                                            </div>
                                                                            <div class="col">
                                                                                <label for="name">Long</label>
                                                                                <input type="text" id="lng" name="lng"
                                                                                    class="form-control"
                                                                                    placeholder="Long">
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div> --}}
                            {{-- <h2 class="fs-title">Payment Information</h2>
                                                            <div class="radio-group">
                                                                <div class='radio' data-value="credit"><img
                                                                        src="https://i.imgur.com/XzOzVHZ.jpg"
                                                                        width="200px" height="100px"></div>
                                                                <div class='radio' data-value="paypal"><img
                                                                        src="https://i.imgur.com/jXjwZlj.jpg"
                                                                        width="200px" height="100px"></div> <br>
                                                            </div> <label class="pay">Card Holder Name*</label> <input
                                                                type="text" name="holdername" placeholder="" />
                                                            <div class="row">
                                                                <div class="col-9"> <label class="pay">Card
                                                                        Number*</label> <input type="text" name="cardno"
                                                                        placeholder="" /> </div>
                                                                <div class="col-3"> <label class="pay">CVC*</label>
                                                                    <input type="password" name="cvcpwd"
                                                                        placeholder="***" /> </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-3"> <label class="pay">Expiry
                                                                        Date*</label> </div>
                                                                <div class="col-9"> <select class="list-dt" id="month"
                                                                        name="expmonth">
                                                                        <option selected>Month</option>
                                                                        <option>January</option>
                                                                        <option>February</option>
                                                                        <option>March</option>
                                                                        <option>April</option>
                                                                        <option>May</option>
                                                                        <option>June</option>
                                                                        <option>July</option>
                                                                        <option>August</option>
                                                                        <option>September</option>
                                                                        <option>October</option>
                                                                        <option>November</option>
                                                                        <option>December</option>
                                                                    </select> <select class="list-dt" id="year"
                                                                        name="expyear">
                                                                        <option selected>Year</option>
                                                                    </select> </div>
                                                            </div> --}}
                            {{--
                                                        </div>
                                                        <input type="button" name="previous"
                                                            class="previous btn btn-light" value="Sebelumnya" />
                                                        <button class="next btn btn-primary mr-2 submit"
                                                            id="submit">Simpan</button>

                                                    </fieldset> --}}
                            <fieldset>
                              <div class="form-card">
                                <h2 class="fs-title text-center">Berhasil !</h2> <br><br>
                                <div class="row justify-content-center">
                                  <div class="col-4">

                                    <i class="checked fa fa-check fa-7x" style="border:#25668f; color: #25668f"></i>

                                  </div>
                                </div> <br><br>
                                <div class="row justify-content-center">
                                  <div class="col-7 text-center">
                                    <h5>Input sample telah selesai</h5><br>

                                  </div>
                                </div>
                              </div>
                            </fieldset>
                          </form>
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
    </div>





  </div>
@endsection

@section('scripts')
  <script>
    var dataSet = []

    var total_price_sample, down_payment_sample
    if (localStorage.getItem("dataSet") !== null) {
      dataSet = JSON.parse(localStorage.getItem("dataSet"))

    }
    var sample_save = [],
      total_price_sample = 0;

    var datatable = $('#empTable').DataTable({
      data: dataSet,
      bPaginate: false,
      searching: false,

      columns: [{
          title: "No Sampel"
        },
        {
          title: "Action"
        }
      ]
    });

    $.fn.select2.defaults.set("theme", "classic");



    $('.methodAttributes').select2({
      placeholder: "Pilih Metode",
      allowClear: true,
      ajax: {
        url: "{{ url('/api/method/') }}",
        method: "post",
        dataType: 'json',

        params: { // extra parameters that will be passed to ajax
          contentType: "application/json;",
        },
        data: function(term) {
          return {
            term: term.term || '',
            page: term.page || 1
          };

        },
        cache: true
      }
    });


    if (localStorage.getItem("sample_save") !== null) {

      sample_save = JSON.parse(localStorage.getItem("sample_save"))
      sample_save.forEach(sample => {
        total_price_sample = total_price_sample + parseInt(sample.cost_samples)
      })
      $('#count_sample').val(sample_save.length)
      $('#total_price_sample').val(total_price_sample)
      getPayment()

    }



    var backOfferButton = document.getElementById('add-sample');

    var issend_samples_radio = JSON.parse(localStorage.getItem("issend_samples"))

    if (issend_samples_radio == "on") {
      $("#lab").prop("checked", true);
      $("#pelanggan").prop("checked", false);
      $('.table-sample').hide()
      $('#methodAttributes_sampling').next(".select2-container").hide();
      $('.sampling').show()
    } else {
      $("#lab").prop("checked", false);
      $('#methodAttributes_sampling').next(".select2-container").hide();
      $("#pelanggan").prop("checked", true);
      $('.table-sample').show()
      $('.sampling').hide()
    }



    $('#down_payment_sample').on('input', function() {

      getPayment()
    })

    function getPayment() {
      total_price_sample = parseInt($('#total_price_sample').val())
      down_payment_sample = parseInt($('#down_payment_sample').val())
      if ($('#down_payment_sample').val() == "") {
        $('#underpayment_sample').val("0")
      } else {
        $('#underpayment_sample').val(total_price_sample - down_payment_sample)
      }

    }







    $('input[type=radio][name=issend_samples]').change(function() {
      if (this.value == 'on') {
        $('.table-sample').hide()
        $('.sampling').show()
        // $('#methodAttributes_sampling').next(".select2-container").hide();
      } else if (this.value == 'off') {
        $('.table-sample').show()
        $('.sampling').hide()
      }

      localStorage.clear();
      sample_save = []
      dataSet = []
      datatable.clear();
      datatable.rows.add(dataSet);
      datatable.draw();

      $('#methodAttributes_sampling').next(".select2-container").hide();

      localStorage.setItem("issend_samples", JSON.stringify(this.value));

      $('#total_price_sample').val('0')
      $('#underpayment_sample').val('0')
    });

    resetDelete()





    function CheckWadah(val) {
      var element = document.getElementById('wadah_samples_others');

      if (val == '0')
        element.style.display = 'block';
      else

        element.style.display = 'none';
    }



    $('#subcontract_samples').change(function() {
      var element = document.getElementById('subcontract_name_samples');
      if ($(this).is(":checked")) {
        element.style.display = 'block';
        // it is checked
      } else {
        element.style.display = 'none';
      }
    })


    function CheckPayment(val) {
      var element = document.getElementById('payment_samples_others');

      if (val == '0')
        element.style.display = 'block';
      else

        element.style.display = 'none';
    }



    function CheckCondition(val) {
      var element = document.getElementById('condition_samples_others');

      if (val == '0')
        element.style.display = 'block';
      else

        element.style.display = 'none';
    }



    function CheckPacket(val) {
      var element = document.getElementById('methodAttributes');

      if (val == '0')

        $('#methodAttributes').next(".select2-container").show();

      else
        $('#methodAttributes').next(".select2-container").hide();
      $('#cost_samples').val($('#packet_samples').find(':selected').attr('data-value'))
    }

    function CheckPacketSampling(val) {
      // console.log($('#packet_samples_sampling').find(':selected').attr('data-value'))

      // var element=document.getElementById('methodAttributes_sampling');

      // if(val=='0')

      //     $('#methodAttributes_sampling').next(".select2-container").show();

      // else
      $('#methodAttributes_sampling').next(".select2-container").show();

      // var element = evt.params.data.element;
      // var $element = $(element);
      // $element.detach();
      // $("#methodAttributes_sampling").append($element);
      // $("#methodAttributes_sampling").val(null).trigger("change");

      // $("#methodAttributes_sampling").val(["26378736-0900-4689-b97d-98e914e5a104"]).trigger("change");

      // $("#methodAttributes_sampling").select2('destroy');

      var url = "{{ route('elits-permohonan-uji.getPacketDetail', ['#']) }}"
      url = url.replace('#', val);

      // console.log(url)
      $.ajax({
        url: url,
        type: "GET",
        success: function(response) {

          // console.log(response)
          var array_option = []
          response.packet_sample_detail.forEach(method => {
            // $('#methodAttributes_sampling').append('<option value="'+method.id_method+'" data-extra="'+method.price_method+'" selected>'+method.params_method+'</option>')
            var option = new Option(method.params_method, method.id_method, true, true, method.price_method);
            option.setAttribute("data-extra", method.price_method);
            $("#methodAttributes_sampling").append(option).trigger("change");

          })





          // $('#methodAttributes_sampling').select2({
          //     placeholder: "Pilih Metode",
          //     // allowClear: true,
          //     ajax: {
          //         url: "{{ url('/api/method/') }}",
          //         method: "post",
          //         dataType: 'json',

          //         params: { // extra parameters that will be passed to ajax
          //                 contentType: "application/json;",
          //         },
          //         data: function (term) {
          //                 return {
          //                     term: term.term || '',
          //                     page: term.page || 1
          //                 };

          //             },
          //         cache: true
          //     }
          // });

          $('#total_price_sample').val($('#packet_samples_sampling').find(':selected').attr('data-value'))
          $('#underpayment_sample').val($('#packet_samples_sampling').find(':selected').attr('data-value'))

          $('#methodAttributes_sampling').on('change', function() {
            var option_el = $('#methodAttributes_sampling').select2('data');
            var total = 0;




            option_el.forEach(option_el_obj => {


              if (option_el_obj["data-extra"] != undefined) {

                total = total + option_el_obj["data-extra"]
              } else {
                if (option_el_obj.element.attributes[2].nodeValue != undefined && option_el_obj.element
                  .attributes[2].nodeValue != "") {
                  total = total + parseInt(option_el_obj.element.attributes[2].nodeValue)
                }
              }

            })

            // var data = option_el.data('data')

            // console.log(data['data-extra'])
            $('#total_price_sample').val(total)
            $('#underpayment_sample').val((total - $('#down_payment_sample').val()))
          });


        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
          alert(XMLHttpRequest.responseJSON.message);
        }
      })



    }

    $('#methodAttributes_sampling').on('change', function() {
      var option_el = $('#methodAttributes_sampling').select2('data');
      var total = 0;

      option_el.forEach(option_el_obj => {
        total = total + option_el_obj["data-extra"]
      })
      // var data = option_el.data('data')

      // console.log(data['data-extra'])
      $('#total_price_sample').val(total)
      $('#underpayment_sample').val(total)


    });




    var type, number, id_type, data;

    var current_fs, next_fs, previous_fs, is_submit = false; //fieldsets
    var opacity;

    $(document).ready(function() {



      // $('button').click(function() {
      //     option_el = $('#s1 :selected');
      //     data = option_el.data('data')

      //     console.log(data['data-type'])
      //     console.log(data['data-extra'])
      // });

      $(".next").click(function() {


        if ($(this).attr('data-id') == undefined) {


          if ($(this).hasClass("submit")) {
            is_submit = true;
          }

          if (!is_submit) {
            current_fs = $(this).parent();
            next_fs = $(this).parent().next();
          }

        } else {
          type = $(this).attr('data-type')
          id_type = $(this).attr('data-id')
          var code = $(this).attr('data-code')
          var url = "{{ route('elits-permohonan-uji.getIdSample', ['#']) }}"
          url = url.replace('#', id_type);

          // console.log(url)
          $.ajax({
            url: url,
            type: "GET",
            success: function(response) {

              var count_sample_add = 0,
                codesample;

              // console.log(sample_save.length)


              var codesample = '{{ 'S.KRNGY.' . \Carbon\Carbon::now()->format('ym') }}' + (response
                .samplecount + sample_save.length).toString().padStart(3, "0") + "-" + code
              $("#code_samples").val(codesample)
              number = response.samplecount + sample_save.length;

              $('#methodAttributes').next(".select2-container").hide();

              response.packet_sample_detail.forEach(method => {

                var option = new Option(method.params_method, method.id_method, true, true, method
                  .price_method);
                option.setAttribute("data-extra", method.price_method);
                $("#methodAttributes_selected").append(option).trigger("change");

                // $('#methodAttributes_selected').append('<option value="'+method.id_method+'" data-extra="'+method.price_method+'" selected>'+method.params_method+'</option>')
              })
              // console.log(response.packet_sample_detail)

              $('#cost_samples').val(response.packet_sample.price_packet)

              $("#packet_samples").val(response.packet_sample.packet_id).change();


              $('#methodAttributes_selected').select2({
                placeholder: "Pilih Metode",
                // allowClear: true,
                ajax: {
                  url: "{{ url('/api/method/') }}",
                  method: "post",
                  dataType: 'json',

                  params: { // extra parameters that will be passed to ajax
                    contentType: "application/json;",
                  },
                  data: function(term) {
                    return {
                      term: term.term || '',
                      page: term.page || 1
                    };

                  },
                  cache: true
                }
              });

              $('#methodAttributes_selected').on('change', function() {
                var option_el = $('#methodAttributes_selected').select2('data');
                var total = 0;

                option_el.forEach(option_el_obj => {
                  if (option_el_obj["data-extra"] != undefined) {

                    total = total + option_el_obj["data-extra"]
                  } else {
                    if (option_el_obj.element.attributes[2].nodeValue != undefined && option_el_obj
                      .element.attributes[2].nodeValue != "") {
                      total = total + parseInt(option_el_obj.element.attributes[2].nodeValue)
                    }
                  }

                })

                // var data = option_el.data('data')

                // console.log(data['data-extra'])
                $('#cost_samples').val(total)
              });

              $('#methodAttributes').on('change', function() {
                var option_el = $('#methodAttributes').select2('data');
                var total = 0;


                option_el.forEach(option_el_obj => {
                  if (option_el_obj["data-extra"] != undefined) {

                    total = total + option_el_obj["data-extra"]
                  } else {
                    if (option_el_obj.element.attributes[2].nodeValue != undefined && option_el_obj
                      .element.attributes[2].nodeValue != "") {
                      total = total + parseInt(option_el_obj.element.attributes[2].nodeValue)
                    }
                  }

                })

                // var data = option_el.data('data')

                // console.log(data['data-extra'])
                $('#cost_samples').val(total)
              });


            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.responseJSON.message);
            }
          })
          current_fs = $(this).parent().parent().parent().parent();
          next_fs = $(this).parent().parent().parent().parent().next();
        }


        //Add Class Active

        $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

        //show the next fieldset
        next_fs.show();
        //hide the current fieldset with style
        current_fs.animate({
          opacity: 0
        }, {
          step: function(now) {
            // for making fielset appear animation
            opacity = 1 - now;

            current_fs.css({
              'display': 'none',
              'position': 'relative'
            });
            next_fs.css({
              'opacity': opacity
            });
          },
          duration: 600
        });

        $(".previous").click(function() {



          if ($(this).attr('data-id') == undefined) {


            current_fs = $(this).parent();
            previous_fs = $(this).parent().prev();

          } else {
            current_fs = $(this).parent().parent().parent().parent();
            previous_fs = $(this).parent().parent().parent().parent().prev();
          }


          //Remove class active

          $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

          //show the previous fieldset
          previous_fs.show();

          //hide the current fieldset with style
          current_fs.animate({
            opacity: 0
          }, {
            step: function(now) {
              // for making fielset appear animation
              opacity = 1 - now;

              current_fs.css({
                'display': 'none',
                'position': 'relative'
              });
              previous_fs.css({
                'opacity': opacity
              });
            },
            duration: 600
          });
        });

        $('.radio-group .radio').click(function() {
          $(this).parent().find('.radio').removeClass('selected');
          $(this).addClass('selected');
        });

        $(".submit").click(function() {

          return false;



        })

      });
    })




    $('.datepicker').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.datepicker').datepicker('update', new Date());

    $('.datelab_samples').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.datelab_samples').datepicker('update', new Date());
    $('.datesampling_samples').datepicker('update', new Date());
    $('.date_get_sample').datepicker('update', new Date());
    // $('.date_done_estimation').datepicker('update', new Date());




    $('#issend_samples').on('change', function() {
      if ($('input[name=issend_samples]:checked').val()) {

        $('#sender_samples').next(".select2-container").hide();

        $('#label-sender_samples').hide();
      } else {
        $('#sender_samples').next(".select2-container").show();
        $('#label-sender_samples').show();


      }
    });



    $('#add-sample').on('click', function() {
      // console.log("adadad")

      var index_now

      for (let index = 0; index < 4; index++) {
        if ($("#progressbar li").eq(index).hasClass("active")) {
          index_now = index;
        }
      }


      current_fs = $("fieldset").eq(index_now)
      current_fs.css({
        'display': 'none',
        'position': 'relative',
        'opacity': 0
      });
      $('.first').css({
        'opacity': 1
      });
      next_fs = $("fieldset").eq(1)

      $('.first').show();

      previous_fs = null, is_submit = false; //fieldsets
      opacity = null;
      $("#progressbar li").removeClass("active");
      $("#progressbar li").eq(0).addClass("active");
      $('#msform').trigger("reset");




      $('.datepicker').datepicker({
        format: 'dd/mm/yyyy'
      });
      $('.datepicker').datepicker('update', new Date());

      $('.datelab_samples').datepicker({
        format: 'dd/mm/yyyy'
      });
      $('.datelab_samples').datepicker('update', new Date());
      $('.datesampling_samples').datepicker('update', new Date());
      $('.date_getsampling_samples').datepicker('update', new Date());



      $('.js-customer-basic-multiple').val('').trigger('change');
      $('#sender_samples').val('').trigger('change');
      $('.js-unit-basic-multiple').val('').trigger('change');
      $('.js-example-basic-multiple').val('').trigger('change');
      $('#methodAttributes').next(".select2-container").hide();
      $('#wadah_samples_others').css({
        'display': 'none'
      });

      $('#sender_samples').next(".select2-container").show();
      $('#label-sender_samples').show();

      $('.mymodal').modal('toggle');





      // current_fs.animate({opacity: 0}, {
      //     step: function(now) {
      //     // for making fielset appear animation
      //         opacity = 1 - now;

      //         current_fs.css({
      //             'display': 'none',
      //             'position': 'relative'
      //         });
      //         $('.first').css({'opacity': opacity});
      //     },
      //     duration: 600
      // });


    })



    $('#submit').on('click', function() {
      // var customerAttributes = $("#customerAttributes").val()
      var code_samples = $("#code_samples").val()
      var datelab_samples = $("#datelab_samples").val()

      var codenumber_samples = number;
      var cost_samples = $("#cost_samples").val()
      var wadah_samples = $("#wadah_samples").val()
      var code_samples_user = $("#code_samples_user").val()

      var wadah_samples_others;
      var packet_samples = $("#packet_samples").val();
      var packet_samples_others = [];
      var packet_samples_selected = [];



      if (packet_samples == '0') {
        packet_samples_others = $("#methodAttributes").val()
        packet_samples_selected = $("#methodAttributes_selected").val()
      } else {
        packet_samples_selected = $("#methodAttributes_selected").val()
      }

      if (wadah_samples == '0') {
        wadah_samples_others = $("#wadah_samples_others").val()
      } else {
        wadah_samples_others = '';
      }

      var unitAttributes = $("#unitAttributes").val()
      var typesample_samples = id_type;
      var issend_samples = $("#issend_samples").val()

      if ($("#issend_samples").is(":checked")) {
        issend_samples = "on";
        // it is checked
      } else {
        issend_samples = "off";
      }

      var sender_samples = $("#sender_samples").val()
      var weight_samples = $("#weight_samples").val()
      // var lat = $("#lat").val()
      // var long = $("#lng").val()





      var hasil = {
        codenumber_samples: codenumber_samples,
        typesample_samples: typesample_samples,
        code_samples_user: code_samples_user,
        packet_samples_selected: packet_samples_selected,
        code_samples: code_samples,
        datelab_samples: datelab_samples,
        cost_samples: cost_samples,
        wadah_samples: wadah_samples,
        wadah_samples_others: wadah_samples_others,
        unitAttributes: unitAttributes,
        typesample_samples: typesample_samples,
        packet_samples: packet_samples,
        packet_samples_others: packet_samples_others,
        weight_samples: weight_samples,

      }
      sample_save.push(hasil)
      dataSet.push([code_samples,
        '<button type="submit" class="delete btn btn-outline-danger btn-rounded btn-icon" data-id="' +
        code_samples + '"><i class="fas fa-trash"></i></button>'
      ])
      // console.log(sample_save)

      localStorage.setItem("sample_save", JSON.stringify(sample_save));
      localStorage.setItem("dataSet", JSON.stringify(dataSet));


      datatable.clear();
      datatable.rows.add(dataSet);
      datatable.draw();
      resetDelete()



      $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

      current_fs = $("#submit").parent();
      next_fs = $("#submit").parent().next();


      next_fs.show();
      //hide the current fieldset with style
      current_fs.animate({
        opacity: 0
      }, {
        step: function(now) {
          // for making fielset appear animation
          opacity = 1 - now;

          current_fs.css({
            'display': 'none',
            'position': 'relative'
          });
          next_fs.css({
            'opacity': opacity
          });
        },
        duration: 600
      });

      var total_price_sample = 0

      sample_save.forEach(sample => {
        total_price_sample = total_price_sample + parseInt(sample.cost_samples)
      })
      $('#count_sample').val(sample_save.length)
      $('#total_price_sample').val(total_price_sample)
      getPayment()

      $('#sample-modal').modal('toggle');

      // console.log($("fieldset").index(current_fs).next())
      // $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
      // $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
      // $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");


      // console.log(current_fs)
      // $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
      // current_fs = $(this).parent().parent().parent().parent();
      // previous_fs = $(".first");
      // previous_fs.show();




      //show the previous fieldset





    })

    function resetDelete() {
      $('.delete').on('click', function(event) {

        var temp = []

        var code = $(this).data('id');

        console.log(code)

        sample_save = $.grep(sample_save, function(e) {
          return e.code_samples != code;
        });

        // console.log(temp)

        // sample_save=temp


        // console.log(sample_save)
        dataSet = $.grep(dataSet, function(e) {

          return e[0] != code;
        });

        localStorage.setItem("sample_save", JSON.stringify(sample_save));
        localStorage.setItem("dataSet", JSON.stringify(dataSet));


        datatable.clear();
        datatable.rows.add(dataSet);
        datatable.draw();
        resetDelete()

        var total_price_sample = 0

        sample_save.forEach(sample => {
          total_price_sample = total_price_sample + parseInt(sample.cost_samples)
        })
        $('#count_sample').val(sample_save.length)
        $('#total_price_sample').val(total_price_sample)
        getPayment()
      });
    }


    $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");




      // var selectedValues = ["5c8b7dd9-ff36-4291-ae41-425e637ebe6d","89e359c4-a874-43d1-bc7d-aba263c63ecb","aa5eba43-7038-4f60-820b-c891a550af8b"]

      // $(".js-example-basic-multiple").select2().val(selectedValues).trigger('change')


      $('.js-customer-basic-multiple').select2({
        placeholder: "Pilih Customer",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/customer/') }}",
          method: "post",
          dataType: 'json',

          params: { // extra parameters that will be passed to ajax
            contentType: "application/json;",
          },
          data: function(term) {
            return {
              term: term.term || '',
              page: term.page || 1
            };
          },
          cache: true
        }
      });
      $('#sender_samples').select2({
        placeholder: "Pilih Petugas",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/sampling-officer/') }}",
          method: "post",
          dataType: 'json',

          params: { // extra parameters that will be passed to ajax
            contentType: "application/json;",
          },
          data: function(term) {
            return {
              term: term.term || '',
              page: term.page || 1
            };
          },
          cache: true
        }
      });




      $('.js-unit-basic-multiple').select2({
        placeholder: "Pilih Unit",
        allowClear: true,
        ajax: {
          url: "{{ url('/api/unit/') }}",
          method: "post",
          dataType: 'json',

          params: { // extra parameters that will be passed to ajax
            contentType: "application/json;",
          },
          data: function(term) {
            return {
              term: term.term || '',
              page: term.page || 1
            };
          },
          cache: true
        }
      });


      $("#submitAll").click(function() {
        var code_permohonan_uji = $('#code_permohonan_uji').val()
        var customerAttributes = $('#customerAttributes').val()
        var name_personil = $('#name_personil').val()
        var issend_samples = $("input[name='issend_samples']:checked").val();
        var date_get_sample = "";
        var datesampling_samples = "";
        var sample_save_value = []
        var packet_samples_sampling;
        var methodAttributes_sampling;
        var condition_samples, condition_samples_others;
        if (issend_samples == "on") {
          datesampling_samples = $('#datesampling_samples').val()
          packet_samples_sampling = $('#packet_samples_sampling').val()
          methodAttributes_sampling = $('#methodAttributes_sampling').val()

        } else {
          date_get_sample = $('#date_get_sample').val()
          sample_save_value = sample_save
          condition_samples = $('#condition_samples').val()
          condition_samples_others = $('#condition_samples_others').val()
        }

        // // var date_done_estimation = $('#date_done_estimation').val()
        var count_sample = $('#count_sample').val()
        var total_price_sample = $('#total_price_sample').val()
        var down_payment_sample = $('#down_payment_sample').val()
        var underpayment_sample = $('#underpayment_sample').val()
        var payment_samples = $('#payment_samples').val()
        var payment_samples_others = $('#payment_samples_others').val()
        var note_sample = $('#note_sample').val()
        var subcontract_samples = $('#subcontract_samples').val()
        var subcontract_name_samples = $('#subcontract_name_samples').val()
        let _token = "{{ csrf_token() }}"

        $.ajax({
          url: "{{ route('elits-permohonan-uji.store') }}",
          type: "POST",
          data: {
            code_permohonan_uji: code_permohonan_uji,
            customerAttributes: customerAttributes,
            name_personil: name_personil,
            issend_samples: issend_samples,
            date_get_sample: date_get_sample,
            datesampling_samples: datesampling_samples,
            Allsamples: sample_save_value,
            packet_samples_sampling: packet_samples_sampling,
            methodAttributes_sampling: methodAttributes_sampling,
            condition_samples: condition_samples,
            condition_samples_others: condition_samples_others,
            // // date_done_estimation: date_done_estimation,
            count_sample: count_sample,
            total_price_sample: total_price_sample,
            down_payment_sample: down_payment_sample,
            underpayment_sample: underpayment_sample,
            payment_samples: payment_samples,
            payment_samples_others: payment_samples_others,
            note_sample: note_sample,
            subcontract_samples: subcontract_samples,
            subcontract_name_samples: subcontract_name_samples,
            _token: _token
          },
          success: function(response) {
            localStorage.clear();
            var url = "{{ route('elits-permohonan-uji.index') }}";
            window.location.href = url;
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
            var json_result = XMLHttpRequest.responseJSON.errors;
            var key = Object.keys(json_result)[0];
            alert(json_result[key])
          }
        })

      })














    });
  </script>
@endsection
