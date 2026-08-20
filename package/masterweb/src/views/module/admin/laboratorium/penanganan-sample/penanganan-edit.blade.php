@extends('masterweb::template.admin.layout')
@section('title')
  Penganan Sample
@endsection

@section('content')
  <style>

  </style>








  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>

                <li class="breadcrumb-item">
                  <a href="{{ url('/elits-permohonan-uji') }}">
                    Permohonan Uji</a>
                </li>

                <li class="breadcrumb-item">
                  <a href="{{ url('/elits-samples', [$sample->permohonan_uji_id]) }}">
                    Daftar Pengujian</a>
                </li>

                <li class="breadcrumb-item">
                  <a href="{{ url('/elits-samples/verification', [Request::segment(2), Request::segment(3)]) }}">
                    Analys</a>
                </li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="card">
    <div class="card-header">
      <H4>Penanganan Sampel</H4>
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
              <h5>Parameter {{ $sample->nama_laboratorium }} :</h5>
              @foreach ($laboratoriummethods as $laboratoriummethod)
                - {{ $laboratoriummethod->params_method }}<br>
              @endforeach
              <br>
              <div class="col-md-12">

                <div class="row">
                  <div class="col-md-12">

                    <form
                      action="{{ route('elits-penanganan-sample.store', [Request::segment(3), Request::segment(4)]) }}">
                      <div class="form-group">
                        <label for="wadah_samples">
                          <b>Penyimpanan Sample</b></label>
                        <div class="form-check">
                          <input class="form-check-input" name="penyimpanan_sample" type="radio" value="suhu kamar"
                            {{ isset($penanganan_sample->penyimpanan_sample) ? ($penanganan_sample->penyimpanan_sample == 'suhu kamar' ? 'checked' : '') : '' }}
                            id="suhu_kamar">
                          <label class="form-check-label" for="flexCheckChecked">
                            Suhu Kamar
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="penyimpanan_sample" type="radio" value="2 - 8"
                            {{ isset($penanganan_sample->penyimpanan_sample) ? ($penanganan_sample->penyimpanan_sample == '2 - 8' ? 'checked' : '') : '' }}
                            id="suhu_kamar">
                          <label class="form-check-label" for="flexCheckChecked">
                            2&deg; - 8 &deg;C
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="penyimpanan_sample" type="radio" value="<= 0"
                            {{ isset($penanganan_sample->penyimpanan_sample) ? ($penanganan_sample->penyimpanan_sample == '<= 0' ? 'checked' : '') : '' }}
                            id="suhu_kamar">
                          <label class="form-check-label" for="flexCheckChecked">
                            dibawah atau sampai dengan 0 &deg;C
                          </label>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="datepicker"><b>Tanggal Pemeriksaan</b></label>
                            <div class="input-group date">
                              @php

                                $date_checking = isset($penanganan_sample->date_checking) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penanganan_sample->date_checking)->format('d/m/Y') : Carbon\Carbon::now()->format('d/m/Y');
                                $date_checking2 = isset($penanganan_sample->date_checking) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penanganan_sample->date_checking) : Carbon\Carbon::now();

                              @endphp
                              <input type="text" class="form-control date_checking datepicker"
                                value="{{ $date_checking }}" name="date_checking" id="date_checking"
                                placeholder="Isikan Tanggal Pemeriksaan" data-date-format="dd/mm/yyyy" required>
                              <div class="input-group-append">
                                <span class="input-group-text">
                                  <i class="fas fa-calendar-alt"></i>
                                </span>
                              </div>
                            </div>
                          </div>

                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            @php
                              $date_done_estimation_labs = isset($penanganan_sample->date_done_estimation_labs) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penanganan_sample->date_done_estimation_labs)->format('d/m/Y') : Carbon\Carbon::now()->format('d/m/Y');
                              $date_done_estimation_labs2 = isset($penanganan_sample->date_done_estimation_labs) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penanganan_sample->date_done_estimation_labs) : Carbon\Carbon::now();

                            @endphp
                            <label for="date_done_estimation_labs"><b>Tanggal Selesai Pemeriksaan</b></label>
                            <div class="input-group date">
                              <input type="text" class="form-control date_done_estimation_labs datepicker"
                                value="{{ $date_done_estimation_labs }}" name="date_done_estimation_labs"
                                id="date_done_estimation_labs" placeholder="Isikan Tanggal Selesai Pemeriksaan"
                                data-date-format="dd/mm/yyyy" required>
                              <div class="input-group-append">
                                <span class="input-group-text">
                                  <i class="fas fa-calendar-alt"></i>
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="form-group">
                        @php
                          $destroyed_sample_date = isset($penanganan_sample->destroyed_sample_date) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penanganan_sample->destroyed_sample_date)->format('d/m/Y') : Carbon\Carbon::now()->addDays(14);
                          $destroyed_sample_date2 = isset($penanganan_sample->destroyed_sample_date) ? Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penanganan_sample->destroyed_sample_date)->format('Y/m/d') : Carbon\Carbon::now()->addDays(14);

                        @endphp
                        <label for="datepicker"><b>Tanggal Pemusnah Sampel</b></label>
                        <div class="input-group date">
                          <input type="text" class="form-control destroyed_sample_date"
                            value="{{ $destroyed_sample_date }}" name="destroyed_sample_date" id="destroyed_sample_date"
                            placeholder="Isikan Tanggal Pemusnah Sampel" data-date-format="dd/mm/yyyy" required>
                          <div class="input-group-append">
                            <span class="input-group-text">
                              <i class="fas fa-calendar-alt"></i>
                            </span>
                          </div>
                        </div>
                      </div>



                      <button type="submit" id="submitAll" class="btn btn-primary mr-2">Simpan</button>


                      <button type="button" class="btn btn-light" onclick="window.history.back()">Kembali</button>
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
    // var date = new Date("{{ $destroyed_sample_date }}");
    // date.setDate(date.getDate() + 45);
    // $('.destroyed_sample_date').datepicker('update', date);


    var date_checking = "{{ $date_checking2 }}"


    var date_done_estimation_labs = "{{ $date_done_estimation_labs2 }}"
    var destroyed_sample_date = "{{ $destroyed_sample_date2 }}"

    console.log(new Date(date_checking))
    $('.date_checking').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.date_checking').datepicker('update', new Date(date_checking));

    $('.date_done_estimation_labs').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.date_done_estimation_labs').datepicker('update', new Date(date_done_estimation_labs));

    $('.destroyed_sample_date').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.destroyed_sample_date').datepicker('update', new Date(destroyed_sample_date));


    $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");

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
      var element = document.getElementById('wadah_samples_others');

      console.log($('input[type=radio][name=wadah]:checked').val())
      if ($('input[type=radio][name=wadah]:checked').val() == '0') {

        element.style.display = 'block';
      } else {
        element.style.display = 'none';
      }

      $('input[type=radio][name=wadah]').change(function() {

        if (this.value == '0') {
          element.style.display = 'block';
        } else {
          element.style.display = 'none';
        }

      });

    });
  </script>
@endsection
