@extends('masterweb::template.admin.layout')
@section('title')
  Sample Management
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
      width: 25%;
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
                <li class="breadcrumb-item"><a href="{{ url('/elits-samples') }}">Sample Management</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>create</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

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
                    <li class="active" id="account"><strong>Jenis Sample</strong></li>
                    <li id="personal"><strong>Detail Sample</strong></li>
                    <li id="payment"><strong>Titik Sampling</strong></li>
                    <li id="confirm"><strong>Selesai</strong></li>
                  </ul>
                  <fieldset>
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
                                    <h5 style="color:black;">{{ $sampletype->name_sample_type }}</h5>
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
                            <label for="customer_samples">Customer</label>
                            <select id="customerAttributes" class="js-customer-basic-multiple js-states form-control"
                              required style="width: 100%">
                            </select>
                          </div>

                          <div class="form-group">

                            <label for="name_customer">Paket Method</label>
                            <select name="packet_samples" id="packet_samples" class="form-control"
                              onchange='CheckPacket(this.value);' style="margin-bottom:4px; width: 100%">
                              <option value="" selected disabled>Pilih Packet</option>
                              @foreach ($packets as $packet)
                                <option value="{{ $packet->id_packet }}" data-value="{{ $packet->price_packet }}">
                                  {{ $packet->name_packet }}</option>
                              @endforeach
                              <option value="0">Lainnya</option>
                            </select>
                            <select id="methodAttributes" class="js-example-basic-multiple form-control"
                              style="width: 100%; display:none;" multiple="multiple" required>
                            </select>
                          </div>


                          <div class="form-group">
                            <div class="form-row align-items-center col-md-12" id="fields-relay-1">
                              <div class="col-md-2" id="condition">
                                <input type="checkbox" id="issend_samples" class="issend_samples" name="issend_samples"
                                  data-toggle="toggle"> DIANTAR

                              </div>
                              <div class="col-md-10" id="name_sampling_field">
                                <label id="label-sender_samples" for="sender_samples">Petugas Sampling</label>
                                <select id="sender_samples" class="js-customer-basic-multiple js-states form-control"
                                  required style="width: 100%">
                                </select>
                              </div>
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="wadah_samples">Wadah</label>
                            <select name="wadah_samples" id="wadah_samples" class="form-control hidden"
                              onchange='CheckWadah(this.value);' style="margin-bottom:4px; width: 100%;">
                              <option value="" selected disabled>Pilih Wadah</option>
                              @foreach ($containers as $container)
                                <option value="{{ $container->id_container }}">{{ $container->name_container }}
                                </option>
                              @endforeach
                              <option value="0">Lainnya</option>
                            </select>
                            <input type="text" class="form-control" id="wadah_samples_others" style='display:none;'
                              id="wadah_samples" name="wadah_samples" placeholder="Isikan Wadah" required>
                          </div>


                          <div class="form-group">
                            <div class="form-row align-items-center col-md-12" id="fields-relay-1">
                              <div class="col-md-6" id="condition">
                                <label for="weight_samples">Berat/Volume</label>
                                <input type="number" class="form-control" step="0.01" id="weight_samples"
                                  name="amount_samples" placeholder="Isikan Berat/Volume" required>
                              </div>
                              <div class="col-md-6" id="name_sampling_field">
                                <br>
                                <select id="unitAttributes" class="js-unit-basic-multiple js-states form-control"
                                  required style="width: 100%">

                                </select>

                              </div>
                            </div>
                          </div>

                          <div class="form-group">
                            <label for="datesampling_samples">Tanggal Sampling</label>
                            <div class="input-group date">
                              <input type="text" class="form-control datesampling_samples"
                                name="datesampling_samples" id="datesampling_samples"
                                placeholder="Isikan Tanggal Sampling" data-date-format="dd/mm/yyyy" required>
                              <div class="input-group-append">
                                <span class="input-group-text">
                                  <i class="fas fa-calendar-alt"></i>
                                </span>
                              </div>
                            </div>
                          </div>


                          <div class="form-group">
                            <label for="datelab_samples">Tanggal Masuk Lab</label>
                            <div class="input-group date">
                              <input type="text" class="form-control datelab_samples" name="datelab_samples"
                                id="datelab_samples" placeholder="Isikan Tanggal Masuk Lab"
                                data-date-format="dd/mm/yyyy" required>
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
                              <input type="number" class="form-control" id="cost_samples" name="cost_samples"
                                value="0" placeholder="Isikan Harga" readonly required>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>

                    {{-- <button class="previous action-button-previous">Kembali</button>
                                        <button type="submit" class="next action-button btn btn-primary mr-2" id="submit">Simpan</button> --}}
                    <input type="button" name="previous" class="previous btn btn-light" value="Sebelumnya" /> <input
                      type="button" name="next" class="next btn btn-primary mr-2" value="Lanjut" />


                  </fieldset>
                  <fieldset>
                    <div class="form-card">
                      {{-- <h2 class="fs-title">Input</h2>  --}}
                      <div class="container-fluid">
                        <div class="row">

                          <div class="col-lg-12">

                            <div id="pac-input-div" class="col-md-7 mb-3">
                              <input id="pac-input" class="form-control" type="text" placeholder="Pencarian Lokasi"
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
                                <input type="text" id="lat" name="lat" class="form-control"
                                  placeholder="Lat">
                              </div>
                              <div class="col">
                                <label for="name">Long</label>
                                <input type="text" id="lng" name="lng" class="form-control"
                                  placeholder="Long">
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>
                      {{-- <h2 class="fs-title">Payment Information</h2>
                                            <div class="radio-group">
                                                <div class='radio' data-value="credit"><img src="https://i.imgur.com/XzOzVHZ.jpg" width="200px" height="100px"></div>
                                                <div class='radio' data-value="paypal"><img src="https://i.imgur.com/jXjwZlj.jpg" width="200px" height="100px"></div> <br>
                                            </div> <label class="pay">Card Holder Name*</label> <input type="text" name="holdername" placeholder="" />
                                            <div class="row">
                                                <div class="col-9"> <label class="pay">Card Number*</label> <input type="text" name="cardno" placeholder="" /> </div>
                                                <div class="col-3"> <label class="pay">CVC*</label> <input type="password" name="cvcpwd" placeholder="***" /> </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3"> <label class="pay">Expiry Date*</label> </div>
                                                <div class="col-9"> <select class="list-dt" id="month" name="expmonth">
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
                                                    </select> <select class="list-dt" id="year" name="expyear">
                                                        <option selected>Year</option>
                                                    </select> </div>
                                            </div> --}}
                    </div>
                    <input type="button" name="previous" class="previous btn btn-light" value="Sebelumnya" />
                    <button class="next btn btn-primary mr-2 submit" id="submit">Simpan</button>

                  </fieldset>
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




  <script></script>
