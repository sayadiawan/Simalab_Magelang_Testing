@php
    $metode_raw = $item_satuan_klinik['metode_parameter_satuan_klinik'] ?? '';
    $metode_options_list = \Smt\Masterweb\Helpers\Smt::parseMetodeOptionsList((string) $metode_raw);
    $method_saved = trim((string) ($item_satuan_klinik['method_permohonan_uji_parameter_klinik'] ?? ''));
    $method_selected = \Smt\Masterweb\Helpers\Smt::resolveMethodSelectedForDisplay($method_saved, (string) $metode_raw);
    $has_metode_dropdown = count($metode_options_list) > 1;
    $is_custom_method = $has_metode_dropdown
        && $method_selected !== ''
        && $method_selected !== '-'
        && !in_array($method_selected, $metode_options_list, true);
    $param_id = $item_satuan_klinik['id_permohonan_uji_parameter_klinik'];
    $field_name = 'method_permohonan_uji_parameter_klinik[' . $param_id . ']';
    $field_id = $method_input_id ?? ('method_permohonan_uji_parameter_klinik_' . ($method_index ?? '0'));
@endphp
<td class="text-center align-middle metode-col{{ $has_metode_dropdown ? ' metode-col-has-dropdown' : '' }}">
    @if ($has_metode_dropdown)
        <select class="form-control form-control-sm metode-parameter-select mb-1"
            data-target-id="{{ $field_id }}"
            title="Pilih metode dari master">
            <option value="">-- Pilih Metode --</option>
            @foreach ($metode_options_list as $opt)
                <option value="{{ $opt }}" {{ !$is_custom_method && $method_selected === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
            <option value="__metode_baru__" {{ $is_custom_method ? 'selected' : '' }}>Metode Baru</option>
        </select>
    @endif
    <textarea class="form-control metode-editor"
        id="{{ $field_id }}"
        name="{{ $field_name }}"
        data-has-dropdown="{{ $has_metode_dropdown ? '1' : '0' }}"
        style="display: none;">{{ rubahNilaikeForm($method_selected) }}</textarea>
</td>
