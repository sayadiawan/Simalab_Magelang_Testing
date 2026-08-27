<script>
(function (window, $) {
    if (!$) return;

    var DEFAULTS = {!! json_encode(\Smt\Masterweb\Helpers\KesmasHasilColumnWidth::defaults()['lhu_6col'] ?? []) !!};

    function clamp(val) {
        val = parseFloat(val);
        if (isNaN(val)) val = 1;
        val = Math.min(60, Math.max(1, val));
        return Math.round(val * 2) / 2;
    }

    function collectFlat() {
        var flat = {};
        $('#column-widths-list .column-width-row').each(function () {
            var key = $(this).data('key');
            var val = clamp($(this).find('.column-width-input').val());
            flat[key] = val;
        });
        return flat;
    }

    function collectPayload() {
        var profile = $('#column_widths_profile').val() || 'lhu_6col';
        var nested = {};
        nested[profile] = collectFlat();
        return nested;
    }

    function syncHidden() {
        var payload = collectPayload();
        var json = JSON.stringify(payload);
        $('#column_widths_hasil_hidden').val(json);
        return payload;
    }

    function updateTotalAndPreview() {
        var flat = collectFlat();
        var total = 0;
        Object.keys(flat).forEach(function (k) { total += flat[k]; });
        $('#column-widths-total').text(total.toFixed(1));
        $('#column-widths-total').css('color', Math.abs(total - 100) < 0.6 ? '#155724' : '#856404');

        Object.keys(flat).forEach(function (k) {
            var pct = flat[k];
            var $seg = $('#column-widths-preview-bar .column-width-preview-seg[data-key="' + k + '"]');
            if ($seg.length) {
                $seg.css('width', pct + '%');
                var label = $seg.attr('title') || '';
                var base = label.split(':')[0] || k;
                $seg.attr('title', base + ': ' + pct + '%');
            }
        });
        syncHidden();
    }

    function setRowValue($row, val) {
        val = clamp(val);
        $row.find('.column-width-slider').val(val);
        $row.find('.column-width-input').val(val);
        updateTotalAndPreview();
    }

    function resetDefaults() {
        $('#column-widths-list .column-width-row').each(function () {
            var key = $(this).data('key');
            var val = DEFAULTS[key] != null ? DEFAULTS[key] : 10;
            setRowValue($(this), val);
        });
    }

    function init() {
        if (!$('#column-widths-list').length) return;

        $(document).on('input change', '#column-widths-list .column-width-slider', function () {
            var $row = $(this).closest('.column-width-row');
            setRowValue($row, $(this).val());
        });

        $(document).on('input change', '#column-widths-list .column-width-input', function () {
            var $row = $(this).closest('.column-width-row');
            setRowValue($row, $(this).val());
        });

        $(document).on('click', '#btn-reset-column-widths', function (e) {
            e.preventDefault();
            resetDefaults();
        });

        updateTotalAndPreview();
    }

    window.KesmasColWidths = {
        init: init,
        collect: collectPayload,
        collectFlat: collectFlat,
        syncHidden: syncHidden,
        asQueryValue: function () {
            return JSON.stringify(collectPayload());
        }
    };

    $(function () { init(); });
})(window, window.jQuery);
</script>
