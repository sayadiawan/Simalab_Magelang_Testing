@extends('masterweb::template.admin.layout')

@section('title')
  Daftar Pengujian
@endsection

@section('content')

  <script>
    var role = "admin"
  </script>

  <script src="{{asset('assets/admin/cdn-local/js/firebase.js')}}"></script>
  <script src="{{ asset('assets/admin/js/firebase-js/firebase/config.js') }}"></script>
  <script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js') }}"></script>


  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="">
          <div class="template-demo">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                    Beranda</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji') }}">Permohonan Uji</a></li>
                <li class="breadcrumb-item active" aria-current="page"><span>Daftar Pengujian</span></li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">


    <div class="card-header">
      <h4>Daftar Pengujian</h4>

    </div>


    <div class="card-body">

      <div class="col-md-12">

        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Nama Perusahaan</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">{{ $permohonan_uji->name_customer }}</label>
              </div>
            </div>
            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Tanggal</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                @php
                  $date_permohonan_uji = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $permohonan_uji->date_permohonan_uji)->format('d/m/Y');
                @endphp
                <label for="codesample_samples">{{ $date_permohonan_uji }}</label>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <label for="codesample_samples">Catatan</label>
              </div>
              <div class="col-md-0">
                <label for="codesample_samples">:</label>
              </div>
              <div class="col-md-8">
                <label for="codesample_samples">{{ $permohonan_uji->catatan }}</label>
              </div>
            </div>


          </li>
        </ul>
      </div>

      <div class="d-flex">
        <div class="mr-auto p-2">
          {{-- <div id="datepicker-popup" class="input-group date datepicker">
          <input type="text" class="form-control">
          <span class="input-group-addon input-group-append border-left">
            <span class="far fa-calendar input-group-text"></span>
          </span>
        </div> --}}
        </div>

        @if (getAction('create'))
          <div class="p-2">
            <a href="{{ route('elits-samples.create', [Request::segment(2)]) }}">

              <button type="button" class="btn btn-fw btn-info btn-icon-text">
                Tambah Data <i class="fa fa-plus btn-icon-append"></i>
              </button>
            </a>
          </div>
        @endif

        {{-- @if (isset($packet_prints))
      @if (count($packet_prints) > 0)
      <div class="p-2">
        <a
          href="{{ route('elits-release.print-mikro', [Request::segment(2),$packet_prints[0]->id_sample_type,$packet_prints[0]->packet_id]) }}">
          <button type="button" class="btn btn-fw btn-primary btn-icon-text">
            Mikro <i class="fa fa-print"></i>
          </button>
        </a>
      </div>
      @endif
      @endif --}}

        @if (isset($packet_prints))
          @if (count($packet_prints) > 0)
            <div class="p-2">
              @if (count($packet_prints) == 1)
                <a href="{{ route('elits-release.print-mikro', [Request::segment(2), $packet_prints[0]->id_sample_type]) }}"
                  target="__blank">
                  <button type="button" class="btn btn-fw btn-primary btn-icon-text">
                    Mikro <i class="fa fa-print"></i>
                  </button>
                </a>
              @else
                <div class="dropdown">
                  <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Mikro <i class="fa fa-print"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    @foreach ($packet_prints as $packet_print)
                      <a class="dropdown-item"
                        href="{{ route('elits-release.print-mikro', [Request::segment(2), $packet_print->id_sample_type]) }}?jenis_makanan_id={{ $packet_print->jenis_makanan_id }}"
                        target="__blank">{{ isset($packet_print->name_sample_type) ? $packet_print->name_sample_type : $packet_print->name_sample_type }}
                        {{ isset($packet_print->name_jenis_makanan) ? '| ' . $packet_print->name_jenis_makanan : '' }}</a>
                    @endforeach

                  </div>
                </div>
              @endif


            </div>
          @endif
        @endif

        @if (count($pudam) > 0)
          <div class="p-2">
            <a href="{{ url('/elits-release/print-kimia', [Request::segment(2)]) }}" target="__blank">
              <button type="button" class="btn btn-fw btn-primary btn-icon-text">
                Kimia <i class="fa fa-print"></i>
              </button>
            </a>
          </div>
        @endif

      </div>


      @if (session('status'))
        <div class="alert alert-success">
          {{ session('status') }}

        </div>
      @endif

      <div class="col-12">
        <div class="table-responsive">
          <table id="order-listing" class="table">
            <thead>
              <tr>
                <th>No</th>
                <th>Laboratorium</th>
                <th>Jenis Sarana</th>
                <th>Jenis Makanan</th>
                <th>Nomor Sampel</th>
                <th>Status Terakhir</th>
                @if (getAction('update') || getAction('delete'))
                  <th>Aksi</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @php
                $no = 1;
              @endphp
              @foreach ($samples as $sample)
                <tr>
                  <td>{{ $no }}</td>
                  <td>{{ $sample->nama_laboratorium }}</td>
                  <td>{{ $sample->name_sample_type }}</td>
                  <td>{{ isset($sample->name_jenis_makanan) ? $sample->name_jenis_makanan : '' }}</td>
                  <td>{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</td>
                  <td>

                    @php
                      $laboratoriumsampleanalitikprogress = \Smt\Masterweb\Models\SampleAnalitikProgress::where('tb_sample_analitik_progress.laboratorium_id', $sample->id_laboratorium)
                          ->where('tb_sample_analitik_progress.sample_id', $sample->id_samples)
                          ->orderBy('tb_laboratorium_progress.order_sort', 'desc')
                          ->join('tb_laboratorium_progress', function ($join) {
                              $join
                                  ->on('tb_laboratorium_progress.id_laboratorium_progress', '=', 'tb_sample_analitik_progress.laboratorium_progress_id')
                                  ->whereNull('tb_laboratorium_progress.deleted_at')
                                  ->whereNull('tb_sample_analitik_progress.deleted_at');
                          })
                          ->first();
                    @endphp

                    @if (isset($laboratoriumsampleanalitikprogress))
                      @if ($laboratoriumsampleanalitikprogress->link == 'baca-hasil')
                        @if (isset($sample->pengesahan_hasil_date))
                          <button type="button" class="btn btn-outline-success">
                            Selesai
                          </button>
                        @elseif(isset($sample->verifikasi_hasil_date))
                          <button type="button" class="btn btn-outline-success">
                            Verifikasi Hasil
                          </button>
                        @elseif(isset($sample->pelaporan_hasil_date))
                          <button type="button" class="btn btn-outline-success">
                            Pelaporan Hasil
                          </button>
                        @else
                          <button type="button" class="btn btn-outline-success">
                            Baca Hasil
                          </button>
                        @endif
                      @else
                        @if (isset($laboratoriumsampleanalitikprogress))
                          <button type="button" class="btn btn-outline-primary">
                            {{ $laboratoriumsampleanalitikprogress->name_progress }}
                          </button>
                        @else
                          @if (isset($sample->penanganan_sample_date))
                            <button type="button" class="btn btn-outline-warning">
                              Penanganan Sampel
                            </button>
                          @elseif(isset($sample->penerimaan_sample_date))
                            <button type="button" class="btn btn-outline-warning">
                              Penerimaan Sampel
                            </button>
                          @elseif(isset($sample->date_sending))
                            <button type="button" class="btn btn-outline-warning">
                              Pengambilan Sampel
                            </button>
                          @endif
                        @endif
                      @endif
                    @else
                      @if (isset($laboratoriumsampleanalitikprogress))
                        <button type="button" class="btn btn-outline-primary">
                          {{ $laboratoriumsampleanalitikprogress->name_progress }}
                        </button>
                      @else
                        @if (isset($sample->penanganan_sample_date))
                          <button type="button" class="btn btn-outline-warning">
                            Penanganan Sampel
                          </button>
                        @elseif(isset($sample->penerimaan_sample_date))
                          <button type="button" class="btn btn-outline-warning">
                            Penerimaan Sampel
                          </button>
                        @elseif(isset($sample->date_sending))
                          <button type="button" class="btn btn-outline-warning">
                            Pengambilan Sampel
                          </button>
                        @endif
                      @endif
                    @endif
                  </td>

                  @if (getAction('update') || getAction('delete'))
                    <td>
                      @if (getAction('update'))
                        <a href="{{ route('elits-samples.edit', [$sample->id_samples]) }}">
                          <button type="button" class="btn btn-outline-warning btn-rounded btn-icon">
                            <i class="fas fa-pencil-alt"></i>
                          </button>
                        </a>
                      @endif

                      @if (getSpesialAction(Request::segment(1), 'verification-sampel', ''))
                        <a
                          href="{{ route('elits-samples.verification', [$sample->id_samples, $sample->id_laboratorium]) }}">
                          <button type="button" class="btn btn-outline-success btn-rounded btn-icon">
                            <i class="fa fa-check"></i>
                          </button>
                        </a>
                      @endif

                      @if ($sample->id_laboratorium !== 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                        <a
                          href="{{ route('elits-release.printLHU', [$sample->id_samples, $sample->id_laboratorium]) }}">
                          <button type="button" class="btn btn-outline-primary btn-rounded btn-icon">
                            <i class="fa fa-print"></i>
                          </button>
                        </a>
                      @endif

                      @if (getAction('delete'))
                        <form onsubmit="return confirm('Menghapus Sampel {{ $sample->codesample_samples }} ini?')"
                          class="d-inline" action="{{ route('elits-samples.destroy', [$sample->id_samples]) }}"
                          method="POST">
                          @csrf

                          <input type="hidden" name="_method" value="DELETE">

                          <button type="submit" class="btn btn-outline-danger btn-rounded btn-icon">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      @endif


                    </td>
                  @endif
                </tr>
                @php
                  $no++;
                @endphp
              @endforeach


            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {

    });
  </script>
@endsection
