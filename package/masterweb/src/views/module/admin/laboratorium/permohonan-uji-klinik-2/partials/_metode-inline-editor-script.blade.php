<style>
    .metode-col-has-dropdown .inline-metode-editor.metode-editor-hidden {
        display: none !important;
    }
</style>
<script>
(function($) {
    'use strict';

    var METODE_BARU_VALUE = '__metode_baru__';
    window.METODE_BARU_VALUE = METODE_BARU_VALUE;

    function normalizeMetodePlainText(content) {
        if (!content) {
            return '';
        }
        var $tmp = $('<div>').html(content);
        var text = $.trim($tmp.text());
        if (text) {
            return text;
        }
        return $.trim(String(content).replace(/<[^>]*>/g, ''));
    }

    function getMetodeEditorPlainContent(fieldId) {
        var inlineEditorId = fieldId + '_editor';
        var content = '';

        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get(inlineEditorId);
            if (editor && !editor.removed && typeof editor.getContent === 'function') {
                content = editor.getContent();
            }
        }

        if (!content) {
            var $inline = $('#' + inlineEditorId);
            if ($inline.length) {
                content = $inline.html() || '';
            }
        }

        if (!content) {
            content = $('#' + fieldId).val() || '';
        }

        return normalizeMetodePlainText(content);
    }

    function syncMetodeFieldFromUi(fieldId) {
        if (!fieldId) {
            return '';
        }

        var $textarea = $('#' + fieldId);
        var $select = $textarea.closest('.metode-col').find('.metode-parameter-select');
        var value = '';

        if ($select.length) {
            var selectVal = $select.val() || '';
            if (selectVal && selectVal !== METODE_BARU_VALUE) {
                value = selectVal;
            } else if (selectVal === METODE_BARU_VALUE) {
                value = getMetodeEditorPlainContent(fieldId);
            } else {
                value = normalizeMetodePlainText($textarea.val() || '');
            }
        } else {
            value = getMetodeEditorPlainContent(fieldId);
        }

        $textarea.val(value);
        return value;
    }

    function ensureTinyMceBaseUrl() {
        if (typeof tinymce === 'undefined') {
            return false;
        }
        var tinymceBasePath = '{{ asset('assets/admin/vendors/tinymce') }}';
        if (tinymce.baseURL === undefined ||
            tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 ||
            tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
            tinymce.baseURL = tinymceBasePath;
        }
        return typeof tinymce.init === 'function';
    }

    function getMetodeEditorEl(fieldId) {
        return $('#' + fieldId + '_editor');
    }

    function showMetodeEditor(fieldId) {
        var $editor = getMetodeEditorEl(fieldId);
        if ($editor.length) {
            $editor.removeClass('metode-editor-hidden');
        }
    }

    function hideMetodeEditor(fieldId) {
        var $editor = getMetodeEditorEl(fieldId);
        if ($editor.length) {
            $editor.addClass('metode-editor-hidden');
        }
    }

    function updateMetodeEditorVisibility($select) {
        if (!$select || !$select.length) {
            return;
        }

        var fieldId = $select.data('target-id');
        if (!fieldId) {
            return;
        }

        var value = $select.val() || '';
        if (value === METODE_BARU_VALUE) {
            showMetodeEditor(fieldId);
            return;
        }

        hideMetodeEditor(fieldId);
        if (value) {
            setMetodeEditorContent(fieldId, value);
        }
    }

    window.syncMetodeInlineEditorsToTextareas = function() {
        $('.metode-editor').each(function() {
            syncMetodeFieldFromUi($(this).attr('id'));
        });
    };

    window.collectTempMethod = function() {
        window.syncMetodeInlineEditorsToTextareas();

        var tempMethod = {};
        $('textarea.metode-editor[name^="method_permohonan_uji_parameter_klinik"]').each(function() {
            var $el = $(this);
            var nameAttr = $el.attr('name') || '';
            var match = nameAttr.match(/\[([^\]]+)\]$/);
            if (!match) {
                return;
            }

            var value = syncMetodeFieldFromUi($el.attr('id'));
            if (value) {
                tempMethod[match[1]] = value;
            }
        });

        return tempMethod;
    };

    function setMetodeEditorContent(fieldId, value) {
        value = value || '';
        var $textarea = $('#' + fieldId);
        if ($textarea.length) {
            $textarea.val(value);
        }

        var inlineEditorId = fieldId + '_editor';
        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get(inlineEditorId);
            if (editor && !editor.removed) {
                editor.setContent(value);
                return;
            }
        }

        var $inline = $('#' + inlineEditorId);
        if ($inline.length) {
            $inline.html(value);
        }
    }

    window.initMetodeInlineEditors = function() {
        if (!ensureTinyMceBaseUrl()) {
            return;
        }

        var pendingSelectors = [];

        $('.metode-editor').each(function() {
            var $textarea = $(this);
            var fieldId = $textarea.attr('id');
            if (!fieldId || $textarea.data('metode-inline-ready')) {
                return;
            }

            var hasDropdown = $textarea.data('has-dropdown') === 1 || $textarea.data('has-dropdown') === '1';
            var inlineEditorId = fieldId + '_editor';
            if (tinymce.get(inlineEditorId)) {
                $textarea.data('metode-inline-ready', true);
                return;
            }

            if (!$('#' + inlineEditorId).length) {
                var content = $textarea.val() || '';
                var $editorDiv = $('<div>')
                    .addClass('inline-metode-editor')
                    .attr('id', inlineEditorId)
                    .attr('data-original-id', fieldId)
                    .attr('contenteditable', 'true')
                    .html(content);

                if (hasDropdown) {
                    var $select = $textarea.closest('.metode-col').find('.metode-parameter-select');
                    var selectVal = $select.val() || '';
                    if (selectVal !== METODE_BARU_VALUE) {
                        $editorDiv.addClass('metode-editor-hidden');
                    }
                }

                $textarea.after($editorDiv);
            }

            $textarea.data('metode-inline-ready', true);
            pendingSelectors.push('#' + inlineEditorId);
        });

        if (pendingSelectors.length === 0) {
            return;
        }

        tinymce.init({
            selector: pendingSelectors.join(','),
            inline: true,
            menubar: false,
            theme: 'modern',
            content_css: false,
            document_base_url: window.location.origin,
            plugins: ['lists charmap', 'searchreplace', 'paste'],
            toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
            toolbar_mode: 'floating',
            toolbar_location: 'auto',
            paste_as_text: true,
            content_style: 'body { font-size: 12px; font-family: Arial, sans-serif; } sup { vertical-align: super; font-size: 0.8em; } sub { vertical-align: sub; font-size: 0.8em; }',
            valid_elements: '*[*]',
            extended_valid_elements: 'sup[*],sub[*]',
            formats: {
                superscript: { inline: 'sup', styles: { verticalAlign: 'super' } },
                subscript: { inline: 'sub', styles: { verticalAlign: 'sub' } }
            },
            forced_root_block: false,
            force_br_newlines: true,
            force_p_newlines: false,
            charmap_append: [
                [0x00B1, 'plus-minus sign'],
                [0x2264, 'less-than or equal to'],
                [0x2265, 'greater-than or equal to'],
                [0x00B0, 'degree sign'],
                [0x03BC, 'greek small letter mu']
            ],
            setup: function(editor) {
                editor.on('change blur keyup', function() {
                    var originalId = $(editor.getElement()).data('original-id');
                    if (originalId) {
                        $('#' + originalId).val(normalizeMetodePlainText(editor.getContent()));
                    }
                });
            },
            init_instance_callback: function(editor) {
                var $el = $(editor.getElement());
                var originalId = $el.data('original-id');
                if (!originalId) {
                    return;
                }
                var $select = $('#' + originalId).closest('.metode-col').find('.metode-parameter-select');
                if ($select.length) {
                    updateMetodeEditorVisibility($select);
                }
            }
        });
    };

    $(document).on('change', '.metode-parameter-select', function() {
        var $select = $(this);
        var fieldId = $select.data('target-id');
        var value = $select.val() || '';

        if (!fieldId) {
            return;
        }

        if (value === METODE_BARU_VALUE) {
            showMetodeEditor(fieldId);
            var $textarea = $('#' + fieldId);
            var current = $textarea.val() || '';
            var presetValues = [];
            $select.find('option').each(function() {
                var optVal = $(this).val();
                if (optVal && optVal !== METODE_BARU_VALUE) {
                    presetValues.push(optVal);
                }
            });
            if (current && presetValues.indexOf(current) !== -1) {
                setMetodeEditorContent(fieldId, '');
            }
            setTimeout(function() {
                var editor = tinymce.get(fieldId + '_editor');
                if (editor && !editor.removed) {
                    editor.focus();
                } else {
                    getMetodeEditorEl(fieldId).focus();
                }
            }, 100);
            return;
        }

        hideMetodeEditor(fieldId);
        setMetodeEditorContent(fieldId, value);
    });

    function scheduleMetodeEditorInit(attempt) {
        attempt = attempt || 0;
        if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
            if (attempt < 20) {
                setTimeout(function() {
                    scheduleMetodeEditorInit(attempt + 1);
                }, 250);
            }
            return;
        }
        setTimeout(window.initMetodeInlineEditors, 100);
    }

    $(document).ready(function() {
        scheduleMetodeEditorInit();
    });
    $(document).on('analisEditorReady', function() {
        setTimeout(window.initMetodeInlineEditors, 200);
    });
})(jQuery);
</script>
