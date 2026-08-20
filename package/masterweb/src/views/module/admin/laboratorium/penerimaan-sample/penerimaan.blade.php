@extends('masterweb::template.admin.layout')
@section('title')
  Penerimaan Sample
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
                <li class="breadcrumb-item"><a
                    href="{{ url('/elits-samples/verification', [Request::segment(3), Request::segment(4)]) }}">
                    Penerimaan
                    Sample</a></li>
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
      <H4>Penerimaan Sampel</H4>
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
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('d MMMM Y') }}
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
                  <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('d MMMM Y') }}
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
                      action="{{ route('elits-penerimaan-sample.store', [Request::segment(2), Request::segment(3)]) }}">
                      <div class="form-group">
                        <label for="wadah_samples"><b>1. Wadah</b></label>
                        @foreach ($containers as $container)
                          <div class="form-check">
                            <input class="form-check-input" name="wadah" type="radio"
                              value="{{ $container->id_container }}" id="wadah">
                            <label class="form-check-label" for="flexCheckChecked">
                              {{ $container->name_container }}
                            </label>
                          </div>
                        @endforeach
                        <div class="form-check">
                          <input class="form-check-input" name="wadah" type="radio" value="0" id="wadah">
                          <label class="form-check-label" for="flexCheckChecked">
                            Lainnya
                          </label>
                          <input type="text" class="form-control" id="wadah_samples_others" style='display:none;'
                            id="wadah_samples" name="wadah_samples" placeholder="Isikan Wadah">
                        </div>
                      </div>

                      <div class="form-group">
                        <label for="wadah_samples"><b>2. Pengawet</b></label>

                        <div class="form-check">
                          <input class="form-check-input" name="pengawet" type="radio" value="asam" id="asam">
                          <label class="form-check-label" for="flexCheckChecked">
                            Asam
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="pengawet" type="radio" value="naoh" id="naoh">
                          <label class="form-check-label" for="flexCheckChecked">
                            NaOH
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="pengawet" type="radio" value="toluen" id="toluen">
                          <label class="form-check-label" for="flexCheckChecked">
                            Toluen
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="pengawet" type="radio" value="pendinginan"
                            id="pendinginan">
                          <label class="form-check-label" for="flexCheckChecked">
                            Pendinginan
                          </label>
                        </div>
                      </div>



                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label for="volume"><b>3. Volume</b></label>
                            <input type="number" class="form-control" id="volume" step="any" name="volume"
                              value="" placeholder="Isikan Volume">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <br>
                          <select id="unitAttributes" name="unit" class="form-control js-example-basic-multiple">
                            <option value="" disabled selected> Pilih Satuan</option>
                            @foreach ($units as $unit)
                              <option value="{{ $unit->id_unit }}">{{ $unit->shortname_unit }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>

                      <div class="form-group">
                        <label for="wadah_samples"><b>4. Kondisi Sampel</b></label>

                        <div class="form-check">
                          <input class="form-check-input" name="kondisi_sample" type="radio" value="baik"
                            id="baik">
                          <label class="form-check-label" for="flexCheckChecked">
                            Baik
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="kondisi_sample" type="radio" value="rusak"
                            id="rusak">
                          <label class="form-check-label" for="flexCheckChecked">
                            Rusak
                          </label>
                        </div>

                      </div>

                      <div class="form-group">
                        <label for="wadah_samples"><b>5. Sampel</b></label>

                        <div class="form-check">
                          <input class="form-check-input" name="validation_sample" type="radio" value="diterima"
                            id="diterima">
                          <label class="form-check-label" for="flexCheckChecked">
                            Diterima
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="validation_sample" type="radio" value="ditolak"
                            id="ditolak">
                          <label class="form-check-label" for="flexCheckChecked">
                            Ditolak
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" name="validation_sample" type="radio" value="dirujuk"
                            id="dirujuk">
                          <label class="form-check-label" for="flexCheckChecked">
                            Dirujuk
                          </label>
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
    function CheckWadah(val) {
      var element = document.getElementById('wadah_samples_others');

      if (val == '0')
        element.style.display = 'block';
      else

        element.style.display = 'none';
    }

    $(document).ready(function() {

      $.fn.select2.defaults.set("theme", "classic");
      $('#unitAttributes').select2({
        placeholder: "Pilih Unit",
        allowClear: true
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
