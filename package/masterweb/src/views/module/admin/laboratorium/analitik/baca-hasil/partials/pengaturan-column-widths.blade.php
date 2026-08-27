@php
    /** @var array{profile: string, columns: array<int, array{key: string, label: string, width: float}>} $kesmasColWidthUi */
    $kesmasColWidthUi = $kesmasColWidthUi ?? \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::uiPayload($sample ?? null);
    $kesmasColWidthProfile = $kesmasColWidthUi['profile'] ?? 'lhu_6col';
    $kesmasColWidthColumns = $kesmasColWidthUi['columns'] ?? [];
    $kesmasColWidthJson = [];
    foreach ($kesmasColWidthColumns as $col) {
        $kesmasColWidthJson[$col['key']] = (float) $col['width'];
    }
@endphp

<div class="card border-0 bg-light p-3 mb-3" id="card-column-widths-hasil">
    <label class="font-weight-bold mb-1">
        <i class="fa fa-columns mr-1"></i>Lebar Kolom Tabel (%)
        <small class="text-muted font-weight-normal">(jarak / proporsi antar kolom hasil)</small>
    </label>
    <small class="text-muted d-block mb-2">
        Atur persentase tiap kolom. Total otomatis dinormalisasi ke 100% saat diterapkan.
        Kolom mengikuti layout hasil yang sedang dipakai.
    </small>

    <input type="hidden" name="column_widths_hasil" id="column_widths_hasil_hidden"
           value="{{ e(json_encode([$kesmasColWidthProfile => $kesmasColWidthJson], JSON_UNESCAPED_UNICODE)) }}">
    <input type="hidden" id="column_widths_profile" value="{{ e($kesmasColWidthProfile) }}">

    <div id="column-widths-list">
        @foreach ($kesmasColWidthColumns as $col)
            <div class="d-flex align-items-center mb-2 column-width-row" data-key="{{ $col['key'] }}">
                <div class="flex-grow-1 mr-2" style="min-width: 0;">
                    <div class="small font-weight-bold text-truncate" title="{{ $col['label'] }}">{{ $col['label'] }}</div>
                    <input type="range" class="custom-range column-width-slider mt-1"
                           min="1" max="60" step="0.5" value="{{ $col['width'] }}">
                </div>
                <div class="input-group input-group-sm" style="width: 92px; flex-shrink: 0;">
                    <input type="text" inputmode="decimal"
                           class="form-control text-center font-weight-bold column-width-input"
                           value="{{ $col['width'] }}">
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex align-items-center justify-content-between mt-1">
        <small class="text-muted">
            Total: <strong id="column-widths-total">{{ number_format(array_sum($kesmasColWidthJson), 1) }}</strong>%
        </small>
        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btn-reset-column-widths" title="Kembalikan default">
            <i class="fa fa-undo"></i> Default
        </button>
    </div>
    <div class="mt-2 p-2 border rounded bg-white">
        <div class="d-flex" id="column-widths-preview-bar" style="height: 18px; overflow: hidden; border-radius: 3px;">
            @php
                $palette = ['#0b3a5c', '#1d6a9a', '#2e8bc0', '#5fa8d3', '#8ecae6', '#bde0fe'];
                $i = 0;
            @endphp
            @foreach ($kesmasColWidthColumns as $col)
                <div class="column-width-preview-seg"
                     data-key="{{ $col['key'] }}"
                     title="{{ $col['label'] }}: {{ $col['width'] }}%"
                     style="width: {{ $col['width'] }}%; background: {{ $palette[$i % count($palette)] }};"></div>
                @php $i++; @endphp
            @endforeach
        </div>
        <small class="text-muted d-block mt-1 text-center">Pratinjau proporsi kolom</small>
    </div>
</div>
