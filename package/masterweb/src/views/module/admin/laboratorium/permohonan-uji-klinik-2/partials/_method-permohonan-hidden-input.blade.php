@php
    $metode_raw = $item_satuan_klinik['metode_parameter_satuan_klinik'] ?? '';
    $metode_options_list = array_values(array_filter(array_map('trim', explode(',', (string) $metode_raw)), function ($m) {
        return $m !== '' && $m !== '-';
    }));
    $method_saved = trim((string) ($item_satuan_klinik['method_permohonan_uji_parameter_klinik'] ?? ''));
    $method_default = ($method_saved !== '' && $method_saved !== '-')
        ? $method_saved
        : ($metode_options_list[0] ?? '');
@endphp
<input type="hidden"
    name="method_permohonan_uji_parameter_klinik[{{ $item_satuan_klinik['id_permohonan_uji_parameter_klinik'] }}]"
    id="{{ $method_input_id }}"
    value="{{ $method_default }}"
    data-metode-options="{{ json_encode($metode_options_list) }}">
