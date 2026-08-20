@extends('masterweb::template.admin.layout')
@section('title')
    Verifikasi Sample
@endsection

@section('content')



    <style>
        .ui-datepicker {
            position: relative;
            z-index: 100000;
        }
    </style>





    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/home') }}"><i class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a>
                                </li>

                                @if (getSpesialAction(Request::segment(1), 'is-analis', ''))
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/elits-analys') }}">
                                            Data Analisa</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/elits-permohonan-uji') }}">
                                            Permohonan Uji</a>
                                    </li>

                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/elits-samples', [$sample->permohonan_uji_id]) }}">
                                            Daftar Pengujian</a>
                                    </li>
                                @endif

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
            <div class="row">
                <div class="col-md-12">
                    <H4 class="d-inline-block  float-left margin-top">Verifikasi Sampel</H4>



                    @if ($sample->kode_laboratorium !== 'MBI')
                        <a href="{{ route('elits-release.printLHU', [$sample->id_samples, $sample->id_laboratorium]) }}">
                            <button type="button" class="btn btn-outline-success btn-rounded float-right">
                                <i class="fa fa-print"></i> Cetak Hasil
                            </button>
                        </a>
                    @endif

                    <a
                        href="{{ route('elits-release.print_verifikasi', [$sample->id_samples, $sample->id_laboratorium]) }}">
                        <button type="button" class="btn btn-outline-success btn-rounded float-right"
                            style="margin-right: 1rem">
                            <i class="fa fa-print"></i> Cetak Verifikasi
                        </button>
                    </a>

                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">

                        <!-- utama -->

                        <div class="col-md-12">
                            <h6 class="d-inline-block card-subtitle text-muted  margin-top"
                                style="margin-top:.2rem; background:blue;color:white !important;padding:5px;border-radius:5px;">
                                Laboratorium {{ $sample->nama_laboratorium }}</h6>

                            <table class="table table-bordered">
                                <tr>
                                    <th><b>Nama Pelanggan</b></th>
                                    <td>{{ $sample->namaPelangganDisplay() }}</td>
                                    <th><b>Tanggal Pengambilan</b></th>
                                    <td>
                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          HH:mm') }}
                                    </td>
                                    <th><b>Jenis Sampel</b></th>
                                    @php
                                        $jenis_makanan = $sample->jenis_makanan;
                                        if (isset($jenis_makanan)) {
                                            $jenis_makanan = $jenis_makanan->name_jenis_makanan;
                                        }
                                    @endphp
                                    <td>{{ $sample->name_sample_type }}{{ !isset($jenis_makanan) ? '' : ' - ' . $jenis_makanan }}
                                    </td>

                                </tr>
                                <tr>
                                    <th><b>Nomor Sampel</b></th>
                                    <td>{{ \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($sample->codesample_samples ?? '', (bool) data_get($sample, 'is_nomor_sampel_manual', false), 2) }}</td>
                                    <th><b>Tanggal Pengiriman</b></th>
                                    <td>
                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y HH:mm') }}
                                    </td>
                                    <th><b>Nama Pengambil</b></th>
                                    <td>{{ $sample->nama_pengambil }}</td>
                                </tr>
                                <tr>
                                    <th><b>Laboratorium</b></th>
                                    <td>{{ $sample->nama_laboratorium }}</td>
                                    {{-- <td>{{ isset($lab_num->lab_string)&&($lab_num->lab_string)!=""?$lab_num->lab_string:"" }}</td> --}}
                                    <th colspan="4"></th>

                                </tr>
                            </table>
                            <br>

                            {{-- Note sample dengan kondisi --}}
                            @if ($sample->note_samples !== null)
                                <div class="alert alert-warning" role="alert">
                                    {{ $sample->note_samples }}
                                </div>
                            @endif

                            @if (isset($sample->name_send_sample))
                                <br>
                                <h5>Nama Pengirim Sampel : {{ $sample->name_send_sample }}</h5>
                                <br>
                            @endif
                            @if (isset($sample->code_sample_customer))
                                <br>
                                <h5>Kode Sampel Customer : {{ $sample->code_sample_customer }}</h5>
                                <br>
                            @endif



                            <br>
                            <h5>Parameter {{ $sample->nama_laboratorium }} :</h5>
                            @foreach ($laboratoriummethods as $laboratoriummethod)
                                - {{ $laboratoriummethod->params_method }}<br>
                            @endforeach
                            <br>
                            <div class="col-md-12">
                                <div class="row">
                                    @if (getSpesialAction(Request::segment(1), 'verification-sampel-praanalatik', ''))
                                        <div class="col-md">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Pra Analitik</th>
                                                        <th>Tanggal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Permintaan Pemeriksaan</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              Y') }}
                                                        </td>

                                                    </tr>
                                                    <tr>
                                                        <td>Persiapan Sampel</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              Y') }}
                                                        </td>

                                                    </tr>
                                                    <tr>
                                                        <td>Pengambilan Sampel</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y') }}
                                                        </td>

                                                    </tr>
                                                    <tr>
                                                        <td>Penerimaan Sampel</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              Y') }}



                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Penanganan Sampel </td>
                                                        <td>
                                                            @if (isset($sample->pengesahan_hasil_date) && isset($sample->penanganan_sample_date))
                                                                <div class="input-group date">

                                                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->penanganan_sample_date)->isoFormat('D MMMM Y') }}
                                                                    <div class="input-group-append">
                                                                        <a
                                                                            href="{{ route('elits-penanganan-sample.edit', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <!-- <button type="button" class="btn btn-outline-warning btn-rounded btn-icon"> -->
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                            <!-- </button>  -->
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @elseif(isset($sample->verifikasi_hasil_date) && isset($sample->penanganan_sample_date))
                                                                <div class="input-group date">

                                                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->penanganan_sample_date)->isoFormat('D MMMM Y') }}
                                                                    <div class="input-group-append">
                                                                        <a
                                                                            href="{{ route('elits-penanganan-sample.edit', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <!-- <button type="button" class="btn btn-outline-warning btn-rounded btn-icon"> -->
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                            <!-- </button>  -->
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @elseif(isset($sample->pelaporan_hasil_date) && isset($sample->penanganan_sample_date))
                                                                <div class="input-group date">

                                                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->penanganan_sample_date)->isoFormat('D MMMM Y') }}
                                                                    <div class="input-group-append">
                                                                        <a
                                                                            href="{{ route('elits-penanganan-sample.edit', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <!-- <button type="button" class="btn btn-outline-warning btn-rounded btn-icon"> -->
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                            <!-- </button>  -->
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @elseif(isset($sample->penanganan_sample_date) && isset($sample->penanganan_sample_date))
                                                                <div class="input-group date">

                                                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->penanganan_sample_date)->isoFormat('D MMMM Y') }}
                                                                    <div class="input-group-append">
                                                                        <a
                                                                            href="{{ route('elits-penanganan-sample.edit', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <!-- <button type="button" class="btn btn-outline-warning btn-rounded btn-icon"> -->
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                            <!-- </button>  -->
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @elseif(isset($sample->penerimaan_sample_date))
                                                                <a
                                                                    href="{{ route('elits-penanganan-sample.index', [Request::segment(3), Request::segment(4)]) }}">
                                                                    <button type="button"
                                                                        class="btn btn-outline-success btn-rounded ">
                                                                        <i class="fa fa-check"></i> Selesai
                                                                    </button>
                                                                </a>
                                                            @else
                                                                <button type="button" class="btn btn-secondary btn-rounded"
                                                                    disabled><i class="fa fa-check"></i> Selesai</button>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                </tbody>

                                            </table>
                                        </div>
                                    @endif

                                    @if (getSpesialAction(Request::segment(1), 'verification-sampel-analatik', ''))
                                        <div class="col-md">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Analitik</th>
                                                        <th>Tanggal</th>
                                                    </tr>
                                                    @php
                                                        $i = 0;
                                                    @endphp
                                                    @foreach ($laboratoriumprogress as $laboratoriumprogres)
                                                        <tr>
                                                            <td>{{ $laboratoriumprogres->name_progress }}</td>
                                                            <td>
                                                                @if (isset($sample->penanganan_sample_date))
                                                                    @if (isset($laboratoriumsampleanalitikprogress[$i - 1]->date_done))
                                                                        @if (isset($laboratoriumsampleanalitikprogress[$i]->date_done))
                                                                            @if ($laboratoriumprogres->link == 'baca-hasil')
                                                                                <div
                                                                                    class="input-group date enable_edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}">
                                                                                    {{ \Carbon\Carbon::createFromFormat(
                                                                                        'Y-m-d H:i:s',
                                                                                        $laboratoriumsampleanalitikprogress[$i]->date_done,
                                                                                        'Asia/Jakarta',
                                                                                    )->isoFormat('D MMMM Y') }}
                                                                                    @php
                                                                                        $routes =
                                                                                            'elits-' .
                                                                                            $laboratoriumprogres->link .
                                                                                            '.index';
                                                                                    @endphp
                                                                                    <div class="input-group-append">
                                                                                        <a
                                                                                            href="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}">
                                                                                            <i class="fas fa-pencil-alt"
                                                                                                style="color:#0056b3"
                                                                                                id="edit_{{ $laboratoriumprogres->id_laboratorium_progress }}"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div
                                                                                    class="input-group date enable_edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}">
                                                                                    {{ \Carbon\Carbon::createFromFormat(
                                                                                        'Y-m-d H:i:s',
                                                                                        $laboratoriumsampleanalitikprogress[$i]->date_done,
                                                                                        'Asia/Jakarta',
                                                                                    )->isoFormat('D MMMM Y') }}
                                                                                    @php
                                                                                        $date_done = \Carbon\Carbon::createFromFormat(
                                                                                            'Y-m-d H:i:s',
                                                                                            $laboratoriumsampleanalitikprogress[
                                                                                                $i
                                                                                            ]->date_done,
                                                                                            'Asia/Jakarta',
                                                                                        )->isoFormat('Y/M/D');
                                                                                    @endphp
                                                                                    <div class="input-group-append">
                                                                                        <i class="fas fa-pencil-alt"
                                                                                            style="color:#0056b3"
                                                                                            id="edit_{{ $laboratoriumprogres->id_laboratorium_progress }}"></i>

                                                                                    </div>
                                                                                </div>

                                                                                <script>
                                                                                    $(document).ready(function() {

                                                                                        $('#edit_{{ $laboratoriumprogres->id_laboratorium_progress }}').click(function() {
                                                                                            $('.enable_edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}').css('display',
                                                                                                'none')
                                                                                            $('.edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}').css('display', 'block')
                                                                                        });
                                                                                    })
                                                                                </script>
                                                                                <div class="input-group date edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}"
                                                                                    style="display:none;">
                                                                                    @php
                                                                                        $routes =
                                                                                            'elits-' .
                                                                                            $laboratoriumprogres->link .
                                                                                            '.store';
                                                                                    @endphp
                                                                                    <form
                                                                                        action="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}"
                                                                                        method="POST">
                                                                                        @csrf

                                                                                        <div class="input-group date">
                                                                                            <input type="text"
                                                                                                class="form-control date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }} datepicker"
                                                                                                name="{{ $laboratoriumprogres->form }}"
                                                                                                id="{{ $laboratoriumprogres->form }}"
                                                                                                placeholder="Isikan Tanggal Pemeriksaan"
                                                                                                data-date-format="dd/mm/yyyy"
                                                                                                required>
                                                                                            <div
                                                                                                class="input-group-append">
                                                                                                <button type="submit"
                                                                                                    class="btn btn-outline-success btn-rounded ">
                                                                                                    <i
                                                                                                        class="fa fa-check"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>

                                                                                    </form>
                                                                                </div>

                                                                                <script>
                                                                                    $(document).ready(function() {

                                                                                        $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker({
                                                                                            format: 'dd/mm/yyyy'
                                                                                        });
                                                                                        $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker('update', new Date(
                                                                                            "{{ $date_done }}"));
                                                                                    })
                                                                                </script>
                                                                            @endif
                                                                        @else
                                                                            @if ($laboratoriumprogres->link == 'baca-hasil')
                                                                                @php
                                                                                    $routes =
                                                                                        'elits-' .
                                                                                        $laboratoriumprogres->link .
                                                                                        '.index';
                                                                                @endphp
                                                                                <a
                                                                                    href="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}">
                                                                                    <button type="button"
                                                                                        class="btn btn-outline-success btn-rounded ">
                                                                                        <i class="fa fa-check"></i>
                                                                                        Selesai
                                                                                    </button>
                                                                                </a>
                                                                            @else
                                                                                @php
                                                                                    $routes =
                                                                                        'elits-' .
                                                                                        $laboratoriumprogres->link .
                                                                                        '.store';
                                                                                @endphp
                                                                                <form
                                                                                    action="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}"
                                                                                    method="POST">
                                                                                    @csrf

                                                                                    <div class="input-group date">
                                                                                        <input type="text"
                                                                                            class="form-control date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }} datepicker"
                                                                                            name="{{ $laboratoriumprogres->form }}"
                                                                                            id="{{ $laboratoriumprogres->form }}"
                                                                                            placeholder="Isikan Tanggal Pemeriksaan"
                                                                                            data-date-format="dd/mm/yyyy"
                                                                                            required>
                                                                                        <div class="input-group-append">
                                                                                            <button type="submit"
                                                                                                class="btn btn-outline-success btn-rounded ">
                                                                                                <i class="fa fa-check"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>

                                                                                </form>
                                                                                <script>
                                                                                    $(document).ready(function() {

                                                                                        $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker({
                                                                                            format: 'dd/mm/yyyy'
                                                                                        });
                                                                                        $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker('update',
                                                                                            new Date());
                                                                                    })
                                                                                </script>
                                                                            @endif
                                                                        @endif
                                                                    @else
                                                                        @if ($i == 0)
                                                                            @if (isset($laboratoriumsampleanalitikprogress[$i]->date_done))
                                                                                @if ($laboratoriumprogres->link == 'baca-hasil')
                                                                                    <div
                                                                                        class="input-group date enable_edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}">
                                                                                        {{ \Carbon\Carbon::createFromFormat(
                                                                                            'Y-m-d H:i:s',
                                                                                            $laboratoriumsampleanalitikprogress[$i]->date_done,
                                                                                            'Asia/Jakarta',
                                                                                        )->isoFormat('D MMMM Y') }}
                                                                                        @php
                                                                                            $routes =
                                                                                                'elits-' .
                                                                                                $laboratoriumprogres->link .
                                                                                                '.index';
                                                                                        @endphp
                                                                                        <div class="input-group-append">
                                                                                            <a
                                                                                                href="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}">
                                                                                                <i class="fas fa-pencil-alt"
                                                                                                    style="color:#0056b3"
                                                                                                    id="edit_{{ $laboratoriumprogres->id_laboratorium_progress }}"></i>
                                                                                            </a>

                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    <div
                                                                                        class="input-group date enable_edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}">
                                                                                        {{ \Carbon\Carbon::createFromFormat(
                                                                                            'Y-m-d H:i:s',
                                                                                            $laboratoriumsampleanalitikprogress[$i]->date_done,
                                                                                            'Asia/Jakarta',
                                                                                        )->isoFormat('D MMMM Y') }}
                                                                                        @php
                                                                                            $date_done = \Carbon\Carbon::createFromFormat(
                                                                                                'Y-m-d H:i:s',
                                                                                                $laboratoriumsampleanalitikprogress[
                                                                                                    $i
                                                                                                ]->date_done,
                                                                                                'Asia/Jakarta',
                                                                                            )->isoFormat('Y/M/D');
                                                                                        @endphp
                                                                                        <div class="input-group-append">
                                                                                            <i class="fas fa-pencil-alt"
                                                                                                style="color:#0056b3"
                                                                                                id="edit_{{ $laboratoriumprogres->id_laboratorium_progress }}"></i>

                                                                                        </div>
                                                                                    </div>

                                                                                    <script>
                                                                                        $(document).ready(function() {

                                                                                            $('#edit_{{ $laboratoriumprogres->id_laboratorium_progress }}').click(function() {
                                                                                                $('.enable_edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}').css('display',
                                                                                                    'none')
                                                                                                $('.edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}').css('display', 'block')
                                                                                            });
                                                                                        })
                                                                                    </script>
                                                                                    <div class="input-group date edit_div_{{ $laboratoriumprogres->id_laboratorium_progress }}"
                                                                                        style="display:none;">
                                                                                        @php
                                                                                            $routes =
                                                                                                'elits-' .
                                                                                                $laboratoriumprogres->link .
                                                                                                '.store';
                                                                                        @endphp
                                                                                        <form
                                                                                            action="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}"
                                                                                            method="POST">
                                                                                            @csrf

                                                                                            <div class="input-group date">
                                                                                                <input type="text"
                                                                                                    class="form-control date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }} datepicker"
                                                                                                    name="{{ $laboratoriumprogres->form }}"
                                                                                                    id="{{ $laboratoriumprogres->form }}"
                                                                                                    placeholder="Isikan Tanggal Pemeriksaan"
                                                                                                    data-date-format="dd/mm/yyyy"
                                                                                                    required>
                                                                                                <div
                                                                                                    class="input-group-append">
                                                                                                    <button type="submit"
                                                                                                        class="btn btn-outline-success btn-rounded ">
                                                                                                        <i
                                                                                                            class="fa fa-check"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </form>
                                                                                    </div>

                                                                                    <script>
                                                                                        $(document).ready(function() {

                                                                                            $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker({
                                                                                                format: 'dd/mm/yyyy'
                                                                                            });
                                                                                            $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker('update', new Date(
                                                                                                "{{ $date_done }}"));
                                                                                        })
                                                                                    </script>
                                                                                @endif
                                                                            @else
                                                                                @if ($laboratoriumprogres->link == 'baca-hasil')
                                                                                    @php
                                                                                        $routes =
                                                                                            'elits-' .
                                                                                            $laboratoriumprogres->link .
                                                                                            '.index';
                                                                                    @endphp
                                                                                    <a
                                                                                        href="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}">
                                                                                        <button type="button"
                                                                                            class="btn btn-outline-success btn-rounded ">
                                                                                            <i class="fa fa-check"></i>
                                                                                            Selesai
                                                                                        </button>
                                                                                    </a>
                                                                                @else
                                                                                    @php
                                                                                        $routes =
                                                                                            'elits-' .
                                                                                            $laboratoriumprogres->link .
                                                                                            '.store';
                                                                                    @endphp
                                                                                    <form
                                                                                        action="{{ route($routes, [Request::segment(3), Request::segment(4), $laboratoriumprogres->id_laboratorium_progress]) }}"
                                                                                        method="POST">
                                                                                        @csrf

                                                                                        <div class="input-group date">
                                                                                            <input type="text"
                                                                                                class="form-control date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }} datepicker"
                                                                                                name="{{ $laboratoriumprogres->form }}"
                                                                                                id="{{ $laboratoriumprogres->form }}"
                                                                                                placeholder="Isikan Tanggal Pemeriksaan"
                                                                                                data-date-format="dd/mm/yyyy"
                                                                                                required>
                                                                                            <div
                                                                                                class="input-group-append">
                                                                                                <button type="submit"
                                                                                                    class="btn btn-outline-success btn-rounded ">
                                                                                                    <i
                                                                                                        class="fa fa-check"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </form>
                                                                                    <script>
                                                                                        $(document).ready(function() {

                                                                                            $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker({
                                                                                                format: 'dd/mm/yyyy'
                                                                                            });
                                                                                            $('.date_checking_{{ $laboratoriumprogres->id_laboratorium_progress }}').datepicker('update',
                                                                                                new Date());
                                                                                        })
                                                                                    </script>
                                                                                @endif
                                                                            @endif
                                                                        @else
                                                                            <button type="button"
                                                                                class="btn btn-secondary btn-rounded"
                                                                                disabled><i
                                                                                    class="fa fa-check"></i>Selesai</button>
                                                                        @endif
                                                                    @endif
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-secondary btn-rounded" disabled><i
                                                                            class="fa fa-check"></i> Selesai</button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $i++;

                                                        @endphp
                                                    @endforeach


                                                </thead>

                                            </table>
                                        </div>
                                    @endif

                                    @if (getSpesialAction(Request::segment(1), 'verification-sampel-paskaanalatik', ''))
                                        <div class="col-md">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Paska Analitik</th>
                                                        <th>Tanggal</th>
                                                    </tr>
                                                    <tr>
                                                        <td>Pelaporan Hasil</td>
                                                        <td>



                                                            @if (isset($laboratoriumsampleanalitikprogress[count($laboratoriumsampleanalitikprogress) - 1]->link) &&
                                                                    $laboratoriumsampleanalitikprogress[count($laboratoriumsampleanalitikprogress) - 1]->link == 'baca-hasil')

                                                                @if (isset($sample->pengesahan_hasil_date) && isset($sample->pelaporan_hasil_date))
                                                                    <div
                                                                        class="input-group date enable_edit_pelaporan_hasil_date">
                                                                        @php
                                                                            $date_done = \Carbon\Carbon::createFromFormat(
                                                                                'Y-m-d H:i:s',
                                                                                $sample->pelaporan_hasil_date,
                                                                                'Asia/Jakarta',
                                                                            )->isoFormat('Y/M/D');

                                                                        @endphp
                                                                        <div class="input-group-append">
                                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->pelaporan_hasil_date)->isoFormat('D MMMM Y') }}

                                                                            <i class="fas fa-pencil-alt"
                                                                                style="color:#0056b3"
                                                                                id="edit_pelaporan_hasil_date"></i>

                                                                        </div>
                                                                    </div>

                                                                    <script>
                                                                        $(document).ready(function() {

                                                                            $('#edit_pelaporan_hasil_date').click(function() {
                                                                                $('.enable_edit_pelaporan_hasil_date').css('display', 'none')
                                                                                $('.edit_div_pelaporan_hasil_date').css('display', 'block')
                                                                            });
                                                                        })
                                                                    </script>
                                                                    <div class="input-group date edit_div_pelaporan_hasil_date"
                                                                        style="display:none;">
                                                                        @php
                                                                            $routes = 'elits-pelaporan-hasil.store';
                                                                        @endphp
                                                                        <form
                                                                            action="{{ route($routes, [Request::segment(3), Request::segment(4)]) }}"
                                                                            method="POST">
                                                                            @csrf

                                                                            <div class="input-group date">
                                                                                <input type="text"
                                                                                    class="form-control pelaporan_hasil_date"
                                                                                    name="pelaporan_hasil_date"
                                                                                    id="pelaporan_hasil_date"
                                                                                    placeholder="Isikan Tanggal Pelaporan Hasil"
                                                                                    data-date-format="dd/mm/yyyy" required>
                                                                                <div class="input-group-append">
                                                                                    <button type="submit"
                                                                                        class="btn btn-outline-success btn-rounded ">
                                                                                        <i class="fa fa-check"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>

                                                                    <script>
                                                                        $(document).ready(function() {
                                                                            $('.pelaporan_hasil_date').datepicker({
                                                                                format: 'dd/mm/yyyy',
                                                                                orientation: 'bottom'
                                                                            });

                                                                            $('.pelaporan_hasil_date').datepicker('update', new Date("{{ $date_done }}"));
                                                                        })
                                                                    </script>
                                                                @elseif(isset($sample->verifikasi_hasil_date) && isset($sample->pelaporan_hasil_date))
                                                                    <div
                                                                        class="input-group date enable_edit_pelaporan_hasil_date">
                                                                        @php
                                                                            $date_done = \Carbon\Carbon::createFromFormat(
                                                                                'Y-m-d H:i:s',
                                                                                $sample->pelaporan_hasil_date,
                                                                                'Asia/Jakarta',
                                                                            )->isoFormat('Y/M/D');

                                                                        @endphp
                                                                        <div class="input-group-append">
                                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->pelaporan_hasil_date)->isoFormat('D MMMM Y') }}

                                                                            <i class="fas fa-pencil-alt"
                                                                                style="color:#0056b3"
                                                                                id="edit_pelaporan_hasil_date"></i>

                                                                        </div>
                                                                    </div>

                                                                    <script>
                                                                        $(document).ready(function() {

                                                                            $('#edit_pelaporan_hasil_date').click(function() {
                                                                                $('.enable_edit_pelaporan_hasil_date').css('display', 'none')
                                                                                $('.edit_div_pelaporan_hasil_date').css('display', 'block')
                                                                            });
                                                                        })
                                                                    </script>
                                                                    <div class="input-group date edit_div_pelaporan_hasil_date"
                                                                        style="display:none;">
                                                                        @php
                                                                            $routes = 'elits-pelaporan-hasil.store';
                                                                        @endphp
                                                                        <form
                                                                            action="{{ route($routes, [Request::segment(3), Request::segment(4)]) }}"
                                                                            method="POST">
                                                                            @csrf

                                                                            <div class="input-group date">
                                                                                <input type="text"
                                                                                    class="form-control pelaporan_hasil_date"
                                                                                    name="pelaporan_hasil_date"
                                                                                    id="pelaporan_hasil_date"
                                                                                    placeholder="Isikan Tanggal Pelaporan Hasil"
                                                                                    data-date-format="dd/mm/yyyy" required>
                                                                                <div class="input-group-append">
                                                                                    <button type="submit"
                                                                                        class="btn btn-outline-success btn-rounded ">
                                                                                        <i class="fa fa-check"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>

                                                                    <script>
                                                                        $(document).ready(function() {


                                                                            $('.pelaporan_hasil_date').datepicker({
                                                                                format: 'dd/mm/yyyy',
                                                                                orientation: 'bottom'
                                                                            });

                                                                            $('.pelaporan_hasil_date').datepicker('update', new Date("{{ $date_done }}"));

                                                                        })
                                                                    </script>
                                                                @elseif(isset($sample->pelaporan_hasil_date) && isset($sample->pelaporan_hasil_date))
                                                                    <div
                                                                        class="input-group date enable_edit_pelaporan_hasil_date">
                                                                        @php
                                                                            $date_done = \Carbon\Carbon::createFromFormat(
                                                                                'Y-m-d H:i:s',
                                                                                $sample->pelaporan_hasil_date,
                                                                            )->isoFormat('Y/M/D');
                                                                        @endphp
                                                                        <div class="input-group-append">
                                                                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->pelaporan_hasil_date)->isoFormat('D MMMM Y') }}

                                                                            <i class="fas fa-pencil-alt"
                                                                                style="color:#0056b3"
                                                                                id="edit_pelaporan_hasil_date"></i>

                                                                        </div>
                                                                    </div>

                                                                    <script>
                                                                        $(document).ready(function() {

                                                                            $('#edit_pelaporan_hasil_date').click(function() {
                                                                                $('.enable_edit_pelaporan_hasil_date').css('display', 'none')
                                                                                $('.edit_div_pelaporan_hasil_date').css('display', 'block')
                                                                            });
                                                                        })
                                                                    </script>
                                                                    <div class="input-group date edit_div_pelaporan_hasil_date"
                                                                        style="display:none;">
                                                                        @php
                                                                            $routes = 'elits-pelaporan-hasil.store';
                                                                        @endphp
                                                                        <form
                                                                            action="{{ route($routes, [Request::segment(3), Request::segment(4)]) }}"
                                                                            method="POST">
                                                                            @csrf

                                                                            <div class="input-group date">
                                                                                <input type="text"
                                                                                    class="form-control pelaporan_hasil_date"
                                                                                    name="pelaporan_hasil_date"
                                                                                    id="pelaporan_hasil_date"
                                                                                    placeholder="Isikan Tanggal Pelaporan Hasil"
                                                                                    data-date-format="dd/mm/yyyy" required>
                                                                                <div class="input-group-append">
                                                                                    <button type="submit"
                                                                                        class="btn btn-outline-success btn-rounded ">
                                                                                        <i class="fa fa-check"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>

                                                                    <script>
                                                                        $(document).ready(function() {

                                                                            $('.pelaporan_hasil_date').datepicker({
                                                                                format: 'dd/mm/yyyy',
                                                                                orientation: 'bottom'
                                                                            });

                                                                            $('.pelaporan_hasil_date').datepicker('update', new Date("{{ $date_done }}"));

                                                                        })
                                                                    </script>
                                                                @elseif(isset($sample->penanganan_sample_date))
                                                                    @php
                                                                        $routes = 'elits-pelaporan-hasil.store';
                                                                        $date_done =
                                                                            $laboratoriumsampleanalitikprogress[
                                                                                count(
                                                                                    $laboratoriumsampleanalitikprogress,
                                                                                ) - 1
                                                                            ]->date_done;
                                                                        // dd($laboratoriumsampleanalitikprogress);
                                                                    @endphp

                                                                    @if (isset($date_done))
                                                                        <form
                                                                            action="{{ route($routes, [Request::segment(3), Request::segment(4)]) }}"
                                                                            method="POST">
                                                                            @csrf

                                                                            <div class="input-group date">
                                                                                <input type="text"
                                                                                    class="form-control pelaporan_hasil_date datepicker"
                                                                                    name="pelaporan_hasil_date"
                                                                                    id="pelaporan_hasil_date"
                                                                                    placeholder="Isikan Tanggal Pelaporan Hasil"
                                                                                    data-date-format="dd/mm/yyyy" required>
                                                                                <div class="input-group-append">
                                                                                    <button type="submit"
                                                                                        class="btn btn-outline-success btn-rounded ">
                                                                                        <i class="fa fa-check"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                        <script>
                                                                            $(document).ready(function() {

                                                                                $('.pelaporan_hasil_date').datepicker({
                                                                                    format: 'dd/mm/yyyy',
                                                                                    orientation: 'bottom'
                                                                                });

                                                                                $('.pelaporan_hasil_date').datepicker('update', new Date("{{ $date_done }}"));

                                                                            })
                                                                        </script>
                                                                    @else
                                                                        <button type="button"
                                                                            class="btn btn-secondary btn-rounded"
                                                                            disabled><i class="fa fa-check"></i>
                                                                            Selesai</button>
                                                                    @endif
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-secondary btn-rounded" disabled><i
                                                                            class="fa fa-check"></i>Selesai</button>
                                                                @endif
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-secondary btn-rounded" disabled><i
                                                                        class="fa fa-check"></i> Selesai</button>
                                                            @endif
                                                        </td>

                                                    </tr>

                                                    <tr>
                                                        <td>Verifikasi Hasil</td>

                                                        <td>
                                                            @if (isset($sample->date_analitik_sample))
                                                                @if (isset($sample->pengesahan_hasil_date) && isset($sample->verifikasi_hasil_date))
                                                                    <div class="input-group-append">
                                                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->verifikasi_hasil_date)->isoFormat('D MMMM Y') }}
                                                                        <a
                                                                            href="{{ route('elits-verifikasi-hasil.index', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </a>
                                                                    </div>
                                                                @elseif(isset($sample->verifikasi_hasil_date) && isset($sample->verifikasi_hasil_date))
                                                                    <div class="input-group-append">
                                                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->verifikasi_hasil_date)->isoFormat('D MMMM Y') }}
                                                                        <a
                                                                            href="{{ route('elits-verifikasi-hasil.index', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </a>
                                                                    </div>
                                                                @elseif(isset($sample->pelaporan_hasil_date))
                                                                    <a
                                                                        href="{{ route('elits-verifikasi-hasil.index', [Request::segment(3), Request::segment(4)]) }}">
                                                                        <button type="button"
                                                                            class="btn btn-outline-success btn-rounded ">
                                                                            <i class="fa fa-check"></i> Selesai
                                                                        </button>
                                                                    </a>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-secondary btn-rounded" disabled><i
                                                                            class="fa fa-check"></i>Selesai</button>
                                                                @endif
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-secondary btn-rounded" disabled><i
                                                                        class="fa fa-check"></i> Selesai</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Pengesahan Hasil</td>
                                                        <td>
                                                            @if (isset($sample->date_analitik_sample))
                                                                @if (isset($sample->pengesahan_hasil_date) && isset($sample->pengesahan_hasil_date))
                                                                    <div class="input-group-append">
                                                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->pengesahan_hasil_date)->isoFormat('D MMMM Y') }}
                                                                        <a
                                                                            href="{{ route('elits-pengesahan-hasil.index', [Request::segment(3), Request::segment(4)]) }}">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </a>
                                                                    </div>
                                                                @elseif(isset($sample->verifikasi_hasil_date))
                                                                    <a
                                                                        href="{{ route('elits-pengesahan-hasil.index', [Request::segment(3), Request::segment(4)]) }}">
                                                                        <button type="button"
                                                                            class="btn btn-outline-success btn-rounded ">
                                                                            <i class="fa fa-check"></i> Selesai
                                                                        </button>
                                                                    </a>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-secondary btn-rounded" disabled><i
                                                                            class="fa fa-check"></i>Selesai</button>
                                                                @endif
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn-secondary btn-rounded" disabled><i
                                                                        class="fa fa-check"></i> Selesai</button>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                </thead>

                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- utama -->
                </div>
                <!-- /.row -->
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-12">
                    <style>
                        .next,
                        .previous {
                            text-decoration: none;
                            display: inline-block;
                            padding: 8px 16px;
                        }

                        .next,
                        .previous:hover {
                            background-color: #ddd;
                            color: black;
                        }

                        .previous {
                            background-color: #f1f1f1;
                            color: black;
                        }

                        .next {
                            background-color: #04AA6D;
                            color: white;
                        }

                        .round {
                            border-radius: 50%;
                        }
                    </style>
                    @if (isset($previewssample))
                        <a href="{{ route('elits-samples.verification', [$previewssample->id_samples, $previewssample->id_laboratorium]) }}"
                            class="previous">&laquo; Previous ({{ $previewssample->count_id }})
                            {{-- / --}}
                            {{-- {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $previewssample->date_sending)->format('Y') }})  --}}

                        </a>
                    @endif
                    @if (isset($nextsample))
                        <a href="{{ route('elits-samples.verification', [$nextsample->id_samples, $nextsample->id_laboratorium]) }}"
                            class="next">Next ({{ $nextsample->count_id }})
                            {{-- / --}}
                            {{-- {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $nextsample->date_sending)->format('Y') }}) --}}
                            &raquo;</a>
                    @endif


                </div>
            </div>
        </div>



    </div>



@endsection

@section('scripts')
    <script>
        $(document).scrollTop($(document).height());
    </script>
@endsection
