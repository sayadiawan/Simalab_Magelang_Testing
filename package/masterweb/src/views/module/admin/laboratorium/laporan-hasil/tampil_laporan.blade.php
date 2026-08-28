@extends('masterweb::template.admin.layout')
@section('title')
    Laporan Hasil
@endsection

@section('content')
    <style>
        /* PDF preview full height */
        .pdf-preview-container {
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            overflow: hidden;
            height: calc(100vh - 200px);
            min-height: 720px;
            width: 100%;
        }

        .pdf-preview-container iframe {
            width: 100%;
            height: 100%;
        }

        /* Modal Pengaturan Hasil: body bisa di-scroll, header/footer tetap */
        .modal-dialog.modal-body-scrollable {
            max-height: calc(100vh - 2rem);
            margin: 1rem auto;
        }

        .modal-dialog.modal-body-scrollable.modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 2rem);
        }

        .modal-dialog.modal-body-scrollable .modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            width: 100%;
        }

        .modal-dialog.modal-body-scrollable .modal-header,
        .modal-dialog.modal-body-scrollable .modal-footer {
            flex-shrink: 0;
        }

        .modal-dialog.modal-body-scrollable .modal-body {
            overflow-y: auto !important;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .modal-dialog.modal-body-scrollable .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

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

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-permohonan-uji') }}">
                                        Permohonan Uji</a>
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ url('/elits-samples', [$sample->permohonan_uji_id]) }}">
                                        Daftar Pengujian</a>
                                </li>

                                <li class="breadcrumb-item active" aria-current="page"><span>Laporan Hasil</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fa fa-file-pdf-o mr-2"></i>
            <h4 class="mb-0">Laporan Hasil</h4>
        </div>
        <div class="card-body" style="background-color: #f8f9fa;">
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border-0 mb-3" style="background-color:#e3f2fd;">
                                <div class="card-body">
                                    @php
                                        $defaultFontsizeHasil = old('fontsize_hasil', $sample->fontsize_hasil_baca_hasil ?? 12);
                                        $defaultLineHeightHasil = old('line_height_hasil');
                                        if ($defaultLineHeightHasil === null) {
                                            $lineHeightDb = $sample->line_height_hasil_baca_hasil ?? null;
                                            $defaultLineHeightHasil = $lineHeightDb;
                                        }
                                        $defaultLineHeightHasil = ($defaultLineHeightHasil === null || (float) $defaultLineHeightHasil === 1.5) ? 1 : $defaultLineHeightHasil;

                                        $defaultPaddingHasil = old('padding_hasil');
                                        if ($defaultPaddingHasil === null) {
                                            $paddingDb = $sample->padding_hasil_baca_hasil ?? null;
                                            $defaultPaddingHasil = $paddingDb;
                                        }
                                        $defaultPaddingHasil = ($defaultPaddingHasil === null || (float) $defaultPaddingHasil === 4.0) ? 1 : $defaultPaddingHasil;
                                        $defaultShowKopHasil = (int) old('show_kop_hasil', $sample->show_kop_hasil_baca_hasil ?? 1);
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                                        <h5 class="card-title text-primary mb-0">
                                            <i class="fa fa-file-pdf-o mr-2"></i>Laporan Hasil PDF
                                        </h5>
                                        <button type="button" class="btn btn-warning btn-sm" id="btn-open-pengaturan-hasil" data-toggle="modal" data-target="#modalPengaturanHasil">
                                            <i class="fa fa-cog mr-1"></i>Pengaturan Hasil
                                        </button>
                                    </div>
                                    @php
                                        $isKimia =
                                            $sample->nama_laboratorium == 'Kimia' ||
                                            $sample->kode_laboratorium == 'KIM' ||
                                            $sample->kode_laboratorium == 'KIMIA';

                                        // Khusus KIM + Makanan/Minuman/Lainnya, gunakan format print-kimia.
                                        // Jika MBI (mikro), tetap gunakan format print-mikro.
                                        $isMakananMinumanLainnya =
                                            isset($sample->name_sample_type) &&
                                            $sample->name_sample_type === 'Makanan/Minuman/Lainnya';
                                        $useKimiaMakananFormat = $isKimia && $isMakananMinumanLainnya;

                                        if ($useKimiaMakananFormat) {
                                            // URL print-kimia untuk makanan/minuman/lainnya di lab kimia
                                            $previewUrl = url(
                                                'elits-release/print-kimia/' .
                                                    $sample->permohonan_uji_id .
                                                    '/' .
                                                    $sample->typesample_samples .
                                                    '?agenda=&signOption=0',
                                            );
                                            $kimiaUrl = $previewUrl;
                                            $mikroUrl = $previewUrl;
                                        } else {
                                            // URL print LHU Kimia (per sampel)
                                            $kimiaUrl = url(
                                                'elits-release/printLHU/' .
                                                    $sample->id_samples .
                                                    '/' .
                                                    $sample->id_laboratorium .
                                                    '?agenda=&signOption=0',
                                            );

                                            // Base URL print mikro (gabungan)
                                            $mikroBase = url(
                                                'elits-release/print-mikro/' .
                                                    $sample->permohonan_uji_id .
                                                    '/' .
                                                    $sample->typesample_samples,
                                            );

                                            // Query default: gabungan semua sample mikro di permohonan ini
                                            $mikroQuery = '?agenda=&signOption=0&printall=on';

                                            // KHUSUS UNTUK JENIS SAMPEL "Makanan/Minuman/Lainnya":
                                            // Tidak perlu kirim jenis_makanan_id karena logika sudah berubah
                                            // untuk loop per sampel dan ambil baku mutu masing-masing
                                            if ($sample->name_sample_type !== 'Makanan/Minuman/Lainnya') {
                                                // Jika punya jenis makanan → controller akan membatasi ke jenis_makanan_id tsb (untuk jenis sampel lain)
                                                if (!empty($sample->jenis_makanan_id)) {
                                                    $mikroQuery .=
                                                        '&jenis_makanan_id=' . $sample->jenis_makanan_id;
                                                }
                                            }

                                            try {
                                                $mikroSampleIds = \Smt\Masterweb\Models\Sample::query()
                                                    ->where(
                                                        'tb_samples.permohonan_uji_id',
                                                        $sample->permohonan_uji_id,
                                                    )
                                                    ->join('tb_sample_method', function ($join) {
                                                        $join
                                                            ->on(
                                                                'tb_sample_method.sample_id',
                                                                '=',
                                                                'tb_samples.id_samples',
                                                            )
                                                            ->whereNull('tb_sample_method.deleted_at');
                                                    })
                                                    ->join('ms_laboratorium', function ($join) {
                                                        $join
                                                            ->on(
                                                                'ms_laboratorium.id_laboratorium',
                                                                '=',
                                                                'tb_sample_method.laboratorium_id',
                                                            )
                                                            ->whereNull('ms_laboratorium.deleted_at');
                                                    })
                                                    ->where('ms_laboratorium.kode_laboratorium', 'MBI')
                                                    ->pluck('tb_samples.id_samples')
                                                    ->unique()
                                                    ->toArray();
                                                foreach ($mikroSampleIds as $sid) {
                                                    $mikroQuery .= '&printSamples[]=' . $sid;
                                                }
                                            } catch (\Throwable $e) {
                                                $mikroQuery .= '&printSamples[]=' . $sample->id_samples;
                                            }

                                            $mikroUrl = $mikroBase . $mikroQuery;

                                            $previewUrl = $isKimia ? $kimiaUrl : $mikroUrl;
                                        }
                                    @endphp
                                    <div class="alert alert-info mb-3">
                                        <i class="fa fa-info-circle mr-1"></i>
                                        Tautan sumber PDF:
                                        @if ($useKimiaMakananFormat)
                                            <a href="{{ $previewUrl }}" target="_blank">Link</a>
                                        @elseif ($sample->kode_laboratorium == 'KIM')
                                            <a href="{{ $kimiaUrl }}" target="_blank">Link</a>
                                        @else
                                            <a href="{{ $mikroUrl }}" target="_blank">Link</a>
                                        @endif
                                    </div>
                                    <div class="pdf-preview-container" id="pdfPreviewContainer">
                                        <iframe id="pdfIframe" src="{{ $previewUrl }}" frameborder="0"
                                            onerror="handlePdfError()"></iframe>
                                        <div id="pdfFallback"
                                            style="display: none; height: 100%; flex-direction: column; align-items: center; justify-content: center; background-color: #f5f5f5; border-radius: 6px;">
                                            <div class="text-center p-4">
                                                <i class="fa fa-file-pdf-o"
                                                    style="font-size: 48px; color: #dc3545; margin-bottom: 20px;"></i>
                                                <h5 class="mb-3">PDF tidak dapat ditampilkan di sini</h5>
                                                <p class="text-muted mb-4">Browser Anda memblokir penampilan
                                                    PDF dari localhost dalam iframe. Silakan buka PDF di tab
                                                    baru.</p>
                                                <button type="button" class="btn btn-primary btn-lg"
                                                    onclick="openPdfInNewWindow()">
                                                    <i class="fa fa-external-link mr-2"></i>Buka PDF di Tab
                                                    Baru
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pengaturan Hasil (font, line height, padding, kop) — selaras baca-hasil --}}
    <div class="modal fade" id="modalPengaturanHasil" tabindex="-1" role="dialog" aria-labelledby="modalPengaturanHasilLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-body-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalPengaturanHasilLabel">
                        <i class="fa fa-cog mr-2"></i>Pengaturan Hasil
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fa fa-info-circle mr-1"></i>
                        Sesuaikan pengaturan tampilan, lalu klik <strong>Terapkan</strong> untuk memperbarui preview.
                    </p>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-text-height mr-1"></i>Ukuran Font Hasil
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">6</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="fontsize-slider" min="6" max="20" step="0.5" value="{{ $defaultFontsizeHasil }}">
                            <span class="text-muted small ml-2">20</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="fontsize-minus"><i class="fa fa-minus"></i></button>
                            <div class="input-group mx-2" style="width: 90px;">
                                <input type="text" inputmode="decimal" class="form-control text-center font-weight-bold" id="fontsize-input" value="{{ $defaultFontsizeHasil }}">
                                <div class="input-group-append"><span class="input-group-text">pt</span></div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="fontsize-plus"><i class="fa fa-plus"></i></button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white text-center">
                            <span id="fontsize-preview-sample" style="font-size: {{ $defaultFontsizeHasil }}pt;">
                                Contoh: Hemoglobin = <strong>14.5</strong> g/dL
                            </span>
                        </div>
                    </div>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-align-justify mr-1"></i>Jarak Baris (Line Spacing)
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0.5</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="lineheight-slider" min="0.5" max="3.0" step="0.1" value="{{ $defaultLineHeightHasil }}">
                            <span class="text-muted small ml-2">3.0</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="lineheight-minus"><i class="fa fa-minus"></i></button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="text" inputmode="decimal" class="form-control text-center font-weight-bold" id="lineheight-input" value="{{ $defaultLineHeightHasil }}">
                                <div class="input-group-append"><span class="input-group-text">x</span></div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="lineheight-plus"><i class="fa fa-plus"></i></button>
                        </div>
                        <div class="mt-2 p-2 border rounded bg-white">
                            <span id="lineheight-preview-sample" style="line-height: {{ $defaultLineHeightHasil }}; display: block;">
                                Contoh baris pertama: Hemoglobin = <strong>14.5</strong> g/dL<br>
                                Contoh baris kedua: Leukosit = <strong>8.200</strong> /uL
                            </span>
                        </div>
                    </div>

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-1">
                            <i class="fa fa-arrows-v mr-1"></i>Margin Atas/Bawah Baris
                        </label>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small mr-2">0</span>
                            <input type="range" class="custom-range flex-grow-1 mr-2" id="padding-slider" min="0" max="16" step="0.5" value="{{ $defaultPaddingHasil }}">
                            <span class="text-muted small ml-2">16</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="padding-minus"><i class="fa fa-minus"></i></button>
                            <div class="input-group mx-2" style="width: 100px;">
                                <input type="text" inputmode="decimal" class="form-control text-center font-weight-bold" id="padding-input" value="{{ $defaultPaddingHasil }}">
                                <div class="input-group-append"><span class="input-group-text">pt</span></div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="padding-plus"><i class="fa fa-plus"></i></button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            Mengatur padding atas/bawah setiap sel di tabel hasil pemeriksaan.
                        </small>
                    </div>

                    @php
                        $kesmasColWidthUi = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::uiPayload(
                            $sample ?? null,
                            null,
                            $lab ?? ($laboratorium ?? null)
                        );
                    @endphp
                    @include('masterweb::module.admin.laboratorium.analitik.baca-hasil.partials.pengaturan-column-widths', [
                        'sample' => $sample ?? null,
                        'kesmasColWidthUi' => $kesmasColWidthUi,
                    ])

                    <div class="card border-0 bg-light p-3 mb-3">
                        <label class="font-weight-bold mb-2">
                            <i class="fa fa-file-alt mr-1"></i>Kop Surat
                        </label>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-sm text-muted" id="kop-label-text">
                                {{ $defaultShowKopHasil ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)' }}
                            </div>
                            <div class="custom-control custom-switch ml-3">
                                <input type="checkbox" class="custom-control-input" id="toggle-kop" {{ $defaultShowKopHasil ? 'checked' : '' }}>
                                <label class="custom-control-label" for="toggle-kop"></label>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Jika dimatikan, area kop tetap ada namun kosong (tanpa gambar).
                        </small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="fa fa-exclamation-triangle mr-1"></i>
                        Pengaturan diterapkan saat mengklik <strong>Terapkan</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-info" id="btn-terapkan-pengaturan">
                        <i class="fa fa-spinner fa-spin mr-1 d-none" id="pengaturan-loading-icon"></i>
                        <i class="fa fa-check mr-1" id="pengaturan-save-icon"></i>
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('masterweb::module.admin.laboratorium.analitik.baca-hasil.partials.pengaturan-column-widths-script')
    <script>
        var basePreviewUrl = {!! json_encode($previewUrl) !!};
        var saveSettingUrl = {!! json_encode(route('elits-laporan-hasil.save-fontsize-hasil', [$sample->id_samples, $sample->id_laboratorium])) !!};
        var csrfToken = '{{ csrf_token() }}';

        var currentFontsize = parseFloat({{ json_encode((float) $defaultFontsizeHasil) }}) || 12;
        var currentLineHeight = parseFloat({{ json_encode((float) $defaultLineHeightHasil) }}) || 1;
        var currentPadding = parseFloat({{ json_encode((float) $defaultPaddingHasil) }}) || 1;
        var currentShowKop = {{ (int) $defaultShowKopHasil }} ? 1 : 0;

        function getColumnWidthsPayload() {
            if (window.KesmasColWidths && typeof window.KesmasColWidths.collect === 'function') {
                return window.KesmasColWidths.collect();
            }
            try {
                return JSON.parse($('#column_widths_hasil_hidden').val() || '{}');
            } catch (e) {
                return {};
            }
        }

        function buildPreviewUrl() {
            var sep = basePreviewUrl.indexOf('?') !== -1 ? '&' : '?';
            return basePreviewUrl + sep +
                'mode=preview' +
                '&signOption=0' +
                '&fontsize=' + encodeURIComponent(currentFontsize) +
                '&line_height=' + encodeURIComponent(currentLineHeight) +
                '&padding=' + encodeURIComponent(currentPadding) +
                '&show_kop=' + encodeURIComponent(currentShowKop) +
                '&column_widths=' + encodeURIComponent(JSON.stringify(getColumnWidthsPayload())) +
                '&t=' + Date.now();
        }

        function reloadPdfPreview() {
            var iframe = document.getElementById('pdfIframe');
            var fallback = document.getElementById('pdfFallback');
            if (iframe) {
                iframe.style.display = '';
                iframe.src = buildPreviewUrl();
            }
            if (fallback) {
                fallback.style.display = 'none';
            }
        }

        function handlePdfError() {
            showPdfFallback();
        }

        function openPdfInNewWindow() {
            window.open(buildPreviewUrl());
        }

        function showPdfFallback() {
            var iframe = document.getElementById('pdfIframe');
            var fallback = document.getElementById('pdfFallback');
            if (iframe && fallback) {
                iframe.style.display = 'none';
                fallback.style.display = 'flex';
                fallback.style.flexDirection = 'column';
                fallback.style.alignItems = 'center';
                fallback.style.justifyContent = 'center';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var iframe = document.getElementById('pdfIframe');
            if (iframe) {
                // Muat ulang dengan query pengaturan saat ini
                iframe.src = buildPreviewUrl();

                var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
                if (isFirefox) {
                    iframe.addEventListener('load', function() {
                        setTimeout(function() {
                            try {
                                var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                                if (!iframeDoc || !iframeDoc.body) {
                                    return;
                                }
                                var bodyText = iframeDoc.body.textContent || iframeDoc.body.innerText || '';
                                if (bodyText.includes("Firefox Can't Open This Page") ||
                                    bodyText.includes("To protect your security")) {
                                    showPdfFallback();
                                }
                            } catch (e) { /* cross-origin / blocked */ }
                        }, 500);
                    });
                }
                iframe.addEventListener('error', function() {
                    showPdfFallback();
                });
            }

            var $slider = $('#fontsize-slider');
            var $input = $('#fontsize-input');
            var $preview = $('#fontsize-preview-sample');
            var $lhSlider = $('#lineheight-slider');
            var $lhInput = $('#lineheight-input');
            var $lhPreview = $('#lineheight-preview-sample');
            var $pdSlider = $('#padding-slider');
            var $pdInput = $('#padding-input');
            var $toggleKop = $('#toggle-kop');
            var $kopLabel = $('#kop-label-text');
            var $btnApply = $('#btn-terapkan-pengaturan');
            var $loadingIcon = $('#pengaturan-loading-icon');
            var $saveIcon = $('#pengaturan-save-icon');

            function updateFontsizeUI(val) {
                val = Math.min(20, Math.max(6, parseFloat(val) || 12));
                val = Math.round(val * 2) / 2;
                currentFontsize = val;
                $slider.val(val);
                $input.val(val);
                $preview.css('font-size', val + 'pt');
            }

            function updateLineHeightUI(val) {
                val = Math.min(3.0, Math.max(0.5, parseFloat(val) || 1.0));
                val = Math.round(val * 10) / 10;
                currentLineHeight = val;
                $lhSlider.val(val);
                $lhInput.val(val);
                $lhPreview.css('line-height', val);
            }

            function updatePaddingUI(val) {
                val = Math.min(16, Math.max(0, parseFloat(val)));
                val = isNaN(val) ? 1 : val;
                val = Math.round(val * 2) / 2;
                currentPadding = val;
                $pdSlider.val(val);
                $pdInput.val(val);
            }

            function updateKopUI(checked) {
                currentShowKop = checked ? 1 : 0;
                $kopLabel.text(checked ? 'Kop surat ditampilkan' : 'Kop surat disembunyikan (space kosong)');
            }

            updateFontsizeUI(currentFontsize);
            updateLineHeightUI(currentLineHeight);
            updatePaddingUI(currentPadding);
            $toggleKop.prop('checked', currentShowKop === 1);
            updateKopUI(currentShowKop === 1);

            $slider.on('input change', function() { updateFontsizeUI($(this).val()); });
            $input.on('input change', function() { updateFontsizeUI($(this).val()); });
            $('#fontsize-minus').on('click', function() { updateFontsizeUI(currentFontsize - 0.5); });
            $('#fontsize-plus').on('click', function() { updateFontsizeUI(currentFontsize + 0.5); });

            $lhSlider.on('input change', function() { updateLineHeightUI($(this).val()); });
            $lhInput.on('input change', function() { updateLineHeightUI($(this).val()); });
            $('#lineheight-minus').on('click', function() { updateLineHeightUI(currentLineHeight - 0.1); });
            $('#lineheight-plus').on('click', function() { updateLineHeightUI(currentLineHeight + 0.1); });

            $pdSlider.on('input change', function() { updatePaddingUI($(this).val()); });
            $pdInput.on('input change', function() { updatePaddingUI($(this).val()); });
            $('#padding-minus').on('click', function() { updatePaddingUI(currentPadding - 0.5); });
            $('#padding-plus').on('click', function() { updatePaddingUI(currentPadding + 0.5); });

            $toggleKop.on('change', function() { updateKopUI($(this).is(':checked')); });

            $btnApply.on('click', function() {
                $btnApply.prop('disabled', true);
                $loadingIcon.removeClass('d-none');
                $saveIcon.addClass('d-none');

                $.ajax({
                    url: saveSettingUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        fontsize: currentFontsize,
                        line_height: currentLineHeight,
                        padding: currentPadding,
                        show_kop: currentShowKop,
                        column_widths: JSON.stringify(getColumnWidthsPayload())
                    },
                    success: function(response) {
                        if (response && response.status) {
                            $('#modalPengaturanHasil').modal('hide');
                            reloadPdfPreview();
                            if (typeof swal === 'function') {
                                swal({
                                    title: 'Berhasil',
                                    text: response.pesan || 'Pengaturan hasil diterapkan.',
                                    icon: 'success',
                                    timer: 1600,
                                    buttons: false
                                });
                            }
                        } else {
                            var msg = (response && response.pesan) ? response.pesan : 'Gagal menyimpan pengaturan.';
                            if (typeof swal === 'function') {
                                swal('Gagal', msg, 'error');
                            } else {
                                alert(msg);
                            }
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Terjadi kesalahan saat menyimpan pengaturan.';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof swal === 'function') {
                            swal('Gagal', msg, 'error');
                        } else {
                            alert(msg);
                        }
                    },
                    complete: function() {
                        $btnApply.prop('disabled', false);
                        $loadingIcon.addClass('d-none');
                        $saveIcon.removeClass('d-none');
                    }
                });
            });
        });
    </script>
@endsection

