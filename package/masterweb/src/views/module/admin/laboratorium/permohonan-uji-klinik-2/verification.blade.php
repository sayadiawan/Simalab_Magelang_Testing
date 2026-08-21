@extends('masterweb::template.admin.layout')
@section('title')
    Verifikasi Klinik
@endsection

@section('content')
    <style>
        .ui-datepicker {
            position: relative;
            z-index: 100000;
        }

        .sign-opt {
            width: 200px;
        }

        .sign-opt:hover {
            background-color: #dedcdc;
        }

        .my-custom-popup-class {
            padding-top: 2.5rem !important;
        }

        /* Modern Card Styling */
        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            border: none;
            padding: 15px 20px;
        }

        .shadow-sm {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .badge-pill {
            border-radius: 50px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* Table Styling */
        .table-borderless th {
            padding: 8px 0;
            font-weight: 500;
        }

        .table-borderless td {
            padding: 8px 0;
        }

        /* Badge Animation */
        .badge-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .badge-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .badge-warning {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }

        .badge-primary {
            background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);
        }

        /* Smooth card styling - keep transitions for normal UI */
        .card {
            /* Allow normal rendering, just prevent transform on hover */
            border-radius: 12px;
            overflow: hidden;
        }

        .card:hover {
            /* ONLY disable transform that causes blinking */
            transform: none !important;
        }

        /* Badges - minimal optimization */
        .badge,
        .badge-pill {
            /* Force GPU for smooth rendering */
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        /* Signature Pad Styling - Anti Flickering */
        .signature-container {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .signature-wrapper {
            position: relative;
            width: 100%;
            height: 250px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .signature-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: crosshair;
            touch-action: none;
            background-color: #ffffff !important;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            /* Prevent flickering */
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            -ms-interpolation-mode: nearest-neighbor;
            /* Hardware acceleration */
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            will-change: contents;
            /* Smooth rendering */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Loading state */
        .signature-wrapper::before {
            content: "Tanda tangan di sini";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #ccc;
            font-size: 14px;
            pointer-events: none;
            z-index: 1;
        }

        .signature-wrapper.active::before {
            display: none;
        }

        /* Button styling - No animation inside modal */
        #signatureSampleModal .btn {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Force no transform to prevent reflow */
            will-change: auto;
        }

        #signatureSampleModal .btn:hover {
            /* Only change color, no transform */
            opacity: 0.9;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        /* Prevent button states from triggering canvas redraw */
        .signature-container {
            isolation: isolate;
        }

        .signature-container h6 {
            font-weight: 600;
            color: #495057;
        }

        /* Modal animation fix - FORCE NO ANIMATION */
        #signatureSampleModal,
        #signatureSampleModal *,
        #signatureSampleModal *::before,
        #signatureSampleModal *::after {
            animation: none !important;
            transition: none !important;
        }

        #signatureSampleModal .modal-dialog {
            transition: none !important;
            animation: none !important;
        }

        #signatureSampleModal.show .modal-dialog {
            transform: none !important;
        }

        /* Prevent canvas from triggering reflow */
        #signatureSampleModal .modal-body {
            min-height: 350px;
            /* Force layout stability */
            contain: layout style paint;
        }

        /* Disable action buttons jika belum ada pemeriksaan */
        .table-no-pemeriksaan .btn:not(.btn-secondary):not(.btn-link) {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .table-no-pemeriksaan .btn:not(.btn-secondary):not(.btn-link):disabled {
            opacity: 0.5;
        }

        /* Prevent modal header from causing reflow */
        #signatureSampleModal .modal-header {
            /* Fixed dimensions to prevent layout shift */
            min-height: 60px;
            contain: layout;
        }

        /* Force GPU acceleration on entire modal */
        #signatureSampleModal .modal-content {
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            perspective: 1000px;
            -webkit-perspective: 1000px;
        }

        /* Chrome-specific fixes for signature canvas */
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            .signature-canvas {
                background: white !important;
                -webkit-font-smoothing: antialiased;
                -webkit-backface-visibility: hidden;
                -webkit-transform: translate3d(0, 0, 0);
            }
        }

        /* Firefox-specific fixes */
        @-moz-document url-prefix() {
            .signature-canvas {
                background: white !important;
            }
        }

        /* Safari-specific fixes */
        @supports (-webkit-appearance: none) {
            .signature-canvas {
                background: white !important;
                -webkit-appearance: none;
            }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .signature-wrapper {
                height: 200px;
            }

            .signature-container {
                padding: 10px;
                margin-bottom: 15px;
            }
        }

        /* ============================================
                                                                                                                                                                                                                                                   TARGETED ANTI-BLINKING - Moderate Approach
                                                                                                                                                                                                                                                   Only disable animations that cause issues
                                                                                                                                                                                                                                                   ============================================ */

        /* Disable ONLY card hover transform (main culprit) */
        .card:hover {
            /* NO transform to prevent reflow cascade */
            transform: none !important;
        }

        /* Table hover - instant without transition */
        .table tbody tr {
            transition: background-color 0s !important;
        }

        /* Buttons - remove transition delays */
        .btn {
            transition: opacity 0.15s ease, background-color 0.15s ease !important;
            transform: none !important;
        }

        .btn:hover {
            transform: none !important;
        }

        /* Badges - force GPU but keep visible */
        .badge,
        .badge-pill {
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        /* Only stabilize specific problem areas */
        #signatureSampleModal .card,
        #signatureSampleModal .card:hover {
            transform: none !important;
            transition: none !important;
        }

        /* Prevent signature modal from causing page reflow */
        .modal-backdrop {
            transform: translateZ(0);
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

                                @php
                                    $__infoHajiNav = $info_haji ?? [];
                                    $__isHajiNav = !empty($__infoHajiNav['is_haji']);
                                    $__idHajiNav = $__infoHajiNav['id_haji'] ?? null;
                                @endphp

                                @if ($__isHajiNav && !empty($__idHajiNav))
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('elits-permohonan-uji-klinik-2.haji') }}">Permohonan Uji Klinik Haji</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $__idHajiNav) }}">Daftar Pasien Haji</a>
                                    </li>
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/elits-permohonan-uji-klinik-2') }}">Permohonan Uji Klinik
                                            Management</a>
                                    </li>
                                @endif
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span>Verifikasi</span>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL: Tanggal Cetak Verifikasi ===================== -->
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
                    action="{{ route('elits-permohonan-uji-klinik-2.print_verifikasi', $item->id_permohonan_uji_klinik) }}"
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

                        {{-- Pengaturan Kop Surat --}}
                        <div class="card border-0 bg-light p-3 mb-3">
                            <label class="font-weight-bold mb-2">
                                <i class="fa fa-file-alt mr-1"></i>Kop Surat
                            </label>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-sm text-muted" id="verif-print-kop-label-text">
                                        {{ ($item->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)' }}
                                    </div>
                                </div>
                                <div class="custom-control custom-switch ml-3">
                                    <input type="checkbox" class="custom-control-input" id="verif-print-toggle-kop"
                                        name="showKop"
                                        value="1"
                                        {{ ($item->show_kop_hasil_permohonan_uji_klinik ?? 1) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="verif-print-toggle-kop"></label>
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">
                                Jika dimatikan, area kop tetap ada namun kosong (tanpa gambar).
                            </small>
                        </div>

                        {{-- Ukuran Font Tabel --}}
                        <div class="card border-0 bg-light p-3 mb-3">
                            <label class="font-weight-bold mb-1">
                                <i class="fa fa-text-height mr-1"></i>Ukuran Font Tabel
                            </label>
                            <div class="d-flex align-items-center mt-1">
                                <span class="text-muted small mr-2">8</span>
                                <input type="range" class="custom-range flex-grow-1 mr-2" id="verif-print-fontsize-slider"
                                    name="fontsize"
                                    min="8" max="16" step="0.5"
                                    value="{{ $item->fontsize_verifikasi_permohonan_uji_klinik ?? 12 }}">
                                <span class="text-muted small ml-2">16</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-print-fontsize-minus">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <div class="input-group mx-2" style="width: 90px;">
                                    <input type="number" class="form-control text-center font-weight-bold" id="verif-print-fontsize-input"
                                        name="fontsize"
                                        min="8" max="16" step="0.5"
                                        value="{{ $item->fontsize_verifikasi_permohonan_uji_klinik ?? 12 }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">pt</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-print-fontsize-plus">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Padding Tabel --}}
                        <div class="card border-0 bg-light p-3 mb-3">
                            <label class="font-weight-bold mb-1">
                                <i class="fa fa-arrows-v mr-1"></i>Padding Tabel (Jarak Antar Sel)
                            </label>
                            <div class="d-flex align-items-center mt-1">
                                <span class="text-muted small mr-2">0</span>
                                <input type="range" class="custom-range flex-grow-1 mr-2" id="verif-print-padding-slider"
                                    name="padding"
                                    min="0" max="16" step="0.5"
                                    value="{{ $item->padding_verifikasi_permohonan_uji_klinik ?? 5 }}">
                                <span class="text-muted small ml-2">16</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-print-padding-minus">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <div class="input-group mx-2" style="width: 100px;">
                                    <input type="number" class="form-control text-center font-weight-bold" id="verif-print-padding-input"
                                        name="padding"
                                        min="0" max="16" step="0.5"
                                        value="{{ $item->padding_verifikasi_permohonan_uji_klinik ?? 5 }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">pt</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="verif-print-padding-plus">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                Mengatur padding atas/bawah setiap sel di tabel verifikasi. Nilai lebih besar = baris lebih renggang.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cetak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL: Input NIK & Password BSRE (Pengambil Sample) ===================== -->
    <!-- BSRE DISABLED TEMPORARILY
                                                                                                                                                                                                                                                                                <div class="modal fade" id="inputNikAndPasword" tabindex="-1" aria-labelledby="inputNikAndPassword" aria-hidden="true">
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
                                                                                                                                                                                                                                                                                -->

    <!-- (duplicate modal with same id removed to avoid confusion) -->

    <!-- ===================== MODAL: Pilih Metode Tanda Tangan ===================== -->
    <div class="modal fade" id="signOptionModal" tabindex="-1" aria-labelledby="signOptionTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="signOptionTitle">Pilih metode tanda tangan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agendaNumber">Nomor Agenda</label>
                        <input type="text" class="form-control" id="agendaNumber" name="agenda"
                            placeholder="Masukkan nomor agenda">
                    </div>
                </div>
                <div class="d-flex mx-auto m-2 justify-content-around">
                    <a id="linkTTDManual"
                        href="{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', [$item->id_permohonan_uji_klinik]) }}?signoption=0"
                        target="_blank">
                        <button class="btn text-center m-2 p-2 sign-opt">
                            <img src="{{ asset('assets/admin/images/sign-icon.png') }}" width="80" height="80">
                            <h5 class="mt-2">Tanda Tangan Manual</h5>
                        </button>
                    </a>
                    <!-- BSRE DISABLED TEMPORARILY
                                                                                                                                                                                                                                                                                                <a id="linkTTDElektronik"
                                                                                                                                                                                                                                                                                                    href="{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', [$item->id_permohonan_uji_klinik]) }}?signoption=1"
                                                                                                                                                                                                                                                                                   target="_blank">
                                                                                                                                                                                                                                                                                    <button class="btn text-center m-2 p-2 sign-opt">
                                                                                                                                                                                                                                                                                                        <img src="{{ asset('assets/admin/images/logo/logo-bsre.png') }}" width="80"
                                                                                                                                                                                                                                                                                                            height="80">
                                                                                                                                                                                                                                                                                      <h5 class="mt-2">Tanda Tangan Elektronik</h5>
                                                                                                                                                                                                                                                                                    </button>
                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                                -->
                </div>
            </div>
        </div>
    </div>

    @php
        setlocale(LC_TIME, 'id_ID');
        \Carbon\Carbon::setLocale('id');

    @endphp

    <div class="card">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var agendaInput = document.getElementById('agendaNumber');
                if (!agendaInput) return;

                function updateLinks() {
                    var agenda = encodeURIComponent(agendaInput.value || '');
                    var manual = document.getElementById('linkTTDManual');
                    var elektronik = document.getElementById('linkTTDElektronik');
                    [manual, elektronik].forEach(function(a) {
                        if (!a) return;
                        var base = a.href.split('?')[0];
                        var params = new URLSearchParams(a.href.split('?')[1] || '');
                        if (agenda) {
                            params.set('agenda', agenda);
                        } else {
                            params.delete('agenda');
                        }
                        a.href = base + '?' + params.toString();
                    });
                }
                agendaInput.addEventListener('input', updateLinks);
                $('#signOptionModal').on('shown.bs.modal', updateLinks);
            });
        </script>
        <div class="card-header">
            <div class="row">
                <div class="col-md-12">
                    <H4 class="d-inline-block  float-left margin-top">Verifikasi</H4>
                    <button type="button" class="btn btn-outline-success btn-rounded float-right" data-toggle="modal"
                        data-target="#signOptionModal">
                        <i class="fa fa-print"></i> Cetak Hasil
                    </button>
                    <button type="button" class="btn btn-outline-success btn-rounded float-right mr-2"
                        data-toggle="modal" data-target="#editTanggalCetakVerifikasi">
                        <i class="fa fa-print"></i> Cetak Verifikasi
                    </button>
                    @if (!empty($phone_pasien_wa))
                        <button type="button"
                            class="btn btn-outline-success btn-rounded float-right mr-2"
                            id="btnKirimHasilWhatsApp"
                            @if (empty($bisa_kirim_hasil_whatsapp_manual)) disabled title="Selesaikan validasi dulu sebelum mengirim WhatsApp" @endif>
                            <i class="fab fa-whatsapp"></i>
                            {{ !empty($validasi_selesai) ? 'Kirim Ulang WA' : 'Kirim Hasil WA' }}
                        </button>
                    @endif

                </div>
            </div>
        </div>
        {{-- BSRE DISABLED TEMPORARILY
        @if (session('error-bsre'))
            <div class="col-12">
                <div class="alert alert-danger">
                    {{ session('error-bsre') }}
                </div>
            </div>
        @endif
        --}}
        {{-- BSRE DISABLED TEMPORARILY
        @if (session('error-laporan'))
          <script>
            document.addEventListener('DOMContentLoaded', function() {
              Swal.fire({
                title: "Gagal Melakukan Tanda Tangan Elektronik",
                text: "Terjadi kesalahan saat melakukan tanda tangan elektronik. Silakan coba lagi.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Coba Lagi",
                showLoaderOnConfirm: true,
                preConfirm: () => {
                  Swal.showLoading();
                  return new Promise((resolve) => {
                    setTimeout(() => {
                      resolve();
                                    window.location.href =
                                        "{{ route('elits-permohonan-uji-klinik-2.print-permohonan-uji-klinik-hasil', [$item->id_permohonan_uji_klinik]) }}?signoption=1";
                    }, 1000);
                  });
                },
                customClass: {
                  popup: 'my-custom-popup-class'
                }
              });
            });
          </script>
        @endif
        --}}
        {{-- BSRE DISABLED TEMPORARILY
        @if (session('error-verifikasi'))
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
        --}}
        <div class="card-body">
            <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Badge Labels -->
                        <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
                            <div>
                                <span class="badge badge-primary badge-pill px-3 py-2 mr-2" style="font-size: 13px;">
                                    <i class="fa fa-flask mr-1"></i> Laboratorium Klinik
                                </span>
                                @if ($item->is_prolanis_gula == 1)
                                    <span class="badge badge-danger badge-pill px-3 py-2 mr-2" style="font-size: 13px;">
                                        <i class="fa fa-heartbeat mr-1"></i> Prolanis Gula
                                    </span>
                                @endif
                                @if ($item->is_prolanis_urine == 1)
                                    <span class="badge badge-warning badge-pill px-3 py-2 mr-2" style="font-size: 13px;">
                                        <i class="fa fa-tint mr-1"></i> Prolanis Urine
                                    </span>
                                @endif
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                                    'info_haji' => $info_haji ?? null,
                                    'mode' => 'badge',
                                ])
                            </div>
                            @if (!empty(($info_haji['is_haji'] ?? false)) && !empty($info_haji['id_haji'] ?? null))
                                <a href="{{ route('elits-permohonan-uji-klinik-2.haji.daftar-pasien', $info_haji['id_haji']) }}"
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fa fa-users mr-1"></i> Kembali ke Daftar Pasien Haji
                                </a>
                            @endif
                        </div>

                        <!-- Patient Information Card -->
                        <div class="card shadow-sm mb-4" style="border-left: 4px solid #007bff;">
                            <div class="card-header bg-gradient-primary text-white"
                                style="background: linear-gradient(135deg, #0b3a5c 0%, #0d8f7f 100%);">
                                <h5 class="mb-0"><i class="fa fa-user-circle mr-2"></i>Informasi Pasien</h5>
                            </div>
                            <div class="card-body">
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                                    'info_haji' => $info_haji ?? null,
                                    'mode' => 'alert',
                                ])
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-id-card text-primary mr-2"></i>No. Register
                                                </th>
                                                <td style="font-weight: 600;">:
                                                    {{ $item->getDisplayNoregister() }}</td>
                                                <input type="hidden" name="permohonan_uji_klinik"
                                                    id="permohonan_uji_klinik"
                                                    value="{{ $item->id_permohonan_uji_klinik }}" readonly>
                                            </tr>
                                            <!-- Modal Signature Pengambil Sample -->
                                            <div class="modal fade" id="signatureSampleModal" tabindex="-1"
                                                role="dialog" aria-labelledby="signatureSampleModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="signatureSampleModalLabel">Tanda
                                                                Tangan
                                                                Pengambil Sample</h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="signature-container">
                                                                        <h6 class="mb-3"><i
                                                                                class="fa fa-user-circle mr-2"></i>Pasien/Wali
                                                                        </h6>
                                                                        <div class="signature-wrapper">
                                                                            <canvas id="sigPadPasien"
                                                                                class="signature-canvas"></canvas>
                                                                        </div>
                                                                        <div class="mt-3 text-center">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-secondary"
                                                                                id="clearSigPasien">
                                                                                <i class="fa fa-eraser mr-1"></i>Clear
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-success"
                                                                                id="saveSigPasien">
                                                                                <i class="fa fa-save mr-1"></i>Simpan
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-info"
                                                                                id="downloadSigPasien">
                                                                                <i class="fa fa-download mr-1"></i>Download
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="signature-container">
                                                                        <h6 class="mb-3"><i
                                                                                class="fa fa-user-md mr-2"></i>Petugas</h6>
                                                                        <div class="signature-wrapper">
                                                                            <canvas id="sigPadPetugas"
                                                                                class="signature-canvas"></canvas>
                                                                        </div>
                                                                        <div class="mt-3 text-center">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-secondary"
                                                                                id="clearSigPetugas">
                                                                                <i class="fa fa-eraser mr-1"></i>Clear
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-success"
                                                                                id="saveSigPetugas">
                                                                                <i class="fa fa-save mr-1"></i>Simpan
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-info"
                                                                                id="downloadSigPetugas">
                                                                                <i class="fa fa-download mr-1"></i>Download
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light"
                                                                data-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-id-badge text-primary mr-2"></i>No. Rekam Medis
                                                </th>
                                                <td style="font-weight: 600;">
                                                    :
                                                    {{ $item->getNoRekamMedis() }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-calendar text-primary mr-2"></i>Tgl. Register
                                                </th>
                                                <td style="font-weight: 600;">
                                                    :
                                                    {{ \Carbon\Carbon::parse($tgl_register)->locale('id')->isoFormat('D MMMM YYYY') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-user text-primary mr-2"></i>Nama Pasien
                                                </th>
                                                <td style="font-weight: 600; text-transform: uppercase;">: {{ mb_strtoupper($item->pasien->nama_pasien, 'UTF-8') }}</td>
                                            </tr>
                                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials._keterangan-haji', [
                                                'info_haji' => $info_haji ?? null,
                                                'mode' => 'table-rows',
                                                'thWidth' => '180px',
                                            ])
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-birthday-cake text-primary mr-2"></i>Tanggal Lahir
                                                </th>
                                                <td style="font-weight: 600;">
                                                    : {{ \Carbon\Carbon::parse($item->pasien->tgllahir_pasien)->locale('id')->isoFormat('D MMMM YYYY') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-venus-mars text-primary mr-2"></i>Umur/Jenis Kelamin
                                                </th>
                                                <td style="font-weight: 600;">
                                                    :
                                                    {{ $item->umurtahun_pasien_permohonan_uji_klinik . ' tahun ' . $item->umurbulan_pasien_permohonan_uji_klinik . ' bulan ' . $item->umurhari_pasien_permohonan_uji_klinik . ' hari' }}
                                                    / {{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-phone text-primary mr-2"></i>Nomor HP
                                                </th>
                                                <td style="font-weight: 600;">
                                                    : {{ $item->pasien->phone_pasien ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fab fa-whatsapp text-success mr-2"></i>Kirim Hasil WA
                                                </th>
                                                <td style="font-weight: 600;">
                                                    :
                                                    @if (!empty($kirim_hasil_whatsapp) && !empty($phone_pasien_wa))
                                                        <span class="badge badge-success">Otomatis aktif</span>
                                                    @elseif (!empty($kirim_hasil_whatsapp) && empty($phone_pasien_wa))
                                                        <span class="badge badge-warning text-dark">Aktif, HP kosong</span>
                                                    @else
                                                        <span class="badge badge-secondary">Tidak otomatis</span>
                                                    @endif
                                                    @if (!empty($phone_pasien_wa))
                                                        <button type="button"
                                                            class="btn btn-sm btn-success ml-2"
                                                            id="btnKirimHasilWhatsAppInline"
                                                            @if (empty($bisa_kirim_hasil_whatsapp_manual)) disabled title="Selesaikan validasi dulu sebelum mengirim WhatsApp" @endif>
                                                            <i class="fab fa-whatsapp"></i>
                                                            {{ !empty($validasi_selesai) ? 'Kirim Ulang' : 'Kirim Manual' }}
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="180px" style="color: #666;">
                                                    <i class="fa fa-map-marker-alt text-primary mr-2"></i>Alamat
                                                </th>
                                                <td style="font-weight: 600;">
                                                    : {{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item->pasien) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pemeriksaan Yang Diujikan Card -->
                        <div class="card shadow-sm mb-4" style="border-left: 4px solid #28a745;">
                            <div class="card-header bg-gradient-success text-white"
                                style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                <h5 class="mb-0"><i class="fa fa-microscope mr-2"></i>Pemeriksaan Yang Diujikan</h5>
                            </div>
                            <div class="card-body">
                                @if (isset($parameters) && count($parameters) > 0)
                                    @php
                                        // Group parameters by package name
                                        $groupedParams = [];
                                        foreach ($parameters as $param) {
                                            $packageName = $param->nama_paket ?? '-';
                                            if (!isset($groupedParams[$packageName])) {
                                                $groupedParams[$packageName] = [];
                                            }
                                            $groupedParams[$packageName][] = $param;
                                        }
                                        $counter = 1;
                                    @endphp

                                    <div class="row">
                                        @foreach ($groupedParams as $packageName => $params)
                                            @if ($packageName !== '-')
                                                {{-- Jika PAKET: Tampilkan nama paket saja --}}
                                                <div class="col-md-6 mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-success mr-2"
                                                            style="font-size: 12px;">{{ $counter++ }}</span>
                                                        <span style="font-size: 14px;">
                                                            <i class="fa fa-check-circle text-success mr-1"></i>
                                                            <strong>

                                                            {{ $packageName }}

                                                        </strong>
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Jika BUKAN PAKET: Tampilkan parameter satuan saja --}}
                                                @foreach ($params as $param)
                                                    <div class="col-md-6 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge badge-success mr-2"
                                                                style="font-size: 12px;">{{ $counter++ }}</span>
                                                            <span style="font-size: 14px;">
                                                                <i class="fa fa-check-circle text-success mr-1"></i>
                                                                {{ $param->parametersatuanklinik->name_parameter_satuan_klinik ?? '-' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        <i class="fa fa-info-circle mr-2"></i>Belum ada pemeriksaan yang ditambahkan
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if (session('status'))
                    <div class="col-12">
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    </div>
                    @if (session('status_popup'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                if (typeof swal !== 'undefined') {
                                    swal({
                                        title: 'Berhasil!',
                                        text: @json(session('status')),
                                        icon: 'success',
                                        button: 'OK'
                                    });
                                }
                            });
                        </script>
                    @endif
                @endif
                @if (session('error'))
                    <div class="col-12">
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
                @if ($errors->has('error'))
                    <div class="alert alert-danger">
                        <strong>Error:</strong> {{ $errors->first('error') }}
                    </div>
                @endif
                <div class="col-md-6">
                    <input type="hidden" name="_token-select" id="csrf-token" value="{{ Session::token() }}" />
                    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                </div>

                @php
                    // Cek apakah sudah ada pemeriksaan
                    $hasPemeriksaan = isset($parameters) && count($parameters) > 0;
                    $isUrineOnlySample = $isUrineOnlySample ?? false;
                    $isDibawaPelanggan = $isDibawaPelanggan ?? false;
                    // Tombol selesai pemeriksa sampel hanya aktif jika sudah ada hasil input.
                    $hasInputPemeriksa = collect($parameters ?? [])->contains(function ($parameter) {
                        return isset($parameter->hasil_permohonan_uji_parameter_klinik) &&
                            $parameter->hasil_permohonan_uji_parameter_klinik !== null &&
                            $parameter->hasil_permohonan_uji_parameter_klinik !== '';
                    });
                    // Tombol selesai verifikasi hanya aktif jika sudah ada status verifikasi
                    // yang bukan "pending" dari halaman Verifikasi parameter.
                    $hasInputVerifikasi = collect($parameters ?? [])->contains(function ($parameter) {
                        $status = null;
                        if (is_array($parameter)) {
                            $status = $parameter['status_verifikasi'] ?? null;
                        } else {
                            $status = $parameter->status_verifikasi ?? null;
                        }
                        $status = strtolower(trim((string) $status));
                        return $status !== '' && $status !== 'pending';
                    });

                    $anchorRegVerifikasi = \Carbon\Carbon::parse($item->created_at ?? $item->tglregister_permohonan_uji_klinik);

                    $formatJamVerifikasi = function ($record) {
                        if (!$record || empty($record->start_date)) {
                            return '-';
                        }
                        try {
                            return \Carbon\Carbon::parse($record->start_date)->format('d/m/Y H:i');
                        } catch (\Exception $e) {
                            return '-';
                        }
                    };

                    $formatNamaPetugasVerifikasi = function ($record) {
                        if (!$record || empty($record->nama_petugas)) {
                            return '-';
                        }
                        return \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::labelPetugasPengambil($record->nama_petugas);
                    };

                    try {
                        $defaultJamPengambilSample = $anchorRegVerifikasi
                            ->copy()
                            ->setTimeFromTimeString(\Carbon\Carbon::now()->format('H:i:s'))
                            ->format('d/m/Y H:i');
                    } catch (\Exception $e) {
                        $defaultJamPengambilSample = \Carbon\Carbon::now()->format('d/m/Y H:i');
                    }

                    $listPetugasPengambilSample = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($petugasPengambilSampel ?? []);
                    if (!empty($namaPetugasLogin)) {
                        $listPetugasPengambilSample = [$namaPetugasLogin];
                    } elseif (empty($listPetugasPengambilSample) && isset($verificationActivity[6]) && !empty($verificationActivity[6]->klinik)) {
                        $listPetugasPengambilSample = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames(
                            array_values(array_filter(array_map('trim', preg_split('/,\s*/', $verificationActivity[6]->klinik))))
                        );
                    }
                    $prefillPetugasPengambil = $prefillPetugasPengambil ?? '';
                    if ($prefillPetugasPengambil !== '') {
                        $foundPrefillPetugas = false;
                        foreach ($listPetugasPengambilSample as $namaPetugasItem) {
                            if (strcasecmp(trim((string) $namaPetugasItem), $prefillPetugasPengambil) === 0) {
                                $prefillPetugasPengambil = (string) $namaPetugasItem;
                                $foundPrefillPetugas = true;
                                break;
                            }
                        }
                        if (!$foundPrefillPetugas) {
                            $listPetugasPengambilSample[] = $prefillPetugasPengambil;
                        }
                    }

                    $defaultJamVerificationStep = $defaultJamPengambilSample;
                    $formatJamInlineInput = function ($record) {
                        if (!$record || empty($record->start_date)) {
                            return '';
                        }
                        try {
                            return \Carbon\Carbon::parse($record->start_date)->format('d/m/Y H:i');
                        } catch (\Exception $e) {
                            return '';
                        }
                    };

                    $listPetugasPenerimaRow = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($petugasPenerima ?? []);
                    if (!empty($namaPetugasLogin)) {
                        $listPetugasPenerimaRow = [$namaPetugasLogin];
                    } elseif (empty($listPetugasPenerimaRow) && isset($verificationActivity[7]) && !empty($verificationActivity[7]->klinik)) {
                        $listPetugasPenerimaRow = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames(
                            array_values(array_filter(array_map('trim', preg_split('/,\s*/', $verificationActivity[7]->klinik))))
                        );
                    }

                    $listPetugasPemeriksaRow = [];
                    if (!empty($namaPetugasLogin)) {
                        $listPetugasPemeriksaRow = [$namaPetugasLogin];
                    } elseif (isset($verificationActivity[2]) && !empty($verificationActivity[2]->klinik)) {
                        $listPetugasPemeriksaRow = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames(
                            array_values(array_filter(array_map('trim', preg_split('/,\s*/', $verificationActivity[2]->klinik))))
                        );
                    }

                    $listPetugasVerifikasiRow = [];
                    if (!empty($namaPetugasLogin)) {
                        $listPetugasVerifikasiRow = [$namaPetugasLogin];
                    } elseif (isset($verificationActivity[3]) && !empty($verificationActivity[3]->klinik)) {
                        $listPetugasVerifikasiRow = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames(
                            array_values(array_filter(array_map('trim', preg_split('/,\s*/', $verificationActivity[3]->klinik))))
                        );
                    } elseif (!empty($listPetugasPemeriksaRow)) {
                        $listPetugasVerifikasiRow = $listPetugasPemeriksaRow;
                    }

                    $listPetugasValidasiRow = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::uniquePetugasNames($petugasValidator ?? []);
                    if (!empty($namaPetugasLogin)) {
                        $listPetugasValidasiRow = [$namaPetugasLogin];
                    }

                    $penerimaHref = route('elits-permohonan-uji-klinik-2.create-penerima-sampel', $item->id_permohonan_uji_klinik);
                    $defaultJamFromPreviousStep = function ($previousRecord) use ($anchorRegVerifikasi) {
                        if (!$previousRecord || empty($previousRecord->start_date)) {
                            return '';
                        }
                        try {
                            $prev = \Carbon\Carbon::parse($previousRecord->start_date)->copy()->addMinute();
                            return $anchorRegVerifikasi
                                ->copy()
                                ->setTime((int) $prev->format('H'), (int) $prev->format('i'), 0)
                                ->format('d/m/Y H:i');
                        } catch (\Exception $e) {
                            return '';
                        }
                    };
                    $defaultJamPenerima = !isset($listVerifications[7]) ? $defaultJamFromPreviousStep($listVerifications[6] ?? null) : '';
                    $defaultJamPengolah = !isset($listVerifications[2]) ? $defaultJamFromPreviousStep($listVerifications[7] ?? null) : '';
                    $defaultJamPemeriksa = !isset($listVerifications[3]) ? $defaultJamFromPreviousStep($listVerifications[2] ?? null) : '';
                    $defaultJamVerifikasi = !isset($listVerifications[4]) ? $defaultJamFromPreviousStep($listVerifications[3] ?? null) : '';
                    $defaultJamValidasi = !isset($listVerifications[5]) ? $defaultJamFromPreviousStep($listVerifications[4] ?? null) : '';
                    $penerimaEnabled = $isUrineOnlySample || $isDibawaPelanggan || (isset($listVerifications[6]) && $listVerifications[6]->is_done == 1);
                    $penerimaJam = isset($listVerifications[7]) ? $formatJamInlineInput($listVerifications[7]) : '';
                    if ($penerimaJam === '') {
                        $penerimaJam = $defaultJamPenerima;
                    }
                    $penerimaPetugas = isset($listVerifications[7]) ? ($listVerifications[7]->nama_petugas ?? '') : '';

                    $pemeriksaHref = route('elits-permohonan-uji-klinik-2.create-permohonan-uji-analis2', $item->id_permohonan_uji_klinik);
                    $pemeriksaEnabled = isset($listVerifications[2]) && $listVerifications[2]->is_done == 1;
                    $pemeriksaJam = isset($listVerifications[3]) ? $formatJamInlineInput($listVerifications[3]) : '';
                    if ($pemeriksaJam === '') {
                        $pemeriksaJam = $defaultJamPemeriksa;
                    }
                    $pemeriksaPetugas = isset($listVerifications[3]) ? ($listVerifications[3]->nama_petugas ?? '') : '';

                    $verifikasiHref = route('elits-permohonan-uji-klinik-2.verification-permohonan-uji-paramater-klinik', $item->id_permohonan_uji_klinik);
                    $verifikasiEnabled = isset($listVerifications[3]) && $listVerifications[3]->is_done == 1;
                    $verifikasiJam = isset($listVerifications[4]) ? $formatJamInlineInput($listVerifications[4]) : '';
                    if ($verifikasiJam === '') {
                        $verifikasiJam = $defaultJamVerifikasi;
                    }
                    $verifikasiPetugas = isset($listVerifications[4]) ? ($listVerifications[4]->nama_petugas ?? '') : '';

                    $validasiHref = route('elits-permohonan-uji-klinik-2.disabled-permohonan-uji-analis2', $item->id_permohonan_uji_klinik);
                    $validasiEnabled = isset($listVerifications[4]) && $listVerifications[4]->is_done == 1;
                    $validasiJam = isset($listVerifications[5]) ? $formatJamInlineInput($listVerifications[5]) : '';
                    if ($validasiJam === '') {
                        $validasiJam = $defaultJamValidasi;
                    }
                    $validasiPetugas = isset($listVerifications[5]) ? ($listVerifications[5]->nama_petugas ?? '') : '';
                @endphp

                @if (!$hasPemeriksaan)
                    <div class="alert alert-warning mb-3">
                        <i class="fa fa-exclamation-triangle mr-2"></i>
                        <strong>Peringatan:</strong> Belum ada pemeriksaan yang ditambahkan. Silakan tambahkan pemeriksaan terlebih dahulu sebelum melakukan action di tabel.
                    </div>
                @endif

                <table class="table table-bordered @if(!$hasPemeriksaan) table-no-pemeriksaan @endif">
                    <thead>
                        <tr>
                            <th scope="col" class="border border-primary">Jenis Kegiatan Lab Klinik</th>
                            <th scope="col" class="border border-primary">Jam</th>
                            <th scope="col" class="border border-primary">Nama Petugas</th>
                            <th scope="col" class="text-center border border-primary">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- IF: ROLE = SOLAB or ADMIN (blok verifikasi lengkap) --}}
                        @if (Auth::user()->getlevel->level == 'SOLAB')


                            <tr id="sample">
                                <th scope="row">Pengambil Sample</th>

                                @if ($isDibawaPelanggan)
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-dibawa-pelanggan-skip-cells')
                                @elseif ($isUrineOnlySample)
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-urine-skip-cells')
                                @elseif (isset($listVerifications[6]) && $listVerifications[6]->is_done == 1)
                                    <td>{{ $formatJamVerifikasi($listVerifications[6]) }}</td>
                                    <td class="d-none">{{ $formatJamVerifikasi($listVerifications[6]) }}</td>
                                    <td>{{ $formatNamaPetugasVerifikasi($listVerifications[6]) }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-sample" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @php
                                        $jamValueInline = $defaultJamPengambilSample;
                                        $selectedPetugasInline = $prefillPetugasPengambil ?? '';
                                        if (isset($listVerifications[6]) && !empty($listVerifications[6]->start_date)) {
                                            try {
                                                $jamValueInline = \Carbon\Carbon::parse($listVerifications[6]->start_date)->format('d/m/Y H:i');
                                            } catch (\Exception $e) {
                                            }
                                        }
                                    @endphp
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-inline-row', [
                                        'jamValue' => $jamValueInline,
                                        'selectedPetugas' => $selectedPetugasInline,
                                        'inputIdSuffix' => '',
                                        'sampleCount' => 1,
                                    ])
                                @endif
                            </tr>
                            <tr id="sample-update" style="display:none;">
                                <th scope="row">Pengambil Sample</th>
                                @php
                                    $jamValueInlineUpdate = $defaultJamPengambilSample ?? '';
                                    $selectedPetugasInlineUpdate = $prefillPetugasPengambil ?? '';
                                    if (isset($listVerifications[6]) && !empty($listVerifications[6]->start_date)) {
                                        try {
                                            $jamValueInlineUpdate = \Carbon\Carbon::parse($listVerifications[6]->start_date)->format('d/m/Y H:i');
                                        } catch (\Exception $e) {
                                        }
                                        $selectedPetugasInlineUpdate = $listVerifications[6]->nama_petugas ?? $selectedPetugasInlineUpdate;
                                    }
                                    if ($selectedPetugasInlineUpdate !== '') {
                                        $selectedPetugasInlineUpdate = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::resolvePetugasCanonicalName($selectedPetugasInlineUpdate);
                                        $matchedUpdate = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::findMatchingPetugasName($selectedPetugasInlineUpdate, $listPetugasPengambilSample);
                                        if ($matchedUpdate !== null) {
                                            $selectedPetugasInlineUpdate = $matchedUpdate;
                                        } elseif (!in_array($selectedPetugasInlineUpdate, $listPetugasPengambilSample, true)) {
                                            $listPetugasPengambilSample[] = $selectedPetugasInlineUpdate;
                                        }
                                    }
                                @endphp
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-inline-row', [
                                    'jamValue' => $jamValueInlineUpdate,
                                    'selectedPetugas' => $selectedPetugasInlineUpdate,
                                    'inputIdSuffix' => '_update',
                                    'sampleCount' => 1,
                                ])
                            </tr>

                            @if (isset($listVerifications[6]) && isset($pengambilanSampleKlinik->status_sampling))

                                @if ($pengambilanSampleKlinik->status_sampling == 'Gagal' && $listVerifications[6]->is_done == 1)


                                    @if (count($resampleSamples) > 0)


                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($resampleSamples as $sample)
                                            <tr id="sample-{{ $i }}">

                                                <th scope="row">Pengambil Sample (ulang ke-{{ $i }})</th>

                                                @if (isset($sample))
                                                    <td>{{ \Carbon\Carbon::parse($sample->start_date)->isoFormat('HH:mm') }}
                                                    </td>
                                                    <td class="d-none">
                                                        {{ \Carbon\Carbon::parse($sample->stop_date)->isoFormat('HH:mm') }}
                                                    </td>
                                                    <td>{{ $sample->nama_petugas }}</td>
                                                    @if ($sample->is_done == 0)
                                                        <td class="text-center">
                                                            @if($hasPemeriksaan)
                                                                <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 1)) }}">
                                                                    <button type="button" class="btn btn-primary">Input</button>
                                                                </a>
                                                            @else
                                                                <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                            @endif
                                                            <button type="button" class="btn btn-warning"
                                                                data-toggle="modal" data-sampling="{{ $i }}"
                                                                data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                    @else
                                                        <td class="text-center">
                                                            <svg id="toggle-sample-{{ $i }}"
                                                                xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                                width="48" height="48" viewBox="0 0 48 48">
                                                                <path fill="#c8e6c9"
                                                                    d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                                                </path>
                                                                <path fill="#4caf50"
                                                                    d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                                                </path>
                                                            </svg>
                                                        </td>
                                                    @endif
                                                @else
                                                    <td>-</td>
                                                    <td class="d-none"></td>
                                                    <td>-</td>
                                                    <td class="text-center">
                                                        @if($hasPemeriksaan)
                                                            <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 1)) }}">
                                                                <button type="button" class="btn btn-primary">Input</button>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                        @endif
                                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                                            data-sampling="{{ $i }}" data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                    </td>
                                                @endif
                                            </tr>
                                            <tr id="sample-update-{{ $i }}" style="display:none;">
                                                <th scope="row">Pengambil Sample (ulang ke-{{ $i }})</th>
                                                <td>{{ isset($sample->start_date) ? \Carbon\Carbon::parse($sample->start_date)->isoFormat('HH:mm') : '' }}</td>
                                                <td class="d-none"></td>
                                                <td>{{ $sample->nama_petugas ?? '-' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 1)) }}">
                                                        <button type="button" class="btn btn-primary">Input</button>
                                                    </a>
                                                    <button type="button" class="btn btn-warning" data-toggle="modal"
                                                        data-target="#signatureSampleModal">Tanda Tangan</button>
                                                </td>
                                            </tr>

                                            <script>
                                                (function() {
                                                    var sampleIndex = {{ $i }};
                                                    var toggleBtn = document.getElementById('toggle-sample-' + sampleIndex);

                                                    if (!toggleBtn) return;

                                                    // Store flatpickr instances untuk index ini
                                                    if (typeof window.flatpickrInstances === 'undefined') {
                                                        window.flatpickrInstances = {};
                                                    }

                                                    toggleBtn.addEventListener('click', function() {
                                                        var sampleRow = document.getElementById('sample-' + sampleIndex);
                                                        var sampleUpdateRow = document.getElementById('sample-update-' + sampleIndex);

                                                        if (sampleRow.style.display === 'none') {
                                                            sampleRow.style.display = '';
                                                            sampleUpdateRow.style.display = 'none';
                                                        } else {
                                                            sampleRow.style.display = 'none';
                                                            sampleUpdateRow.style.display = '';

                                                            // Inisialisasi flatpickr hanya sekali
                                                            var instanceKey = 'sample_update_' + sampleIndex;
                                                            if (!window.flatpickrInstances[instanceKey + '_start']) {
                                                                var sampleUpdateRowStart = $('#sample-update-' + sampleIndex + ' [name="start_date"]')
                                                                    .val();

                                                                window.flatpickrInstances[instanceKey + '_start'] = flatpickr('#sample-update-' +
                                                                    sampleIndex + ' [name="start_date"]', {
                                                                        allowInput: true,
                                                                        enableTime: true,
                                                                        noCalendar: true,
                                                                        dateFormat: "H:i",
                                                                        time_24hr: true
                                                                    });

                                                                // Set initial value if exists
                                                                if (sampleUpdateRowStart && /^\d{1,2}:\d{2}$/.test(sampleUpdateRowStart)) {
                                                                    window.flatpickrInstances[instanceKey + '_start'].setDate(sampleUpdateRowStart,
                                                                        false, 'H:i');
                                                                }

                                                                $('#sample-update-' + sampleIndex + ' [name="start_date"]').inputmask('99:99', {
                                                                    placeholder: 'hh:mm'
                                                                });
                                                            }

                                                            if (!window.flatpickrInstances[instanceKey + '_stop']) {
                                                                var sampleUpdateRowStop = $('#sample-update-' + sampleIndex + ' [name="stop_date"]')
                                                                    .val();

                                                                window.flatpickrInstances[instanceKey + '_stop'] = flatpickr('#sample-update-' +
                                                                    sampleIndex + ' [name="stop_date"]', {
                                                                        allowInput: true,
                                                                        locale: "id",
                                                                        enableTime: true,
                                                                        noCalendar: true,
                                                                        dateFormat: "H:i",
                                                                        time_24hr: true
                                                                    });

                                                                if (sampleUpdateRowStop && /^\d{1,2}:\d{2}$/.test(sampleUpdateRowStop)) {
                                                                    window.flatpickrInstances[instanceKey + '_stop'].setDate(sampleUpdateRowStop,
                                                                        false, 'H:i');
                                                                }

                                                                $('#sample-update-' + sampleIndex + ' [name="stop_date"]').inputmask('99:99', {
                                                                    placeholder: 'hh:mm'
                                                                });
                                                            }
                                                        }
                                                    });
                                                })
                                                ();
                                            </script>


                                            {{-- Tampilkan form baru untuk sampling ulang berikutnya jika sampling terakhir gagal --}}
                                            @php
                                                $lastIndex = count($resampleSamples) - 1;
                                                $lastSample = $resampleSamples[$lastIndex] ?? null;
                                                $lastPengambilanSample =
                                                    $pengambilanSampleUlangKlinik[$lastIndex] ?? null;
                                            @endphp
                                            @if (
                                                $lastIndex == $i - 1 &&
                                                    $lastPengambilanSample &&
                                                    $lastPengambilanSample->status_sampling == 'Gagal' &&
                                                    $lastSample->is_done == 1)
                                                <tr id="sample-{{ $i + 1 }}">
                                                    <th scope="row">Pengambil Sample (ulang {{ $i + 1 }})</th>
                                                    <td>-</td>
                                                    <td class="d-none"></td>
                                                    <td>-</td>
                                                    <td class="text-center">
                                                        @if($hasPemeriksaan)
                                                            <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 2)) }}">
                                                                <button type="button" class="btn btn-primary">Input</button>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                        @endif
                                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                                            data-sampling="{{ $i + 1 }}"
                                                            data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                    </td>
                                                </tr>
                                            @endif


                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr id="sample">
                                            <th scope="row">Pengambil Sample (ulang)</th>

                                            @if (isset($resampleSamples[0]))
                                                <td>{{ \Carbon\Carbon::parse($resampleSamples[0]->start_date)->isoFormat('HH:mm') }}
                                                </td>
                                                <td class="d-none">
                                                    {{ \Carbon\Carbon::parse($resampleSamples[0]->stop_date)->isoFormat('HH:mm') }}
                                                </td>
                                                <td>{{ $resampleSamples[0]->nama_petugas }}</td>
                                                @if ($resampleSamples[0]->is_done == 0)
                                                    <td class="text-center">
                                                        @if($hasPemeriksaan)
                                                            <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/2') }}">
                                                                <button type="button" class="btn btn-primary">Input</button>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                        @endif
                                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                                            data-sampling="1" data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                @else
                                                    <td class="text-center">
                                                        <svg id="toggle-sample" xmlns="http://www.w3.org/2000/svg" x="0px"
                                                            y="0px" width="48" height="48" viewBox="0 0 48 48">
                                                            <path fill="#c8e6c9"
                                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                                            </path>
                                                            <path fill="#4caf50"
                                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                                            </path>
                                                        </svg>
                                                    </td>
                                                @endif
                                            @else
                                                <td>-</td>
                                                <td class="d-none"></td>
                                                <td>-</td>
                                                <td class="text-center">
                                                    @if($hasPemeriksaan)
                                                        <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/2') }}">
                                                            <button type="button" class="btn btn-primary">Input</button>
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                    @endif
                                                    <button type="button" class="btn btn-warning" data-toggle="modal"
                                                        data-sampling="1" data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                </td>
                                            @endif
                                        </tr>
                                        <tr id="sample-update" style="display:none;">
                                            <th scope="row">Pengambil Sample</th>
                                            @php
                                                $jamResampleUpdate = isset($resampleSamples[0]->start_date)
                                                    ? \Carbon\Carbon::parse($resampleSamples[0]->start_date)->format('d/m/Y H:i')
                                                    : ($defaultJamPengambilSample ?? '');
                                                $petugasResampleUpdate = $resampleSamples[0]->nama_petugas ?? ($prefillPetugasPengambil ?? '');
                                            @endphp
                                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-inline-row', [
                                                'jamValue' => $jamResampleUpdate,
                                                'selectedPetugas' => $petugasResampleUpdate,
                                                'inputIdSuffix' => '_resample_update',
                                                'sampleCount' => 2,
                                            ])
                                        </tr>
                                    @endif

                                @endif
                            @endif
                        @elseif(Auth::user()->getlevel->level != 'DKTR' &&
                                Auth::user()->getlevel->level != 'SOLAB' &&
                                Auth::user()->getlevel->level != 'ANLS'
                                &&
                                Auth::user()->getlevel->level != 'ALAB')
                            <tr id="registrasi">
                                <th scope="row">Pendaftaran / Registrasi</th>
                                @if (isset($listVerifications[1]))
                                    <td>{{ $formatJamVerifikasi($listVerifications[1]) }}</td>
                                    <td class="d-none">
                                        {{ \Carbon\Carbon::parse($listVerifications[1]->stop_date)->format('H:i') }}</td>
                                    <td>{{ $listVerifications[1]->nama_petugas }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-registrasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @endif
                            </tr>
                            <tr id="registrasi-update" style="display: none;">
                                <th scope="row">Pendaftaran / Registrasi</th>
                                <form
                                    action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                    method="post" class="formRegistrasi">
                                    @csrf
                                    <input type="hidden" name="is_selesai" value="1">
                                    <td>
                                        <input type="number" value="{{ 1 }}" name="verification_step" hidden>
                                        <input type="text" value="{{ $listVerifications[1]->nama_petugas }}"
                                            name="nama_petugas" hidden>
                                        <input type="text" class="form-control" name="start_date"
                                            value="{{ isset($listVerifications[1]->start_date) ? \Carbon\Carbon::parse($listVerifications[1]->start_date)->format('H:i') : '' }}"
                                            data-base-date="{{ isset($listVerifications[1]->start_date) ? \Carbon\Carbon::parse($listVerifications[1]->start_date)->format('Y-m-d') : '' }}"
                                            placeholder="HH:mm" required></td>
                                    <td class="d-none"><input type="hidden" class="form-control" name="stop_date"
                                            value="{{ isset($listVerifications[1]->stop_date) ? \Carbon\Carbon::parse($listVerifications[1]->stop_date)->format('H:i') : '' }}"
                                            data-base-date="{{ isset($listVerifications[1]->stop_date) ? \Carbon\Carbon::parse($listVerifications[1]->stop_date)->format('Y-m-d') : '' }}">
                                    </td>
                                    <td>
                                        <select name="nama_petugas" id="namaPetugasRegistrasi" required>
                                            @foreach (['PETUGAS DUMMY 10', 'PETUGAS DUMMY 01', 'PETUGAS DUMMY 08', 'PETUGAS DUMMY 09'] as $nama_petugas)
                                                <option value="{{ $nama_petugas }}"
                                                    {{ isset($listVerifications[1]->nama_petugas) && $listVerifications[1]->nama_petugas == $nama_petugas
                                                        ? 'selected'
                                                        : '' }}>
                                                    {{ $nama_petugas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <button type="submit" class="btn btn-success"
                                            @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasRegistrasi').value, 'formRegistrasi')" @endif>Selesai</button>
                                    </td>
                                </form>
                            </tr>

                            <tr id="sample">
                                <th scope="row">Pengambil Sample</th>

                                @if ($isDibawaPelanggan)
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-dibawa-pelanggan-skip-cells')
                                @elseif ($isUrineOnlySample)
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-urine-skip-cells')
                                @elseif (isset($listVerifications[6]) && $listVerifications[6]->is_done == 1)
                                    <td>{{ $formatJamVerifikasi($listVerifications[6]) }}</td>
                                    <td class="d-none">{{ $formatJamVerifikasi($listVerifications[6]) }}</td>
                                    <td>{{ $formatNamaPetugasVerifikasi($listVerifications[6]) }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-sample" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @php
                                        $jamValueInline = $defaultJamPengambilSample;
                                        $selectedPetugasInline = $prefillPetugasPengambil ?? '';
                                        if (isset($listVerifications[6]) && !empty($listVerifications[6]->start_date)) {
                                            try {
                                                $jamValueInline = \Carbon\Carbon::parse($listVerifications[6]->start_date)->format('d/m/Y H:i');
                                            } catch (\Exception $e) {
                                            }
                                        }
                                    @endphp
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-inline-row', [
                                        'jamValue' => $jamValueInline,
                                        'selectedPetugas' => $selectedPetugasInline,
                                        'inputIdSuffix' => '_admin',
                                        'sampleCount' => 1,
                                    ])
                                @endif
                            </tr>
                            <tr id="sample-update" style="display:none;">
                                <th scope="row">Pengambil Sample</th>
                                @php
                                    $jamValueInlineUpdateAdmin = $defaultJamPengambilSample ?? '';
                                    $selectedPetugasInlineUpdateAdmin = $prefillPetugasPengambil ?? '';
                                    if (isset($listVerifications[6]) && !empty($listVerifications[6]->start_date)) {
                                        try {
                                            $jamValueInlineUpdateAdmin = \Carbon\Carbon::parse($listVerifications[6]->start_date)->format('d/m/Y H:i');
                                        } catch (\Exception $e) {
                                        }
                                        $selectedPetugasInlineUpdateAdmin = $listVerifications[6]->nama_petugas ?? $selectedPetugasInlineUpdateAdmin;
                                    }
                                    if ($selectedPetugasInlineUpdateAdmin !== '') {
                                        $selectedPetugasInlineUpdateAdmin = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::resolvePetugasCanonicalName($selectedPetugasInlineUpdateAdmin);
                                        $matchedUpdateAdmin = \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::findMatchingPetugasName($selectedPetugasInlineUpdateAdmin, $listPetugasPengambilSample);
                                        if ($matchedUpdateAdmin !== null) {
                                            $selectedPetugasInlineUpdateAdmin = $matchedUpdateAdmin;
                                        } elseif (!in_array($selectedPetugasInlineUpdateAdmin, $listPetugasPengambilSample, true)) {
                                            $listPetugasPengambilSample[] = $selectedPetugasInlineUpdateAdmin;
                                        }
                                    }
                                @endphp
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-inline-row', [
                                    'jamValue' => $jamValueInlineUpdateAdmin,
                                    'selectedPetugas' => $selectedPetugasInlineUpdateAdmin,
                                    'inputIdSuffix' => '_admin_update',
                                    'sampleCount' => 1,
                                ])
                            </tr>


                            @if (isset($listVerifications[6]) && isset($pengambilanSampleKlinik->status_sampling))

                                @if ($pengambilanSampleKlinik->status_sampling == 'Gagal' && $listVerifications[6]->is_done == 1)

                                    @if (count($resampleSamples) > 0)


                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($resampleSamples as $sample)
                                            <tr id="sample-{{ $i }}">
                                                <th scope="row">Pengambil Sample (ulang ke-{{ $i }})</th>

                                                @if (isset($sample))
                                                    <td>{{ \Carbon\Carbon::parse($sample->start_date)->isoFormat('HH:mm') }}
                                                    </td>
                                                    <td class="d-none">
                                                        {{ \Carbon\Carbon::parse($sample->stop_date)->isoFormat('HH:mm') }}
                                                    </td>
                                                    <td>{{ $sample->nama_petugas }}</td>
                                                    @if ($sample->is_done == 0)
                                                        <td class="text-center">
                                                            @if($hasPemeriksaan)
                                                                <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 1)) }}">
                                                                    <button type="button" class="btn btn-primary">Input</button>
                                                                </a>
                                                            @else
                                                                <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                            @endif
                                                            <button type="button" class="btn btn-warning"
                                                                data-toggle="modal" data-sampling="{{ $i }}"
                                                                data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                    @else
                                                        <td class="text-center">
                                                            <svg id="toggle-sample-{{ $i }}"
                                                                xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                                width="48" height="48" viewBox="0 0 48 48">
                                                                <path fill="#c8e6c9"
                                                                    d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                                                </path>
                                                                <path fill="#4caf50"
                                                                    d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                                                </path>
                                                            </svg>
                                                        </td>
                                                    @endif
                                                @else
                                                    <td>-</td>
                                                    <td class="d-none"></td>
                                                    <td>-</td>
                                                    <td class="text-center">
                                                        @if($hasPemeriksaan)
                                                            <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 1)) }}">
                                                                <button type="button" class="btn btn-primary">Input</button>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                        @endif
                                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                                            data-sampling="{{ $i }}" data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                    </td>
                                                @endif
                                            </tr>
                                            <tr id="sample-update-{{ $i }}" style="display:none;">
                                                <th scope="row">Pengambil Sample (ulang ke-{{ $i }})</th>
                                                <td>{{ isset($sample->start_date) ? \Carbon\Carbon::parse($sample->start_date)->isoFormat('HH:mm') : '' }}</td>
                                                <td class="d-none"></td>
                                                <td>{{ $sample->nama_petugas ?? '-' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 1)) }}">
                                                        <button type="button" class="btn btn-primary">Input</button>
                                                    </a>
                                                    <button type="button" class="btn btn-warning" data-toggle="modal"
                                                        data-target="#signatureSampleModal">Tanda Tangan</button>
                                                </td>
                                            </tr>

                                            <script>
                                                (function() {
                                                    var sampleIndex = {{ $i }};
                                                    var toggleBtn = document.getElementById('toggle-sample-' + sampleIndex);

                                                    if (!toggleBtn) return;

                                                    // Store flatpickr instances untuk index ini
                                                    if (typeof window.flatpickrInstances === 'undefined') {
                                                        window.flatpickrInstances = {};
                                                    }

                                                    toggleBtn.addEventListener('click', function() {
                                                        var sampleRow = document.getElementById('sample-' + sampleIndex);
                                                        var sampleUpdateRow = document.getElementById('sample-update-' + sampleIndex);

                                                        if (sampleRow.style.display === 'none') {
                                                            sampleRow.style.display = '';
                                                            sampleUpdateRow.style.display = 'none';
                                                        } else {
                                                            sampleRow.style.display = 'none';
                                                            sampleUpdateRow.style.display = '';

                                                            // Inisialisasi flatpickr hanya sekali
                                                            var instanceKey = 'sample_update_' + sampleIndex;
                                                            if (!window.flatpickrInstances[instanceKey + '_start']) {
                                                                var sampleUpdateRowStart = $('#sample-update-' + sampleIndex + ' [name="start_date"]')
                                                                    .val();

                                                                window.flatpickrInstances[instanceKey + '_start'] = flatpickr('#sample-update-' +
                                                                    sampleIndex + ' [name="start_date"]', {
                                                                        allowInput: true,
                                                                        enableTime: true,
                                                                        noCalendar: true,
                                                                        dateFormat: "H:i",
                                                                        time_24hr: true
                                                                    });

                                                                // Set initial value if exists
                                                                if (sampleUpdateRowStart && /^\d{1,2}:\d{2}$/.test(sampleUpdateRowStart)) {
                                                                    window.flatpickrInstances[instanceKey + '_start'].setDate(sampleUpdateRowStart,
                                                                        false, 'H:i');
                                                                }

                                                                $('#sample-update-' + sampleIndex + ' [name="start_date"]').inputmask('99:99', {
                                                                    placeholder: 'hh:mm'
                                                                });
                                                            }

                                                            if (!window.flatpickrInstances[instanceKey + '_stop']) {
                                                                var sampleUpdateRowStop = $('#sample-update-' + sampleIndex + ' [name="stop_date"]')
                                                                    .val();

                                                                window.flatpickrInstances[instanceKey + '_stop'] = flatpickr('#sample-update-' +
                                                                    sampleIndex + ' [name="stop_date"]', {
                                                                        allowInput: true,
                                                                        locale: "id",
                                                                        enableTime: true,
                                                                        noCalendar: true,
                                                                        dateFormat: "H:i",
                                                                        time_24hr: true
                                                                    });

                                                                if (sampleUpdateRowStop && /^\d{1,2}:\d{2}$/.test(sampleUpdateRowStop)) {
                                                                    window.flatpickrInstances[instanceKey + '_stop'].setDate(sampleUpdateRowStop,
                                                                        false, 'H:i');
                                                                }

                                                                $('#sample-update-' + sampleIndex + ' [name="stop_date"]').inputmask('99:99', {
                                                                    placeholder: 'hh:mm'
                                                                });
                                                            }
                                                        }
                                                    });
                                                })
                                                ();
                                            </script>


                                            {{-- Tampilkan form baru untuk sampling ulang berikutnya jika sampling terakhir gagal --}}
                                            @php
                                                $lastIndex = count($resampleSamples) - 1;
                                                $lastSample = $resampleSamples[$lastIndex] ?? null;
                                                $lastPengambilanSample =
                                                    $pengambilanSampleUlangKlinik[$lastIndex] ?? null;
                                            @endphp
                                            @if (
                                                $lastIndex == $i - 1 &&
                                                    $lastPengambilanSample &&
                                                    $lastPengambilanSample->status_sampling == 'Gagal' &&
                                                    $lastSample->is_done == 1)
                                                <tr id="sample-{{ $i + 1 }}">
                                                    <th scope="row">Pengambil Sample (ulang {{ $i + 1 }})</th>
                                                    <td>-</td>
                                                    <td class="d-none"></td>
                                                    <td>-</td>
                                                    <td class="text-center">
                                                        @if($hasPemeriksaan)
                                                            <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/' . ($i + 2)) }}">
                                                                <button type="button" class="btn btn-primary">Input</button>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                        @endif
                                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                                            data-sampling="{{ $i + 1 }}"
                                                            data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                    </td>
                                                </tr>
                                            @endif


                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr id="sample">
                                            <th scope="row">Pengambil Sample (ulang)</th>

                                            @if (isset($resampleSamples[0]))
                                                <td>{{ \Carbon\Carbon::parse($resampleSamples[0]->start_date)->isoFormat('HH:mm') }}
                                                </td>
                                                <td class="d-none">
                                                    {{ \Carbon\Carbon::parse($resampleSamples[0]->stop_date)->isoFormat('HH:mm') }}
                                                </td>
                                                <td>{{ $resampleSamples[0]->nama_petugas }}</td>
                                                @if ($resampleSamples[0]->is_done == 0)
                                                    <td class="text-center">
                                                        @if($hasPemeriksaan)
                                                            <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/2') }}">
                                                                <button type="button" class="btn btn-primary">Input</button>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                        @endif
                                                        <button type="button" class="btn btn-warning" data-toggle="modal"
                                                            data-sampling="1" data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                @else
                                                    <td class="text-center">
                                                        <svg id="toggle-sample" xmlns="http://www.w3.org/2000/svg" x="0px"
                                                            y="0px" width="48" height="48" viewBox="0 0 48 48">
                                                            <path fill="#c8e6c9"
                                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                                            </path>
                                                            <path fill="#4caf50"
                                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                                            </path>
                                                        </svg>
                                                    </td>
                                                @endif
                                            @else
                                                <td>-</td>
                                                <td class="d-none"></td>
                                                <td>-</td>
                                                <td class="text-center">
                                                    @if($hasPemeriksaan)
                                                        <a href="{{ route('elits-permohonan-uji-klinik-2.create-permohonan-uji-sample', $item->id_permohonan_uji_klinik . '/2') }}">
                                                            <button type="button" class="btn btn-primary">Input</button>
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-primary" disabled title="Belum ada pemeriksaan">Input</button>
                                                    @endif
                                                    <button type="button" class="btn btn-warning" data-toggle="modal"
                                                        data-sampling="1" data-target="#signatureSampleModal" @if(!$hasPemeriksaan) disabled title="Belum ada pemeriksaan" @endif>Tanda Tangan</button>
                                                </td>
                                            @endif
                                        </tr>
                                        <tr id="sample-update" style="display:none;">
                                            <th scope="row">Pengambil Sample</th>
                                            @php
                                                $jamResampleUpdateAdmin = isset($resampleSamples[0]->start_date)
                                                    ? \Carbon\Carbon::parse($resampleSamples[0]->start_date)->format('d/m/Y H:i')
                                                    : ($defaultJamPengambilSample ?? '');
                                                $petugasResampleUpdateAdmin = $resampleSamples[0]->nama_petugas ?? ($prefillPetugasPengambil ?? '');
                                            @endphp
                                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.pengambil-sample-inline-row', [
                                                'jamValue' => $jamResampleUpdateAdmin,
                                                'selectedPetugas' => $petugasResampleUpdateAdmin,
                                                'inputIdSuffix' => '_admin_resample_update',
                                                'sampleCount' => 2,
                                            ])
                                        </tr>
                                    @endif

                                @endif
                            @endif
                            <tr id="penerima-sampel">
                                <th scope="row">Penerima Sampel</th>
                                @if (isset($listVerifications[7]) && $listVerifications[7]->is_done == 1)
                                    <td>{{ $formatJamVerifikasi($listVerifications[7]) }}</td>
                                    <td class="d-none">{{ $formatJamVerifikasi($listVerifications[7]) }}</td>
                                    <td>{{ $formatNamaPetugasVerifikasi($listVerifications[7]) }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-penerima-sampel" xmlns="http://www.w3.org/2000/svg" x="0px"
                                            y="0px" width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                        'stepKey' => 'penerima',
                                        'suffix' => '',
                                        'jamValue' => $penerimaJam,
                                        'selectedPetugas' => $penerimaPetugas,
                                        'listPetugas' => $listPetugasPenerimaRow,
                                        'baseHref' => $penerimaHref,
                                        'buttonLabel' => 'Input',
                                        'actionEnabled' => $penerimaEnabled,
                                        'disabledTitle' => 'Pengambilan sampel belum selesai',
                                    ])
                                @endif
                            </tr>
                            <tr id="penerima-sampel-update" style="display:none;">
                                <th scope="row">Penerima Sampel</th>
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                    'stepKey' => 'penerima',
                                    'suffix' => '_update',
                                    'jamValue' => $penerimaJam ?? '',
                                    'selectedPetugas' => $penerimaPetugas ?? '',
                                    'listPetugas' => $listPetugasPenerimaRow,
                                    'baseHref' => $penerimaHref ?? route('elits-permohonan-uji-klinik-2.create-penerima-sampel', $item->id_permohonan_uji_klinik),
                                    'buttonLabel' => 'Input',
                                    'actionEnabled' => true,
                                ])
                            </tr>


                            <tr id="analitik">
                                <th scope="row">Pengolah Sampel</th>
                                @if (isset($listVerifications[2]))
                                    <td>{{ $formatJamVerifikasi($listVerifications[2]) }}</td>
                                    <td class="d-none">
                                        {{ $formatJamVerifikasi($listVerifications[2]) }}</td>
                                    <td>{{ $listVerifications[2]->nama_petugas }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-analitik" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @if (!isset($listVerifications[7]) || (isset($listVerifications[7]) && $listVerifications[7]->is_done == 0))
                                        <form
                                        action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                        method="post" class="formAnalitik">
                                            @csrf
                                            <input type="hidden" name="is_selesai" value="1">

                                            <td>
                                            <input type="number" value="{{ 2 }}" name="verification_step"
                                            hidden>
                                                <input type="text" class="form-control" name="start_date"
                                                    id="start_date_analitik" required disabled></td>
                                            <td class="d-none"><input type="hidden" name="stop_date"
                                                    id="stop_date_analitik" disabled></td>
                                            <td>
                                                <select name="nama_petugas" id="namaPetugasAnalitik" required disabled>
                                                    @php
                                                        if ($namaPetugasLogin !== null) {
                                                            $list_name_petugas = [$namaPetugasLogin];
                                                        } else {
                                                        $list_name_petugas = explode(
                                                            ', ',
                                                            $verificationActivity[2]->klinik,
                                                        );
                                                        }
                                                    @endphp
                                                    @foreach ($list_name_petugas as $nama_petugas)
                                                        <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                @if (isset($listVerifications[6]))
                                                    @if ($listVerifications[6]->is_done == 1)
                                                        <button type="submit" class="btn btn-success js-analitik-step-submit" disabled
                                                            @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasAnalitik').value, 'formAnalitik')" @endif>Selesai</button>
                                                    @else
                                                        <button type="submit" class="btn btn-success" disabled
                                                            disabled>Selesai</button>
                                                    @endif
                                                @else
                                                    <button type="submit" class="btn btn-success" disabled>Selesai</button>
                                                @endif
                                            </td>
                                        </form>
                                    @else
                                        <td>
                                            <form id="formAnalitik"
                                                action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                                method="post" class="formAnalitik" style="display:none;" aria-hidden="true">
                                                @csrf
                                                <input type="hidden" name="is_selesai" value="1">
                                                <input type="hidden" name="verification_step" value="2">
                                            </form>
                                            <input type="text" class="form-control" name="start_date"
                                                id="start_date_analitik" form="formAnalitik" required
                                                value="{{ $defaultJamPengolah }}">
                                        </td>
                                        <td class="d-none"><input type="hidden" name="stop_date"
                                                id="stop_date_analitik" form="formAnalitik"></td>
                                        <td>
                                            <select name="nama_petugas" id="namaPetugasAnalitik" form="formAnalitik" required>
                                                <option value="">-- Pilih Petugas --</option>
                                                @php
                                                    if ($namaPetugasLogin !== null) {
                                                        $list_name_petugas = [$namaPetugasLogin];
                                                    } else {
                                                    $list_name_petugas = explode(
                                                        ', ',
                                                        $verificationActivity[2]->klinik,
                                                    );
                                                    }
                                                @endphp
                                                @foreach ($list_name_petugas as $nama_petugas)
                                                    <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            @if (isset($listVerifications[6]))
                                                @if ($listVerifications[6]->is_done == 1)
                                                    <button type="submit" form="formAnalitik" class="btn btn-success js-analitik-step-submit" disabled
                                                        @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasAnalitik').value, 'formAnalitik')" @endif>Selesai</button>
                                                @else
                                                    <button type="submit" form="formAnalitik" class="btn btn-success"
                                                        disabled>Selesai</button>
                                                @endif
                                            @else
                                                <button type="submit" form="formAnalitik" class="btn btn-success" disabled>Selesai</button>
                                            @endif
                                        </td>
                                    @endif
                                @endif
                            </tr>
                            <tr id="analitik-update" style="display: none;">
                                <th scope="row">Pengolah Sampel</th>
                                <td>
                                    <form id="formAnalitikUpdate"
                                        action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                        method="post" class="formAnalitikUpdate" style="display:none;" aria-hidden="true">
                                        @csrf
                                        <input type="hidden" name="is_selesai" value="1">
                                        <input type="hidden" name="verification_step" value="2">
                                    </form>
                                    <input type="text" class="form-control" name="start_date"
                                        value="{{ isset($listVerifications[2]->start_date) ? \Carbon\Carbon::parse($listVerifications[2]->start_date)->format('H:i') : '' }}"
                                        data-base-date="{{ isset($listVerifications[2]->start_date) ? \Carbon\Carbon::parse($listVerifications[2]->start_date)->format('Y-m-d') : '' }}"
                                        form="formAnalitikUpdate" required>
                                </td>
                                <td class="d-none"><input type="text" class="form-control" name="stop_date"
                                        value="{{ isset($listVerifications[2]->stop_date) ? \Carbon\Carbon::parse($listVerifications[2]->stop_date)->format('H:i') : '' }}"
                                        data-base-date="{{ isset($listVerifications[2]->stop_date) ? \Carbon\Carbon::parse($listVerifications[2]->stop_date)->format('Y-m-d') : '' }}"
                                        form="formAnalitikUpdate">
                                </td>
                                <td>
                                    <select name="nama_petugas" id="namaPetugasAnalitikUpdate" form="formAnalitikUpdate" required>
                                            @php
                                                if ($namaPetugasLogin !== null) {
                                                    $list_name_petugas = [$namaPetugasLogin];
                                                } elseif (isset($verificationActivity[2]) && !empty($verificationActivity[2]->klinik)) {
                                                    $list_name_petugas = array_values(array_filter(array_map('trim', explode(', ', $verificationActivity[2]->klinik))));
                                                } else {
                                                    $list_name_petugas = [];
                                                }
                                                $selectedPengolah = isset($listVerifications[2]) ? trim($listVerifications[2]->nama_petugas ?? '') : '';
                                                if ($selectedPengolah !== '' && !in_array($selectedPengolah, $list_name_petugas, true)) {
                                                    $list_name_petugas[] = $selectedPengolah;
                                                }
                                            @endphp
                                            @foreach ($list_name_petugas as $nama_petugas)
                                                <option value="{{ $nama_petugas }}"
                                                    {{ $selectedPengolah !== '' && $selectedPengolah == $nama_petugas ? 'selected' : '' }}>
                                                    {{ $nama_petugas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        @if (isset($listVerifications[6]))
                                            @if ($listVerifications[6]->is_done == 1)
                                                <button type="submit" form="formAnalitikUpdate" class="btn btn-success js-analitik-step-submit" disabled
                                                    @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasAnalitikUpdate').value, 'formAnalitikUpdate')" @endif>Selesai</button>
                                            @else
                                                <button type="submit" form="formAnalitikUpdate" class="btn btn-success" disabled>Selesai</button>
                                            @endif
                                        @else
                                            <button type="submit" form="formAnalitikUpdate" class="btn btn-success" disabled>Selesai</button>
                                        @endif
                                    </td>
                            </tr>
                            <tr id="hasil-px">
                                <th scope="row">Pemeriksa Sampel</th>
                                @if (isset($listVerifications[3]) && $listVerifications[3]->is_done == 1)
                                    <td>{{ $formatJamVerifikasi($listVerifications[3]) }}</td>
                                    <td class="d-none">{{ $formatJamVerifikasi($listVerifications[3]) }}</td>
                                    <td>{{ $formatNamaPetugasVerifikasi($listVerifications[3]) }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-hasil-px" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                        'stepKey' => 'pemeriksa',
                                        'suffix' => '',
                                        'jamValue' => $pemeriksaJam,
                                        'selectedPetugas' => $pemeriksaPetugas,
                                        'listPetugas' => $listPetugasPemeriksaRow,
                                        'baseHref' => $pemeriksaHref,
                                        'buttonLabel' => 'Input',
                                        'actionEnabled' => $pemeriksaEnabled,
                                        'disabledTitle' => 'Pengolah sampel belum selesai',
                                    ])
                                @endif
                        </tr>
                        <tr id="hasil-px-update" style="display: none;">
                            <th scope="row">Pemeriksa Sampel</th>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                'stepKey' => 'pemeriksa',
                                'suffix' => '_update',
                                'jamValue' => $pemeriksaJam,
                                'selectedPetugas' => $pemeriksaPetugas,
                                'listPetugas' => $listPetugasPemeriksaRow,
                                'baseHref' => $pemeriksaHref,
                                'buttonLabel' => 'Input',
                                'actionEnabled' => true,
                            ])
                        </tr>
                        <tr id="verifikasi">
                            <th scope="row">Verifikasi</th>
                            @if (isset($listVerifications[4]) && $listVerifications[4]->is_done == 1)
                                <td>{{ $formatJamVerifikasi($listVerifications[4]) }}</td>
                                <td class="d-none">{{ $formatJamVerifikasi($listVerifications[4]) }}</td>
                                <td>{{ $formatNamaPetugasVerifikasi($listVerifications[4]) }}</td>
                                <td class="text-center">
                                    <svg id="toggle-verifikasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                        width="48" height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                                        <path fill="#c8e6c9"
                                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                        </path>
                                        <path fill="#4caf50"
                                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                        </path>
                                    </svg>
                                </td>
                            @else
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                    'stepKey' => 'verifikasi',
                                    'suffix' => '',
                                    'jamValue' => $verifikasiJam,
                                    'selectedPetugas' => $verifikasiPetugas,
                                    'listPetugas' => $listPetugasVerifikasiRow,
                                    'baseHref' => $verifikasiHref,
                                    'buttonLabel' => 'Verifikasi',
                                    'actionEnabled' => $verifikasiEnabled,
                                    'disabledTitle' => 'Pemeriksa sampel belum selesai',
                                ])
                            @endif
                        </tr>
                        <tr id="verifikasi-update" style="display: none;">
                            <th scope="row">Verifikasi</th>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                'stepKey' => 'verifikasi',
                                'suffix' => '_update',
                                'jamValue' => $verifikasiJam,
                                'selectedPetugas' => $verifikasiPetugas,
                                'listPetugas' => $listPetugasVerifikasiRow,
                                'baseHref' => $verifikasiHref,
                                'buttonLabel' => 'Verifikasi',
                                'actionEnabled' => true,
                            ])
                        </tr>


                        <tr id="validasi">
                            <th scope="row">Validasi</th>
                            @if (isset($listVerifications[5]))
                                <td>{{ $formatJamVerifikasi($listVerifications[5]) }}</td>
                                <td class="d-none">{{ $formatJamVerifikasi($listVerifications[5]) }}</td>
                                <td>{{ $formatNamaPetugasVerifikasi($listVerifications[5]) }}</td>
                                <td class="text-center">
                                    <svg id="toggle-validasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                        width="48" height="48" viewBox="0 0 48 48">
                                        <path fill="#c8e6c9"
                                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                        </path>
                                        <path fill="#4caf50"
                                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                        </path>
                                    </svg>
                                </td>
                            @else
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                    'stepKey' => 'validasi',
                                    'suffix' => '',
                                    'jamValue' => $validasiJam,
                                    'selectedPetugas' => $validasiPetugas,
                                    'listPetugas' => $listPetugasValidasiRow,
                                    'baseHref' => $validasiHref,
                                    'buttonLabel' => 'Input',
                                    'actionEnabled' => $validasiEnabled,
                                    'disabledTitle' => 'Verifikasi belum selesai',
                                ])
                            @endif
                        </tr>
                        <tr id="validasi-update" style="display: none;">
                            <th scope="row">Validasi</th>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                'stepKey' => 'validasi',
                                'suffix' => '_update',
                                'jamValue' => $validasiJam,
                                'selectedPetugas' => $validasiPetugas,
                                'listPetugas' => $listPetugasValidasiRow,
                                'baseHref' => $validasiHref,
                                'buttonLabel' => 'Input',
                                'actionEnabled' => true,
                            ])
                        </tr>
                        @endif


                        {{-- IF: ROLE = ANLS (hanya tampil Penerima Sampel, Pengolah Sampel, Pemeriksa Sampel, Verifikasi) --}}
                        @if (Auth::user()->getlevel->level == 'ANLS' || Auth::user()->getlevel->level == 'ALAB')
                            <tr id="penerima-sampel">
                                <th scope="row">Penerima Sampel</th>
                                @if (isset($listVerifications[7]) && $listVerifications[7]->is_done == 1)
                                    <td>{{ $formatJamVerifikasi($listVerifications[7]) }}</td>
                                    <td class="d-none">{{ $formatJamVerifikasi($listVerifications[7]) }}</td>
                                    <td>{{ $formatNamaPetugasVerifikasi($listVerifications[7]) }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-penerima-sampel" xmlns="http://www.w3.org/2000/svg" x="0px"
                                            y="0px" width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                        'stepKey' => 'penerima',
                                        'suffix' => '_anls',
                                        'jamValue' => $penerimaJam ?? '',
                                        'selectedPetugas' => $penerimaPetugas ?? '',
                                        'listPetugas' => $listPetugasPenerimaRow,
                                        'baseHref' => $penerimaHref ?? route('elits-permohonan-uji-klinik-2.create-penerima-sampel', $item->id_permohonan_uji_klinik),
                                        'buttonLabel' => 'Input',
                                        'actionEnabled' => $penerimaEnabled ?? false,
                                        'disabledTitle' => 'Pengambilan sampel belum selesai',
                                    ])
                                @endif
                            </tr>
                            <tr id="penerima-sampel-update" style="display:none;">
                                <th scope="row">Penerima Sampel</th>
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                    'stepKey' => 'penerima',
                                    'suffix' => '_anls_update',
                                    'jamValue' => $penerimaJam ?? '',
                                    'selectedPetugas' => $penerimaPetugas ?? '',
                                    'listPetugas' => $listPetugasPenerimaRow,
                                    'baseHref' => $penerimaHref ?? route('elits-permohonan-uji-klinik-2.create-penerima-sampel', $item->id_permohonan_uji_klinik),
                                    'buttonLabel' => 'Input',
                                    'actionEnabled' => true,
                                ])
                            </tr>


                            <tr id="analitik">
                                <th scope="row">Pengolah Sampel</th>
                                @if (isset($listVerifications[2]))
                                    <td>{{ $formatJamVerifikasi($listVerifications[2]) }}</td>
                                    <td class="d-none">
                                        {{ $formatJamVerifikasi($listVerifications[2]) }}</td>
                                    <td>{{ $listVerifications[2]->nama_petugas }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-analitik" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @if (!isset($listVerifications[7]) || (isset($listVerifications[7]) && $listVerifications[7]->is_done == 0))
                                        <form
                                        action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                        method="post" class="formAnalitik">
                                            @csrf
                                            <input type="hidden" name="is_selesai" value="1">

                                            <td><input type="number" value="{{ 2 }}" name="verification_step"
                                            hidden><input type="text" class="form-control" name="start_date"
                                                    id="start_date_analitik" required disabled></td>
                                            <td class="d-none"><input type="hidden" name="stop_date"
                                                    id="stop_date_analitik" disabled></td>
                                            <td>
                                                <select name="nama_petugas" id="namaPetugasAnalitik" required disabled>
                                                    @php
                                                        if ($namaPetugasLogin !== null) {
                                                            $list_name_petugas = [$namaPetugasLogin];
                                                        } else {
                                                        $list_name_petugas = explode(
                                                            ', ',
                                                            $verificationActivity[2]->klinik,
                                                        );
                                                        }
                                                    @endphp
                                                    @foreach ($list_name_petugas as $nama_petugas)
                                                        <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                @if (isset($listVerifications[6]))
                                                    @if ($listVerifications[6]->is_done == 1)
                                                        <button type="submit" class="btn btn-success js-analitik-step-submit" disabled
                                                            @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasAnalitik').value, 'formAnalitik')" @endif>Selesai</button>
                                                    @else
                                                        <button type="submit" class="btn btn-success" disabled
                                                            disabled>Selesai</button>
                                                    @endif
                                                @else
                                                    <button type="submit" class="btn btn-success" disabled>Selesai</button>
                                                @endif
                                            </td>
                                        </form>
                                    @else
                                        <td>
                                            <form id="formAnalitik"
                                                action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                                method="post" class="formAnalitik" style="display:none;" aria-hidden="true">
                                                @csrf
                                                <input type="hidden" name="is_selesai" value="1">
                                                <input type="hidden" name="verification_step" value="2">
                                            </form>
                                            <input type="text" class="form-control" name="start_date"
                                                id="start_date_analitik" form="formAnalitik" required
                                                value="{{ $defaultJamPengolah }}">
                                        </td>
                                        <td class="d-none"><input type="hidden" name="stop_date"
                                                id="stop_date_analitik" form="formAnalitik"></td>
                                        <td>
                                            <select name="nama_petugas" id="namaPetugasAnalitik" form="formAnalitik" required>
                                                <option value="">-- Pilih Petugas --</option>
                                                @php
                                                    if ($namaPetugasLogin !== null) {
                                                        $list_name_petugas = [$namaPetugasLogin];
                                                    } else {
                                                    $list_name_petugas = explode(
                                                        ', ',
                                                        $verificationActivity[2]->klinik,
                                                    );
                                                    }
                                                @endphp
                                                @foreach ($list_name_petugas as $nama_petugas)
                                                    <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            @if (isset($listVerifications[6]))
                                                @if ($listVerifications[6]->is_done == 1)
                                                    <button type="submit" form="formAnalitik" class="btn btn-success js-analitik-step-submit" disabled
                                                        @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasAnalitik').value, 'formAnalitik')" @endif>Selesai</button>
                                                @else
                                                    <button type="submit" form="formAnalitik" class="btn btn-success"
                                                        disabled>Selesai</button>
                                                @endif
                                            @else
                                                <button type="submit" form="formAnalitik" class="btn btn-success" disabled>Selesai</button>
                                            @endif
                                        </td>
                                    @endif
                                @endif
                            </tr>
                            <tr id="analitik-update" style="display: none;">
                                <th scope="row">Pengolah Sampel</th>
                                <td>
                                    <form id="formAnalitikUpdate"
                                        action="{{ route('elits-permohonan-uji-klinik-2.verification-analytic', [$item->id_permohonan_uji_klinik]) }}"
                                        method="post" class="formAnalitikUpdate" style="display:none;" aria-hidden="true">
                                        @csrf
                                        <input type="hidden" name="is_selesai" value="1">
                                        <input type="hidden" name="verification_step" value="2">
                                    </form>
                                    <input type="text" class="form-control" name="start_date"
                                        value="{{ isset($listVerifications[2]->start_date) ? \Carbon\Carbon::parse($listVerifications[2]->start_date)->format('H:i') : '' }}"
                                        data-base-date="{{ isset($listVerifications[2]->start_date) ? \Carbon\Carbon::parse($listVerifications[2]->start_date)->format('Y-m-d') : '' }}"
                                        form="formAnalitikUpdate" required>
                                </td>
                                <td class="d-none"><input type="text" class="form-control" name="stop_date"
                                        value="{{ isset($listVerifications[2]->stop_date) ? \Carbon\Carbon::parse($listVerifications[2]->stop_date)->format('H:i') : '' }}"
                                        data-base-date="{{ isset($listVerifications[2]->stop_date) ? \Carbon\Carbon::parse($listVerifications[2]->stop_date)->format('Y-m-d') : '' }}"
                                        form="formAnalitikUpdate">
                                </td>
                                <td>
                                    <select name="nama_petugas" id="namaPetugasAnalitikUpdate" form="formAnalitikUpdate" required>
                                            @php
                                                if ($namaPetugasLogin !== null) {
                                                    $list_name_petugas = [$namaPetugasLogin];
                                                } elseif (isset($verificationActivity[2]) && !empty($verificationActivity[2]->klinik)) {
                                                    $list_name_petugas = array_values(array_filter(array_map('trim', explode(', ', $verificationActivity[2]->klinik))));
                                                } else {
                                                    $list_name_petugas = [];
                                                }
                                                $selectedPengolah = isset($listVerifications[2]) ? trim($listVerifications[2]->nama_petugas ?? '') : '';
                                                if ($selectedPengolah !== '' && !in_array($selectedPengolah, $list_name_petugas, true)) {
                                                    $list_name_petugas[] = $selectedPengolah;
                                                }
                                            @endphp
                                            @foreach ($list_name_petugas as $nama_petugas)
                                                <option value="{{ $nama_petugas }}"
                                                    {{ $selectedPengolah !== '' && $selectedPengolah == $nama_petugas ? 'selected' : '' }}>
                                                    {{ $nama_petugas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        @if (isset($listVerifications[6]))
                                            @if ($listVerifications[6]->is_done == 1)
                                                <button type="submit" form="formAnalitikUpdate" class="btn btn-success js-analitik-step-submit" disabled
                                                    @if (config('app.bsre_use', true)) onclick="checkNikAndPassword(document.getElementById('namaPetugasAnalitikUpdate').value, 'formAnalitikUpdate')" @endif>Selesai</button>
                                            @else
                                                <button type="submit" form="formAnalitikUpdate" class="btn btn-success" disabled>Selesai</button>
                                            @endif
                                        @else
                                            <button type="submit" form="formAnalitikUpdate" class="btn btn-success" disabled>Selesai</button>
                                        @endif
                                    </td>
                            </tr>
                            <tr id="hasil-px">
                                <th scope="row">Pemeriksa Sampel</th>
                                @if (isset($listVerifications[3]) && $listVerifications[3]->is_done == 1)
                                    <td>{{ $formatJamVerifikasi($listVerifications[3]) }}</td>
                                    <td class="d-none">{{ $formatJamVerifikasi($listVerifications[3]) }}</td>
                                    <td>{{ $formatNamaPetugasVerifikasi($listVerifications[3]) }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-hasil-px" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                        'stepKey' => 'pemeriksa',
                                        'suffix' => '_anls',
                                        'jamValue' => $pemeriksaJam,
                                        'selectedPetugas' => $pemeriksaPetugas,
                                        'listPetugas' => $listPetugasPemeriksaRow,
                                        'baseHref' => $pemeriksaHref,
                                        'buttonLabel' => 'Input',
                                        'actionEnabled' => $pemeriksaEnabled,
                                        'disabledTitle' => 'Pengolah sampel belum selesai',
                                    ])
                                @endif
                        </tr>
                        <tr id="hasil-px-update" style="display: none;">
                            <th scope="row">Pemeriksa Sampel</th>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                'stepKey' => 'pemeriksa',
                                'suffix' => '_anls_update',
                                'jamValue' => $pemeriksaJam,
                                'selectedPetugas' => $pemeriksaPetugas,
                                'listPetugas' => $listPetugasPemeriksaRow,
                                'baseHref' => $pemeriksaHref,
                                'buttonLabel' => 'Input',
                                'actionEnabled' => true,
                            ])
                        </tr>
                        <tr id="verifikasi">
                            <th scope="row">Verifikasi</th>
                            @if (isset($listVerifications[4]) && $listVerifications[4]->is_done == 1)
                                <td>{{ $formatJamVerifikasi($listVerifications[4]) }}</td>
                                <td class="d-none">{{ $formatJamVerifikasi($listVerifications[4]) }}</td>
                                <td>{{ $formatNamaPetugasVerifikasi($listVerifications[4]) }}</td>
                                <td class="text-center">
                                    <svg id="toggle-verifikasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                        width="48" height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                                        <path fill="#c8e6c9"
                                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                        </path>
                                        <path fill="#4caf50"
                                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                        </path>
                                    </svg>
                                </td>
                            @else
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                    'stepKey' => 'verifikasi',
                                    'suffix' => '_anls',
                                    'jamValue' => $verifikasiJam,
                                    'selectedPetugas' => $verifikasiPetugas,
                                    'listPetugas' => $listPetugasVerifikasiRow,
                                    'baseHref' => $verifikasiHref,
                                    'buttonLabel' => 'Verifikasi',
                                    'actionEnabled' => $verifikasiEnabled,
                                    'disabledTitle' => 'Pemeriksa sampel belum selesai',
                                ])
                            @endif
                        </tr>
                        <tr id="verifikasi-update" style="display: none;">
                            <th scope="row">Verifikasi</th>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                'stepKey' => 'verifikasi',
                                'suffix' => '_anls_update',
                                'jamValue' => $verifikasiJam,
                                'selectedPetugas' => $verifikasiPetugas,
                                'listPetugas' => $listPetugasVerifikasiRow,
                                'baseHref' => $verifikasiHref,
                                'buttonLabel' => 'Verifikasi',
                                'actionEnabled' => true,
                            ])
                        </tr>

                        {{-- Validasi (step 5) — hanya menampilkan petugas validator klinik --}}
                        <tr id="validasi">
                            <th scope="row">Validasi</th>
                            @if (isset($listVerifications[5]))
                                <td>{{ $formatJamVerifikasi($listVerifications[5]) }}</td>
                                <td class="d-none">{{ $formatJamVerifikasi($listVerifications[5]) }}</td>
                                <td>{{ $formatNamaPetugasVerifikasi($listVerifications[5]) }}</td>
                                <td class="text-center">
                                    <svg id="toggle-validasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                        width="48" height="48" viewBox="0 0 48 48">
                                        <path fill="#c8e6c9"
                                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                        </path>
                                        <path fill="#4caf50"
                                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                        </path>
                                    </svg>
                                </td>
                            @else
                                @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                    'stepKey' => 'validasi',
                                    'suffix' => '_anls',
                                    'jamValue' => $validasiJam,
                                    'selectedPetugas' => $validasiPetugas,
                                    'listPetugas' => $listPetugasValidasiRow,
                                    'baseHref' => $validasiHref,
                                    'buttonLabel' => 'Input',
                                    'actionEnabled' => $validasiEnabled,
                                    'disabledTitle' => 'Verifikasi belum selesai',
                                ])
                            @endif
                        </tr>
                        <tr id="validasi-update" style="display: none;">
                            <th scope="row">Validasi</th>
                            @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.partials.verification-step-inline-row', [
                                'stepKey' => 'validasi',
                                'suffix' => '_anls_update',
                                'jamValue' => $validasiJam,
                                'selectedPetugas' => $validasiPetugas,
                                'listPetugas' => $listPetugasValidasiRow,
                                'baseHref' => $validasiHref,
                                'buttonLabel' => 'Input',
                                'actionEnabled' => true,
                            ])
                        </tr>
                        @endif

                        {{-- IF: ROLE = ANLS (hanya tampil Penerima Sampel, Pengolah Sampel, Pemeriksa Sampel, Verifikasi) --}}

                        @if (Auth::user()->getlevel->level == 'DKTR')
                            <tr id="validasi">
                                <th scope="row">Validasi</th>
                                @if (isset($listVerifications[5]))
                                    <td>{{ \Carbon\Carbon::parse($listVerifications[5]->start_date)->format('H:i') }}</td>
                                    <td class="d-none">
                                        {{ \Carbon\Carbon::parse($listVerifications[5]->stop_date)->format('H:i') }}</td>
                                    <td>{{ $listVerifications[5]->nama_petugas ?? '-' }}</td>
                                    <td class="text-center">
                                        <svg id="toggle-validasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                            width="48" height="48" viewBox="0 0 48 48">
                                            <path fill="#c8e6c9"
                                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                                            </path>
                                            <path fill="#4caf50"
                                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                                            </path>
                                        </svg>
                                    </td>
                                @else
                                    <td>-</td>
                                    <td class="d-none"></td>
                                    <td>-</td>
                                    <td class="text-center">
                                        @if (isset($listVerifications[4]) && $listVerifications[4]->is_done == 1)
                                            <a href="{{ route('elits-permohonan-uji-klinik-2.disabled-permohonan-uji-analis2', $item->id_permohonan_uji_klinik) }}">
                                                <button type="button" class="btn btn-primary">Input</button>
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-primary" disabled title="Verifikasi belum selesai">Input</button>
                                        @endif
                                    </td>
                                @endif
                        </tr>
                        <tr id="validasi-update" style="display: none;">
                            <th scope="row">Validasi</th>
                            <td>{{ isset($listVerifications[5]->start_date) ? \Carbon\Carbon::parse($listVerifications[5]->start_date)->format('H:i') : '' }}</td>
                            <td class="d-none"></td>
                            <td>{{ $listVerifications[5]->nama_petugas ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('elits-permohonan-uji-klinik-2.disabled-permohonan-uji-analis2', $item->id_permohonan_uji_klinik) }}">
                                    <button type="button" class="btn btn-primary">Input</button>
                                </a>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/cdn-local/js/signature_pad.min.js') }}"></script>
    <script>
        $(document).scrollTop($(document).height());

        // Map TTD yang sudah tersimpan, berindeks berdasarkan nilai data-sampling
        var existingSignatures = {
            @php
                // sampling 0 = pengambilan pertama
                $allPengambilan = collect();
                if ($pengambilanSampleKlinik) {
                    $allPengambilan->push($pengambilanSampleKlinik);
                }
                foreach ($pengambilanSampleUlangKlinik as $ulang) {
                    $allPengambilan->push($ulang);
                }
            @endphp
            @foreach ($allPengambilan as $p)
                {{ (int)$p->resampling }}: {
                    pasien: @if(!empty($p->signature_pengambil_sample_pasien)) '{{ addslashes($p->signature_pengambil_sample_pasien) }}' @else null @endif,
                    petugas: @if(!empty($p->signature_pengambil_sample_petugas)) '{{ addslashes($p->signature_pengambil_sample_petugas) }}' @else null @endif
                },
            @endforeach
        };

        $(document).ready(function() {
            // Signature Pad setup for Pengambil Sample
            var sigPadPasien, sigPadPetugas;
            var currentSampling = 0; // Variable global untuk menyimpan nilai sampling
            var isInitialized = false;
            var autoSignature = {{ filter_var(request()->query('auto_signature'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' }};
            var autoSignatureSampling = {{ max(0, (int) request()->query('sampling', 0)) }};
            var autoSignatureReturnTo = @json(request()->query('return_to', ''));

            function safeAutoSignatureReturnUrl() {
                if (!autoSignatureReturnTo) {
                    return '';
                }

                try {
                    var parsed = new URL(autoSignatureReturnTo, window.location.origin);
                    return parsed.origin === window.location.origin ? parsed.href : '';
                } catch (e) {
                    return '';
                }
            }

            $('#signatureSampleModal').on('shown.bs.modal', function(event) {
                // Prevent multiple initializations
                if (isInitialized) {
                    return;
                }

                var button = $(event.relatedTarget); // tombol yang memicu modal

                var canvasPasien = document.getElementById('sigPadPasien');
                var canvasPetugas = document.getElementById('sigPadPetugas');

                // Ambil nilai data-sampling dari tombol itu
                currentSampling = button.data('sampling') || 0;

                // Fungsi resize yang lebih stabil dan mencegah flickering
                function resizeCanvas(canvas) {
                    // Batch DOM reads
                    var wrapper = canvas.parentElement;
                    var wrapperWidth = wrapper.offsetWidth;
                    var wrapperHeight = wrapper.offsetHeight;
                    var ratio = Math.max(window.devicePixelRatio || 1, 1);

                    // Batch DOM writes using requestAnimationFrame
                    requestAnimationFrame(function() {
                        canvas.width = wrapperWidth * ratio;
                        canvas.height = wrapperHeight * ratio;
                        canvas.style.width = wrapperWidth + 'px';
                        canvas.style.height = wrapperHeight + 'px';

                        var ctx = canvas.getContext('2d', {
                            alpha: true,
                            desynchronized: true // Enable low-latency rendering
                        });
                        ctx.scale(ratio, ratio);

                        // Enable smoothing for better quality
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';
                    });
                }

                // Increased delay to ensure modal backdrop animation is complete
                setTimeout(function() {
                    // Disable animations temporarily
                    document.body.style.pointerEvents = 'none';

                    resizeCanvas(canvasPasien);
                    resizeCanvas(canvasPetugas);

                    // Initialize signature pads dengan konfigurasi anti-flickering
                    setTimeout(function() {
                        // Clear canvas dan set background putih secara eksplisit
                        var ctxPasien = canvasPasien.getContext('2d');
                        var ctxPetugas = canvasPetugas.getContext('2d');

                        ctxPasien.fillStyle = '#ffffff';
                        ctxPasien.fillRect(0, 0, canvasPasien.width, canvasPasien.height);

                        ctxPetugas.fillStyle = '#ffffff';
                        ctxPetugas.fillRect(0, 0, canvasPetugas.width, canvasPetugas
                            .height);

                        sigPadPasien = new SignaturePad(canvasPasien, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(0, 0, 0)',
                            minWidth: 1,
                            maxWidth: 2.5,
                            velocityFilterWeight: 0.7,
                            throttle: 16 // ~60fps untuk smooth drawing
                        });

                        sigPadPetugas = new SignaturePad(canvasPetugas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(0, 0, 0)',
                            minWidth: 1,
                            maxWidth: 2.5,
                            velocityFilterWeight: 0.7,
                            throttle: 16
                        });

                        // Force clear dengan background putih
                        sigPadPasien.clear();
                        sigPadPetugas.clear();

                        // Load TTD yang sudah tersimpan sebelumnya (jika ada)
                        var existing = existingSignatures[currentSampling];
                        if (existing) {
                            if (existing.pasien) {
                                sigPadPasien.fromDataURL(existing.pasien, {
                                    ratio: Math.max(window.devicePixelRatio || 1, 1),
                                    width: canvasPasien.offsetWidth,
                                    height: canvasPasien.offsetHeight
                                });
                                $(canvasPasien).parent().addClass('active');
                            }
                            if (existing.petugas) {
                                sigPadPetugas.fromDataURL(existing.petugas, {
                                    ratio: Math.max(window.devicePixelRatio || 1, 1),
                                    width: canvasPetugas.offsetWidth,
                                    height: canvasPetugas.offsetHeight
                                });
                                $(canvasPetugas).parent().addClass('active');
                            }
                        }

                        // Add active class when starting to draw
                        sigPadPasien.addEventListener('beginStroke', function() {
                            requestAnimationFrame(function() {
                                $(canvasPasien).parent().addClass('active');
                            });
                        });

                        sigPadPetugas.addEventListener('beginStroke', function() {
                            requestAnimationFrame(function() {
                                $(canvasPetugas).parent().addClass(
                                    'active');
                            });
                        });

                        isInitialized = true;

                        // Re-enable pointer events
                        document.body.style.pointerEvents = '';
                    }, 50);
                }, 200);
            });

            // Cleanup saat modal ditutup untuk mencegah memory leak
            $('#signatureSampleModal').on('hidden.bs.modal', function() {
                if (sigPadPasien) {
                    sigPadPasien.off();
                    sigPadPasien = null;
                }
                if (sigPadPetugas) {
                    sigPadPetugas.off();
                    sigPadPetugas = null;
                }
                isInitialized = false;
                // Remove active class
                $('.signature-wrapper').removeClass('active');
            });

            $('#clearSigPasien').on('click', function() {
                if (sigPadPasien) {
                    sigPadPasien.clear();
                    // Pastikan background putih setelah clear
                    var ctx = sigPadPasien.canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, sigPadPasien.canvas.width, sigPadPasien.canvas.height);
                    $(sigPadPasien.canvas).parent().removeClass('active');
                }
            });
            $('#clearSigPetugas').on('click', function() {
                if (sigPadPetugas) {
                    sigPadPetugas.clear();
                    // Pastikan background putih setelah clear
                    var ctx = sigPadPetugas.canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, sigPadPetugas.canvas.width, sigPadPetugas.canvas.height);
                    $(sigPadPetugas.canvas).parent().removeClass('active');
                }
            });
            $('#downloadSigPasien').on('click', function() {
                if (sigPadPasien && !sigPadPasien.isEmpty()) {
                    var dataURL = sigPadPasien.toDataURL('image/png');
                    var a = document.createElement('a');
                    a.href = dataURL;
                    a.download = 'ttd_pasien.png';
                    a.click();
                }
            });
            $('#downloadSigPetugas').on('click', function() {
                if (sigPadPetugas && !sigPadPetugas.isEmpty()) {
                    var dataURL = sigPadPetugas.toDataURL('image/png');
                    var a = document.createElement('a');
                    a.href = dataURL;
                    a.download = 'ttd_petugas.png';
                    a.click();
                }
            });

            function markPengambilTtdCompleteIfReady(sampling) {
                var ex = existingSignatures[sampling] || {};
                if (!ex.pasien || !ex.petugas) {
                    return;
                }
                var completeKey = 'pengambil_ttd_complete_{{ $item->id_permohonan_uji_klinik }}_' + sampling;
                try {
                    localStorage.setItem(completeKey, '1');
                    localStorage.setItem('signature_saved_{{ $item->id_permohonan_uji_klinik }}_' + sampling, Date.now().toString());
                } catch (e) {}
            }

            function saveSignatures(part) {
                var payload = {};
                if (part === 'pasien' && sigPadPasien && !sigPadPasien.isEmpty()) {
                    payload.signature_pasien = sigPadPasien.toDataURL('image/png');
                }
                if (part === 'petugas' && sigPadPetugas && !sigPadPetugas.isEmpty()) {
                    payload.signature_petugas = sigPadPetugas.toDataURL('image/png');
                }
                if (Object.keys(payload).length === 0) {
                    return;
                }
                // Tambahkan sampling ke payload
                payload.sampling = currentSampling;

                $.ajax({
                    url: '{{ route('elits-permohonan-uji-klinik-2.save-signature-pengambil-sample', $item->id_permohonan_uji_klinik) }}',
                    type: 'POST',
                    dataType: 'json',
                    data: Object.assign(payload, {
                        _token: '{{ csrf_token() }}'
                    }),
                    success: function(resp) {
                        if (resp.status) {
                            if (!existingSignatures[currentSampling]) {
                                existingSignatures[currentSampling] = { pasien: null, petugas: null };
                            }
                            if (payload.signature_pasien) {
                                existingSignatures[currentSampling].pasien = payload.signature_pasien;
                            }
                            if (payload.signature_petugas) {
                                existingSignatures[currentSampling].petugas = payload.signature_petugas;
                            }
                            markPengambilTtdCompleteIfReady(currentSampling);

                            var isAutoComplete = autoSignature
                                && Number(currentSampling) === Number(autoSignatureSampling)
                                && existingSignatures[currentSampling].pasien
                                && existingSignatures[currentSampling].petugas;
                            var returnUrl = isAutoComplete ? safeAutoSignatureReturnUrl() : '';

                            swal({
                                title: 'Success',
                                text: isAutoComplete
                                    ? 'Tanda tangan lengkap. Melanjutkan ke pengambilan sample...'
                                    : resp.pesan,
                                icon: 'success'
                            }).then(function() {
                                if (returnUrl) {
                                    window.location.href = returnUrl;
                                }
                            });

                            // Trigger event untuk memberitahu form lain bahwa TTD sudah diisi
                            if (window.parent && window.parent !== window) {
                                window.parent.postMessage({
                                    type: 'signature_saved',
                                    permohonan_id: '{{ $item->id_permohonan_uji_klinik }}',
                                    sampling: currentSampling
                                }, '*');
                            }

                            try {
                                localStorage.setItem('signature_saved_{{ $item->id_permohonan_uji_klinik }}_' + currentSampling, Date.now().toString());
                            } catch (e) {}
                        } else {
                            swal({
                                title: 'Error',
                                text: resp.pesan,
                                icon: 'warning'
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: 'Error',
                            text: 'Gagal menyimpan tanda tangan',
                            icon: 'error'
                        });
                    }
                });
            }

            $('#saveSigPasien').on('click', function() {
                saveSignatures('pasien');
            });
            $('#saveSigPetugas').on('click', function() {
                saveSignatures('petugas');
            });

            if (autoSignature) {
                currentSampling = autoSignatureSampling;
                setTimeout(function() {
                    $('#signatureSampleModal').modal('show');
                }, 250);
            }

            for (var samplingKey in existingSignatures) {
                if (Object.prototype.hasOwnProperty.call(existingSignatures, samplingKey)) {
                    markPengambilTtdCompleteIfReady(samplingKey);
                }
            }

            function parseServerDateTime(str) {
                str = String(str || '').trim();
                var m = str.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{1,2}):(\d{2})(?::(\d{2}))?)?/);
                if (!m) {
                    return new Date(str);
                }
                return new Date(
                    parseInt(m[1], 10),
                    parseInt(m[2], 10) - 1,
                    parseInt(m[3], 10),
                    parseInt(m[4] || '0', 10),
                    parseInt(m[5] || '0', 10),
                    parseInt(m[6] || '0', 10)
                );
            }

            let pendaftaranStop, pengambilStart, pengambilStop, penerimaStart, penerimaStop,
                pemeriksaanStop, inputStart, inputStop, verifikasiStart,
                verifikasiStop, validasiStart, validasiStop;

            @if (isset($listVerifications[1]))
                pendaftaranStop = parseServerDateTime("{{ $listVerifications[1]->start_date }}");
            @else
                pendaftaranStop = parseServerDateTime("{{ $listVerifications[1]->start_date }}");
            @endif

            // 1. Sample
            @if (isset($listVerifications[6]))
                sampleStart = parseServerDateTime("{{ $listVerifications[6]->start_date }}");
                sampleStop = parseServerDateTime("{{ $listVerifications[6]->stop_date }}");
            @else
                sampleStart = new Date(pendaftaranStop);
                sampleStart.setMinutes(sampleStart.getMinutes() + 1);
                sampleStop = new Date(sampleStart);
                sampleStop.setMinutes(sampleStop.getMinutes());
            @endif

            // 2. Penerimaan
            @if (isset($listVerifications[7]))
                penerimaanStart = parseServerDateTime("{{ $listVerifications[7]->start_date }}");
                penerimaanStop = parseServerDateTime("{{ $listVerifications[7]->stop_date }}");
            @else

                penerimaanStart = new Date(sampleStart);
                penerimaanStart.setMinutes(penerimaanStart.getMinutes() + 1);
                penerimaanStop = new Date(penerimaanStart);
            @endif


            // 3. Pemeriksaan
            @if (isset($listVerifications[2]))
                pemeriksaanStart = parseServerDateTime("{{ $listVerifications[2]->start_date }}");
                pemeriksaanStop = parseServerDateTime("{{ $listVerifications[2]->stop_date }}");
            @else
                pemeriksaanStart = new Date(penerimaanStart);
                pemeriksaanStart.setMinutes(pemeriksaanStart.getMinutes() + 1);
                pemeriksaanStop = new Date(pemeriksaanStart);
            @endif

            // 4. Input / Output
            @if (isset($listVerifications[3]))
                inputStart = parseServerDateTime("{{ $listVerifications[3]->start_date }}");
                inputStop = parseServerDateTime("{{ $listVerifications[3]->stop_date }}");
            @else
                inputStart = new Date(pemeriksaanStart);
                inputStart.setMinutes(inputStart.getMinutes() + 1);
                inputStop = new Date(inputStart);
            @endif

            // 5. Verifikasi
            @if (isset($listVerifications[4]))
                verifikasiStart = parseServerDateTime("{{ $listVerifications[4]->start_date }}");
                verifikasiStop = parseServerDateTime("{{ $listVerifications[4]->stop_date }}");
            @else
                verifikasiStart = new Date(inputStart);

                verifikasiStart.setMinutes(verifikasiStart.getMinutes() + 1);
                verifikasiStop = new Date(verifikasiStart);
            @endif

            // 6. Validasi
            @if (isset($listVerifications[5]))
                validasiStart = parseServerDateTime("{{ $listVerifications[5]->start_date }}");
                validasiStop = parseServerDateTime("{{ $listVerifications[5]->stop_date }}");
            @else
                validasiStart = new Date(verifikasiStart);
                validasiStart.setMinutes(validasiStart.getMinutes() + 1);
                validasiStop = new Date(validasiStart);
            @endif

            // if (document.querySelector('#start_date_sample')) {
            //     document.querySelector('#start_date_sample').value = formatDate(sampleStart);
            // }
            // if (document.querySelector('#stop_date_sample')) {
            //     document.querySelector('#stop_date_sample').value = formatDate(sampleStop);
            // }

            function formatTime(date) {
                let h = date.getHours().toString().padStart(2, '0');
                let m = date.getMinutes().toString().padStart(2, '0');
                return `${h}:${m}`;
            }

            var tglRegister = parseServerDateTime(@json(\Carbon\Carbon::parse($item->created_at ?? $item->tglregister_permohonan_uji_klinik)->format('Y-m-d H:i:s')));
            window.tglRegisterIso = @json(\Carbon\Carbon::parse($item->created_at ?? $item->tglregister_permohonan_uji_klinik)->format('Y-m-d'));

            function parseDmyHiToDate(str) {
                var m = (str || '').trim().match(/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{1,2}):(\d{2})$/);
                if (!m) return null;
                return new Date(parseInt(m[3], 10), parseInt(m[2], 10) - 1, parseInt(m[1], 10),
                    parseInt(m[4], 10), parseInt(m[5], 10), 0, 0);
            }

            /**
             * registerAnchorDate / timeStrToDate — dipakai sebelum initPicker.
             */
            function registerAnchorDate() {
                var regParts = (window.tglRegisterIso || '').split('-');
                if (regParts.length === 3) {
                    return new Date(parseInt(regParts[0], 10), parseInt(regParts[1], 10) - 1, parseInt(regParts[2], 10));
                }
                return new Date();
            }

            window.timeStrToDate = function(timeStr) {
                var d = registerAnchorDate();
                if (timeStr && /^\d{1,2}:\d{2}$/.test(timeStr.trim())) {
                    var parts = timeStr.trim().split(':');
                    d.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10), 0, 0);
                }
                return d;
            };

            // Selalu tampilkan tanggal registrasi + jam (d/m/Y H:i), termasuk jika registrasi hari ini.
            var isSameDay = false;

            function formatDmyHi(date) {
                if (!(date instanceof Date) || isNaN(date.getTime())) {
                    return '';
                }
                var d = registerAnchorDate();
                d.setHours(date.getHours(), date.getMinutes(), 0, 0);
                return String(d.getDate()).padStart(2, '0') + '/' +
                    String(d.getMonth() + 1).padStart(2, '0') + '/' +
                    d.getFullYear() + ' ' + formatTime(d);
            }

            function initPicker(selector, defaultDate, options) {
                options = options || {};
                var el = document.querySelector(selector);
                if (!el) return null;
                var existingVal = (el.value || el.getAttribute('value') || '').trim();
                var isActive = !el.disabled;
                var autoDefault = options.autoDefault !== false;

                if (existingVal && /^\d{1,2}:\d{2}$/.test(existingVal)) {
                    existingVal = formatDmyHi(window.timeStrToDate(existingVal));
                    el.value = existingVal;
                }

                if (!existingVal && isActive && defaultDate) {
                    autoDefault = true;
                    var activeDefault = defaultDate instanceof Date
                        ? new Date(defaultDate.getTime())
                        : window.timeStrToDate(String(defaultDate));
                    existingVal = formatDmyHi(activeDefault);
                    el.value = existingVal;
                    $(el).trigger('change').trigger('input');
                } else if (!existingVal && !isActive) {
                    autoDefault = false;
                    defaultDate = null;
                    el.value = '';
                }

                var pickerHooks = {
                    onChange: function(selectedDates, dateStr, instance) {
                        $(instance.input).trigger('change').trigger('input');
                        if (typeof window.syncAnalitikSubmitButtons === 'function') {
                            window.syncAnalitikSubmitButtons();
                        }
                    },
                    onClose: function(selectedDates, dateStr, instance) {
                        $(instance.input).trigger('change').trigger('input');
                        if (typeof window.syncAnalitikSubmitButtons === 'function') {
                            window.syncAnalitikSubmitButtons();
                        }
                    }
                };

                var dtDefault = parseDmyHiToDate(existingVal);
                if (!dtDefault && isActive && autoDefault && defaultDate) {
                    dtDefault = defaultDate instanceof Date
                        ? registerAnchorDate()
                        : window.timeStrToDate(String(defaultDate));
                    if (defaultDate instanceof Date) {
                        dtDefault.setHours(defaultDate.getHours(), defaultDate.getMinutes(), 0, 0);
                    }
                }

                return flatpickr(selector, Object.assign({
                    enableTime: true,
                    noCalendar: false,
                    allowInput: true,
                    dateFormat: 'd/m/Y H:i',
                    time_24hr: true,
                    defaultDate: dtDefault || undefined
                }, pickerHooks));
            }

            @if (!empty($defaultJamPengolah))
            (function() {
                var pengolahDefaultJam = @json($defaultJamPengolah);
                if (/^\d{1,2}:\d{2}$/.test(pengolahDefaultJam)) {
                    pemeriksaanStart = window.timeStrToDate(pengolahDefaultJam);
                } else {
                    pemeriksaanStart = parseDmyHiToDate(pengolahDefaultJam) || pemeriksaanStart;
                }
                pemeriksaanStop = new Date(pemeriksaanStart);
            })();
            @endif
            window.isSameDay   = isSameDay;
            window.initPicker  = initPicker;
            window.tglRegister = tglRegister;
            window.sampleStart = sampleStart;
            window.penerimaSampelStart = penerimaanStart;
            window.pemeriksaanStart = pemeriksaanStart;
            window.inputStart = inputStart;
            window.verifikasiStart = verifikasiStart;
            window.validasiStart = validasiStart;

            var verificationStepJamDefaults = {
                penerima: penerimaanStart,
                pemeriksa: inputStart,
                verifikasi: verifikasiStart,
                validasi: validasiStart
            };

            // Pengambil Sample
            initPicker('#start_date_sample', sampleStart);
            initPicker('#stop_date_sample',  sampleStop);
            initPicker('#start_date_sample_admin', sampleStart);
            initPicker('#stop_date_sample_admin', sampleStop);

            // Create sample (form baru, default ke sampleStart)
            initPicker('#start_date_sample_create', sampleStart);

            // Penerima Sampel
            var penerimaSampelStart = penerimaanStart;
            var penerimaSampelStop  = penerimaanStop;
            initPicker('#start_date_penerima_sampel', penerimaSampelStart);
            initPicker('#stop_date_penerima_sampel',  penerimaSampelStop);

            // Pengolah Sampel (Analitik)
            initPicker('#start_date_analitik', pemeriksaanStart);
            initPicker('#stop_date_analitik', pemeriksaanStop);
            initPicker('#analitik-update input[name="start_date"]', pemeriksaanStart);
            initPicker('#analitik-update input[name="stop_date"]', pemeriksaanStop);

            // Input / Pemeriksa
            initPicker('#start_date_input', inputStart);
            initPicker('#stop_date_input',  inputStop);

            // Verifikasi
            initPicker('#start_date_verifikasi', verifikasiStart);
            initPicker('#stop_date_verifikasi',  verifikasiStop);

            // Validasi
            initPicker('#start_date_validasi', validasiStart);
            initPicker('#stop_date_validasi',  validasiStop);

            $('.verification-step-jam').each(function() {
                var $el = $(this);
                var id = '#' + $el.attr('id');
                var initial = ($el.data('initial-jam') || $el.val() || '').trim();
                if (initial && /^\d{4}-\d{2}-\d{2}/.test(initial)) {
                    var p = initial.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2})/);
                    if (p) {
                        $el.val(p[3] + '/' + p[2] + '/' + p[1] + ' ' +
                            String(p[4]).padStart(2, '0') + ':' + p[5]);
                    }
                } else if (initial && /^\d{1,2}:\d{2}$/.test(initial)) {
                    $el.val(formatDmyHi(window.timeStrToDate(initial)));
                }
                var stepKey = $el.data('step-key') || '';
                var pickerDefault = verificationStepJamDefaults[stepKey] || sampleStart;
                var displayVal = ($el.val() || '').trim();
                if (displayVal) {
                    if (/^\d{2}\/\d{2}\/\d{4}/.test(displayVal)) {
                        pickerDefault = parseDmyHiToDate(displayVal) || pickerDefault;
                    } else if (/^\d{1,2}:\d{2}$/.test(displayVal)) {
                        pickerDefault = window.timeStrToDate(displayVal);
                    }
                }
                initPicker(id, pickerDefault);
            });

            // if (document.querySelector('#start_date_pemeriksaan')) {
            //     document.querySelector('#start_date_pemeriksaan').value = formatDate(pemeriksaanStart);
            // }
            // if (document.querySelector('#stop_date_pemeriksaan')) {
            //     document.querySelector('#stop_date_pemeriksaan').value = formatDate(pemeriksaanStop);
            // }

            // if (document.querySelector('#start_date_input')) {
            //     document.querySelector('#start_date_input').value = formatDate(inputStart);
            // }
            // if (document.querySelector('#stop_date_input')) {
            //     document.querySelector('#stop_date_input').value = formatDate(inputStop);
            // }

            // if (document.querySelector('#start_date_verifikasi')) {
            //     document.querySelector('#start_date_verifikasi').value = formatDate(verifikasiStart);
            // }
            // if (document.querySelector('#stop_date_verifikasi')) {
            //     document.querySelector('#stop_date_verifikasi').value = formatDate(verifikasiStop);
            // }

            // if (document.querySelector('#start_date_validasi')) {
            //     document.querySelector('#start_date_validasi').value = formatDate(validasiStart);
            // }
            // if (document.querySelector('#stop_date_validasi')) {
            //     document.querySelector('#stop_date_validasi').value = formatDate(validasiStop);
            // }

            // ANLS - Pengolah Sampel: initPicker di atas sudah menangani #start_date_analitik

            // ANLS - Pemeriksa Sampel time picker
            if (document.querySelector('#start_date_hasil_px')) {
                const start_date_hasil_px = flatpickr("#start_date_hasil_px", {
                    enableTime: true,
                    noCalendar: true,
                    allowInput: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    defaultDate: formatTime(new Date()) // <--- set default di sini
                });

                $('#start_date_hasil_px').inputmask('99:99', {
                    placeholder: 'hh:mm'
                });
            }

            if (document.querySelector('#stop_date_hasil_px')) {
                const stop_date_hasil_px = flatpickr("#stop_date_hasil_px", {
                    enableTime: true,
                    noCalendar: true,
                    allowInput: true,
                    dateFormat: "H:i",
                    time_24hr: true,
                    defaultDate: formatTime(new Date()) // <--- set default di sini
                });

                $('#stop_date_hasil_px').inputmask('99:99', {
                    placeholder: 'hh:mm'
                });
            }

            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Simpan base date (ISO yyyy-mm-dd) dari nilai awal saat edit
            $('tr[id$="-update"] input[name="start_date"], tr[id$="-update"] input[name="stop_date"]').each(function() {
                var raw = $(this).attr('value') || '';
                var base = $(this).data('baseDate'); // Ambil dari attribute data-base-date

                if (!base) {
                    var mIso = raw.match(/(\d{4})-(\d{2})-(\d{2})/);
                    if (mIso) {
                        base = mIso[1] + '-' + mIso[2] + '-' + mIso[3];
                    } else {
                        var mDMY = raw.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                        if (mDMY) base = mDMY[3] + '-' + mDMY[2] + '-' + mDMY[1];
                    }
                }

                if (base) $(this).data('baseDate', base);
            });

            function getAnalitikFormFields($form) {
                var $row = $form.closest('tr');
                if (!$row.length) {
                    $row = $form.hasClass('formAnalitikUpdate') ? $('#analitik-update') : $('#analitik');
                }
                if ($row.length) {
                    var $start = $row.find('input[name="start_date"], input[data-compose-name="start_date"]').not(':disabled').first();
                    if (!$start.length) {
                        $start = $row.find('input[name="start_date"], input[data-compose-name="start_date"]').first();
                    }
                    var $petugas = $row.find('select[name="nama_petugas"]').not(':disabled').first();
                    if (!$petugas.length) {
                        $petugas = $row.find('select[name="nama_petugas"]').first();
                    }
                    var $stop = $row.find('input[name="stop_date"], input[data-compose-name="stop_date"]').first();
                    return {
                        $start: $start,
                        $stop: $stop,
                        $petugas: $petugas
                    };
                }
                var formId = $form.attr('id');
                if (formId) {
                    return {
                        $start: $('[form="' + formId + '"][name="start_date"], [form="' + formId + '"][data-compose-name="start_date"]').not(':disabled').first(),
                        $stop: $('[form="' + formId + '"][name="stop_date"], [form="' + formId + '"][data-compose-name="stop_date"]').first(),
                        $petugas: $('[form="' + formId + '"][name="nama_petugas"]').not(':disabled').first()
                    };
                }
                return {
                    $start: $form.find('input[name="start_date"]'),
                    $stop: $form.find('input[name="stop_date"]'),
                    $petugas: $form.find('select[name="nama_petugas"]')
                };
            }

            function readAnalitikJamValue($input) {
                var el = $input.get(0);
                if (el && el._flatpickr && el._flatpickr.selectedDates && el._flatpickr.selectedDates.length) {
                    var fp = el._flatpickr;
                    return fp.formatDate(fp.selectedDates[0], fp.config.dateFormat);
                }
                if (el && el._flatpickr && el._flatpickr.input) {
                    return (el._flatpickr.input.value || '').trim();
                }
                return ($input.val() || '').trim();
            }

            function dateToISO(d) {
                if (!(d instanceof Date) || isNaN(d.getTime())) return null;
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var yy = d.getFullYear();
                var dd = String(d.getDate()).padStart(2, '0');
                return yy + '-' + mm + '-' + dd;
            }

            function isoToDmyHi(isoDate, jam) {
                if (!isoDate || !/^\d{1,2}:\d{2}$/.test(jam)) return jam;
                var parts = isoDate.split('-');
                if (parts.length !== 3) return jam;
                var hm = jam.split(':');
                return parts[2] + '/' + parts[1] + '/' + parts[0] + ' ' +
                    String(hm[0]).padStart(2, '0') + ':' + String(hm[1]).padStart(2, '0');
            }

            function detectVerificationBaseDate($form, $start) {
                var base = $start.data('baseDate');
                if (base) return base;
                if ($form.hasClass('formPengambilanSampel') || $form.hasClass('formPengambilSampelUpdate')) {
                    return dateToISO(window.sampleStart);
                }
                if ($form.hasClass('formPenerimaSampel') || $form.hasClass('formPenerimaSampelUpdate')) {
                    return dateToISO(window.penerimaSampelStart);
                }
                // Default: tanggal klik (hari ini)
                var todayIso = dateToISO(new Date());
                if ($form.hasClass('formAnalitik') || $form.hasClass('formAnalitikUpdate')) {
                    return window.tglRegisterIso || dateToISO(window.pemeriksaanStart);
                }
                if ($form.hasClass('formHasilPx') || $form.hasClass('formHasilPxUpdate')) {
                    return dateToISO(window.inputStart) || window.tglRegisterIso;
                }
                if ($form.hasClass('formVerifikasi') || $form.hasClass('formVerifikasi2') || $form.hasClass('formVerifikasi2Update')) {
                    return dateToISO(window.verifikasiStart) || window.tglRegisterIso;
                }
                if ($form.hasClass('formValidasi') || $form.hasClass('formValidasiUpdate')) {
                    return dateToISO(window.validasiStart) || window.tglRegisterIso;
                }
                return window.tglRegisterIso || dateToISO(new Date());
            }

            function setVerificationSubmitValue($input, value) {
                if (!$input || !$input.length) return;
                var el = $input.get(0);
                if (!el) return;
                if (el.inputmask) {
                    try { el.inputmask.remove(); } catch (e) { /* ignore */ }
                }
                try {
                    $input.inputmask('remove');
                } catch (e) { /* ignore */ }
                $input.val(value);
            }

            function extractTimeFromComposed(value) {
                var v = (value || '').trim();
                var m = v.match(/(\d{1,2}):(\d{2})$/);
                if (!m) return v;
                return String(m[1]).padStart(2, '0') + ':' + m[2];
            }

            function composeDatetimeString($input, baseIso) {
                if (!$input.length) return '';
                var t = readAnalitikJamValue($input);
                if (!t) {
                    t = ($input.val() || '').trim();
                }
                if (!t) return '';
                if (/^\d{2}\/\d{2}\/\d{4}\s+\d{1,2}:\d{2}$/.test(t)) {
                    return t;
                }
                if (/^\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{2}/.test(t)) {
                    var ym = t.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2})/);
                    if (ym) {
                        return ym[3] + '/' + ym[2] + '/' + ym[1] + ' ' +
                            String(ym[4]).padStart(2, '0') + ':' + ym[5];
                    }
                }
                if (/^\d{1,2}:\d{2}$/.test(t) && baseIso) {
                    return isoToDmyHi(baseIso, t);
                }
                return t;
            }

            function ensureFormHiddenField($form, fieldName) {
                var $hidden = $form.find('input[type="hidden"][data-compose-for="' + fieldName + '"]');
                if (!$hidden.length) {
                    $hidden = $('<input>', {
                        type: 'hidden',
                        name: fieldName,
                        'data-compose-for': fieldName
                    });
                    $form.append($hidden);
                }
                return $hidden;
            }

            function setTimeOnlyDisplay($input, timeStr) {
                if (!$input.length || !timeStr || $input.attr('type') === 'hidden') return;
                var el = $input.get(0);
                if (el && el._flatpickr) {
                    try {
                        el._flatpickr.setDate(timeStr, false, 'H:i');
                    } catch (e) {
                        $input.val(timeStr);
                    }
                } else {
                    $input.val(timeStr);
                }
            }

            function detachVisibleFieldFromSubmit($input) {
                if (!$input.length) return;
                var currentName = $input.attr('name');
                if (currentName) {
                    $input.attr('data-compose-name', currentName);
                    $input.removeAttr('name');
                }
            }

            function findVerificationJamInput($scope, fieldName) {
                var $field = $scope.find('input[name="' + fieldName + '"]').first();
                if (!$field.length) {
                    $field = $scope.find('input[data-compose-name="' + fieldName + '"]').first();
                }
                return $field;
            }

            function applySameDayCompose($form, $start, $stop, composedStart, composedStop) {
                ensureFormHiddenField($form, 'start_date').val(composedStart);
                ensureFormHiddenField($form, 'stop_date').val(composedStop || composedStart);

                detachVisibleFieldFromSubmit($start);
                if ($stop.length) {
                    detachVisibleFieldFromSubmit($stop);
                }

                setTimeOnlyDisplay($start, extractTimeFromComposed(composedStart));
            }

            function applyDifferentDayCompose($start, $stop, composedStart, composedStop) {
                setVerificationSubmitValue($start, composedStart);
                if ($stop.length) {
                    setVerificationSubmitValue($stop, composedStop || composedStart);
                }
            }

            window.composeVerificationFormDatetime = function($form) {
                if (!$form || !$form.length) return;

                var $start = $form.find('input[name="start_date"]');
                var $stop = $form.find('input[name="stop_date"]');

                if ($form.hasClass('formAnalitik') || $form.hasClass('formAnalitikUpdate')) {
                    var analitikFields = getAnalitikFormFields($form);
                    $start = analitikFields.$start;
                    $stop = analitikFields.$stop;
                }

                if (!$start.length) {
                    $start = findVerificationJamInput($form, 'start_date');
                }
                if (!$stop.length) {
                    $stop = findVerificationJamInput($form, 'stop_date');
                }

                var base = detectVerificationBaseDate($form, $start);
                var composedStart = composeDatetimeString($start, base);
                var composedStop = composeDatetimeString($stop, base) || composedStart;

                if (window.isSameDay) {
                    applySameDayCompose($form, $start, $stop, composedStart, composedStop);
                } else {
                    applyDifferentDayCompose($start, $stop, composedStart, composedStop);
                }
            };

            // Compose nilai yang dikirim ke backend: d/m/Y HH:mm
            $(document).on('submit', 'form', function(e) {
                var $form = $(this);
                var analitikFields = null;
                var $start = $form.find('input[name="start_date"]');
                var $stop = $form.find('input[name="stop_date"]');

                if ($form.hasClass('formAnalitik') || $form.hasClass('formAnalitikUpdate')) {
                    analitikFields = getAnalitikFormFields($form);
                    $start = analitikFields.$start;
                    $stop = analitikFields.$stop;
                    var jamAnalitik = readAnalitikJamValue($start);
                    var petugasAnalitik = (analitikFields.$petugas.val() || '').trim();
                    if (!jamAnalitik || !petugasAnalitik) {
                        e.preventDefault();
                        if (typeof swal !== 'undefined') {
                            swal({ title: 'Perhatian', text: 'Isi jam dan nama petugas terlebih dahulu.', icon: 'warning' });
                        } else {
                            alert('Isi jam dan nama petugas terlebih dahulu.');
                        }
                        return false;
                    }
                }

                window.composeVerificationFormDatetime($form);
            });

            var PETUGAS_DIISI_PELANGGAN = @json(\Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2::PETUGAS_PENGAMBIL_DIISI_PELANGGAN);

            function isDiisiPelangganPetugas(val) {
                return (val || '').trim() === PETUGAS_DIISI_PELANGGAN;
            }

            var pengambilSamplePermohonanId = @json($item->id_permohonan_uji_klinik);

            function pengambilSampleStorageKey(sampleCount) {
                return 'pengambil_sample_meta_' + pengambilSamplePermohonanId + '_' + (sampleCount || 1);
            }

            function savePengambilToLocalStorage(sampleCount, jam, petugas) {
                if (!jam || !petugas) return;
                try {
                    localStorage.setItem(pengambilSampleStorageKey(sampleCount), JSON.stringify({
                        jam_sampling: jam,
                        nama_petugas_pengambil: petugas,
                        saved_at: Date.now()
                    }));
                } catch (e) {
                    /* quota / private mode */
                }
            }

            function jamToInputFormat(val) {
                val = (val || '').trim();
                if (!val) return '';

                var dmy = val.match(/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{1,2}):(\d{2})$/);
                if (dmy) {
                    return dmy[3] + '-' + dmy[2] + '-' + dmy[1] + ' ' +
                        String(dmy[4]).padStart(2, '0') + ':' + dmy[5];
                }

                if (/^\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}/.test(val)) {
                    return val.substring(0, 16);
                }

                if (/^\d{1,2}:\d{2}$/.test(val)) {
                    var baseIso = window.tglRegisterIso || '';
                    if (baseIso) {
                        return baseIso + ' ' + val;
                    }
                    var d = new Date();
                    return d.getFullYear() + '-' +
                        String(d.getMonth() + 1).padStart(2, '0') + '-' +
                        String(d.getDate()).padStart(2, '0') + ' ' + val;
                }

                return val;
            }

            function readJamFromPengambilInput($input) {
                if (!$input.length) return '';
                var raw = ($input.val() || $input.attr('value') || '').trim();
                return jamToInputFormat(raw);
            }

            function getPengambilRowContext(suffix) {
                suffix = suffix || '';
                return {
                    suffix: suffix,
                    $start: $('#start_date_sample' + suffix),
                    $stop: $('#stop_date_sample' + suffix),
                    $sel: $('#namaPetugasPengambilanSampel' + suffix),
                    $metaJam: $('#pengambil_meta_jam' + suffix),
                    $metaPetugas: $('#pengambil_meta_petugas' + suffix),
                    $link: $('a.link-input-pengambil-sample[data-suffix="' + suffix + '"]')
                };
            }

            function readPetugasFromSelect($sel) {
                if (!$sel.length) return '';
                var petugas = ($sel.val() || '').trim();
                if (!petugas) {
                    petugas = ($sel.find('option:selected').text() || '').trim();
                    if (petugas === '-- Pilih Petugas --') petugas = '';
                }
                return petugas;
            }

            function composePengambilDatesForSubmit(ctx) {
                var $start = ctx.$start;
                var $stop = ctx.$stop;
                if (!$start.length) return;

                function dateToISO(d) {
                    if (!(d instanceof Date)) return null;
                    var mm = String(d.getMonth() + 1).padStart(2, '0');
                    var yy = d.getFullYear();
                    var dd = String(d.getDate()).padStart(2, '0');
                    return yy + '-' + mm + '-' + dd;
                }

                var base = $start.data('baseDate') || dateToISO(new Date()) || dateToISO(window.sampleStart);
                var t = ($start.val() || '').trim();
                if (/^\d{1,2}:\d{2}$/.test(t) && base) {
                    $start.val(base + ' ' + t);
                }
                if ($stop.length) {
                    $stop.val($start.val());
                }
            }

            function updatePengambilMetaBySuffix(suffix) {
                var ctx = getPengambilRowContext(suffix);
                var jam = readJamFromPengambilInput(ctx.$start);
                var petugas = readPetugasFromSelect(ctx.$sel);
                if (jam) ctx.$metaJam.val(jam);
                if (petugas) ctx.$metaPetugas.val(petugas);
                if (jam && ctx.$link.length) ctx.$link.attr('data-jam', jam);
                if (petugas && ctx.$link.length) ctx.$link.attr('data-petugas', petugas);
            }

            function persistPengambilRowToLocalStorage(suffix) {
                var ctx = getPengambilRowContext(suffix);
                updatePengambilMetaBySuffix(suffix);
                composePengambilDatesForSubmit(ctx);
                updatePengambilMetaBySuffix(suffix);

                var jam = (ctx.$metaJam.val() || readJamFromPengambilInput(ctx.$start) || '').trim();
                var petugas = (ctx.$metaPetugas.val() || readPetugasFromSelect(ctx.$sel) || '').trim();
                var sampleCount = parseInt(
                    (ctx.$link.attr('data-sample-count') || ctx.$start.attr('data-sample-count') || '1'),
                    10
                );
                savePengambilToLocalStorage(sampleCount, jam, petugas);
            }

            $('.pengambil-start-date').each(function() {
                updatePengambilMetaBySuffix($(this).data('suffix') || '');
            });

            $(document).on('change', '.pengambil-start-date, .pengambil-nama-petugas', function() {
                var suffix = $(this).data('suffix') || '';
                var ctx = getPengambilRowContext(suffix);
                if (ctx.$stop.length) ctx.$stop.val(ctx.$start.val());
                persistPengambilRowToLocalStorage(suffix);
            });

            $(document).on('click', 'a.link-input-pengambil-sample, a.link-input-pengambil-sample *', function(e) {
                var $link = $(this).closest('a.link-input-pengambil-sample');
                if (!$link.length) return;

                var baseHref = $link.attr('data-base-href') ||
                    ($link.attr('href') || '').split('?')[0];
                if (!baseHref || baseHref === 'javascript:void(0)') return;

                e.preventDefault();

                var suffix = $link.attr('data-suffix') || '';
                var ctx = getPengambilRowContext(suffix);

                composePengambilDatesForSubmit(ctx);
                updatePengambilMetaBySuffix(suffix);

                var jam = (ctx.$metaJam.val() || '').trim();
                if (!jam) jam = readJamFromPengambilInput(ctx.$start);
                var petugas = (ctx.$metaPetugas.val() || '').trim();
                if (!petugas) petugas = readPetugasFromSelect(ctx.$sel);

                if (!jam || !petugas) {
                    var warnText = 'Isi tanggal/jam dan nama petugas pengambil sampel terlebih dahulu.';
                    if (typeof swal !== 'undefined') {
                        swal({ title: 'Perhatian', text: warnText, icon: 'warning' });
                    } else {
                        alert(warnText);
                    }
                    return;
                }

                var sampleCount = parseInt($link.attr('data-sample-count') || '1', 10);
                savePengambilToLocalStorage(sampleCount, jam, petugas);
                window.location.href = baseHref;
            });

            function verificationStepStorageKey(stepKey) {
                return 'verification_step_meta_' + pengambilSamplePermohonanId + '_' + stepKey;
            }

            function saveVerificationStepToLocalStorage(stepKey, jam, petugas) {
                if (!jam && !petugas) return;
                try {
                    localStorage.setItem(verificationStepStorageKey(stepKey), JSON.stringify({
                        jam: jam,
                        nama_petugas: petugas,
                        saved_at: Date.now()
                    }));
                } catch (e) {}
            }

            function getVerificationStepRowContext(stepKey, suffix) {
                suffix = suffix || '';
                var fieldId = stepKey + suffix;
                return {
                    stepKey: stepKey,
                    suffix: suffix,
                    fieldId: fieldId,
                    $start: $('#verification_step_jam_' + fieldId),
                    $stop: $('#verification_step_stop_' + fieldId),
                    $sel: $('#verification_step_petugas_' + fieldId),
                    $metaJam: $('#verification_meta_jam_' + fieldId),
                    $metaPetugas: $('#verification_meta_petugas_' + fieldId),
                    $link: $('a.link-input-verification-step[data-step-key="' + stepKey + '"][data-suffix="' + suffix + '"]')
                };
            }

            function readJamFromVerificationStepInput($input) {
                return readJamFromPengambilInput($input);
            }

            function composeVerificationStepDates(ctx) {
                // Jangan ubah tampilan input jam (H:i) — cukup sinkronkan hidden stop.
                // Konversi ke Y-m-d H:i untuk URL/meta ditangani readJamFromVerificationStepInput.
                if (ctx.$stop.length && ctx.$start.length) {
                    ctx.$stop.val(ctx.$start.val());
                }
            }

            function updateVerificationStepMeta(ctx) {
                var jam = readJamFromVerificationStepInput(ctx.$start);
                var petugas = readPetugasFromSelect(ctx.$sel);
                if (jam) ctx.$metaJam.val(jam);
                if (petugas) ctx.$metaPetugas.val(petugas);
                if (jam && ctx.$link.length) ctx.$link.attr('data-jam', jam);
                if (petugas && ctx.$link.length) ctx.$link.attr('data-petugas', petugas);
            }

            function normalizePetugasNameKey(name) {
                return String(name || '')
                    .toLowerCase()
                    .replace(/[,.]/g, '')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            function findPetugasOptionValue($sel, petugasName) {
                var target = normalizePetugasNameKey(petugasName);
                if (!target) return null;
                var matched = null;
                $sel.find('option').each(function() {
                    var val = $(this).val();
                    if (normalizePetugasNameKey(val) === target) {
                        matched = val;
                        return false;
                    }
                });
                return matched;
            }

            function dedupePetugasSelectOptions($sel) {
                if (!$sel || !$sel.length) return;
                var seen = {};
                var selectedValue = $sel.val();
                $sel.find('option').each(function() {
                    var val = ($(this).val() || '').trim();
                    if (val === '') return;
                    var key = normalizePetugasNameKey(val);
                    if (seen[key]) {
                        if ($(this).is(':selected')) {
                            selectedValue = seen[key];
                        }
                        $(this).remove();
                    } else {
                        seen[key] = val;
                    }
                });
                if (selectedValue) {
                    var matched = findPetugasOptionValue($sel, selectedValue);
                    if (matched) $sel.val(matched);
                }
            }

            function restoreVerificationStepFromLocalStorage(stepKey, suffix) {
                var raw;
                try {
                    raw = localStorage.getItem(verificationStepStorageKey(stepKey));
                } catch (e) {
                    return;
                }
                if (!raw) return;
                var data;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    return;
                }
                var ctx = getVerificationStepRowContext(stepKey, suffix);
                if (!ctx.$start.length) return;
                if (data.jam) {
                    var jam = String(data.jam);
                    if (/^\d{4}-\d{2}-\d{2}/.test(jam) && window.isSameDay) {
                        var p = jam.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2})/);
                        if (p) jam = String(p[4]).padStart(2, '0') + ':' + p[5];
                    } else if (/^\d{4}-\d{2}-\d{2}/.test(jam) && !window.isSameDay) {
                        var p2 = jam.match(/^(\d{4})-(\d{2})-(\d{2})\s+(\d{1,2}):(\d{2})/);
                        if (p2) jam = p2[3] + '/' + p2[2] + '/' + p2[1] + ' ' +
                            String(p2[4]).padStart(2, '0') + ':' + p2[5];
                    }
                    ctx.$start.val(jam);
                    if (ctx.$stop.length) ctx.$stop.val(jam);
                }
                if (data.nama_petugas && ctx.$sel.length) {
                    var matchedPetugas = findPetugasOptionValue(ctx.$sel, data.nama_petugas);
                    if (matchedPetugas) {
                        ctx.$sel.val(matchedPetugas);
                    } else {
                        ctx.$sel.append($('<option>', {
                            value: data.nama_petugas,
                            text: data.nama_petugas,
                            selected: true
                        }));
                    }
                    dedupePetugasSelectOptions(ctx.$sel);
                }
                updateVerificationStepMeta(ctx);
            }

            function syncVerificationStepActionButton(ctx) {
                if (!ctx.$link.length) return;
                var requireJam = ctx.$link.attr('data-require-jam') !== '0';
                var requirePetugas = ctx.$link.attr('data-require-petugas') !== '0';
                var jam = (ctx.$start.val() || '').trim();
                var petugas = readPetugasFromSelect(ctx.$sel);
                var ready = true;
                if (requireJam && !jam) ready = false;
                if (requirePetugas && !petugas) ready = false;
                ctx.$link.find('.verification-step-action-btn').prop('disabled', !ready);
            }

            function syncAllVerificationStepActionButtons() {
                $('a.link-input-verification-step').each(function() {
                    var $link = $(this);
                    syncVerificationStepActionButton(getVerificationStepRowContext(
                        $link.attr('data-step-key'),
                        $link.attr('data-suffix') || ''
                    ));
                });
            }

            function showVerificationStepWarning(text) {
                if (typeof swal !== 'undefined') {
                    swal({ title: 'Perhatian', text: text, icon: 'warning' });
                } else {
                    alert(text);
                }
            }

            function syncAnalitikSubmitButtons() {
                ['#analitik', '#analitik-update'].forEach(function(rowSelector) {
                    var $row = $(rowSelector);
                    if (!$row.length || !$row.is(':visible')) return;

                    var $btn = $row.find('button.js-analitik-step-submit');
                    if (!$btn.length) return;

                    var $start = $row.find('input[name="start_date"]');
                    var $sel = $row.find('select[name="nama_petugas"]');
                    if (!$start.length || !$sel.length) return;

                    if ($start.prop('disabled') || $sel.prop('disabled')) {
                        $btn.prop('disabled', true);
                        return;
                    }

                    var jam = readAnalitikJamValue($start);
                    var petugas = ($sel.val() || '').trim();
                    var ready = !!(jam && petugas);
                    $btn.prop('disabled', !ready);
                    if (ready) {
                        $btn.removeAttr('disabled');
                    }
                });
            }
            window.syncAnalitikSubmitButtons = syncAnalitikSubmitButtons;

            ['penerima', 'pemeriksa', 'verifikasi', 'validasi'].forEach(function(stepKey) {
                $('a.link-input-verification-step[data-step-key="' + stepKey + '"]').each(function() {
                    restoreVerificationStepFromLocalStorage(stepKey, $(this).attr('data-suffix') || '');
                });
            });

            $('.verification-step-petugas').each(function() {
                dedupePetugasSelectOptions($(this));
            });

            syncAllVerificationStepActionButtons();
            syncAnalitikSubmitButtons();
            setTimeout(syncAnalitikSubmitButtons, 100);
            setTimeout(syncAnalitikSubmitButtons, 500);

            $(document).on('change input', '.verification-step-jam, .verification-step-petugas', function() {
                var stepKey = $(this).data('step-key') || '';
                var suffix = $(this).data('suffix') || '';
                var ctx = getVerificationStepRowContext(stepKey, suffix);
                if (ctx.$stop.length) ctx.$stop.val(ctx.$start.val());
                updateVerificationStepMeta(ctx);
                syncVerificationStepActionButton(ctx);
                var jam = (ctx.$metaJam.val() || readJamFromVerificationStepInput(ctx.$start) || '').trim();
                var petugas = (ctx.$metaPetugas.val() || readPetugasFromSelect(ctx.$sel) || '').trim();
                saveVerificationStepToLocalStorage(stepKey, jam, petugas);
            });

            $(document).on('change input', '#analitik input[name="start_date"], #analitik select[name="nama_petugas"], #analitik-update input[name="start_date"], #analitik-update select[name="nama_petugas"]', function() {
                syncAnalitikSubmitButtons();
            });

            $(document).on('click', 'a.link-input-verification-step, a.link-input-verification-step *', function(e) {
                var $link = $(this).closest('a.link-input-verification-step');
                if (!$link.length) return;

                var baseHref = $link.attr('data-base-href') || ($link.attr('href') || '').split('?')[0];
                if (!baseHref || baseHref === 'javascript:void(0)') return;

                e.preventDefault();

                var stepKey = $link.attr('data-step-key') || '';
                var suffix = $link.attr('data-suffix') || '';
                var ctx = getVerificationStepRowContext(stepKey, suffix);
                var requireJam = $link.attr('data-require-jam') !== '0';
                var requirePetugas = $link.attr('data-require-petugas') !== '0';

                composeVerificationStepDates(ctx);
                updateVerificationStepMeta(ctx);

                var jam = (ctx.$metaJam.val() || readJamFromVerificationStepInput(ctx.$start) || '').trim();
                var petugas = (ctx.$metaPetugas.val() || readPetugasFromSelect(ctx.$sel) || '').trim();

                if ((requireJam && !jam) || (requirePetugas && !petugas)) {
                    showVerificationStepWarning('Isi jam dan nama petugas terlebih dahulu.');
                    return;
                }

                saveVerificationStepToLocalStorage(stepKey, jam, petugas);
                var sep = baseHref.indexOf('?') >= 0 ? '&' : '?';
                window.location.href = baseHref + sep
                    + 'jam=' + encodeURIComponent(jam)
                    + '&petugas=' + encodeURIComponent(petugas);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            function formatTime(date) {
                let h = date.getHours().toString().padStart(2, '0');
                let m = date.getMinutes().toString().padStart(2, '0');
                return `${h}:${m}`;
            }

            if (document.getElementById('toggle-registrasi')) {
                document.getElementById('toggle-registrasi').addEventListener('click', function() {
                    var registrasiRow = document.getElementById('registrasi');
                    var registrasiUpdateRow = document.getElementById('registrasi-update');

                    if (registrasiRow.style.display === 'none') {
                        registrasiRow.style.display = '';
                        registrasiUpdateRow.style.display = 'none';
                    } else {
                        registrasiRow.style.display = 'none';
                        registrasiUpdateRow.style.display = '';
                    }


                    var registrasiUpdateRowStart = $('#registrasi-update [name="start_date"]').val();
                    initPicker('#registrasi-update [name="start_date"]', window.timeStrToDate(registrasiUpdateRowStart));

                    var registrasiUpdateRowStop = $('#registrasi-update [name="stop_date"]').val();

                    const register_update_stop = flatpickr('#registrasi-update [name="stop_date"]', {
                        // Opsi lain jika diperlukan

                        allowInput: true,
                        locale: "id", // Setting locale to Indonesian
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        defaultDate: registrasiUpdateRowStop
                    });



                    $('#registrasi-update [name="stop_date"]').inputmask('99:99', {
                        placeholder: 'hh:mm',

                    });
                });
            }

            // Centang Pengambil Sample → mode edit (jam + petugas)
            $(document).on('click', '#toggle-sample', function() {
                var sampleRow = document.getElementById('sample');
                var sampleUpdateRow = document.getElementById('sample-update');
                if (!sampleRow || !sampleUpdateRow) {
                    return;
                }

                if (sampleRow.style.display === 'none') {
                    sampleRow.style.display = '';
                    sampleUpdateRow.style.display = 'none';
                    return;
                }

                sampleRow.style.display = 'none';
                sampleUpdateRow.style.display = '';

                try {
                    var $start = $('#sample-update [name="start_date"]').first();
                    var $stop = $('#sample-update [name="stop_date"]').first();
                    var sampleUpdateRowStart = ($start.val() || '').trim();
                    var timeOnly = sampleUpdateRowStart;
                    var dmyMatch = sampleUpdateRowStart.match(/(\d{1,2}:\d{2})\s*$/);
                    if (dmyMatch) {
                        timeOnly = dmyMatch[1];
                    }
                    if ($start.length && typeof initPicker === 'function') {
                        initPicker('#' + $start.attr('id'), window.timeStrToDate(timeOnly));
                    }
                    if ($stop.length && typeof initPicker === 'function') {
                        var stopVal = ($stop.val() || sampleUpdateRowStart || '').trim();
                        var stopTime = stopVal;
                        var stopMatch = stopVal.match(/(\d{1,2}:\d{2})\s*$/);
                        if (stopMatch) {
                            stopTime = stopMatch[1];
                        }
                        initPicker('#' + $stop.attr('id'), window.timeStrToDate(stopTime));
                    }
                } catch (e) {
                    console.warn('Gagal init picker sample-update:', e);
                }
            });
            $('#toggle-sample').css('cursor', 'pointer');

            if (document.getElementById('toggle-penerima-sampel')) {

                document.getElementById('toggle-penerima-sampel').addEventListener('click', function() {
                    var penerimaSampelRow = document.getElementById('penerima-sampel');
                    var penerimaSampelUpdateRow = document.getElementById('penerima-sampel-update');

                    if (penerimaSampelRow.style.display === 'none') {
                        penerimaSampelRow.style.display = '';
                        penerimaSampelUpdateRow.style.display = 'none';
                    } else {
                        penerimaSampelRow.style.display = 'none';
                        penerimaSampelUpdateRow.style.display = '';
                    }
                    var penerimaSampelUpdateRowStart = $('#penerima-sampel-update [name="start_date"]').val();
                    initPicker('#penerima-sampel-update [name="start_date"]', window.timeStrToDate(penerimaSampelUpdateRowStart));

                    var penerimaSampelUpdateRowStop = $('#penerima-sampel-update [name="stop_date"]').val();

                    const penerima_sampel_update_stop = flatpickr('#penerima-sampel-update [name="stop_date"]', {
                        allowInput: true,
                        locale: "id",
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        time_24hr: true,
                        defaultDate: penerimaSampelUpdateRowStop
                    });




                    $('#penerima-sampel-update [name="stop_date"]').inputmask('99:99', {
                        placeholder: 'hh:mm'
                    });
                });

            }

            if (document.getElementById('toggle-analitik')) {

                document.getElementById('toggle-analitik').addEventListener('click', function() {
                    var analitikRow = document.getElementById('analitik');
                    var analitikUpdateRow = document.getElementById('analitik-update');

                    if (analitikRow.style.display === 'none') {
                        analitikRow.style.display = '';
                        analitikUpdateRow.style.display = 'none';
                    } else {
                        analitikRow.style.display = 'none';
                        analitikUpdateRow.style.display = '';
                    }

                    var analitikUpdateRowStart = $('#analitik-update [name="start_date"]').val();
                    initPicker('#analitik-update [name="start_date"]', window.timeStrToDate(analitikUpdateRowStart));

                    var analitikUpdateRowStop = $('#analitik-update [name="stop_date"]').val();

                    const analitik_update_stop = flatpickr('#analitik-update [name="stop_date"]', {
                        // Opsi lain jika diperlukan

                        allowInput: true,
                        locale: "id", // Setting locale to Indonesian
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        defaultDate: analitikUpdateRowStop
                    });



                    $('#analitik-update [name="stop_date"]').inputmask('99:99', {
                        placeholder: 'hh:mm',

                    });

                    if (typeof window.syncAnalitikSubmitButtons === 'function') {
                        setTimeout(window.syncAnalitikSubmitButtons, 50);
                    }
                });
            }

            if (document.getElementById('toggle-hasil-px')) {

                document.getElementById('toggle-hasil-px').addEventListener('click', function() {
                    var bacaHasilRow = document.getElementById('hasil-px');
                    var bacaHasilUpdateRow = document.getElementById('hasil-px-update');

                    if (bacaHasilRow.style.display === 'none') {
                        bacaHasilRow.style.display = '';
                        bacaHasilUpdateRow.style.display = 'none';
                    } else {
                        bacaHasilRow.style.display = 'none';
                        bacaHasilUpdateRow.style.display = '';
                    }
                    var bacaHasilUpdateRowStart = $('#hasil-px-update [name="start_date"]').val();
                    initPicker('#hasil-px-update [name="start_date"]', window.timeStrToDate(bacaHasilUpdateRowStart));

                    var bacaHasilUpdateRowStop = $('#hasil-px-update [name="stop_date"]').val();

                    const bacaHasil_update_stop = flatpickr('#hasil-px-update [name="stop_date"]', {
                        // Opsi lain jika diperlukan

                        allowInput: true,
                        locale: "id", // Setting locale to Indonesian
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        defaultDate: bacaHasilUpdateRowStop
                    });



                    $('#hasil-px-update [name="stop_date"]').inputmask('99:99', {
                        placeholder: 'hh:mm',

                    });
                });

            }


            if (document.getElementById('toggle-verifikasi')) {

                document.getElementById('toggle-verifikasi').addEventListener('click', function() {
                    var verifikasiRow = document.getElementById('verifikasi');
                    var verifikasiUpdateRow = document.getElementById('verifikasi-update');

                    if (verifikasiRow.style.display === 'none') {
                        verifikasiRow.style.display = '';
                        verifikasiUpdateRow.style.display = 'none';
                    } else {
                        verifikasiRow.style.display = 'none';
                        verifikasiUpdateRow.style.display = '';
                    }

                    var verifikasiUpdateRowStart = $('#verifikasi-update [name="start_date"]').val();
                    initPicker('#verifikasi-update [name="start_date"]', window.timeStrToDate(verifikasiUpdateRowStart));

                    var verifikasiUpdateRowStop = $('#verifikasi-update [name="stop_date"]').val();

                    const verifikasi_update_stop = flatpickr('#verifikasi-update [name="stop_date"]', {
                        // Opsi lain jika diperlukan

                        allowInput: true,
                        locale: "id", // Setting locale to Indonesian
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        defaultDate: verifikasiUpdateRowStop
                    });



                    $('#verifikasi-update [name="stop_date"]').inputmask('99:99', {
                        placeholder: 'hh:mm',

                    });
                });

            }

            if (document.getElementById('toggle-validasi')) {

                document.getElementById('toggle-validasi').addEventListener('click', function() {
                    var validasiRow = document.getElementById('validasi');
                    var validasiUpdateRow = document.getElementById('validasi-update');

                    if (validasiRow.style.display === 'none') {
                        validasiRow.style.display = '';
                        validasiUpdateRow.style.display = 'none';
                    } else {
                        validasiRow.style.display = 'none';
                        validasiUpdateRow.style.display = '';
                    }

                    var validasiUpdateRowStart = $('#validasi-update [name="start_date"]').val();
                    initPicker('#validasi-update [name="start_date"]', window.timeStrToDate(validasiUpdateRowStart));

                    var validasiUpdateRowStop = $('#validasi-update [name="stop_date"]').val();

                    const validasi_update_stop = flatpickr('#validasi-update [name="stop_date"]', {
                        // Opsi lain jika diperlukan

                        allowInput: true,
                        locale: "id", // Setting locale to Indonesian
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i", // 24-hour format
                        time_24hr: true,
                        defaultDate: validasiUpdateRowStop
                    });


                    $('#validasi-update [name="stop_date"]').inputmask('99:99', {
                        placeholder: 'hh:mm',

                    });
                });
            }

            function formatDate(date) {
                let year = date.getFullYear();
                let month = String(date.getMonth() + 1).padStart(2, '0');
                let day = String(date.getDate()).padStart(2, '0');
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }
        });
    </script>
    <script>
        $(document).ready(function() {

            const printVerifikasiDate = flatpickr('#tanggal-cetak-verifikasi', {
                allowInput: true,
                locale: "id",
                enableTime: false,
                dateFormat: "d/m/Y",
            });

            var printVerifikasiUpdateDate = $('#tanggal-cetak-verifikasi').val();


            if (printVerifikasiUpdateDate != "") {
                printVerifikasiDate.setDate(formatDate(new Date(printVerifikasiUpdateDate)), true);
            } else {
                printVerifikasiDate.setDate(formatDate(new Date()), true);
            }



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
        // Update input tersembunyi saat halaman dimuat (guard jika elemen tidak ada)
        window.onload = function() {
            if (document.getElementById('nama_petugas_select') && document.getElementById('nama_petugas_input')) {
                updateNamaPetugas();
            }
        };

        function updateNamaPetugas() {
            var selectEl = document.getElementById('nama_petugas_select');
            var hiddenEl = document.getElementById('nama_petugas_input');
            if (!selectEl || !hiddenEl) return; // guard
            var selectedName = selectEl.value || '';
            hiddenEl.value = selectedName;
        }
    </script>
    <script>
        let namaPetugasValue = null;

        // Check if BSRE is enabled from environment
        const BSRE_ENABLED = {{ config('app.bsre_use', false) ? 'true' : 'false' }};

        function submitVerificationForm(form) {
            if (!form) {
                return;
            }
            if (window.jQuery) {
                var $form = jQuery(form);
                if (typeof window.composeVerificationFormDatetime === 'function') {
                    window.composeVerificationFormDatetime($form);
                }
                HTMLFormElement.prototype.submit.call(form);
                return;
            }
            form.submit();
        }

        function checkNikAndPassword(namaPetugas, className) {
            event.preventDefault();

            const form = document.getElementById(className) || document.querySelector(`.${className}`);

            // If BSRE is disabled, directly submit the form
            if (!BSRE_ENABLED) {
                submitVerificationForm(form);
                return;
            }

            // If BSRE is enabled, check NIK and Password
            namaPetugasValue = namaPetugas;
            $.ajax({
                url: "{{ url('elits-samples/check-petugas') }}/" + encodeURIComponent(namaPetugas),
                type: "GET",
                success: function(response) {
                    if (response === "true") {
                        submitVerificationForm(form);
                    } else {
                        $('#inputNikAndPasword').modal('show');
                    }
                },
                error: function() {
                    swal({
                        title: "Failed!",
                        text: "An error occurred. Please try again.",
                        icon: "error",
                    });
                }
            });
        }

        function submitNikAndPassword() {
            event.preventDefault();

            if (namaPetugasValue != null) {
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
                            namaPetugasValue = null;
                            $('#inputNikAndPasword').modal('hide');

                            if (formClassNameValue) {
                                const form = document.getElementById(formClassNameValue) || document.querySelector(`.${formClassNameValue}`);
                                submitVerificationForm(form);
                            }
                        } else {
                            swal({
                                title: "Failed!",
                                text: "Failed to submit data. Please try again.",
                                icon: "error",
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Failed!",
                            text: "An error occurred. Please try again.",
                            icon: "error",
                        });
                    }
                });
            }
        }
    </script>

    <script>
        // Disable semua action button di tabel jika belum ada pemeriksaan
        $(document).ready(function() {
            @if(!$hasPemeriksaan)
                function disableAllTableActions() {
                    // Disable semua button di dalam tabel (kecuali button secondary/link)
                    $('.table-no-pemeriksaan').find('button.btn-success, button.btn-primary, button.btn-warning').not(':disabled').each(function() {
                        if (!$(this).attr('disabled')) {
                            $(this).prop('disabled', true);
                            $(this).attr('title', 'Belum ada pemeriksaan');
                            $(this).css({
                                'opacity': '0.5',
                                'cursor': 'not-allowed'
                            });
                        }
                    });

                    // Disable semua input field di dalam form (termasuk di row Pengambil Sample)
                    $('.table-no-pemeriksaan').find('input[type="text"]:not([disabled]), input[type="number"]:not([disabled]), input[type="time"]:not([disabled]), select:not([disabled])').each(function() {
                        $(this).prop('disabled', true);
                        $(this).attr('title', 'Belum ada pemeriksaan');
                        $(this).css({
                            'opacity': '0.5',
                            'cursor': 'not-allowed',
                            'background-color': '#e9ecef'
                        });
                    });

                    // Disable semua link yang berisi button Input (termasuk di row Pengambil Sample)
                    $('.table-no-pemeriksaan').find('a').each(function() {
                        var $button = $(this).find('button.btn-primary');
                        if ($button.length > 0 && ($button.text().trim() === 'Input' || $button.text().trim().includes('Input'))) {
                            $(this).css('pointer-events', 'none');
                            $(this).attr('href', 'javascript:void(0)');
                            $button.prop('disabled', true);
                            $button.attr('title', 'Belum ada pemeriksaan');
                            $button.css({
                                'opacity': '0.5',
                                'cursor': 'not-allowed'
                            });
                        }
                    });

                    // Disable semua form di tabel (termasuk formPengambilanSampel, formSample1, dll)
                    $('.table-no-pemeriksaan').find('form').each(function() {
                        $(this).on('submit', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            swal({
                                title: "Tidak Dapat Melakukan Action",
                                text: "Belum ada pemeriksaan yang ditambahkan. Silakan tambahkan pemeriksaan terlebih dahulu.",
                                icon: "warning",
                                button: "OK"
                            });
                            return false;
                        });
                    });

                    // Prevent modal opening (termasuk signatureSampleModal)
                    $('.table-no-pemeriksaan').find('button[data-toggle="modal"]').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        swal({
                            title: "Tidak Dapat Melakukan Action",
                            text: "Belum ada pemeriksaan yang ditambahkan. Silakan tambahkan pemeriksaan terlebih dahulu.",
                            icon: "warning",
                            button: "OK"
                        });
                        return false;
                    });

                    // Prevent link clicks ke create-permohonan-uji-sample
                    $('.table-no-pemeriksaan').find('a[href*="create-permohonan-uji-sample"]').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        swal({
                            title: "Tidak Dapat Melakukan Action",
                            text: "Belum ada pemeriksaan yang ditambahkan. Silakan tambahkan pemeriksaan terlebih dahulu.",
                            icon: "warning",
                            button: "OK"
                        });
                        return false;
                    });

                    // Disable toggle/edit button untuk row Pengambil Sample
                    $('.table-no-pemeriksaan').find('#toggle-sample, #toggle-registrasi').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        swal({
                            title: "Tidak Dapat Melakukan Action",
                            text: "Belum ada pemeriksaan yang ditambahkan. Silakan tambahkan pemeriksaan terlebih dahulu.",
                            icon: "warning",
                            button: "OK"
                        });
                        return false;
                    });
                }

                // Jalankan saat document ready
                disableAllTableActions();

                // Jalankan lagi setelah 500ms untuk memastikan semua elemen sudah ter-render
                setTimeout(function() {
                    disableAllTableActions();
                }, 500);

                // Jalankan lagi setelah 1 detik untuk memastikan semua elemen dinamis sudah ter-render
                setTimeout(function() {
                    disableAllTableActions();
                }, 1000);

                // Monitor perubahan DOM untuk elemen yang muncul secara dinamis (seperti saat toggle row)
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes.length) {
                            disableAllTableActions();
                        }
                    });
                });

                // Observe perubahan pada tabel
                var tableElement = document.querySelector('.table-no-pemeriksaan');
                if (tableElement) {
                    observer.observe(tableElement, {
                        childList: true,
                        subtree: true
                    });
                }

                // Re-run saat ada event toggle atau perubahan visibility
                $(document).on('click', '.table-no-pemeriksaan [id^="toggle-"]', function() {
                    setTimeout(function() {
                        disableAllTableActions();
                    }, 100);
                });
            @endif
        });
    </script>

    <script>
        // Pengaturan untuk modal cetak verifikasi
        $(document).ready(function() {
            var $fontsizeSlider = $('#verif-print-fontsize-slider');
            var $fontsizeInput = $('#verif-print-fontsize-input');
            var $fontsizeMinus = $('#verif-print-fontsize-minus');
            var $fontsizePlus = $('#verif-print-fontsize-plus');

            var $paddingSlider = $('#verif-print-padding-slider');
            var $paddingInput = $('#verif-print-padding-input');
            var $paddingMinus = $('#verif-print-padding-minus');
            var $paddingPlus = $('#verif-print-padding-plus');

            var $toggleKop = $('#verif-print-toggle-kop');
            var $kopLabel = $('#verif-print-kop-label-text');

            // Sync fontsize slider dan input
            function updateFontsizeUI(val) {
                val = Math.min(16, Math.max(8, parseFloat(val) || 12));
                val = Math.round(val * 2) / 2; // step 0.5
                $fontsizeSlider.val(val);
                $fontsizeInput.val(val);
            }

            // Sync padding slider dan input
            function updatePaddingUI(val) {
                val = Math.min(16, Math.max(0, parseFloat(val)));
                val = isNaN(val) ? 5 : val;
                val = Math.round(val * 2) / 2; // step 0.5
                $paddingSlider.val(val);
                $paddingInput.val(val);
            }

            // Update kop label
            function updateKopUI(checked) {
                $kopLabel.text(checked ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)');
                if (!checked) {
                    $toggleKop.removeAttr('checked');
                    // Tambahkan hidden input untuk memastikan nilai dikirim saat unchecked
                    if ($toggleKop.siblings('input[type="hidden"][name="showKop"]').length === 0) {
                        $toggleKop.after('<input type="hidden" name="showKop" value="0">');
                    } else {
                        $toggleKop.siblings('input[type="hidden"][name="showKop"]').val('0');
                    }
                } else {
                    $toggleKop.attr('checked', 'checked');
                    $toggleKop.siblings('input[type="hidden"][name="showKop"]').remove();
                }
            }

            // Event handlers untuk fontsize
            $fontsizeSlider.on('input change', function() { updateFontsizeUI($(this).val()); });
            $fontsizeInput.on('input change', function() { updateFontsizeUI($(this).val()); });
            $fontsizeMinus.on('click', function() { updateFontsizeUI(parseFloat($fontsizeSlider.val()) - 0.5); });
            $fontsizePlus.on('click', function() { updateFontsizeUI(parseFloat($fontsizeSlider.val()) + 0.5); });

            // Event handlers untuk padding
            $paddingSlider.on('input change', function() { updatePaddingUI($(this).val()); });
            $paddingInput.on('input change', function() { updatePaddingUI($(this).val()); });
            $paddingMinus.on('click', function() { updatePaddingUI(parseFloat($paddingSlider.val()) - 0.5); });
            $paddingPlus.on('click', function() { updatePaddingUI(parseFloat($paddingSlider.val()) + 0.5); });

            // Event handler untuk kop
            $toggleKop.on('change', function() { updateKopUI($(this).is(':checked')); });
        });
    </script>

    <script>
        $(document).ready(function() {
            function kirimHasilWhatsAppManual($btn) {
                if ($btn.prop('disabled')) {
                    return;
                }

                var phone = @json($phone_pasien_wa ?? '');
                var confirmText = phone
                    ? 'PDF hasil akan dikirim ke WhatsApp ' + phone + '.'
                    : 'PDF hasil akan dikirim via WhatsApp.';

                Swal.fire({
                    title: 'Kirim hasil via WhatsApp?',
                    text: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#25d366',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (!result.isConfirmed && !result.value) {
                        return;
                    }

                    var $buttons = $('#btnKirimHasilWhatsApp, #btnKirimHasilWhatsAppInline');
                    $buttons.prop('disabled', true);
                    var originalHtml = $btn.html();
                    $buttons.each(function() {
                        $(this).data('original-html', $(this).html());
                        $(this).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');
                    });

                    $.ajax({
                        url: "{{ route('elits-permohonan-uji-klinik-2.resend-hasil-whatsapp', $item->id_permohonan_uji_klinik) }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            Swal.fire({
                                title: res.status ? 'Berhasil' : 'Gagal',
                                text: res.pesan || (res.status ? 'Hasil terkirim via WhatsApp.' : 'Gagal mengirim WhatsApp.'),
                                icon: res.status ? 'success' : 'error'
                            });
                        },
                        error: function(xhr) {
                            var pesan = (xhr.responseJSON && xhr.responseJSON.pesan)
                                ? xhr.responseJSON.pesan
                                : 'Gagal mengirim WhatsApp.';
                            Swal.fire({
                                title: 'Gagal',
                                text: pesan,
                                icon: 'error'
                            });
                        },
                        complete: function() {
                            $buttons.each(function() {
                                var html = $(this).data('original-html') || originalHtml;
                                $(this).html(html);
                            });
                            @if (!empty($bisa_kirim_hasil_whatsapp_manual))
                                $buttons.prop('disabled', false);
                            @endif
                        }
                    });
                });
            }

            $(document).on('click', '#btnKirimHasilWhatsApp, #btnKirimHasilWhatsAppInline', function() {
                kirimHasilWhatsAppManual($(this));
            });
        });
    </script>
@endsection
