@php
    $parseMetodeList = function ($raw, $oldKey) {
        $metodeValues = [];
        if (!empty($raw) && $raw !== '-') {
            $metodeValues = array_values(array_filter(array_map('trim', explode(',', (string) $raw)), function ($v) {
                return $v !== '' && $v !== '-';
            }));
        }
        if (empty($metodeValues) && old($oldKey)) {
            $metodeValues = array_values(array_filter(array_map('trim', (array) old($oldKey)), function ($v) {
                return $v !== '' && $v !== '-';
            }));
        }
        if (empty($metodeValues)) {
            $metodeValues = [''];
        }

        return $metodeValues;
    };

    $itemRow = isset($item) ? $item : null;
    $metodeValues = $parseMetodeList($itemRow->metode_parameter_satuan_klinik ?? null, 'metode_parameter_satuan_klinik_list');
    $metodeHajiValues = $parseMetodeList($itemRow->metode_parameter_satuan_klinik_haji ?? null, 'metode_parameter_satuan_klinik_haji_list');
    $isHajiParam = (int) ($itemRow->is_haji ?? 0) === 1;
@endphp

<div class="form-group" id="metode-non-haji-group">
    <label>
        <span id="metode-label-default">Metode Parameter Satuan</span>
        <span id="metode-label-non-haji" style="display:none;">Metode Parameter Satuan (Non Haji)</span>
    </label>
    <small class="form-text text-muted mb-2" id="metode-hint-default">Bisa lebih dari satu. Saat disimpan akan digabung dengan koma (,).</small>
    <small class="form-text text-muted mb-2" id="metode-hint-non-haji" style="display:none;">Dipakai untuk permohonan non-haji. Bisa lebih dari satu.</small>

    <div id="metode-parameter-list">
        @foreach ($metodeValues as $idx => $metodeVal)
            <div class="input-group mb-2 metode-parameter-row">
                <input type="text" class="form-control metode-parameter-input"
                    name="metode_parameter_satuan_klinik_list[]"
                    placeholder="Metode parameter satuan klinik.."
                    value="{{ $metodeVal }}">
                <div class="input-group-append">
                    @if ($loop->first)
                        <button type="button" class="btn btn-success btn-add-metode-parameter" data-target="#metode-parameter-list" data-name="metode_parameter_satuan_klinik_list[]" title="Tambah metode">
                            <i class="fas fa-plus"></i>
                        </button>
                    @else
                        <button type="button" class="btn btn-danger btn-remove-metode-parameter" data-target="#metode-parameter-list" title="Hapus metode">
                            <i class="fas fa-minus"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="form-group" id="metode-haji-group" style="{{ $isHajiParam ? '' : 'display:none;' }}">
    <label>Metode Parameter Satuan (Haji)</label>
    <small class="form-text text-muted mb-2">Dipakai untuk permohonan haji (pemeriksaan, verifikasi, cetak). Kosong = pakai metode non-haji.</small>

    <div id="metode-parameter-haji-list">
        @foreach ($metodeHajiValues as $idx => $metodeVal)
            <div class="input-group mb-2 metode-parameter-row">
                <input type="text" class="form-control metode-parameter-input"
                    name="metode_parameter_satuan_klinik_haji_list[]"
                    placeholder="Metode parameter satuan klinik (haji).."
                    value="{{ $metodeVal }}">
                <div class="input-group-append">
                    @if ($loop->first)
                        <button type="button" class="btn btn-success btn-add-metode-parameter" data-target="#metode-parameter-haji-list" data-name="metode_parameter_satuan_klinik_haji_list[]" title="Tambah metode">
                            <i class="fas fa-plus"></i>
                        </button>
                    @else
                        <button type="button" class="btn btn-danger btn-remove-metode-parameter" data-target="#metode-parameter-haji-list" title="Hapus metode">
                            <i class="fas fa-minus"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    (function() {
        function bindMetodeParameterButtons() {
            $(document).off('click.metodeParam', '.btn-add-metode-parameter').on('click.metodeParam', '.btn-add-metode-parameter', function() {
                var $btn = $(this);
                var target = $btn.data('target') || '#metode-parameter-list';
                var name = $btn.data('name') || 'metode_parameter_satuan_klinik_list[]';
                var $row = $('<div class="input-group mb-2 metode-parameter-row">' +
                    '<input type="text" class="form-control metode-parameter-input" name="' + name + '" placeholder="Metode parameter satuan klinik..">' +
                    '<div class="input-group-append">' +
                    '<button type="button" class="btn btn-danger btn-remove-metode-parameter" data-target="' + target + '" title="Hapus metode"><i class="fas fa-minus"></i></button>' +
                    '</div></div>');
                $(target).append($row);
                $row.find('input').focus();
            });

            $(document).off('click.metodeParam', '.btn-remove-metode-parameter').on('click.metodeParam', '.btn-remove-metode-parameter', function() {
                var target = $(this).data('target') || '#metode-parameter-list';
                var $list = $(target);
                if ($list.find('.metode-parameter-row').length <= 1) {
                    $(this).closest('.metode-parameter-row').find('input').val('');
                    return;
                }
                $(this).closest('.metode-parameter-row').remove();
            });
        }

        $(document).ready(bindMetodeParameterButtons);
    })();
</script>
