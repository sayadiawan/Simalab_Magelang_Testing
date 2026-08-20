<script>
    window.setupModalMetodeField = function(methodValue, metodeOptions) {
        metodeOptions = metodeOptions || [];
        if (typeof metodeOptions === 'string') {
            try {
                metodeOptions = JSON.parse(metodeOptions);
            } catch (e) {
                metodeOptions = [];
            }
        }
        if (!Array.isArray(metodeOptions)) {
            metodeOptions = [];
        }
        metodeOptions = metodeOptions.map(function(m) {
            return (m || '').toString().trim();
        }).filter(function(m) {
            return m !== '' && m !== '-';
        });

        var $container = $('#modal-metode-container');
        if (!$container.length) {
            return;
        }

        $container.empty();
        methodValue = (methodValue || '').toString().trim();

        if (metodeOptions.length > 0) {
            var $select = $('<select class="form-control" id="modal-metode"></select>');
            if (metodeOptions.length > 1) {
                $select.append('<option value="">-- Pilih Metode --</option>');
            }
            metodeOptions.forEach(function(opt) {
                var $opt = $('<option></option>').attr('value', opt).text(opt);
                if (methodValue && methodValue === opt) {
                    $opt.prop('selected', true);
                }
                $select.append($opt);
            });
            if (!methodValue && metodeOptions.length === 1) {
                $select.val(metodeOptions[0]);
            } else if (methodValue) {
                $select.val(methodValue);
            }
            $container.append($select);
        } else {
            var $input = $('<input type="text" class="form-control" id="modal-metode" placeholder="Masukkan metode">');
            $input.val(methodValue);
            $container.append($input);
        }
    };

    window.getModalMetodeValue = function() {
        var $el = $('#modal-metode');
        return $el.length ? ($el.val() || '').toString().trim() : '';
    };

    window.resolveMetodeOptionsFromRow = function(type, index) {
        var selector = type === 'sub'
            ? '#method_permohonan_uji_parameter_klinik_sub_' + index
            : '#method_permohonan_uji_parameter_klinik_' + index;
        var $methodInput = $(selector);
        if (!$methodInput.length) {
            return { methodValue: '', metodeOptions: [] };
        }
        var methodValue = ($methodInput.val() || '').toString().trim();
        var metodeOptions = [];
        try {
            metodeOptions = JSON.parse($methodInput.attr('data-metode-options') || '[]');
        } catch (e) {
            metodeOptions = [];
        }
        return { methodValue: methodValue, metodeOptions: metodeOptions };
    };
</script>
