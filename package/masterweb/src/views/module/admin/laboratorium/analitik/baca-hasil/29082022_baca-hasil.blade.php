@extends('masterweb::template.admin.layout')
@section('title')
  Baca Hasil
@endsection

@section('content')
  <style>
    [data-tip] {
      position: relative;

    }

    [data-tip]:before {
      content: '';
      /* hides the tooltip when not hovered */
      display: none;
      content: '';
      border-left: 5px solid transparent;
      border-right: 5px solid transparent;
      border-bottom: 5px solid #1a1a1a;
      position: absolute;
      top: 30px;
      left: 35px;
      z-index: 8;
      font-size: 0;
      line-height: 0;
      width: 0;
      height: 0;
      white-space: pre-line;
    }

    [data-tip]:after {
      display: none;
      content: attr(data-tip);
      position: absolute;
      top: 35px;
      left: 0px;
      padding: 5px 8px;
      background: #1a1a1a;
      color: #fff;
      z-index: 9;
      font-size: 0.75em;
      height: 180px;
      line-height: 18px;
      -webkit-border-radius: 3px;
      -moz-border-radius: 3px;
      border-radius: 3px;
      white-space: nowrap;
      word-wrap: normal;
      white-space: pre-line;
    }

    [data-tip]:hover:before,
    [data-tip]:hover:after {
      display: block;
    }

    @media only screen and (max-width: 600px) {
      [data-tip]:after {
        height: 280px;
      }
    }

    @media only screen and (min-width: 601px) {
      [data-tip]:after {
        height: 180px;
      }
    }
  </style>



  <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">




  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda </a></li>
                <li class="breadcrumb-item"><a
                    href="{{ url('/elits-samples/verification', [Request::segment(2), Request::segment(3)]) }}"> Baca
                    Hasil</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Analys</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="card">
    <div class="card-header">
      <H4>Baca Hasil</H4>
    </div>
    <div class="card-body">
      <div class="content">
        <div class="container-fluid">
          <div class="row">
            <!-- utama -->

            <div class="col-md-12">
              <table class="table table-bordered">
                <tr>
                  <th><b>Laboratorium</b></th>
                  <td>{{ $sample->nama_laboratorium }}</td>
                  <th><b>Tanggal Pengambilan</b></th>
                  <td>
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}
                  </td>
                  <th><b>Jenis Sampel</b></th>
                  @php
                    $jenis_makanan = $sample->jenis_makanan;
                    if (isset($jenis_makanan)) {
                        $jenis_makanan = $jenis_makanan->name_jenis_makanan;
                    }
                  @endphp
                  <td>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) ? '' : ' - ' . $jenis_makanan }} </td>
                </tr>
                <tr>
                  <th><b>Nomor Sampel</b></th>
                  <td>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</td>
                  <th><b>Tanggal Pengiriman</b></th>
                  <td>
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y HH:mm') }}
                  </td>
                  <th><b>Nama Pengambil</b></th>
                  <td>{{ $sample->nama_pengambil }}</td>
                </tr>
              </table>
              <br>

              {{-- Note sample dengan kondisi --}}
              @if ($sample->note_samples !== null)
                <div class="alert alert-warning" role="alert">
                  {{ $sample->note_samples }}
                </div>
              @endif

              <h5>Parameter {{ $sample->nama_laboratorium }} :</h5>
              @foreach ($laboratoriummethods as $laboratoriummethod)
                - {{ $laboratoriummethod->params_method }}<br>
              @endforeach
              <br>

              <div class="col-md-12">

                <div class="row">
                  <div class="col-md-12">

                    <form class="form"
                      action="{{ route('elits-baca-hasil.store', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}"
                      method="POST">
                      @csrf

                      <div class="form-group">
                        <label for="wadah_samples"><b>Pengujian dilakukan:</b></label>

                        @php
                          if (isset($sampleanalitikprogress->date_done)) {
                              $pengujian_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sampleanalitikprogress->date_done)->isoFormat('Y/M/D');
                          } else {
                              $pengujian_date = '';
                          }
                        @endphp
                        <div class="input-group date">
                          <input type="text" class="form-control pengujian" name="pengujian" id="pengujian"
                            placeholder="Isikan Tanggal Pengujian" data-date-format="dd/mm/yyyy" required>
                          <div class="input-group-append">
                            <span class="input-group-text">
                              <i class="fas fa-calendar-alt"></i>
                            </span>
                          </div>
                        </div>

                      </div>

                      <div class="form-group">
                        <table class="table">
                          <tr>
                            <th width="5%">No</th>
                            <th width="30%">Jenis Parameter</th>
                            <th width="10%">Tidak Dikosongi</th>
                            <th width="10%">Kadar Maksimum Yang diperbolehkan</th>
                            <th width="10%">Satuan</th>
                            <th width="30%">Hasil</th>
                            <th width="10%">Dianggap melewati baku mutu</th>
                          </tr>
                          @php
                            $no = 1;
                          @endphp
                          @foreach ($laboratoriummethods as $laboratoriummethod)
                            @if (count($laboratoriummethod['detail']) == 0)
                              <tr>
                                <td>{{ $no }}</td>
                                <td>
                                  <b>{!! $laboratoriummethod->name_report !!}</b>
                                </td>
                                <td>
                                  <input type="checkbox" id="status_{{ $laboratoriummethod->method_id }}" value="true"
                                    name="status_{{ $laboratoriummethod->method_id }}" class="status-relay" checked>
                                </td>
                                <td>{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
                                <td>{!! isset($laboratoriummethod->shortname_unit) ? $laboratoriummethod->shortname_unit : '-' !!}</td>
                                <td>
                                  <span class="not_show_{{ $laboratoriummethod->method_id }}"
                                    style="display: none;">-</span>
                                  <div class="show_{{ $laboratoriummethod->method_id }}">

                                    <div data-html="true" data-toggle="tooltip"
                                      data-tip="Syarat: &#10;&#013;Apabila angka desimal (dengan koma) maka gunakan titik. ">
                                      <textarea class="form-control result_method result_method_{{ $laboratoriummethod->method_id }}"
                                        id="result_method_{{ $laboratoriummethod->method_id }}" name="result_method_{{ $laboratoriummethod->method_id }}"
                                        data-min="{{ $laboratoriummethod->min }}" data-max="{{ $laboratoriummethod->max }}"
                                        data-equal="{{ $laboratoriummethod->equal }}" placeholder="Hasil" required>{!! isset($laboratoriummethod->hasil)
                                            ? rubahNilaikeForm($laboratoriummethod->hasil)
                                            : (isset($laboratoriummethod->equal)
                                                ? rubahNilaikeForm($laboratoriummethod->equal)
                                                : '') !!}</textarea>
                                      <br>

                                      <b>Output: {!! cek_hasil_color(
                                          isset($laboratoriummethod->hasil)
                                              ? $laboratoriummethod->hasil
                                              : (isset($laboratoriummethod->equal)
                                                  ? $laboratoriummethod->equal
                                                  : ''),
                                          $laboratoriummethod->min,
                                          $laboratoriummethod->max,
                                          $laboratoriummethod->equal,
                                          'result_output_method_' . $laboratoriummethod->method_id,
                                          $laboratoriummethod->offset_baku_mutu,
                                      ) !!}</b>
                                    </div>
                                  </div>
                                </td>
                                <td>
                                  <input type="radio" id="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                    value="true" name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                    class="offset_baku_mutu"
                                    {{ $laboratoriummethod->offset_baku_mutu == 'true' ? 'checked' : '' }}> Ya<br>
                                  <input type="radio" id="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                    value="false" name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                    class="offset_baku_mutu"
                                    {{ $laboratoriummethod->offset_baku_mutu == 'false' ? 'checked' : '' }}>Tidak<br>
                                  <input type="radio" id="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                    value="default" name="offset_baku_mutu_{{ $laboratoriummethod->method_id }}"
                                    class="offset_baku_mutu"
                                    {{ $laboratoriummethod->offset_baku_mutu == 'default' || !isset($laboratoriummethod->offset_baku_mutu) ? 'checked' : '' }}>Default
                                </td>
                              </tr>
                            @else
                              <tr>
                                <td style="vertical-align:top" rowspan="{{ count($laboratoriummethod['detail']) + 1 }}">
                                  {{ $no }}</td>
                                <td colspan="6">
                                  <b>{!! $laboratoriummethod->name_report !!}</b>
                                </td>
                              </tr>
                              @foreach ($laboratoriummethod['detail'] as $detail)
                                <tr>
                                  <td>
                                    {!! $detail->name_sample_result_detail !!}
                                  </td>
                                  <td>
                                    <input type="checkbox" id="status_{{ $detail->id_sample_result_detail }}"
                                      value="true" name="status_{{ $detail->id_sample_result_detail }}"
                                      class="status-relay" checked>
                                  </td>
                                  <td>{!! $detail->nilai_sample_result_detail !!}</td>
                                  <td>{!! isset($laboratoriummethod->shortname_unit) ? $laboratoriummethod->shortname_unit : '-' !!}</td>
                                  <td>
                                    <span class="not_show_{{ $detail->id_sample_result_detail }}"
                                      style="display: none;">-</span>
                                    <div class="show_{{ $detail->id_sample_result_detail }}">
                                      <div data-html="true" data-toggle="tooltip"
                                        data-tip="Syarat: &#10;&#013; Apabila angka desimal (dengan koma) maka gunakan titik. &#10;&#013;">
                                        <textarea class="form-control result_method result_method_{{ $detail->id_sample_result_detail }}"
                                          id="result_method_{{ $detail->id_sample_result_detail }}"
                                          name="result_method_{{ $detail->id_sample_result_detail }}" data-min="{{ $detail->min_sample_result_detail }}"
                                          data-max="{{ $detail->max_sample_result_detail }}" data-equal="{{ $detail->equal_sample_result_detail }}"
                                          placeholder="Hasil" required>{!! isset($detail->hasil)
                                              ? rubahNilaikeForm($detail->hasil)
                                              : (isset($detail->equal_sample_result_detail)
                                                  ? rubahNilaikeForm($detail->equal_sample_result_detail)
                                                  : '') !!}</textarea>
                                        <br>
                                        <b>Output: {!! cek_hasil_color(
                                            isset($detail->hasil)
                                                ? $detail->hasil
                                                : (isset($detail->equal_sample_result_detail)
                                                    ? $detail->equal_sample_result_detail
                                                    : ''),
                                            $detail->min_sample_result_detail,
                                            $detail->max_sample_result_detail,
                                            $detail->equal_sample_result_detail,
                                            'result_output_method_' . $detail->id_sample_result_detail,
                                            $detail->offset_baku_mutu,
                                        ) !!}</b>

                                      </div>
                                    </div>
                                  </td>
                                  <td>
                                    <input type="radio" id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                      value="true" name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                      class="offset_baku_mutu"
                                      {{ $detail->offset_baku_mutu == 'true' ? 'checked' : '' }}>
                                    Ya<br>
                                    <input type="radio" id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                      value="false" name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                      class="offset_baku_mutu"
                                      {{ $detail->offset_baku_mutu == 'false' ? 'checked' : '' }}>
                                    Tidak<br>
                                    <input type="radio" id="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                      value="default" name="offset_baku_mutu_{{ $detail->id_sample_result_detail }}"
                                      class="offset_baku_mutu"
                                      {{ $detail->offset_baku_mutu == 'default' || !isset($detail->offset_baku_mutu) ? 'checked' : '' }}>
                                    Default
                                  </td>
                                </tr>
                              @endforeach
                            @endif


                            @php
                              $no++;
                            @endphp
                          @endforeach
                        </table>
                        {{-- <label for="wadah_samples"><b>Apakah baca hasil sudah siap?</b></label> --}}

                        <div class="form-check">
                          <input class="form-check-input" name="baca_hasil" type="checkbox" value="ya"
                            id="ya" required>
                          <label class="form-check-label" for="flexCheckChecked">
                            Pengisian Hasil sudah sesuai dengan hasil uji lapangan.
                          </label>
                        </div>
                        {{-- <div class="form-check">
                                                <input class="form-check-input" name="persiapan_reagen" type="radio" value="tidak" id="tidak" >
                                                <label class="form-check-label" for="flexCheckChecked">
                                                    Tidak
                                                </label>
                                            </div> --}}

                      </div>



                      <button type="submit" id="submitAll" class="btn btn-primary mr-2">Selesai</button>


                      <button type="button" class="btn btn-light" onclick="window.history.back()">Kembali</button>
                      <button type="button" id="saveAll" class="btn btn-primary mr-2"
                        style="float: right;">Simpan</button>

                    </form>




                  </div>
                </div>


              </div>


            </div>






          </div>

          <!-- utama -->
        </div>
        <!-- /.row -->
      </div>
    </div>

  </div>
@endsection

@section('scripts')
  <script>
    // saveAll
    $(".offset_baku_mutu").click(function() {
      var value_offset_baku_mutu = $(this).attr('value')

      var id = $(this).attr('id')

      id = id.substring(17, id.length);
      var value = $("#result_method_" + id).val();

      var min = $("#result_method_" + id).attr('data-min')
      min = (min != "") ? parseFloat(min) : "";
      var max = parseFloat($("#result_method_" + id).attr('data-max'));
      max = (max != "") ? parseFloat(max) : "";
      var equal = $("#result_method_" + id).attr('data-equal');

      value = toFormatHtml(value)

      if (value_offset_baku_mutu == "false") {
        $("#result_output_method_" + id).empty()
        $("#result_output_method_" + id).append(value)
        $("#result_output_method_" + id).attr("style", "color:black");
      } else if (value_offset_baku_mutu == "true") {
        $("#result_output_method_" + id).empty()
        $("#result_output_method_" + id).append(value)
        $("#result_output_method_" + id).attr("style", "color:red");
        $("#result_output_method_" + id).append("*")
      } else {
        $("#result_output_method_" + id).empty()
        $("#result_output_method_" + id).append(value)
        $("#result_output_method_" + id).attr("style", "color:black");
        if (min != undefined) {
          if (parseFloat(value) < min) {
            $("#result_output_method_" + id).attr("style", "color:red");
            $("#result_output_method_" + id).append("*")
          }
        }

        if (max != undefined) {
          if (parseFloat(value) > max) {
            $("#result_output_method_" + id).attr("style", "color:red");
            $("#result_output_method_" + id).append("*")
          }
        }

        if (equal != "") {
          if (value != equal) {
            $("#result_output_method_" + id).attr("style", "color:red");
            $("#result_output_method_" + id).append("*")
          }
        }
      }

    })
    $(".result_method").bind('input propertychange', function() {
      var id = $(this).attr('id')
      id = id.substring(14, id.length);
      var min = $(this).attr('data-min')
      min = (min != "") ? parseFloat(min) : "";
      var max = $(this).attr('data-max');

      max = (max != "") ? parseFloat(max) : "";

      var equal = $(this).attr('data-equal');

      var offset_baku_mutu = $('[name="offset_baku_mutu_' + id + '"]:checked').val();
      var value = this.value

      value = toFormatHtml(value)

      if (offset_baku_mutu == "false") {
        $("#result_output_method_" + id).empty()
        $("#result_output_method_" + id).append(value)
        $("#result_output_method_" + id).attr("style", "color:black");
      } else if (offset_baku_mutu == "true") {
        $("#result_output_method_" + id).empty()
        $("#result_output_method_" + id).append(value)
        $("#result_output_method_" + id).attr("style", "color:red");
        $("#result_output_method_" + id).append("*")
      } else {
        $("#result_output_method_" + id).empty()
        $("#result_output_method_" + id).append(value)
        $("#result_output_method_" + id).attr("style", "color:black");
        if (min != undefined) {
          if (parseFloat(value) < min) {
            $("#result_output_method_" + id).attr("style", "color:red");
            $("#result_output_method_" + id).append("*")
          }
        }

        if (max != undefined) {
          if (parseFloat(value) > max) {
            // console.log("#result_output_method_"+id);
            $("#result_output_method_" + id).attr("style", "color:red");
            $("#result_output_method_" + id).append("*")
          }
        }

        if (equal != "") {
          if (value != equal) {
            $("#result_output_method_" + id).attr("style", "color:red");
            $("#result_output_method_" + id).append("*")
          }
        }
      }


    })

    // $("input[name='category']").click(function() {
    //     category = this.value;
    // });
    $("#saveAll").click(function() {
      // alert( "Handler for .click() called." );
      var data = $('.form').serializeArray().reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
      }, {});
      var url =
        "{{ route('elits-baca-hasil.save', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}"
      // console.log(url)
      $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(data) {
          // Successful POST
          // do whatever you want
          swal(
            'Success!',
            'Data Berhasil Disimpan!',
            'success'
          )
        },
        error: function(data) {
          // Something went wrong
          // HERE you can handle asynchronously the response

          // Log in the console
          swal({
            type: 'error',
            title: 'Gagal!',
            text: 'Data belum Tersimpan!'
          });

        }
      });


      // console.log(data)

    });


    var date;
    if ("{{ $pengujian_date ? $pengujian_date : '' }}" != '') {
      date = new Date("{{ $pengujian_date ? $pengujian_date : '' }}")
    } else {
      date = new Date()
    }
    $('.pengujian').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.pengujian').datepicker('update', date);
    var laboratoriummethods = @json($laboratoriummethods);

    laboratoriummethods.forEach(laboratoriummethod => {
      $('#status_' + laboratoriummethod.method_id).change(function() {
        // console.log($(this).val())
        if ($(this).is(':checked')) {
          $(".not_show_" + laboratoriummethod.method_id).hide();
          $(".show_" + laboratoriummethod.method_id).show();
          // $("#result_method_"+laboratoriummethod.method_id).val("");

        } else {
          // $("#result_method_"+laboratoriummethod.method_id).val("-");
          $(".show_" + laboratoriummethod.method_id).hide();
          $(".not_show_" + laboratoriummethod.method_id).show();


        }
      })
      laboratoriummethod.detail.forEach(detail => {
        $('#status_' + detail.id_sample_result_detail).change(function() {
          // console.log($(this).val())
          if ($(this).is(':checked')) {
            $(".not_show_" + detail.id_sample_result_detail).hide();
            $(".show_" + detail.id_sample_result_detail).show();
            // $("#result_method_"+laboratoriummethod.method_id).val("");

          } else {
            // $("#result_method_"+laboratoriummethod.method_id).val("-");
            $(".show_" + detail.id_sample_result_detail).hide();
            $(".not_show_" + detail.id_sample_result_detail).show();


          }
        })
      })

    })

    function toFormatHtml(value) {
      value = value.replaceAll('^(', "<sup>");
      value = value.replaceAll(')', "</sup>");
      value = value.replaceAll('&#60', "<");
      value = value.replaceAll('&#62', ">");
      // console.log(value)
      return value;
      // let result = value.indexOf("^");
      // console.log(value.substring(result+1,value.length))
    }
  </script>
@endsection
