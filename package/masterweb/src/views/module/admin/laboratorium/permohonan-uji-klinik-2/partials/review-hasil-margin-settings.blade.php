<style>
    #modalReviewHasil input[type=number],
    #modalReviewHasilVerif input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    #modalReviewHasil input[type=number]::-webkit-outer-spin-button,
    #modalReviewHasil input[type=number]::-webkit-inner-spin-button,
    #modalReviewHasilVerif input[type=number]::-webkit-outer-spin-button,
    #modalReviewHasilVerif input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

@php
    $idPrefix = $idPrefix ?? '';
    $resolveRowPadding = function ($item, $side) {
        $column = $side === 'top'
            ? 'padding_top_hasil_permohonan_uji_klinik'
            : 'padding_bottom_hasil_permohonan_uji_klinik';
        $value = $item->{$column} ?? null;

        if ($value !== null) {
            return (float) $value;
        }

        $legacy = $item->padding_hasil_permohonan_uji_klinik ?? null;
        if ($legacy === null || (float) $legacy === 4.0) {
            return 1.0;
        }

        return (float) $legacy;
    };

    $paddingTopValue = $resolveRowPadding($item_permohonan_uji_klinik, 'top');
    $paddingBottomValue = $resolveRowPadding($item_permohonan_uji_klinik, 'bottom');
    $marginLeftValue = (float) ($item_permohonan_uji_klinik->margin_left_hasil_permohonan_uji_klinik ?? 32);
    $marginRightValue = (float) ($item_permohonan_uji_klinik->margin_right_hasil_permohonan_uji_klinik ?? 32);
    if ($marginLeftValue === 20.0) {
        $marginLeftValue = 32;
    }
    if ($marginRightValue === 20.0) {
        $marginRightValue = 32;
    }

    $lebarKolomDefs = [
        'pemeriksaan' => [
            'label' => 'Pemeriksaan',
            'column' => 'lebar_kolom_pemeriksaan_hasil_permohonan_uji_klinik',
            'default' => 24,
            'min' => 10,
            'max' => 45,
        ],
        'hasil' => [
            'label' => 'Hasil',
            'column' => 'lebar_kolom_hasil_hasil_permohonan_uji_klinik',
            'default' => 10,
            'min' => 5,
            'max' => 25,
        ],
        'satuan' => [
            'label' => 'Satuan',
            'column' => 'lebar_kolom_satuan_hasil_permohonan_uji_klinik',
            'default' => 14,
            'min' => 5,
            'max' => 25,
        ],
        'metode' => [
            'label' => 'Metode',
            'column' => 'lebar_kolom_metode_hasil_permohonan_uji_klinik',
            'default' => 12,
            'min' => 5,
            'max' => 25,
        ],
        'nilai_normal' => [
            'label' => 'Nilai Normal',
            'column' => 'lebar_kolom_nilai_normal_hasil_permohonan_uji_klinik',
            'default' => 26,
            'min' => 15,
            'max' => 50,
        ],
    ];

    $lebarKolomValues = [];
    foreach ($lebarKolomDefs as $key => $def) {
        $raw = $item_permohonan_uji_klinik->{$def['column']} ?? $def['default'];
        $lebarKolomValues[$key] = max($def['min'], min($def['max'], (float) $raw));
    }
@endphp

{{-- Margin atas baris (cell padding top) --}}
<div class="card border-0 bg-light p-3 mb-3">
    <label class="font-weight-bold mb-1">
        <i class="fa fa-arrow-up mr-1"></i>Margin Atas Baris
        <small class="text-muted font-weight-normal">(jarak atas setiap baris tabel)</small>
    </label>
    <div class="d-flex align-items-center mt-1">
        <span class="text-muted small mr-2">0</span>
        <input type="range" class="custom-range flex-grow-1 mr-2" id="{{ $idPrefix }}padding-top-slider"
            min="0" max="16" step="0.5" value="{{ $paddingTopValue }}">
        <span class="text-muted small ml-2">16</span>
    </div>
    <div class="d-flex align-items-center justify-content-center mt-2">
        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}padding-top-minus">
            <i class="fa fa-minus"></i>
        </button>
        <div class="input-group mx-2" style="width: 100px;">
            <input type="number" class="form-control text-center font-weight-bold" id="{{ $idPrefix }}padding-top-input"
                min="0" max="16" step="0.5" value="{{ $paddingTopValue }}">
            <div class="input-group-append">
                <span class="input-group-text">pt</span>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}padding-top-plus">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div>

{{-- Margin bawah baris (cell padding bottom) --}}
<div class="card border-0 bg-light p-3 mb-3">
    <label class="font-weight-bold mb-1">
        <i class="fa fa-arrow-down mr-1"></i>Margin Bawah Baris
        <small class="text-muted font-weight-normal">(jarak bawah setiap baris tabel)</small>
    </label>
    <div class="d-flex align-items-center mt-1">
        <span class="text-muted small mr-2">0</span>
        <input type="range" class="custom-range flex-grow-1 mr-2" id="{{ $idPrefix }}padding-bottom-slider"
            min="0" max="16" step="0.5" value="{{ $paddingBottomValue }}">
        <span class="text-muted small ml-2">16</span>
    </div>
    <div class="d-flex align-items-center justify-content-center mt-2">
        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}padding-bottom-minus">
            <i class="fa fa-minus"></i>
        </button>
        <div class="input-group mx-2" style="width: 100px;">
            <input type="number" class="form-control text-center font-weight-bold" id="{{ $idPrefix }}padding-bottom-input"
                min="0" max="16" step="0.5" value="{{ $paddingBottomValue }}">
            <div class="input-group-append">
                <span class="input-group-text">pt</span>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}padding-bottom-plus">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div>

