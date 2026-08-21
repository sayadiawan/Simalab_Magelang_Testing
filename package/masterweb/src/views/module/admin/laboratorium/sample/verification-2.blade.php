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

        .my-custom-popup-class {
            padding-top: 2.5rem !important;
        }

        /* Modern Card Styling */
        .info-card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .info-card .card-header {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
            color: white;
            border: none;
            padding: 15px 20px;
        }

        .info-card .card-body {
            padding: 20px;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .info-row:hover {
            background-color: #f8f9fa;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .info-icon i {
            font-size: 18px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            color: #212529;
            font-weight: 600;
        }

        .location-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .location-card h5 {
            color: white;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .location-content {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 15px;
            backdrop-filter: blur(10px);
        }

        .notes-card {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(252, 182, 159, 0.3);
        }

        .notes-card h5 {
            color: #8b4513;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .notes-content {
            background: white;
            border-radius: 8px;
            padding: 15px;
            color: #6c757d;
            font-style: italic;
        }

        .badge-lab {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .badge-lab i {
            font-size: 16px;
        }
    </style>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert2.min.js') }}"></script>





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

                    @if ($sample->kode_laboratorium == 'KIM')
                        @if ($sample->name_sample_type == 'Makanan/Minuman/Lainnya')
                            <button type="button" class="btn btn-outline-success btn-rounded float-right"
                                data-toggle="modal" data-target="#tambahAgendaKimiaModal">
                                <i class="fa fa-print"></i> Cetak Hasil
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-success btn-rounded float-right"
                                data-toggle="modal" data-target="#tambahAgendaLHUModal">
                                <i class="fa fa-print"></i> Cetak Hasil
                            </button>
                        @endif
                    @elseif($sample->kode_laboratorium == 'MBI')
                        <button type="button" class="btn btn-outline-success btn-rounded float-right" data-toggle="modal"
                            data-target="#tambahAgendaMikroModal">
                            <i class="fa fa-print"></i> Cetak Hasil
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-success btn-rounded float-right" data-toggle="modal"
                            data-target="#tambahAgendaMikroModal">
                            <i class="fa fa-print"></i> Cetak Hasil
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-success btn-rounded float-right mr-2" data-toggle="modal"
                        data-target="#editTanggalCetakVerifikasi">
                        <i class="fa fa-print"></i> Cetak Verifikasi
                    </button>

                    @if (isset($listVerifications[7]) && $listVerifications[7]->is_done == 1)
                        <button type="button" class="btn btn-outline-primary btn-rounded float-right mr-2"
                            data-toggle="modal" data-target="#cetakFormulirPengamanan">
                            <i class="fa fa-print"></i> Cetak Formulir Pengamanan Sampel
                        </button>
                    @endif

                </div>
            </div>
        </div>

        @if (session('error-bsre'))
            <div class="col-12 mt-2">
                <div class="alert alert-danger">
                    {{ session('error-bsre') }}
                </div>
            </div>
        @endif
        @if (session('error-laporan') or session('error-verifikasi'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: "Gagal Melakukan Tanda Tangan Elektronik",
                        text: "Terjadi kesalahan saat melakukan tanda tangan elektronik. Silakan coba lagi.",
                        icon: "warning",
                        customClass: {
                            popup: 'my-custom-popup-class'
                        }
                    });
                });
            </script>
        @endif

        <div class="card-body">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">

                        @if (session('status'))
                            <div class="col-12">
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="col-12">
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif
                        <!-- utama -->

                        <div class="col-md-12">
                            <!-- Badge Laboratorium -->
                            <div class="mb-3">
                                @if ($sample->kode_laboratorium == 'KIM')
                                    <span class="badge badge-lab"
                                        style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%); color: white;">
                                        <i class="fa fa-flask"></i> Laboratorium {{ $sample->nama_laboratorium }}
                                    </span>
                                @elseif($sample->kode_laboratorium == 'MBI')
                                    <span class="badge badge-lab"
                                        style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                                        <i class="fa fa-microscope"></i> Laboratorium {{ $sample->nama_laboratorium }}
                                    </span>
                                @else
                                    <span class="badge badge-lab"
                                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                                        <i class="fa fa-flask"></i> Laboratorium {{ $sample->nama_laboratorium }}
                                    </span>
                                @endif
                            </div>

                            <!-- Info Card -->
                            <div class="card info-card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fa fa-info-circle mr-2"></i>Informasi Sampel</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Column 1 -->
                                        <div class="col-md-6">
                                            <!-- Nama Pelanggan -->
                                            <div class="info-row">
                                                <div class="info-icon" style="background: #e3f2fd;">
                                                    <i class="fa fa-user" style="color: #1976d2;"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Nama Pelanggan</div>
                                                    <div class="info-value">
                                                        @php
                                                            $customer = str_replace(
                                                                'π',
                                                                '<span style="font-family: \'DejaVu Sans\', sans-serif;">π</span>',
                                                                $sample->name_pelanggan ??
                                                                    optional(optional($permohonanUji)->customer)->name_customer ??
                                                                    optional(optional($sample->permohonanuji)->customer)->name_customer ??
                                                                    '-',
                                                            );
                                                        @endphp
                                                        {!! $customer !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Nomor Sampel -->
                                            <div class="info-row">
                                                <div class="info-icon" style="background: #f3e5f5;">
                                                    <i class="fa fa-barcode" style="color: #7b1fa2;"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Nomor Sampel</div>
                                                    <div class="info-value">{!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}</div>
                                                </div>
                                            </div>

                                            <!-- Jenis Sampel -->
                                            <div class="info-row">
                                                <div class="info-icon" style="background: #fff3e0;">
                                                    <i class="fa fa-vial" style="color: #e65100;"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Jenis Sampel</div>
                                                    <div class="info-value">
                                                        {{ $sample->jenisSampelDisplay() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Column 2 -->
                                        <div class="col-md-6">
                                            <!-- Tanggal Pengambilan -->
                                            <div class="info-row">
                                                <div class="info-icon" style="background: #e8f5e9;">
                                                    <i class="fa fa-calendar-check" style="color: #2e7d32;"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Tanggal Pengambilan</div>
                                                    <div class="info-value">
                                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y HH:mm') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tanggal Pengiriman -->
                                            <div class="info-row">
                                                <div class="info-icon" style="background: #fce4ec;">
                                                    <i class="fa fa-shipping-fast" style="color: #c2185b;"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Tanggal Pengiriman</div>
                                                    <div class="info-value">
                                                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y HH:mm') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Titik Pengambilan -->
                                            <div class="info-row">
                                                <div class="info-icon" style="background: #fff9c4;">
                                                    <i class="fa fa-map-marker-alt" style="color: #f57f17;"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Titik Pengambilan</div>
                                                    <div class="info-value">
                                                        @php
                                                            if (
                                                                isset($sample->location_samples) &&
                                                                $sample->location_samples != ''
                                                            ) {
                                                                $titik_lokasi = strip_tags($sample->location_samples);
                                                            } else {
                                                                if ($sample->is_pudam) {
                                                                    if ($sample->kode_laboratorium === 'MBI') {
                                                                        $titik_lokasi =
                                                                            $sample->address_location_pdam ?? '-';
                                                                    } else {
                                                                        $titik_lokasi =
                                                                            $sample->name_customer_pdam ?? '-';
                                                                    }
                                                                } else {
                                                                    $titik_lokasi = $sample->titik_pengambilan ?? '-';
                                                                }
                                                            }
                                                        @endphp
                                                        {!! Str::limit($titik_lokasi, 100) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Note sample dengan kondisi --}}
                            @if ($sample->note_samples !== null)
                                <div class="notes-card">
                                    <h5><i class="fa fa-sticky-note mr-2"></i>Catatan Sampel</h5>
                                    <div class="notes-content">
                                        <i class="fa fa-quote-left mr-2"></i>
                                        {{ $sample->note_samples }}
                                        <i class="fa fa-quote-right ml-2"></i>
                                    </div>
                                </div>
                            @endif

                            @if ($sample->is_pudam == 1)
                                <div class="location-card">
                                    <h5>
                                        <i class="fa fa-map-marker-alt mr-2"></i>
                                        @if ($sample->kode_laboratorium === 'MBI')
                                            Lokasi Sampel
                                        @else
                                            Asal Contoh Air / Lokasi Sampel
                                        @endif
                                    </h5>
                                    <div class="location-content">
                                        @if (isset($sample->location_samples) && $sample->location_samples != '')
                                            @if ($sample->kode_laboratorium === 'MBI')
                                                {!! $sample->location_samples !!}
                                            @else
                                                {!! $sample->location_samples !!}
                                            @endif
                                        @else
                                            @php
                                                if ($sample->is_pudam) {
                                                    if ($sample->kode_laboratorium === 'MBI') {
                                                        $location =
                                                            $sample->address_location_pdam ??
                                                            old('address_location_pdam');
                                                    } else {
                                                        $location =
                                                            $sample->name_customer_pdam ?? old('name_customer_pdam');
                                                    }
                                                } else {
                                                    $location = $sample->titik_pengambilan ?? old('titik_pengambilan');
                                                }
                                            @endphp
                                            @if ($sample->kode_laboratorium === 'MBI')
                                                <strong style="font-size: 16px;">{!! $location !!}</strong>
                                            @else
                                                <strong style="font-size: 16px;">{!! $location !!}</strong>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif



                            <!-- Parameter Card -->
                            <div class="card"
                                style="border-radius: 12px; border-left: 4px solid #28a745; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none;">
                                    <h5 class="mb-0"><i class="fa fa-flask mr-2"></i>Parameter
                                        {{ $sample->nama_laboratorium }}</h5>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="row">
                                        @foreach ($laboratoriummethods as $index => $laboratoriummethod)
                                            <div class="col-md-3 mb-3">
                                                <div
                                                    style="padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #28a745;">
                                                    <i class="fa fa-check-circle text-success mr-2"></i>
                                                    <span
                                                        style="font-weight: 500;">{{ $laboratoriummethod->params_method }}</span>
                                                </div>
                                            </div>

                                            @if (($index + 1) % 4 == 0)
                                    </div>
                                    <div class="row">
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <br>

                            @if ($sample->kode_laboratorium == 'KIM')
                                @include('masterweb::module.admin.laboratorium.sample.table-verification-kimia')
                            @elseif($sample->kode_laboratorium === 'MBI')
                                @include('masterweb::module.admin.laboratorium.sample.table-verification-mikro')
                            @endif
                        </div>
                    </div>

                    <!-- utama -->
                </div>
                <!-- /.row -->
            </div>
        </div>

    </div>

    <!-- Modal Edit Nama Pengambil-->
    <div class="modal fade" id="editNamaPengambilModal" tabindex="-1" aria-labelledby="editNamaPengambilLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNamaPengambilLabel">Edit Nama Pengambil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('elits-samples.update-nama-pengambil', $sample->sample_id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name_send_sample">Nama Pengambil</label>
                            <input type="text" class="form-control" id="name_send_sample" name="name_send_sample"
                                value="{{ old('name_send_sample', $sample->name_send_sample ?? optional($permohonanUji)->name_sampling ?? '') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button> --}}
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Agenda Kimia -->
    <div class="modal fade" id="tambahAgendaKimiaModal" tabindex="-1" role="dialog"
        aria-labelledby="tambahAgendaKimiaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAgendaModalLabel">Tambah Agenda Sebelum Cetak Hasil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="agendaForm" action="{{ route('elits-release.print-kimia', [$sample->permohonan_uji_id]).'/'.$sample->typesample_samples }}"
                    method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="agenda">Agenda</label>
                            <input type="text" class="form-control" id="agenda" name="agenda"
                                placeholder="Masukkan agenda">
                        </div>
                        <div class="form-group">
                            <label for="signOption">Metode Tanda Tangan</label>
                            <select class="form-control" name="signOption" id="signOption">
                                <option value="0">Tanda Tangan Manual</option>
                                <option value="1">Tanda Tangan Elektronik</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cetak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Agenda LHU -->
    <div class="modal fade" id="tambahAgendaLHUModal" tabindex="-1" role="dialog"
        aria-labelledby="tambahAgendaLHUModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAgendaModalLabel">Tambah Agenda Sebelum Cetak Hasil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="agendaForm"
                    action="{{ route('elits-release.printLHU', [$sample->id_samples, $sample->id_laboratorium]) }}"
                    method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="agenda">Agenda</label>
                            <input type="text" class="form-control" id="agenda" name="agenda"
                                placeholder="Masukkan agenda">
                        </div>
                        <div class="form-group">
                            <label for="signOption">Metode Tanda Tangan</label>
                            <select class="form-control" name="signOption" id="signOption">
                                <option value="0">Tanda Tangan Manual</option>
                                <option value="1">Tanda Tangan Elektronik</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cetak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Agenda Mikro -->
    <div class="modal fade" id="tambahAgendaMikroModal" tabindex="-1" role="dialog"
        aria-labelledby="tambahAgendaModalMikroLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAgendaModalLabel">Tambah Agenda Sebelum Cetak Hasil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="clearListSamples()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="agendaForm"
                    action="{{ route('elits-release.print-mikro', [$sample->permohonan_uji_id, $sample->typesample_samples, $sample->packet_id]) }}"
                    method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="agenda">Agenda</label>
                            <input type="text" class="form-control" id="agenda" name="agenda"
                                placeholder="Masukkan agenda">
                        </div>
                        <div class="form-group">
                            <label for="signOption">Metode Tanda Tangan</label>
                            <select class="form-control" name="signOption" id="signOption">
                                <option value="0">Tanda Tangan Manual</option>
                                <option value="1">Tanda Tangan Elektronik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="d-flex justify-content-between">
                                <label for="samples">List Sampel</label>
                                <div class="d-flex align-items-center">
                                    <label for="printAll"
                                        style="font-size: 11px; margin-right: 5px; margin-top: 8px;">Cetak Semua
                                        Hasil</label>
                                    <input type="checkbox" name="printall" id="printAll">
                                </div>
                            </div>
                            <ol id="listSamples" class="mt-2">

                            </ol>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cetak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit tanggal cetak verifikasi -->
    <div class="modal fade" id="editTanggalCetakVerifikasi" tabindex="-1" role="dialog"
        aria-labelledby="editTanggalCetakVerifikasiLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTanggalCetakVerifikasiLabel">Tanggal Cetak Verifikasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="agendaForm"
                    action="{{ route('elits-release.print_verifikasi', [$sample->id_samples, $sample->id_laboratorium]) }}"
                    method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tanggal-cetak-verifikasi">Tanggal</label>
                            <input type="date" class="form-control datetime" id="tanggal-cetak-verifikasi"
                                name="tanggal_cetak_verifikasi">
                        </div>
                        <div class="form-group">
                            <label for="signOption">Metode Tanda Tangan</label>
                            <select class="form-control" name="signOption" id="signOption">
                                <option value="0">Tanda Tangan Manual</option>
                                <option value="1">Tanda Tangan Elektronik</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cetak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Cetak Formulir Pengamanan Sampel -->
    <div class="modal fade" id="cetakFormulirPengamanan" tabindex="-1" role="dialog"
        aria-labelledby="cetakFormulirPengamananLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cetakFormulirPengamananLabel">Cetak Formulir Pengamanan Sampel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form
                    action="{{ route('elits-samples.print-formulir-pengamanan', [$sample->permohonan_uji_id, $sample->id_laboratorium]) }}"
                    method="GET" target="_blank">
                    <div class="modal-body">
                        <p class="text-muted">Klik tombol cetak untuk mencetak formulir pengamanan sampel</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-print"></i> Cetak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Input NIK dan Password -->
    <div class="modal fade" id="inputNikAndPasword" tabindex="-1" aria-labelledby="inputNikAndPassword"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inputNikAndPassword">Input NIK dan Password BSRE</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nikPetugas">NIK</label>
                            <input type="text" class="form-control" name="nik" id="nikPetugas"
                                placeholder="Nomor Induk Kependudukan" required>
                        </div>
                        <div class="form-group mt-2">
                            <label for="passwordPetugas">Password</label>
                            <input type="text" class="form-control" name="password" id="passwordPetugas"
                                placeholder="Password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="submitNikAndPassword()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).scrollTop($(document).height());
    </script>
    <script>
        $(document).ready(function() {
            flatpickr('#tanggal-cetak-verifikasi', {
                enableTime: false,
                dateFormat: "d/m/Y"
            });

            const printVerifikasiDate = flatpickr('#tanggal-cetak-verifikasi', {
                allowInput: true,
                locale: "id",
                enableTime: false,
                dateFormat: "d/m/Y",
            });

            var printVerifikasiUpdateDate = $('#tanggal-cetak-verifikasi').val();

            printVerifikasiDate.setDate(formatDate(new Date(printVerifikasiUpdateDate)), true);

            $('#tanggal-cetak-verifikasi').inputmask("date", {
                placeholder: "dd/mm/yyyy",

            });

            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                return `${day}/${month}/${year}`;
            }
        });
    </script>
    <script>
        let namaPetugasValue = null;
        let formClassNameValue = null;
        const BSRE_USE = {{ config('app.bsre_use', false) ? 'true' : 'false' }};

        // Function to convert datetime-local format (YYYY-MM-DDTHH:mm) to d/m/Y H:i
        function convertDateTimeFormat(dateTimeValue) {
            if (!dateTimeValue) return '';

            // If already in d/m/Y H:i format, return as is
            if (dateTimeValue.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
                return dateTimeValue;
            }

            // Convert from YYYY-MM-DDTHH:mm to d/m/Y H:i
            if (dateTimeValue.includes('T')) {
                const [datePart, timePart] = dateTimeValue.split('T');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            // If format is YYYY-MM-DD HH:mm:ss, convert it
            if (dateTimeValue.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/)) {
                const [datePart, timePart] = dateTimeValue.split(' ');
                const [year, month, day] = datePart.split('-');
                const [hours, minutes] = timePart.split(':');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }

            return dateTimeValue;
        }

        // Function to convert date inputs in form before submission
        function convertFormDates(form) {
            if (!form) return;

            const dateInputs = form.querySelectorAll('input[type="datetime-local"][name="start_date"], input[type="datetime-local"][name="stop_date"]');
            dateInputs.forEach(input => {
                if (input.value) {
                    let convertedValue = '';

                    // Get the flatpickr instance if available
                    const flatpickrInstance = input._flatpickr;
                    if (flatpickrInstance && flatpickrInstance.selectedDates && flatpickrInstance.selectedDates.length > 0) {
                        // Get formatted date from flatpickr using the configured format
                        try {
                            convertedValue = flatpickrInstance.formatDate(flatpickrInstance.selectedDates[0], 'd/m/Y H:i');
                        } catch (e) {
                            // If flatpickr formatDate fails, convert manually
                            convertedValue = convertDateTimeFormat(input.value);
                        }
                    } else {
                        // Fallback: convert manually from datetime-local format
                        convertedValue = convertDateTimeFormat(input.value);
                    }

                    // Create hidden input with converted format and replace the original input name
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    hiddenInput.value = convertedValue;
                    form.appendChild(hiddenInput);

                    // Disable the original input so it won't be submitted
                    input.disabled = true;
                }
            });
        }

        function checkNikAndPassword(namaPetugas, className) {
            namaPetugasValue = namaPetugas;
            formClassNameValue = className;
            event.preventDefault();

            const form = document.querySelector(`.${className}`);
            if (!form) {
                console.error('Form not found:', className);
                return;
            }

            // Convert date formats before submission
            convertFormDates(form);

            if (BSRE_USE === true || BSRE_USE === 'true') {
                // Wajib input popup
                $('#inputNikAndPasword').modal('show');
            } else {
                // Tidak pakai BSRE, langsung submit
                form.submit();
            }
        }

        function submitNikAndPassword() {
            event.preventDefault();

            if (namaPetugasValue != null) {
                // Jangan simpan DB, kirim ke server via endpoint session sekali-pakai
                const formData = {
                    nik: document.getElementById("nikPetugas").value,
                    password: document.getElementById("passwordPetugas").value,
                    _token: '{{ csrf_token() }}'
                };
                $.ajax({
                    url: "{{ url('elits-samples/update-petugas') }}/" + encodeURIComponent(namaPetugasValue),
                    type: "PUT",
                    data: formData,
                    success: function(response) {
                        if (response === "true") {
                            $('#inputNikAndPasword').modal('hide');
                            // submit form yang diminta sebelumnya
                            if (formClassNameValue) {
                                const form = document.querySelector(`.${formClassNameValue}`);
                                if (form) {
                                    // Convert dates again before submitting
                                    convertFormDates(form);
                                    form.submit();
                                }
                            } else {
                                // Fallback: submit form aktif terakhir di halaman
                                const forms = document.querySelectorAll('form');
                                if (forms && forms.length) {
                                    const lastForm = forms[forms.length - 1];
                                    convertFormDates(lastForm);
                                    lastForm.submit();
                                }
                            }
                        } else {
                            swal({
                                title: "Failed!",
                                text: "Gagal mengirim kredensial BSRE.",
                                icon: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Failed!",
                            text: "Terjadi kesalahan jaringan.",
                            icon: "error"
                        });
                    }
                })
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#tambahAgendaMikroModal').on('show.bs.modal', function(event) {

                var link = window.location.href;

                console.error(link);



                var idSamplelab = link.split('/');

                var idSample = idSamplelab[idSamplelab.length - 2];
                var idLab = idSamplelab[idSamplelab.length - 1];
                var idlab = idLab.split('?')[0];

                getSamples(idSample, idlab, function(samples) {
                    if (samples) {
                        const listSamples = $('#listSamples')
                        samples.forEach(sample => {
                            var location = sample.location_samples !== null ? sample
                                .location_samples : sample.name_pelanggan;

                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = location;
                            location = tempDiv.textContent || tempDiv.innerText;

                            listSamples.append(`<li class="w-full d-flex justify-content-between" style="padding: 0px !important; height: 14px !important;">
                                <label style="font-size: 11px; margin-right: 5px;" >00${sample.count_id} - ${location}</label>
                                <input type="checkbox" name="printSamples[]" class="samplesCheckbox" value="${sample.id_samples}">
                            </li><hr>`)
                        });
                    }
                })

                $('#agendaForm').attr('action', link);
            })

            $('#printAll').on('change', function() {
                var isChecked = $(this).prop('checked');
                $('#listSamples .samplesCheckbox').prop('checked', isChecked);
            });
        })

        function getSamples(idSample, idlab, callback) {
            const url = `/elits-samples/list-samples-by-id-sample/${idSample}/${idlab}`;

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    callback(data);
                },
                error: function(xhr, status, error) {
                    callback(null);
                }
            });
        }

        function clearListSamples() {
            const listSamples = $('#listSamples');
            listSamples.empty();
        }
    </script>
@endsection
