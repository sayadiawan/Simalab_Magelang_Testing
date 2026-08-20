    function initReviewHasilMarginSettings(idPrefix, onChange) {
        idPrefix = idPrefix || '';

        var $pdTopSlider = $('#' + idPrefix + 'padding-top-slider');
        var $pdTopInput = $('#' + idPrefix + 'padding-top-input');
        var $pdBottomSlider = $('#' + idPrefix + 'padding-bottom-slider');
        var $pdBottomInput = $('#' + idPrefix + 'padding-bottom-input');
        var $mgLeftSlider = $('#' + idPrefix + 'margin-left-slider');
        var $mgLeftInput = $('#' + idPrefix + 'margin-left-input');
        var $mgRightSlider = $('#' + idPrefix + 'margin-right-slider');
        var $mgRightInput = $('#' + idPrefix + 'margin-right-input');
        var $lebarTotal = $('#' + idPrefix + 'lebar-kolom-total');
        var $lebarCard = $('#' + idPrefix + 'lebar-kolom-card');

        var kolomConfig = {
            pemeriksaan:  { min: 10, max: 45, default: 24 },
            hasil:        { min: 5,  max: 25, default: 10 },
            satuan:       { min: 5,  max: 25, default: 14 },
            metode:       { min: 5,  max: 25, default: 12 },
            nilai_normal: { min: 15, max: 50, default: 26 },
        };

        var state = {
            paddingTop: parseFloat($pdTopSlider.val()) || 1,
            paddingBottom: parseFloat($pdBottomSlider.val()) || 1,
            marginLeft: parseFloat($mgLeftSlider.val()) || 32,
            marginRight: parseFloat($mgRightSlider.val()) || 32,
            lebarKolom: {},
        };

        var original = {
            paddingTop: state.paddingTop,
            paddingBottom: state.paddingBottom,
            marginLeft: state.marginLeft,
            marginRight: state.marginRight,
            lebarKolom: {},
        };

        function notify() {
            if (typeof onChange === 'function') {
                onChange();
            }
        }

        function clampPadding(val) {
            val = Math.min(16, Math.max(0, parseFloat(val)));
            val = isNaN(val) ? 1 : val;
            return Math.round(val * 2) / 2;
        }

        function clampMargin(val) {
            val = Math.min(60, Math.max(0, parseFloat(val)));
            val = isNaN(val) ? 32 : val;
            return Math.round(val);
        }

        function clampLebarKolom(key, val) {
            var cfg = kolomConfig[key];
            if (!cfg) return val;
            val = Math.min(cfg.max, Math.max(cfg.min, parseFloat(val)));
            val = isNaN(val) ? cfg.default : val;
            return Math.round(val);
        }

        function updateLebarTotal() {
            var total = 0;
            $.each(state.lebarKolom, function(_, v) { total += v; });
            $lebarTotal.text(total);
        }

        function updateLebarKolomUI(key, val) {
            val = clampLebarKolom(key, val);
            $('#' + idPrefix + 'lebar-' + key + '-slider').val(val);
            $('#' + idPrefix + 'lebar-' + key + '-input').val(val);
            state.lebarKolom[key] = val;
            updateLebarTotal();
            notify();
        }

        function updatePaddingTopUI(val) {
            val = clampPadding(val);
            $pdTopSlider.val(val);
            $pdTopInput.val(val);
            state.paddingTop = val;
            notify();
        }

        function updatePaddingBottomUI(val) {
            val = clampPadding(val);
            $pdBottomSlider.val(val);
            $pdBottomInput.val(val);
            state.paddingBottom = val;
            notify();
        }

        function updateMarginLeftUI(val) {
            val = clampMargin(val);
            $mgLeftSlider.val(val);
            $mgLeftInput.val(val);
            state.marginLeft = val;
            notify();
        }

        function updateMarginRightUI(val) {
            val = clampMargin(val);
            $mgRightSlider.val(val);
            $mgRightInput.val(val);
            state.marginRight = val;
            notify();
        }

        $pdTopSlider.on('input change', function() { updatePaddingTopUI($(this).val()); });
        $pdTopInput.on('input change', function() { updatePaddingTopUI($(this).val()); });
        $('#' + idPrefix + 'padding-top-minus').on('click', function() { updatePaddingTopUI(state.paddingTop - 0.5); });
        $('#' + idPrefix + 'padding-top-plus').on('click', function() { updatePaddingTopUI(state.paddingTop + 0.5); });

        $pdBottomSlider.on('input change', function() { updatePaddingBottomUI($(this).val()); });
        $pdBottomInput.on('input change', function() { updatePaddingBottomUI($(this).val()); });
        $('#' + idPrefix + 'padding-bottom-minus').on('click', function() { updatePaddingBottomUI(state.paddingBottom - 0.5); });
        $('#' + idPrefix + 'padding-bottom-plus').on('click', function() { updatePaddingBottomUI(state.paddingBottom + 0.5); });

        $mgLeftSlider.on('input change', function() { updateMarginLeftUI($(this).val()); });
        $mgLeftInput.on('input change', function() { updateMarginLeftUI($(this).val()); });
        $('#' + idPrefix + 'margin-left-minus').on('click', function() { updateMarginLeftUI(state.marginLeft - 1); });
        $('#' + idPrefix + 'margin-left-plus').on('click', function() { updateMarginLeftUI(state.marginLeft + 1); });

        $mgRightSlider.on('input change', function() { updateMarginRightUI($(this).val()); });
        $mgRightInput.on('input change', function() { updateMarginRightUI($(this).val()); });
        $('#' + idPrefix + 'margin-right-minus').on('click', function() { updateMarginRightUI(state.marginRight - 1); });
        $('#' + idPrefix + 'margin-right-plus').on('click', function() { updateMarginRightUI(state.marginRight + 1); });

        $.each(kolomConfig, function(key, cfg) {
            var $slider = $('#' + idPrefix + 'lebar-' + key + '-slider');
            if (!$slider.length) {
                return;
            }
            var val = parseFloat($slider.val()) || cfg.default;
            state.lebarKolom[key] = clampLebarKolom(key, val);
            original.lebarKolom[key] = state.lebarKolom[key];
        });

        $lebarCard.find('.lebar-kolom-slider').on('input change', function() {
            updateLebarKolomUI($(this).data('kolom-key'), $(this).val());
        });
        $lebarCard.find('.lebar-kolom-input').on('input change', function() {
            updateLebarKolomUI($(this).data('kolom-key'), $(this).val());
        });
        $lebarCard.find('.lebar-kolom-minus').on('click', function() {
            var key = $(this).data('kolom-key');
            updateLebarKolomUI(key, state.lebarKolom[key] - 1);
        });
        $lebarCard.find('.lebar-kolom-plus').on('click', function() {
            var key = $(this).data('kolom-key');
            updateLebarKolomUI(key, state.lebarKolom[key] + 1);
        });
        $('#' + idPrefix + 'lebar-kolom-reset').on('click', function() {
            $.each(kolomConfig, function(key, cfg) {
                updateLebarKolomUI(key, cfg.default);
            });
        });

        updatePaddingTopUI(state.paddingTop);
        updatePaddingBottomUI(state.paddingBottom);
        updateMarginLeftUI(state.marginLeft);
        updateMarginRightUI(state.marginRight);
        $.each(state.lebarKolom, function(key, val) {
            updateLebarKolomUI(key, val);
        });

        return {
            getValues: function() {
                return {
                    padding_top: state.paddingTop,
                    padding_bottom: state.paddingBottom,
                    margin_left: state.marginLeft,
                    margin_right: state.marginRight,
                    lebar_kolom_pemeriksaan: state.lebarKolom.pemeriksaan,
                    lebar_kolom_hasil: state.lebarKolom.hasil,
                    lebar_kolom_satuan: state.lebarKolom.satuan,
                    lebar_kolom_metode: state.lebarKolom.metode,
                    lebar_kolom_nilai_normal: state.lebarKolom.nilai_normal,
                    padding: Math.round(((state.paddingTop + state.paddingBottom) / 2) * 2) / 2,
                };
            },
            resetToOriginal: function() {
                updatePaddingTopUI(original.paddingTop);
                updatePaddingBottomUI(original.paddingBottom);
                updateMarginLeftUI(original.marginLeft);
                updateMarginRightUI(original.marginRight);
                $.each(original.lebarKolom, function(key, val) {
                    updateLebarKolomUI(key, val);
                });
            },
            commitOriginal: function() {
                var values = this.getValues();
                original.paddingTop = values.padding_top;
                original.paddingBottom = values.padding_bottom;
                original.marginLeft = values.margin_left;
                original.marginRight = values.margin_right;
                original.lebarKolom = {
                    pemeriksaan: values.lebar_kolom_pemeriksaan,
                    hasil: values.lebar_kolom_hasil,
                    satuan: values.lebar_kolom_satuan,
                    metode: values.lebar_kolom_metode,
                    nilai_normal: values.lebar_kolom_nilai_normal,
                };
            },
        };
    }
