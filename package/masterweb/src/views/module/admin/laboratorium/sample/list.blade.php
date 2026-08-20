@extends('masterweb::template.admin.layout')

@section('title')
    Daftar Pengujian
@endsection

@section('content')
    <style>
        .dropdown-scroll-menu {
            /* background-color: lightblue !important; */
            height: 200px !important;
            width: auto !important;
            overflow-y: auto !important;
        }

        .pointer {
            cursor: pointer;
        }

        .my-custom-popup-class {
            padding-top: 2.5rem !important;
        }

        /* TinyMCE Editor Styling */
        .titik-item .card {
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .titik-item .card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }

        .titik-item .card-header {
            border-bottom: 2px solid #e9ecef;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .tox-tinymce {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem;
        }

        /* Custom scrollbar for modal body */
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <script>
        var role = "admin"
    </script>

    <script src="{{ asset('assets/admin/cdn-local/js/firebase.js') }}"></script>
    <script src="{{ asset('assets/admin/js/firebase-js/firebase/config.js') }}"></script>
    <script src="{{ asset('assets/admin/js/firebase-js/firebase/database.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/sweetalert2.min.js') }}"></script>


    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-permohonan-uji') }}">Permohonan Uji</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Daftar Pengujian</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

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
                                <label for="codesample_samples">{{ ($permohonan_uji && $permohonan_uji->customer) ? $permohonan_uji->customer->name_customer : '-' }}</label>
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
                                    $date_permohonan_uji = '-';
                                    if ($permohonan_uji && isset($permohonan_uji->date_permohonan_uji)) {
                                        try {
                                            $date_permohonan_uji = \Carbon\Carbon::createFromFormat(
                                                'Y-m-d H:i:s',
                                                $permohonan_uji->date_permohonan_uji,
                                            )->format('d/m/Y');
                                        } catch (\Exception $e) {
                                            $date_permohonan_uji = '-';
                                        }
                                    }
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
                                <label for="codesample_samples">{{ $permohonan_uji && isset($permohonan_uji->catatan) ? $permohonan_uji->catatan : '-' }}</label>
                            </div>
                        </div>


                    </li>
                </ul>
            </div>

            <div class="d-flex">
                <div class="mr-auto p-2">
                </div>

                @if (getAction('create') || getAction('update') || getAction('read'))
                    <div class="p-2">
                        <a href="{{ route('elits-permohonan-uji.nomer-lab', [Request::segment(2)]) }}">
                            <button type="button" class="btn btn-fw btn-success btn-icon-text">
                                <i class="fa fa-hashtag btn-icon-prepend"></i> Input Nomor Lab
                            </button>
                        </a>
                    </div>
                @endif

                @if (getAction('create'))
                    <div class="p-2">
                        <a href="{{ route('elits-samples.create', [Request::segment(2)]) }}">
                            <button type="button" class="btn btn-fw btn-info btn-icon-text">
                                Tambah Data <i class="fa fa-plus btn-icon-append"></i>
                            </button>
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="{{ route('elits-sample-draft.index', [Request::segment(2)]) }}">
                            <button type="button" class="btn btn-fw btn-warning btn-icon-text">
                                <i class="fa fa-file-text btn-icon-prepend"></i> Lihat Draft
                            </button>
                        </a>
                    </div>
                @endif

                @if (isset($packet_prints))
                    @if (count($packet_prints) > 0)
                        <div class="p-2">
                            @if (count($packet_prints) == 1)
                                @php
                                    $packet_print_sample_type = \Smt\Masterweb\Models\Sample::where(
                                        'permohonan_uji_id',
                                        '=',
                                        Request::segment(2),
                                    )
                                        ->where('id_sample_type', $packet_prints[0]['id_sample_type'])
                                        ->where('jenis_makanan_id', $packet_prints[0]['jenis_makanan_id'])
                                        ->join('tb_sample_method', function ($join) {
                                            $join
                                                ->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                                                ->whereNull('tb_sample_method.deleted_at')
                                                ->whereNull('tb_samples.deleted_at')
                                                ->join('ms_laboratorium', function ($join) {
                                                    $join
                                                        ->on(
                                                            'ms_laboratorium.id_laboratorium',
                                                            '=',
                                                            'tb_sample_method.laboratorium_id',
                                                        )
                                                        ->whereNull('ms_laboratorium.deleted_at')
                                                        ->whereNull('tb_sample_method.deleted_at');
                                                });
                                        })
                                        ->leftjoin('tb_pengesahan_hasil', function ($join) {
                                            $join
                                                ->on(
                                                    'tb_pengesahan_hasil.id_pengesahan_hasil',
                                                    '=',
                                                    DB::raw('(SELECT id_pengesahan_hasil FROM
                                    tb_pengesahan_hasil WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples AND
                                    tb_pengesahan_hasil.deleted_at is NULL AND tb_samples.deleted_at is NULL LIMIT 1)'),
                                                )

                                                ->whereNull('tb_pengesahan_hasil.deleted_at')
                                                ->whereNull('tb_samples.deleted_at');
                                        })
                                        ->leftjoin('ms_jenis_makanan', function ($join) {
                                            $join
                                                ->on(
                                                    'ms_jenis_makanan.id_jenis_makanan',
                                                    '=',
                                                    'tb_samples.jenis_makanan_id',
                                                )
                                                ->whereNull('tb_samples.deleted_at')
                                                ->whereNull('ms_jenis_makanan.deleted_at');
                                        })
                                        ->leftjoin('ms_sample_type', function ($join) {
                                            $join
                                                ->on(
                                                    'ms_sample_type.id_sample_type',
                                                    '=',
                                                    'tb_samples.typesample_samples',
                                                )

                                                ->whereNull('ms_sample_type.deleted_at')
                                                ->whereNull('tb_samples.deleted_at');
                                        })
                                        ->select(
                                            'id_laboratorium',
                                            'ms_jenis_makanan.*',
                                            'tb_pengesahan_hasil.*',
                                            'typesample_samples',
                                            'ms_sample_type.id_sample_type',
                                            'ms_sample_type.name_sample_type',
                                            'tb_samples.jenis_makanan_id',
                                        )
                                        ->distinct(
                                            'id_laboratorium',
                                            'ms_sample_type.id_sample_type',
                                            'tb_samples.jenis_makanan_id',
                                        )
                                        ->where('id_laboratorium', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                                        ->whereNotNull('pengesahan_hasil_date')
                                        ->get();

                                @endphp

                                @if (count($packet_print_sample_type) > 0)
                                    <a data-href="{{ route('elits-release.print-mikro', [Request::segment(2), $packet_prints[0]->id_sample_type]) }}?jenis_makanan_id={{ $packet_prints[0]->jenis_makanan_id }}"
                                        target="__blank" data-toggle="modal" data-laboratorium="mikro" data-target="#signOptionModal" data-laboratorium="mikro">
                                        <button type="button" class="btn btn-fw btn-primary btn-icon-text">
                                            Mikro <i class="fa fa-print"></i>
                                        </button>
                                    </a>
                                @endif
                            @else
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Mikro <i class="fa fa-print"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-scroll-menu" aria-labelledby="dropdownMenuButton">
                                        @php
                                            // Kumpulkan semua jenis sampel yang sudah selesai (pengesahan_hasil_date != null)
                                            $samplesType = [];
                                        @endphp
                                        @foreach ($packet_prints as $packet_print)
                                            @php
                                                $packet_print_sample_type = \Smt\Masterweb\Models\Sample::where(
                                                    'permohonan_uji_id',
                                                    '=',
                                                    Request::segment(2),
                                                )
                                                    ->where('id_sample_type', $packet_print->id_sample_type)
                                                    ->where('jenis_makanan_id', $packet_print->jenis_makanan_id)
                                                    ->join('tb_sample_method', function ($join) {
                                                        $join
                                                            ->on(
                                                                'tb_sample_method.sample_id',
                                                                '=',
                                                                'tb_samples.id_samples',
                                                            )
                                                            ->whereNull('tb_sample_method.deleted_at')
                                                            ->whereNull('tb_samples.deleted_at')
                                                            ->join('ms_laboratorium', function ($join) {
                                                                $join
                                                                    ->on(
                                                                        'ms_laboratorium.id_laboratorium',
                                                                        '=',
                                                                        'tb_sample_method.laboratorium_id',
                                                                    )
                                                                    ->whereNull('ms_laboratorium.deleted_at')
                                                                    ->whereNull('tb_sample_method.deleted_at');
                                                            });
                                                    })
                                                    ->leftJoin('tb_pengesahan_hasil', function ($join) {
                                                        $join
                                                            ->on(
                                                                'tb_pengesahan_hasil.id_pengesahan_hasil',
                                                                '=',
                                                                DB::raw('(SELECT id_pengesahan_hasil FROM tb_pengesahan_hasil
                                                                WHERE tb_pengesahan_hasil.sample_id = tb_samples.id_samples
                                                                  AND tb_pengesahan_hasil.deleted_at IS NULL
                                                                  AND tb_samples.deleted_at IS NULL
                                                                LIMIT 1)'),
                                                            )
                                                            ->whereNull('tb_pengesahan_hasil.deleted_at')
                                                            ->whereNull('tb_samples.deleted_at');
                                                    })
                                                    ->leftJoin('ms_jenis_makanan', function ($join) {
                                                        $join
                                                            ->on(
                                                                'ms_jenis_makanan.id_jenis_makanan',
                                                                '=',
                                                                'tb_samples.jenis_makanan_id',
                                                            )
                                                            ->whereNull('tb_samples.deleted_at')
                                                            ->whereNull('ms_jenis_makanan.deleted_at');
                                                    })
                                                    ->leftJoin('ms_sample_type', function ($join) {
                                                        $join
                                                            ->on(
                                                                'ms_sample_type.id_sample_type',
                                                                '=',
                                                                'tb_samples.typesample_samples',
                                                            )
                                                            ->whereNull('ms_sample_type.deleted_at')
                                                            ->whereNull('tb_samples.deleted_at');
                                                    })
                                                    ->select(
                                                        'id_laboratorium',
                                                        'ms_jenis_makanan.*',
                                                        'tb_pengesahan_hasil.pengesahan_hasil_date',
                                                        'typesample_samples',
                                                        'ms_sample_type.id_sample_type',
                                                        'ms_sample_type.name_sample_type',
                                                        'tb_samples.jenis_makanan_id',
                                                    )
                                                    ->distinct(
                                                        'id_laboratorium',
                                                        'ms_sample_type.id_sample_type',
                                                        'tb_samples.jenis_makanan_id',
                                                    )
                                                    ->where('id_laboratorium', 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5')
                                                    ->whereNotNull('pengesahan_hasil_date')
                                                    ->get();
                                            @endphp

                                            @if ($packet_print_sample_type->count() > 0)
                                                @php
                                                    // Simpan jenis sampel yang sudah punya pengesahan
                                                    $samplesType[] = $packet_print->id_sample_type;
                                                @endphp
                                                <a class="dropdown-item pointer"
                                                    data-href="{{ route('elits-release.print-mikro', [Request::segment(2), $packet_print->id_sample_type]) }}"
                                                    target="__blank" data-toggle="modal" data-target="#signOptionModal" data-laboratorium="mikro">
                                                    {{ $packet_print->name_sample_type }}
                                                </a>
                                            @endif
                                        @endforeach

                                        @php
                                            // Jika ada Air Higiene (nama lama: Air Bersih) dan Air Minum sekaligus, tampilkan opsi gabungan
                                            $idAirMinum = 'c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0';
                                            $idAirBersih = '65df8403-b29f-4645-a1ed-12d2aeff1fbd';
                                            $hasAirMinum = in_array($idAirMinum, $samplesType, true);
                                            $hasAirBersih = in_array($idAirBersih, $samplesType, true);
                                        @endphp

                                        @if ($hasAirMinum && $hasAirBersih)
                                            <a class="dropdown-item pointer"
                                                data-href="{{ route('elits-release.print-mikro', [Request::segment(2), 'air-bersih-air-minum']) }}"
                                                target="__blank" data-toggle="modal" data-target="#signOptionModal"  data-laboratorium="mikro">
                                                Air Higiene dan Air Minum
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif


                        </div>
                    @endif
                @endif

                @if (count($pudam) > 0 || count($makmin) > 0)
                    @if (count($pudam) > 0)
                        <div class="p-2">
                            <a data-href="{{ url('/elits-release/print-kimia', [Request::segment(2),'65df8403-b29f-4645-a1ed-12d2aeff1fbd']) }}" target="__blank"
                                data-toggle="modal" data-target="#signOptionModal"  data-laboratorium="kimia">
                                <button type="button" class="btn btn-fw btn-primary btn-icon-text">
                                    Kimia <i class="fa fa-print"></i>
                                </button>
                            </a>
                        </div>
                    @endif

                    @if (count($makmin) > 0)
                        <div class="p-2">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownKimiaMakmin"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Kimia <i class="fa fa-print"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownKimiaMakmin">
                                    <a class="dropdown-item pointer"
                                        data-href="{{ url('/elits-release/print-kimia', [Request::segment(2),'d34b4a50-4560-4fce-96c3-046c7080a986']) }}" data-version="version"
                                        target="__blank" data-toggle="modal" data-target="#signOptionModal" data-laboratorium="kimia">
                                        Versi Default
                                    </a>
                                    <a class="dropdown-item pointer"
                                        data-href="{{ url('/elits-release/print-kimia', [Request::segment(2),'d34b4a50-4560-4fce-96c3-046c7080a986']) }}"  data-version="default"
                                        target="__blank" data-toggle="modal" data-target="#signOptionModal" data-laboratorium="kimia">
                                        Versi Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

            </div>


            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}

                </div>
            @endif

            <div class="col-12">
                <table id="order-listing-sample" class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Laboratorium</th>
                            <th>Jenis Sarana</th>
                            <th>Method</th>

                            <th>Nomor Sampel</th>
                            <th>Status Terakhir</th>
                            @if (getAction('update') || getAction('delete'))
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{--    Modal Option SIGN --}}
    <div class="modal fade" id="signOptionModal" tabindex="-1" aria-labelledby="signOptionTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAgendaModalLabel">Tambah Agenda Sebelum Cetak Hasil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="clearListSamples()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="agendaForm" action="" method="GET">
                    <div class="modal-body">
                    <input type="text" class="form-control" id="version" name="version"
                    placeholder="Masukkan version" hidden>
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

    {{-- Modal Duplicate Sample --}}
    <div class="modal fade" id="modalDuplicateSample" tabindex="-1" role="dialog"
        aria-labelledby="modalDuplicateSampleLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDuplicateSampleLabel">
                        <i class="fas fa-copy mr-2"></i>Duplicate Sample
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formDuplicateSample" method="POST">
                    @csrf
                    <input type="hidden" name="sample_id" id="duplicate_sample_id">
                    <input type="hidden" name="lab_id" id="duplicate_lab_id">

                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Sample akan diduplikasi dengan semua parameter dan pengaturan yang
                            sama. Anda dapat mengisi titik pengambilan yang berbeda untuk setiap duplikat.
                        </div>

                        <div class="form-group">
                            <label for="duplicate_count">
                                <i class="fas fa-calculator"></i> Jumlah Duplikasi <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="duplicate_count" name="duplicate_count"
                                min="1" max="20" value="1" required>
                            <small class="form-text text-muted">Masukkan jumlah sampel yang ingin diduplikasi (maksimal
                                20)</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-map-marker-alt"></i> Titik Pengambilan <span
                                    class="text-muted">(Opsional)</span>
                            </label>
                            <small class="form-text text-muted mb-3">
                                Kosongkan jika ingin menggunakan titik pengambilan yang sama dengan sample asli
                            </small>
                            <div id="titik_pengambilan_container">
                                <!-- Dynamic TinyMCE editors will be added here -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitDuplicate">
                            <i class="fas fa-copy"></i> Duplikasi Sample
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Duplicate Group --}}
    <div class="modal fade" id="modalDuplicateGroup" tabindex="-1" role="dialog"
        aria-labelledby="modalDuplicateGroupLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalDuplicateGroupLabel">
                        <i class="fas fa-copy mr-2"></i>Duplicate Group
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formDuplicateGroup" method="POST">
                    @csrf
                    <input type="hidden" name="group_id" id="duplicate_group_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            Semua samples dalam group ini akan diduplikasi dengan group_id baru.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" id="btnSubmitDuplicateGroup">
                            <i class="fas fa-copy mr-2"></i>Duplicate Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TinyMCE optional: disabled when CDN/local assets unavailable -->

    <script>
        $(document).ready(function() {
            if ("{{ getAction('update') || getAction('delete') }}") {
                var table = $('#order-listing-sample').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    responsive: true,
                    ajax: {
                        url: "/elits-samples/" + "{{ $id }}",
                        type: "GET",
                        data: function(d) {
                            d.search = $('input[type="search"]').val()
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nama_laboratorium',
                            name: 'nama_laboratorium'
                        },
                        {
                            data: 'name_sample_type',
                            name: 'name_sample_type'
                        },
                        {
                            data: 'count_method',
                            name: 'count_method'
                        },

                        {
                            data: 'codesample_samples',
                            name: 'codesample_samples'
                        },
                        {
                            data: 'last_status',
                            name: 'last_status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ]
                });
            } else {
                var table = $('#order-listing-sample').DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    responsive: true,
                    ajax: {
                        url: "/elits-samples/" + "{{ $id }}",
                        type: "GET",
                        data: function(d) {
                            d.search = $('input[type="search"]').val()
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nama_laboratorium',
                            name: 'nama_laboratorium'
                        },
                        {
                            data: 'name_sample_type',
                            name: 'name_sample_type'
                        },
                        {
                            data: 'count_method',
                            name: 'count_method'
                        },
                        {
                            data: 'codesample_samples',
                            name: 'codesample_samples'
                        },
                        {
                            data: 'last_status',
                            name: 'last_status'
                        }
                    ]
                });
            }

            // datatables responsive
            new $.fn.dataTable.FixedHeader(table);

            $('.dropdown-scroll-menu').mousewheel(function(e, delta) {
                this.scrollLeft -= (delta * 40);
                e.preventDefault();
            });

            $('#order-listing-sample').on('click', '.btn-hapus', function() {
                var kode = $(this).data('id');
                var nama = $(this).data('nama');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus data : " + nama,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'ajax',
                                method: 'get',
                                url: '/elits-samples-destroy/' + kode,
                                async: true,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status == true) {
                                        swal({
                                                title: "Success!",
                                                text: response.pesan,
                                                icon: "success"
                                            })
                                            .then(function() {
                                                document.location = '/elits-samples/' +
                                                    "{{ $id }}";
                                            });
                                    } else {
                                        swal("Hapus Data Gagal!", {
                                            icon: "warning",
                                            title: "Failed!",
                                            text: response.pesan,
                                        });
                                    }
                                },
                                error: function() {
                                    swal("ERROR", "System tidak dapat menghapus data!",
                                        "error");
                                }
                            });
                        } else {
                            swal("Cancelled", "Hapus data dibatalkan!", "error");
                        }
                    });
            });

            // Handle Delete Group
            $('#order-listing-sample').on('click', '.btn-hapus-group', function() {
                var groupId = $(this).data('group-id');
                var nama = $(this).data('nama');

                swal({
                        title: "Apakah anda yakin?",
                        text: "Untuk menghapus " + nama +
                            "? Semua samples dalam group ini akan dihapus.",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                type: 'get',
                                url: '/elits-samples-destroy-group/' + groupId,
                                async: true,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status == true) {
                                        swal({
                                                title: "Success!",
                                                text: response.pesan,
                                                icon: "success"
                                            })
                                            .then(function() {
                                                document.location = '/elits-samples/' +
                                                    "{{ $id }}";
                                            });
                                    } else {
                                        swal("Hapus Group Gagal!", {
                                            icon: "warning",
                                            title: "Failed!",
                                            text: response.pesan,
                                        });
                                    }
                                },
                                error: function() {
                                    swal("ERROR", "System tidak dapat menghapus group!",
                                        "error");
                                }
                            });
                        } else {
                            swal("Cancelled", "Hapus group dibatalkan!", "error");
                        }
                    });
            });

            // Handle Duplicate Group
            $(document).on('click', '.btn-duplicate-group', function() {
                var groupId = $(this).data('group-id');
                $('#duplicate_group_id').val(groupId);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#signOptionModal').on('show.bs.modal', function(event) {
                var link = $(event.relatedTarget).data('href');
                var laboratorium = $(event.relatedTarget).data('laboratorium');
                var version = $(event.relatedTarget).data('version');
                var idSampleType = link.split('/');

                var idPermohonanUji = idSampleType[idSampleType.length - 2];
                var idSampleTypeFull = idSampleType[idSampleType.length - 1];
                var idSampleTypeParts = idSampleTypeFull.split('?');
                var idSampleType = idSampleTypeParts[0];
                
                // Preserve query parameters (like version=alt)
                var queryParams = '';
                if (idSampleTypeParts.length > 1) {
                    queryParams = '?' + idSampleTypeParts.slice(1).join('?') + '&version=' + version;
                }

                console.log(idPermohonanUji, idSampleType, version);

                // Jika idSampleType adalah 'air-bersih-air-minum', ubah menjadi ID Air Minum
                if (idSampleType === 'air-bersih-air-minum') {
                    var idAirMinum = 'c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0';
                    // Ubah link untuk menggunakan ID Air Minum
                    var linkParts = link.split('/');
                    linkParts[linkParts.length - 1] = idAirMinum;
                    link = linkParts.join('/');
                    // Update idSampleType untuk digunakan di getSamples
                    idSampleType = 'air-bersih-air-minum';
                }

                console.log('Updated link:', link);
                console.log('Updated idSampleType:', idSampleType);


                if(laboratorium == 'mikro'){
                    getSamplesMikro(idPermohonanUji, idSampleType, function(samples) {
                        if (samples) {
                            
                            console.log(samples);
                            
                            const listSamples = $('#listSamples')
                            samples.forEach(sample => {
                                    var location = sample.nama_jenis_makanan !== null && sample.nama_jenis_makanan !== '' ? sample.nama_jenis_makanan : (sample.titik_pengambilan !== null ? sample
                                        .titik_pengambilan : sample.location_samples);

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
                }else{
                    getSamplesKimia(idPermohonanUji, idSampleType, function(samples) {
                        if (samples) {
                            
                            console.log(samples);
                            
                            const listSamples = $('#listSamples')
                            
                            // Group samples by codesample_samples
                            const groupedSamples = {};
                            samples.forEach(sample => {
                                const sampleCode = sample.codesample_samples || sample.id_samples;
                                if (!groupedSamples[sampleCode]) {
                                    groupedSamples[sampleCode] = [];
                                }
                                groupedSamples[sampleCode].push(sample);
                            });
                            
                            // Display grouped samples
                            Object.keys(groupedSamples).forEach(sampleCode => {
                                const sampleGroup = groupedSamples[sampleCode];
                                const firstSample = sampleGroup[0];
                                
                                var location = firstSample.titik_pengambilan !== null && firstSample.titik_pengambilan !== '' ? firstSample
                                    .titik_pengambilan : firstSample.location_samples;

                                var tempDiv = document.createElement('div');
                                tempDiv.innerHTML = location;
                                location = tempDiv.textContent || tempDiv.innerText;
                                
                                // Get all unique sample IDs for this group
                                const sampleIds = [...new Set(sampleGroup.map(s => s.id_samples))];
                                
                                // Create hidden inputs for all sample IDs and a single checkbox
                                const hiddenInputs = sampleIds.map(id => 
                                    `<input type="hidden" name="printSamples[]" class="sample-hidden-${sampleCode.replace(/[^a-zA-Z0-9]/g, '_')}" value="${id}">`
                                ).join('');
                                
                                const checkboxId = `sample-checkbox-${sampleCode.replace(/[^a-zA-Z0-9]/g, '_')}`;

                                listSamples.append(`<li class="w-full d-flex justify-content-between" style="padding: 0px !important; height: 14px !important;">
                                <label style="font-size: 11px; margin-right: 5px;" >00${firstSample.count_id} - ${location}</label>
                                ${hiddenInputs}
                                <input type="checkbox" id="${checkboxId}" class="samplesCheckbox" data-sample-code="${sampleCode}" checked>
                            </li><hr>`)
                            });
                            
                            // Handle checkbox change to toggle hidden inputs
                            $(document).off('change', '.samplesCheckbox[data-sample-code]').on('change', '.samplesCheckbox[data-sample-code]', function() {
                                const isChecked = $(this).prop('checked');
                                const sampleCode = $(this).data('sample-code');
                                const safeCode = sampleCode.replace(/[^a-zA-Z0-9]/g, '_');
                                $(`.sample-hidden-${safeCode}`).each(function() {
                                    if (isChecked) {
                                        $(this).attr('name', 'printSamples[]');
                                    } else {
                                        $(this).attr('name', '');
                                    }
                                });
                            });
                            
                            // Trigger initial state after a short delay to ensure DOM is ready
                            setTimeout(function() {
                                $('#listSamples .samplesCheckbox[data-sample-code]').each(function() {
                                    if ($(this).prop('checked')) {
                                        $(this).trigger('change');
                                    }
                                });
                            }, 100);
                        }
                    })
                }

                // Preserve query parameters in the form action
                var finalLink = link;
                if (queryParams) {
                    // Add query params if not already in link
                    if (!finalLink.includes('?')) {
                        finalLink = finalLink + queryParams + '&version=' + version;
                    } else {
                        finalLink = finalLink + '&' + queryParams.replace('?', '') + '&version=' + version;
                    }
                }

                console.log("fdafddfs");
                console.log(finalLink);
                
                
                $('#agendaForm').attr('action', finalLink);

                $('#version').attr('value', version);
               
            })

            $('#printAll').on('change', function() {
                var isChecked = $(this).prop('checked');
                $('#listSamples .samplesCheckbox').prop('checked', isChecked).trigger('change');
            });
        })

        function getSamplesMikro(idPermohonanUji, idSampleType, callback) {
            const url = `/elits-samples/list-samples-mikro/${idPermohonanUji}/${idSampleType}`;

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

        function getSamplesKimia(idPermohonanUji, idSampleType, callback) {
            const url = `/elits-samples/list-samples-kimia/${idPermohonanUji}/${idSampleType}`;

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

        // Handle Duplicate Sample Modal
        $(document).on('click', '.btn-duplicate-sample', function() {
            var sampleId = $(this).data('sample-id');
            var labId = $(this).data('lab-id');

            $('#duplicate_sample_id').val(sampleId);
            $('#duplicate_lab_id').val(labId);

            // Reset form
            $('#duplicate_count').val(1);
            generateTitikPengambilanInputs(1);
        });

        // Generate input fields for titik pengambilan based on count
        $('#duplicate_count').on('input change', function() {
            var count = parseInt($(this).val()) || 1;
            if (count < 1) count = 1;
            if (count > 20) count = 20;
            $(this).val(count);

            generateTitikPengambilanInputs(count);
        });

        function generateTitikPengambilanInputs(count) {
            var container = $('#titik_pengambilan_container');

            // Check if TinyMCE is available
            if (typeof tinymce !== 'undefined') {
                // Destroy existing TinyMCE instances first
                tinymce.remove('.titik-pengambilan-editor');
            }

            container.empty();

            for (var i = 1; i <= count; i++) {
                var editorId = 'titik_pengambilan_' + i;
                var editorGroup = `
                    <div class="mb-4 titik-item">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <strong class="text-primary">
                                    <i class="fas fa-map-marker-alt"></i> Titik Pengambilan #${i}
                                </strong>
                            </div>
                            <div class="card-body p-2">
                                <textarea id="${editorId}" class="titik-pengambilan-editor" 
                                    name="titik_pengambilan[]" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                `;
                container.append(editorGroup);
            }

            // Initialize TinyMCE for all new editors with delay to ensure DOM is ready
            setTimeout(function() {
                initializeTinyMCE();
            }, 100);
        }

        function initializeTinyMCE() {
            // Check if TinyMCE is loaded
            if (typeof tinymce === 'undefined') {
                // TinyMCE not available; keep plain textarea inputs
                return;
            }

            tinymce.init({
                selector: '.titik-pengambilan-editor',
                height: 200,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'charmap',
                    'searchreplace', 'visualblocks', 'code',
                    'insertdatetime', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | bold italic underline | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist outdent indent | removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                placeholder: 'Masukkan titik pengambilan (opsional)...',
                branding: false,
                promotion: false,
                setup: function(editor) {
                    editor.on('init', function() {
                        console.log('TinyMCE Editor initialized: ' + editor.id);
                    });
                }
            }).catch(function(error) {
                console.error('TinyMCE initialization error:', error);
            });
        }

        // Submit duplicate form
        $('#formDuplicateSample').on('submit', function(e) {
            e.preventDefault();

            var submitBtn = $('#btnSubmitDuplicate');
            var originalText = submitBtn.html();

            // Disable button and show loading
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            // Trigger TinyMCE save to update textarea values if TinyMCE is loaded
            if (typeof tinymce !== 'undefined' && tinymce.get()) {
                tinymce.triggerSave();
            }

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ route('elits-samples.store-duplicate-multiple') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.status) {
                        swal({
                            title: "Berhasil!",
                            text: response.message,
                            icon: "success"
                        }).then(function() {
                            $('#modalDuplicateSample').modal('hide');

                            // Reload table if exists
                            if (typeof table !== 'undefined') {
                                table.ajax.reload();
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        swal({
                            title: "Gagal!",
                            text: response.message || "Terjadi kesalahan saat duplikasi sample",
                            icon: "error"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = "Terjadi kesalahan saat duplikasi sample";

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    swal({
                        title: "Error!",
                        text: errorMessage,
                        icon: "error"
                    });
                },
                complete: function() {
                    // Re-enable button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Reset form when modal is hidden
        $('#modalDuplicateSample').on('hidden.bs.modal', function() {
            // Remove all TinyMCE instances if available
            if (typeof tinymce !== 'undefined') {
                tinymce.remove('.titik-pengambilan-editor');
            }

            // Reset form
            $('#formDuplicateSample')[0].reset();

            // Regenerate default editor
            generateTitikPengambilanInputs(1);
        });

        // Pre-load TinyMCE when document is ready
        if (typeof tinymce !== 'undefined') {
            console.log('TinyMCE loaded successfully');
        } else {
            console.warn('TinyMCE not loaded - editors will not be available');
        }

        // Submit duplicate group form
        $('#formDuplicateGroup').on('submit', function(e) {
            e.preventDefault();
            var submitBtn = $('#btnSubmitDuplicateGroup');
            var groupId = $('#duplicate_group_id').val();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');

            $.ajax({
                url: '/elits-samples/duplicate-group/' + groupId,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status) {
                        swal({
                            title: "Success!",
                            text: response.message,
                            icon: "success"
                        }).then(function() {
                            $('#modalDuplicateGroup').modal('hide');
                            document.location = '/elits-samples/' + "{{ $id }}";
                        });
                    } else {
                        swal("Duplicate Group Gagal!", {
                            icon: "error",
                            title: "Failed!",
                            text: response.message,
                        });
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Gagal menduplikasi group';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    swal("ERROR", errorMsg, "error");
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(
                        '<i class="fas fa-copy mr-2"></i>Duplicate Group');
                }
            });
        });
    </script>
@endsection