{{-- Margin kiri & kanan halaman --}}
<div class="card border-0 bg-light p-3 mb-3">
    <label class="font-weight-bold mb-2">
        <i class="fa fa-arrows-h mr-1"></i>Margin Kiri / Kanan Halaman
        <small class="text-muted font-weight-normal">(tepi kiri & kanan lembar cetak PDF)</small>
    </label>
    <div class="row">
        <div class="col-md-6 mb-3 mb-md-0">
            <label class="small text-muted mb-1 d-block">Margin Kiri</label>
            <div class="d-flex align-items-center">
                <span class="text-muted small mr-2">0</span>
                <input type="range" class="custom-range flex-grow-1 mr-2" id="{{ $idPrefix }}margin-left-slider"
                    min="0" max="60" step="1" value="{{ $marginLeftValue }}">
                <span class="text-muted small ml-2">60</span>
            </div>
            <div class="d-flex align-items-center justify-content-center mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}margin-left-minus">
                    <i class="fa fa-minus"></i>
                </button>
                <div class="input-group mx-2" style="width: 100px;">
                    <input type="number" class="form-control text-center font-weight-bold" id="{{ $idPrefix }}margin-left-input"
                        min="0" max="60" step="1" value="{{ $marginLeftValue }}">
                    <div class="input-group-append">
                        <span class="input-group-text">px</span>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}margin-left-plus">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <label class="small text-muted mb-1 d-block">Margin Kanan</label>
            <div class="d-flex align-items-center">
                <span class="text-muted small mr-2">0</span>
                <input type="range" class="custom-range flex-grow-1 mr-2" id="{{ $idPrefix }}margin-right-slider"
                    min="0" max="60" step="1" value="{{ $marginRightValue }}">
                <span class="text-muted small ml-2">60</span>
            </div>
            <div class="d-flex align-items-center justify-content-center mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}margin-right-minus">
                    <i class="fa fa-minus"></i>
                </button>
                <div class="input-group mx-2" style="width: 100px;">
                    <input type="number" class="form-control text-center font-weight-bold" id="{{ $idPrefix }}margin-right-input"
                        min="0" max="60" step="1" value="{{ $marginRightValue }}">
                    <div class="input-group-append">
                        <span class="input-group-text">px</span>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" id="{{ $idPrefix }}margin-right-plus">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Lebar kolom tabel hasil --}}
<div class="card border-0 bg-light p-3 mb-3" id="{{ $idPrefix }}lebar-kolom-card">
    <label class="font-weight-bold mb-2">
        <i class="fa fa-columns mr-1"></i>Lebar Kolom Tabel Hasil
        <small class="text-muted font-weight-normal">(proporsi lebar setiap kolom di cetak PDF)</small>
    </label>

    @foreach ($lebarKolomDefs as $key => $def)
        <div class="mb-3 {{ $loop->last ? 'mb-0' : '' }}">
            <label class="small font-weight-bold mb-1 d-block">{{ $def['label'] }}</label>
            <div class="d-flex align-items-center">
                <span class="text-muted small mr-2" style="min-width: 18px;">{{ $def['min'] }}</span>
                <input type="range" class="custom-range flex-grow-1 mr-2 lebar-kolom-slider"
                    id="{{ $idPrefix }}lebar-{{ $key }}-slider"
                    data-kolom-key="{{ $key }}"
                    min="{{ $def['min'] }}" max="{{ $def['max'] }}" step="1"
                    value="{{ $lebarKolomValues[$key] }}">
                <span class="text-muted small ml-2" style="min-width: 22px;">{{ $def['max'] }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-center mt-1">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1 lebar-kolom-minus"
                    data-kolom-key="{{ $key }}">
                    <i class="fa fa-minus"></i>
                </button>
                <div class="input-group mx-2" style="width: 90px;">
                    <input type="number" class="form-control text-center font-weight-bold lebar-kolom-input"
                        id="{{ $idPrefix }}lebar-{{ $key }}-input"
                        data-kolom-key="{{ $key }}"
                        min="{{ $def['min'] }}" max="{{ $def['max'] }}" step="1"
                        value="{{ $lebarKolomValues[$key] }}">
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1 lebar-kolom-plus"
                    data-kolom-key="{{ $key }}">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
    @endforeach

    <div class="text-center mt-2">
        <small class="text-muted">Total lebar kolom: <strong id="{{ $idPrefix }}lebar-kolom-total">{{ array_sum($lebarKolomValues) }}</strong>%</small>
        <button type="button" class="btn btn-link btn-sm p-0 ml-2" id="{{ $idPrefix }}lebar-kolom-reset">Reset default</button>
    </div>
</div>
