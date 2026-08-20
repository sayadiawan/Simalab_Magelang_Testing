@extends('masterweb::template.admin.layout')
@section('title')
  Persiapan Reagen
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
                    href="{{ url('/elits-samples/verification/', [Request::segment(3), Request::segment(4)]) }}">
                    Persiapan
                    Reagen</a></li>
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
      <H4>Persiapan Reagen</H4>
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

              {{-- Note sample dengan kondisi --}}
              @if ($sample->note_samples !== null)
                <div class="alert alert-warning" role="alert">
                  {{ $sample->note_samples }}
                </div>
              @endif

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
                      action="{{ route('elits-persiapan-reagen.store', [Request::segment(2), Request::segment(3), Request::segment(4)]) }}"
                      method="POST">
                      @csrf

                      <div class="form-group">
                        <label for="wadah_samples"><b>Persiapan Reagen dilakukan:</b></label>

                        <div class="input-group date">
                          <input type="text" class="form-control persiapan_reagen_date" name="persiapan_reagen_date"
                            id="inkubasi_date" placeholder="Isikan Tanggal Inkubasi" data-date-format="dd/mm/yyyy"
                            required>
                          <div class="input-group-append">
                            <span class="input-group-text">
                              <i class="fas fa-calendar-alt"></i>
                            </span>
                          </div>
                        </div>
                        <!-- <label for="wadah_samples"><b>Apakah persiapan reagen sudah siap?</b></label>

                                                      <div class="form-check">
                                                          <input class="form-check-input" name="persiapan_reagen" type="radio" value="ya" id="ya" >
                                                          <label class="form-check-label" for="flexCheckChecked">
                                                              Ya
                                                          </label>
                                                      </div>
                                                      <div class="form-check">
                                                          <input class="form-check-input" name="persiapan_reagen" type="radio" value="tidak" id="tidak" >
                                                          <label class="form-check-label" for="flexCheckChecked">
                                                              Tidak
                                                          </label>
                                                      </div> -->

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
    $('.persiapan_reagen_date').datepicker({
      format: 'dd/mm/yyyy'
    });
    $('.persiapan_reagen_date').datepicker('update', new Date());
  </script>
@endsection