@endsection

@section('scripts')
  <script>
    function CheckWadah(val) {
      var element = document.getElementById('wadah_samples_others');

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



    var type, number, id_type, data;

    $(document).ready(function() {

      var current_fs, next_fs, previous_fs, is_submit = false; //fieldsets
      var opacity;

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
          var url = "{{ route('elits-samples.getIdSample', ['#']) }}"
          url = url.replace("#", type);
          // console.log(url)
          $.ajax({
            url: url,
            type: "GET",
            success: function(response) {

              var codesample = '{{ 'KS.CLCP.' . \Carbon\Carbon::now()->format('ym') }}' + (response
                .samplecount).toString().padStart(3, "0") + "-" + code
              $("#code_samples").val(codesample)
              number = response.samplecount;

              $('#methodAttributes').next(".select2-container").hide();

              $('#methodAttributes').on('change', function() {
                var option_el = $('#methodAttributes').select2('data');
                var total = 0;

                option_el.forEach(option_el_obj => {
                  total = total + option_el_obj["data-extra"]
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

          current_fs = $(this).parent();
          previous_fs = $(this).parent().prev();

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
          var customerAttributes = $("#customerAttributes").val()
          var code_samples = $("#code_samples").val()
          var datelab_samples = $("#datelab_samples").val()
          var datesampling_samples = $("#datesampling_samples").val()
          var codenumber_samples = number;
          var cost_samples = $("#cost_samples").val()
          var wadah_samples = $("#wadah_samples").val()
          var wadah_samples_others;
          var packet_samples = $("#packet_samples").val();
          var packet_samples_others = [];



          if (packet_samples == '0') {
            packet_samples_others = $("#methodAttributes").val()
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
          var lat = $("#lat").val()
          var long = $("#lng").val()



          let _token = "{{ csrf_token() }}"



          $.ajax({
            url: "{{ route('elits-samples.store') }}",
            type: "POST",
            data: {
              codenumber_samples: codenumber_samples,
              typesample_samples: typesample_samples,
              // methodAttributes:methodAttributes,
              customerAttributes: customerAttributes,
              code_samples: code_samples,
              datelab_samples: datelab_samples,
              datesampling_samples: datesampling_samples,
              poinsample_lat_samples: lat,
              poinsample_long_samples: long,
              cost_samples: cost_samples,
              wadah_samples: wadah_samples,
              wadah_samples_others: wadah_samples_others,
              unitAttributes: unitAttributes,
              typesample_samples: typesample_samples,
              issend_samples: issend_samples,
              packet_samples: packet_samples,
              packet_samples_others: packet_samples_others,
              sender_samples: sender_samples,
              weight_samples: weight_samples,
              _token: _token
            },
            success: function(response) {


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
              var url = "{{ route('elits-samples.index') }}";
              window.location.href = url;
              return false;

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.responseJSON.message);
            }
          });


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



    $("#submit").click(function() {


    });



    $('#issend_samples').on('change', function() {
      if ($('input[name=issend_samples]:checked').val()) {

        $('#sender_samples').next(".select2-container").hide();

        $('#label-sender_samples').hide();
      } else {
        $('#sender_samples').next(".select2-container").show();
        $('#label-sender_samples').show();


      }
    });


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







      $('.js-example-basic-multiple').select2({
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







    });
  </script>
@endsection
