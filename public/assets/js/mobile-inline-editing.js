/**
 * Mobile Inline Editing Script
 * Converts hidden textareas to inline editable inputs/dropdowns for mobile views
 * Similar to analis-inline-editing.js but adapted for mobile card-based structure
 */

(function($) {
    'use strict';

    var MobileInlineEditor = {
        settings: {
            hasilInputClass: 'inline-hasil-input',
            hasilEditorClass: 'inline-hasil-editor',
            keteranganEditorClass: 'inline-keterangan-editor',
            badgeContainerClass: 'result-badge-inline'
        },

        /**
         * Initialize mobile inline editing
         */
        init: function() {
            var self = this;
            console.log('Initializing Mobile Inline Editor...');

            // Wait for DOM to be ready
            $(document).ready(function() {
                setTimeout(function() {
                    self.convertHiddenInputsToVisible();
                }, 300);
            });
        },

        /**
         * Convert hidden textareas to visible inline inputs
         */
        convertHiddenInputsToVisible: function() {
            var self = this;
            console.log('Converting hidden inputs to visible...');

            // Find all hidden textareas with class result_method or name containing hasil
            $('.result_method.hidden-field, textarea.hidden-field[name*="hasil"]').each(function() {
                var $textarea = $(this);
                var $card = $textarea.closest('.card');
                
                if ($card.length === 0) {
                    console.warn('Textarea not inside a card, skipping:', $textarea.attr('id'));
                    return;
                }

                // Find the input group for "Hasil"
                var $hasilGroup = $card.find('.input-group-mobile').has($textarea);
                if ($hasilGroup.length === 0) {
                    $hasilGroup = $card.find('.input-group-mobile').filter(function() {
                        return $(this).find('label').text().includes('Hasil');
                    });
                }

                if ($hasilGroup.length > 0) {
                    // Remove existing buttons
                    $hasilGroup.find('.open-dropdown-modal, .open-editor-modal').remove();
                    
                    // Create inline input
                    self.createHasilInput($hasilGroup, $textarea);
                }

                // Find keterangan textarea if exists
                var $keteranganTextarea = $card.find('textarea[name*="keterangan"]');
                if ($keteranganTextarea.length > 0) {
                    var $keteranganGroup = $keteranganTextarea.closest('.input-group-mobile');
                    if ($keteranganGroup.length > 0) {
                        self.createKeteranganEditor($keteranganGroup, $keteranganTextarea);
                    }
                }
            });

            // Setup keyboard navigation
            this.initKeyboardNavigation();
        },

        /**
         * Create inline input for "Hasil"
         */
        createHasilInput: function($group, $textarea) {
            // Get value - keep HTML if exists (for sup/sub tags for pangkat)
            var currentValue = $textarea.val() || '';
            // Don't clean HTML tags - we need to preserve <sup> tags for pangkat
            
            var id = $textarea.attr('id');
            var min = $textarea.data('min') || '';
            var max = $textarea.data('max') || '';
            var equal = $textarea.data('equal') || '';
            var numberFormat = $textarea.data('number-format') || 'en';
            // Get hasil analis - convert to string to handle numbers/HTML safely
            var hasilAnalisRaw = $textarea.data('hasil-analis');
            var hasilAnalis = hasilAnalisRaw != null ? String(hasilAnalisRaw) : ''; // Hasil dari analis untuk perbandingan
            var parameterId = $textarea.data('parameter-id') || '';
            // Get offset baku mutu from closest parameter card or textarea
            var offsetBakuMutu = $textarea.data('offset-baku-mutu') || 'default';
            if (offsetBakuMutu === 'default') {
                var $paramCard = $textarea.closest('.parameter-card, .card');
                if ($paramCard.length > 0) {
                    var offsetAttr = $paramCard.find('[data-offset-baku-mutu]').first().data('offset-baku-mutu');
                    if (offsetAttr) {
                        offsetBakuMutu = offsetAttr;
                    }
                }
            }
            // Get multiple baku mutu data for validation
            var selectedBakuMutuData = $textarea.data('selected-baku-mutu');
            var selectedBakuMutu = selectedBakuMutuData ? (typeof selectedBakuMutuData === 'string' ? JSON.parse(selectedBakuMutuData) : selectedBakuMutuData) : [];

            // Check if this is a dropdown (option-based)
            var isOption = $textarea.data('is-option') == 1 || $textarea.data('is-option') == '1';
            var optionValues = [];
            if (isOption) {
                var optionValuesStr = $textarea.data('option-values');
                if (optionValuesStr) {
                    try {
                        optionValues = typeof optionValuesStr === 'string' ? JSON.parse(optionValuesStr) : optionValuesStr;
                    } catch(e) {
                        console.error('Error parsing option values:', e);
                        optionValues = [];
                    }
                }
            }

            // Get index from textarea id or name
            var index = id ? id.match(/\d+/) : null;
            index = index ? index[0] : '';

            // Clear existing display
            $group.find('.result-display, .result-output, .open-dropdown-modal, .open-editor-modal').remove();

            var $inputContainer = $('<div class="hasil-input-container"></div>');

            if (isOption && optionValues.length > 0) {
                console.log('Creating dropdown for mobile options:', optionValues);
                
                // Create dropdown for option-based parameters
                var $select = $('<select>').addClass('form-control ' + this.settings.hasilInputClass)
                    .attr({
                        'data-index': index,
                        'data-param-id': $textarea.attr('name'),
                        'data-min': min,
                        'data-max': max,
                        'data-equal': equal,
                        'data-number-format': numberFormat
                    })
                    .css({
                        'width': '100%',
                        'padding': '12px',
                        'border': '2px solid #e9ecef',
                        'border-radius': '8px',
                        'font-size': '16px',
                        'background': 'white',
                        'cursor': 'pointer'
                    });

                $select.append('<option value="">- Pilih -</option>');
                $.each(optionValues, function(i, opt) {
                    var $option = $('<option>').val(opt).text(opt);
                    if (opt == currentValue || opt.trim() == currentValue.trim()) {
                        $option.attr('selected', 'selected');
                    }
                    $select.append($option);
                });

                $inputContainer.append($select);

                // Add badge container
                var $badgeDiv = $('<div>').addClass(this.settings.badgeContainerClass)
                    .attr('id', 'badge_mobile_' + index)
                    .css({
                        'margin-top': '10px',
                        'min-height': '20px'
                    });
                $inputContainer.append($badgeDiv);

                // Store data for later validation
                $select.data('initialValidation', {
                    index: index,
                    currentValue: currentValue,
                    min: min,
                    max: max,
                    equal: equal,
                    numberFormat: numberFormat
                });

                // Bind change event
                var self = this;
                $select.on('change', function(e) {
                    console.log('=== MOBILE DROPDOWN CHANGE ===');
                    var newValue = $(this).val();
                    console.log('New value selected:', newValue);
                    
                    $textarea.val(newValue).trigger('change');
                    
                    // Update badge
                    self.updateResultBadgeForOption('mobile_' + index, newValue, min, max, equal, numberFormat);
                    
                    // Update result preview with baku mutu check
                    var $preview = $group.find('.result-preview');
                    if ($preview.length > 0) {
                        self.updateResultPreview($preview, newValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                    }
                });
                
                // Update result preview on initial load if there's a value
                if (currentValue && currentValue.trim() !== '' && currentValue !== '-') {
                    var $preview = $group.find('.result-preview');
                    if ($preview.length > 0) {
                        var self = this;
                        setTimeout(function() {
                            self.updateResultPreview($preview, currentValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                        }, 100);
                    }
                }
            } else {
                // Create contenteditable div with TinyMCE support
                // Use HTML for initial value (to support sup/sub tags for pangkat)
                var $editor = $('<div>').addClass('inline-hasil-editor')
                    .attr({
                        'id': 'hasil_editor_mobile_' + index,
                        'data-index': index,
                        'data-textarea-id': id,
                        'data-min': min,
                        'data-max': max,
                        'data-equal': equal,
                        'data-number-format': numberFormat,
                        'contenteditable': 'true'
                    })
                    .html(currentValue || '') // Use html() to preserve sup/sub tags
                    .css({
                        'min-height': '50px',
                        'padding': '12px',
                        'border': '2px solid #e9ecef',
                        'border-radius': '8px',
                        'background': 'white',
                        'cursor': 'text',
                        'font-size': '16px'
                    });

                if (!currentValue) {
                    $editor.attr('data-placeholder', 'Masukkan hasil...');
                    $editor.css('color', '#999');
                }

                $inputContainer.append($editor);

                // Add badge container
                var $badgeDiv = $('<div>').addClass(this.settings.badgeContainerClass)
                    .attr('id', 'badge_mobile_' + index);
                $inputContainer.append($badgeDiv);

                // Show initial badge if has value
                if (currentValue) {
                    // Extract plain text from HTML for badge validation
                    var textValue = $('<div>').html(currentValue).text();
                    this.updateResultBadge('mobile_' + index, textValue, min, max, equal, numberFormat);
                }
                
                // Update result preview on initial load if there's a value
                if (currentValue && currentValue.trim() !== '' && currentValue !== '-') {
                    var $preview = $group.find('.result-preview');
                    if ($preview.length > 0) {
                        var self = this;
                        // Extract plain text from HTML for preview validation
                        var textValue = $('<div>').html(currentValue).text();
                        setTimeout(function() {
                            self.updateResultPreview($preview, textValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                        }, 100);
                    }
                }

                // Initialize TinyMCE for this editor
                // Wait a bit to ensure element is in DOM before initializing TinyMCE
                var self = this;
                setTimeout(function() {
                    self.initTinyMCEForMobile($editor, index, min, max, equal, numberFormat, $textarea, $group);
                }, 200);
            }

            // Insert after label
            var $label = $group.find('label').first();
            if ($label.length > 0) {
                $label.after($inputContainer);
            } else {
                $group.prepend($inputContainer);
            }

            // Run initial validation after DOM ready
            setTimeout(function() {
                self.runInitialValidation();
            }, 100);
        },

        /**
         * Create inline editor for "Keterangan"
         */
        createKeteranganEditor: function($group, $textarea) {
            var currentValue = $textarea.val() || '';
            var id = $textarea.attr('id');
            var index = id ? id.match(/\d+/)[0] : '';

            // Clear existing display
            $group.find('.keterangan-display').remove();

            // Create editable div
            var $editor = $('<div>').addClass(this.settings.keteranganEditorClass)
                .attr({
                    'id': 'keterangan_editor_mobile_' + index,
                    'data-index': index,
                    'data-textarea-id': id,
                    'contenteditable': 'true'
                })
                .html(currentValue || '');

            if (!currentValue) {
                $editor.addClass('empty');
            }

            // Insert after label
            var $label = $group.find('label').first();
            if ($label.length > 0) {
                $label.after($editor);
            } else {
                $group.prepend($editor);
            }

            // Bind change event
            $editor.on('blur', function() {
                var newValue = $(this).html();
                $textarea.val(newValue);
            });
        },

        /**
         * Initialize TinyMCE for mobile inline editor
         */
        initTinyMCEForMobile: function($editor, index, min, max, equal, numberFormat, $textarea, $group) {
            var self = this;
            var editorId = $editor.attr('id');
            var hasilAnalis = $textarea.data('hasil-analis') || '';
            var parameterId = $textarea.data('parameter-id') || '';
            var offsetBakuMutu = $textarea.data('offset-baku-mutu') || 'default';
            if (offsetBakuMutu === 'default') {
                var $paramCard = $textarea.closest('.parameter-card, .card');
                if ($paramCard.length > 0) {
                    var offsetAttr = $paramCard.find('[data-offset-baku-mutu]').first().data('offset-baku-mutu');
                    if (offsetAttr) {
                        offsetBakuMutu = offsetAttr;
                    }
                }
            }
            // Get multiple baku mutu data for validation
            var selectedBakuMutuData = $textarea.data('selected-baku-mutu');
            var selectedBakuMutu = selectedBakuMutuData ? (typeof selectedBakuMutuData === 'string' ? JSON.parse(selectedBakuMutuData) : selectedBakuMutuData) : [];
            
            // Wait for TinyMCE to be loaded and element to be in DOM
            var checkTinyMCE = function(retries) {
                retries = retries || 0;
                if (retries > 100) { // Max 10 seconds wait
                    console.warn('TinyMCE not loaded after 10 seconds, using contenteditable only');
                    // Fallback: use contenteditable with plain text saving
                    $editor.on('blur', function() {
                        var textValue = $(this).text().trim();
                        $textarea.val(textValue).trigger('change');
                        self.updateResultBadge('mobile_' + index, textValue, min, max, equal, numberFormat);
                        
                        // Update result preview with baku mutu check
                        var $preview = $group.find('.result-preview');
                        if ($preview.length > 0) {
                            self.updateResultPreview($preview, textValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                        }
                    });
                    return;
                }
                
                // Check if TinyMCE is loaded
                if (typeof tinymce === 'undefined' || typeof tinymce.init === 'undefined') {
                    setTimeout(function() {
                        checkTinyMCE(retries + 1);
                    }, 100);
                    return;
                }
                
                // Check if editor element exists in DOM
                var $editorElement = $('#' + editorId);
                if ($editorElement.length === 0) {
                    console.log('Editor element not yet in DOM, waiting...', editorId);
                    setTimeout(function() {
                        checkTinyMCE(retries + 1);
                    }, 100);
                    return;
                }
                
                // Both TinyMCE and element are ready, proceed with initialization
                console.log('TinyMCE and element ready, initializing...', editorId);
                initTinyMCE();
            };
            
            var initTinyMCE = function() {
                // Check if editor element exists in DOM
                var $editorElement = $('#' + editorId);
                if ($editorElement.length === 0) {
                    console.error('Editor element not found in DOM:', editorId);
                    // Fallback: use contenteditable with plain text saving
                    $editor.on('blur', function() {
                        var textValue = $(this).text().trim();
                        $textarea.val(textValue).trigger('change');
                        self.updateResultBadge('mobile_' + index, textValue, min, max, equal, numberFormat);
                        
                        var $preview = $group.find('.result-preview');
                        if ($preview.length > 0) {
                            self.updateResultPreview($preview, textValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                        }
                    });
                    return;
                }

                console.log('Initializing TinyMCE for:', editorId, 'Element found:', $editorElement.length > 0);

                try {
                    tinymce.init({
                        selector: '#' + editorId,
                        inline: true,
                        menubar: false,
                        base_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7',
                        suffix: '.min',
                        skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/ui/oxide',
                        content_css: 'https://cdn.jsdelivr.net/npm/tinymce@5.10.7/skins/content/default/content.css',
                        plugins: [
                            'lists charmap',
                            'searchreplace',
                            'paste'
                        ],
                        toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                        toolbar_mode: 'sliding', // Use sliding mode for better mobile visibility
                        toolbar_location: 'bottom', // Show toolbar at bottom for mobile
                        toolbar_sticky: true, // Keep toolbar visible when scrolling
                        mobile: {
                            toolbar_mode: 'sliding',
                            toolbar_sticky: true
                        },
                        paste_as_text: true,
                        forced_root_block: false, // Don't wrap content in <p> tags
                        force_br_newlines: true, // Use <br> instead of <p> for new lines
                        force_p_newlines: false, // Don't force <p> tags
                        content_style: 'body { font-size: 16px; font-family: Arial, sans-serif; min-height: 40px; padding: 8px; }',
                        charmap_append: [
                            // Simbol matematika dasar
                            [0x00B1, '± plus-minus sign'],
                            [0x00B2, '² superscript two'],
                            [0x00B3, '³ superscript three'],
                            [0x00B9, '¹ superscript one'],
                            [0x2070, '⁰ superscript zero'],
                            [0x2074, '⁴ superscript four'],
                            [0x2075, '⁵ superscript five'],
                            [0x2076, '⁶ superscript six'],
                            [0x2077, '⁷ superscript seven'],
                            [0x2078, '⁸ superscript eight'],
                            [0x2079, '⁹ superscript nine'],
                            [0x2080, '₀ subscript zero'],
                            [0x2081, '₁ subscript one'],
                            [0x2082, '₂ subscript two'],
                            [0x2083, '₃ subscript three'],
                            [0x2084, '₄ subscript four'],
                            [0x2085, '₅ subscript five'],
                            [0x2086, '₆ subscript six'],
                            [0x2087, '₇ subscript seven'],
                            [0x2088, '₈ subscript eight'],
                            [0x2089, '₉ subscript nine'],
                            [0x00B5, 'µ micro sign'],
                            [0x00BC, '¼ vulgar fraction one quarter'],
                            [0x00BD, '½ vulgar fraction one half'],
                            [0x00BE, '¾ vulgar fraction three quarters'],
                            [0x2153, '⅓ vulgar fraction one third'],
                            [0x2154, '⅔ vulgar fraction two thirds'],
                            [0x2155, '⅕ vulgar fraction one fifth'],
                            [0x2156, '⅖ vulgar fraction two fifths'],
                            [0x2157, '⅗ vulgar fraction three fifths'],
                            [0x2158, '⅘ vulgar fraction four fifths'],
                            [0x2159, '⅙ vulgar fraction one sixth'],
                            [0x215A, '⅚ vulgar fraction five sixths'],
                            [0x215B, '⅛ vulgar fraction one eighth'],
                            [0x215C, '⅜ vulgar fraction three eighths'],
                            [0x215D, '⅝ vulgar fraction five eighths'],
                            [0x215E, '⅞ vulgar fraction seven eighths'],
                            // Simbol perbandingan
                            [0x2264, '≤ less-than or equal to'],
                            [0x2265, '≥ greater-than or equal to'],
                            [0x2248, '≈ almost equal to'],
                            [0x2260, '≠ not equal to'],
                            [0x003C, '< less-than sign'],
                            [0x003E, '> greater-than sign'],
                            [0x2261, '≡ identical to'],
                            [0x2243, '≃ asymptotically equal to'],
                            // Simbol kimia dan suhu
                            [0x00B0, '° degree sign'],
                            [0x2103, '℃ degree celsius'],
                            [0x2109, '℉ degree fahrenheit'],
                            [0x00D7, '× multiplication sign'],
                            [0x00F7, '÷ division sign'],
                            [0x2212, '− minus sign'],
                            [0x221E, '∞ infinity'],
                            [0x2211, '∑ n-ary summation'],
                            [0x220F, '∏ n-ary product'],
                            [0x221A, '√ square root'],
                            [0x00B7, '· middle dot'],
                            // Greek letters (untuk notasi ilmiah)
                            [0x03B1, 'α greek small letter alpha'],
                            [0x03B2, 'β greek small letter beta'],
                            [0x03B3, 'γ greek small letter gamma'],
                            [0x03B4, 'δ greek small letter delta'],
                            [0x03B5, 'ε greek small letter epsilon'],
                            [0x03B6, 'ζ greek small letter zeta'],
                            [0x03B7, 'η greek small letter eta'],
                            [0x03B8, 'θ greek small letter theta'],
                            [0x03B9, 'ι greek small letter iota'],
                            [0x03BA, 'κ greek small letter kappa'],
                            [0x03BB, 'λ greek small letter lambda'],
                            [0x03BC, 'μ greek small letter mu'],
                            [0x03BD, 'ν greek small letter nu'],
                            [0x03BE, 'ξ greek small letter xi'],
                            [0x03BF, 'ο greek small letter omicron'],
                            [0x03C0, 'π greek small letter pi'],
                            [0x03C1, 'ρ greek small letter rho'],
                            [0x03C3, 'σ greek small letter sigma'],
                            [0x03C4, 'τ greek small letter tau'],
                            [0x03C5, 'υ greek small letter upsilon'],
                            [0x03C6, 'φ greek small letter phi'],
                            [0x03C7, 'χ greek small letter chi'],
                            [0x03C8, 'ψ greek small letter psi'],
                            [0x03C9, 'ω greek small letter omega'],
                            // Simbol lainnya
                            [0x00A9, '© copyright sign'],
                            [0x00AE, '® registered sign'],
                            [0x2122, '™ trade mark sign'],
                            [0x2022, '• bullet'],
                            [0x2013, '– en dash'],
                            [0x2014, '— em dash'],
                            [0x2018, '\u2018 left single quotation mark'],
                            [0x2019, '\u2019 right single quotation mark'],
                            [0x201C, '\u201C left double quotation mark'],
                            [0x201D, '\u201D right double quotation mark']
                        ],
                        setup: function(editor) {
                            editor.on('blur', function() {
                                // Get HTML content (with sup/sub tags for pangkat)
                                var htmlContent = editor.getContent();
                                
                                // Get plain text for validation
                                var textContent = editor.getContent({format: 'text'});
                                
                                // Save HTML content to textarea (with <sup> tags for pangkat)
                                $textarea.val(htmlContent).trigger('change');
                                
                                // Update badge with plain text for validation
                                self.updateResultBadge('mobile_' + index, textContent, min, max, equal, numberFormat);
                                
                                // Update result preview with baku mutu check
                                var $preview = $group.find('.result-preview');
                                if ($preview.length > 0) {
                                    self.updateResultPreview($preview, textContent, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                                }
                            });
                        }
                    });
                } catch(e) {
                    console.error('Error initializing TinyMCE for mobile:', e);
                    
                    // Fallback: use contenteditable with plain text saving
                    $editor.on('blur', function() {
                        var textValue = $(this).text().trim();
                        $textarea.val(textValue).trigger('change');
                        self.updateResultBadge('mobile_' + index, textValue, min, max, equal, numberFormat);
                        
                        // Update result preview with baku mutu check
                        var $preview = $group.find('.result-preview');
                        if ($preview.length > 0) {
                            self.updateResultPreview($preview, textValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                        }
                    });
                }
            };
            
            // Start checking for TinyMCE
            checkTinyMCE();
        },

        /**
         * Initialize keyboard navigation
         * Note: Enter di textarea/editor tetap untuk new line (default behavior)
         * Navigasi ke hasil berikutnya menggunakan tombol panah bawah yang sudah ada di blade file
         */
        initKeyboardNavigation: function() {
            // Tidak ada keyboard navigation untuk Enter
            // Enter tetap untuk new line (default behavior)
            // Navigasi menggunakan tombol panah bawah yang sudah ada di blade file
        },

        /**
         * Navigate to next hasil field
         * Note: Function ini tidak digunakan lagi karena navigasi menggunakan tombol panah bawah
         * yang sudah ada di blade file (pemeriksa.blade.php dan verifikasi.blade.php)
         */
        navigateToNextHasil: function($current) {
            // Tidak digunakan - navigasi menggunakan tombol panah bawah
        },

        /**
         * Set cursor at end of contenteditable element
         */
        setCursorAtEnd: function(element) {
            if (window.getSelection && document.createRange) {
                var range = document.createRange();
                range.selectNodeContents(element);
                range.collapse(false);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        },

        /**
         * Run initial validation for dropdowns
         */
        runInitialValidation: function() {
            var self = this;
            console.log('Running initial validation for mobile dropdowns...');
            
            $('select.' + this.settings.hasilInputClass).each(function() {
                var $dropdown = $(this);
                var validationData = $dropdown.data('initialValidation');
                
                if (validationData && validationData.currentValue) {
                    console.log('Initial validation for mobile index:', validationData.index, 'value:', validationData.currentValue);
                    self.updateResultBadgeForOption(
                        'mobile_' + validationData.index,
                        validationData.currentValue,
                        validationData.min,
                        validationData.max,
                        validationData.equal,
                        validationData.numberFormat
                    );
                    
                    // Also update result preview if exists
                    var $group = $dropdown.closest('.input-group-mobile, .card');
                    if ($group.length > 0) {
                        var $preview = $group.find('.result-preview');
                        if ($preview.length > 0) {
                            // Get data attributes from textarea - find by data-param-id or closest textarea
                            var paramId = $dropdown.data('param-id') || '';
                            var $textarea = null;
                            
                            if (paramId) {
                                // Try to find textarea by name attribute
                                $textarea = $('textarea[name="' + paramId + '"]');
                            }
                            
                            if (!$textarea || $textarea.length === 0) {
                                // Fallback: find textarea in the same group
                                $textarea = $group.find('textarea.result_method, textarea[name*="hasil_koreksi"]').first();
                            }
                            
                            if ($textarea.length > 0) {
                                var hasilAnalis = $textarea.data('hasil-analis') || '';
                                var parameterId = $textarea.data('parameter-id') || '';
                                var offsetBakuMutu = $textarea.data('offset-baku-mutu') || 'default';
                                var selectedBakuMutuData = $textarea.data('selected-baku-mutu');
                                var selectedBakuMutu = selectedBakuMutuData ? (typeof selectedBakuMutuData === 'string' ? JSON.parse(selectedBakuMutuData) : selectedBakuMutuData) : [];
                                
                                setTimeout(function() {
                                    self.updateResultPreview($preview, validationData.currentValue, hasilAnalis, validationData.min, validationData.max, validationData.equal, validationData.numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu);
                                }, 200);
                            }
                        }
                    }
                }
            });
        },

        /**
         * Setup event delegation for dropdowns
         */
        setupDropdownEventDelegation: function() {
            var self = this;
            console.log('Setting up mobile dropdown event delegation...');
            
            $(document).on('change', 'select.' + this.settings.hasilInputClass, function(e) {
                console.log('=== MOBILE DROPDOWN CHANGE (Event Delegation) ===');
                var $dropdown = $(this);
                var newValue = $dropdown.val();
                var index = $dropdown.data('index');
                var min = $dropdown.data('min') || '';
                var max = $dropdown.data('max') || '';
                var equal = $dropdown.data('equal') || '';
                var numberFormat = $dropdown.data('number-format') || 'en';
                
                // Update textarea
                var $textarea = $dropdown.closest('.card').find('textarea.result_method');
                if ($textarea.length > 0) {
                    $textarea.val(newValue);
                }
                
                // Update badge
                self.updateResultBadgeForOption('mobile_' + index, newValue, min, max, equal, numberFormat);
            });
        },

        /**
         * Update result badge for option-based parameters
         */
        updateResultBadgeForOption: function(index, value, min, max, equal, numberFormat) {
            console.log('updateResultBadgeForOption mobile called:', {index: index, value: value, min: min, max: max, equal: equal, format: numberFormat});
            
            if (!value) {
                $('#badge_' + index).html('');
                return;
            }

            numberFormat = numberFormat || 'en';
            
            // Try to extract numeric value
            var numericValue = null;
            var cleanValue = value.toString().trim();
            var operatorMatch = cleanValue.match(/^([<>≤≥]+)\s*([\d.,]+)/);
            
            if (operatorMatch) {
                numericValue = parseNumberInput(operatorMatch[2], numberFormat);
            } else {
                numericValue = parseNumberInput(cleanValue, numberFormat);
            }

            var isNormal = true;
            var message = '';

            if (numericValue !== null && !isNaN(numericValue)) {
                var minVal = parseNumberInput(min, 'en');
                var maxVal = parseNumberInput(max, 'en');
                var equalVal = parseNumberInput(equal, 'en');

                if (equalVal !== null && !isNaN(equalVal)) {
                    isNormal = (Math.abs(numericValue - equalVal) < 0.0001);
                    message = isNormal ? 'Sesuai nilai baku mutu' : 'Tidak sesuai nilai baku mutu (Expected: ' + equal + ')';
                } else if (minVal !== null && !isNaN(minVal) && maxVal !== null && !isNaN(maxVal)) {
                    isNormal = (numericValue >= minVal && numericValue <= maxVal);
                    message = isNormal ? 
                        'Dalam rentang baku mutu (' + min + ' - ' + max + ')' : 
                        'Di luar rentang baku mutu (' + min + ' - ' + max + ')';
                } else if (minVal !== null && !isNaN(minVal)) {
                    isNormal = (numericValue >= minVal);
                    message = isNormal ? 'Di atas batas minimum (' + min + ')' : 'Di bawah batas minimum (' + min + ')';
                } else if (maxVal !== null && !isNaN(maxVal)) {
                    isNormal = (numericValue <= maxVal);
                    message = isNormal ? 'Di bawah batas maksimum (' + max + ')' : 'Melebihi batas maksimum (' + max + ')';
                }
            } else {
                if (equal && equal != '') {
                    isNormal = (cleanValue.toLowerCase() === equal.toString().toLowerCase());
                    message = isNormal ? 'Sesuai standar' : 'Tidak sesuai standar (Expected: ' + equal + ')';
                } else {
                    message = 'Terpilih';
                }
            }

            var badgeClass = isNormal ? 'badge-success' : 'badge-danger';
            var icon = isNormal ? 'fa-check-circle' : 'fa-times-circle';
            var star = isNormal ? '' : ' <span class="bintang-baku-mutu">*</span>';

            var badgeHtml = '<span class="badge ' + badgeClass + '">' +
                '<i class="fa ' + icon + '"></i> ' + value + star +
                (message ? '<br><small>' + message + '</small>' : '') +
                '</span>';

            $('#badge_' + index).html(badgeHtml);
        },

        /**
         * Update result badge for text input
         */
        updateResultBadge: function(index, value, min, max, equal, numberFormat) {
            if (!value) {
                $('#badge_' + index).html('');
                return;
            }

            numberFormat = numberFormat || 'en';
            
            if (typeof window.checkBakuMutu === 'function') {
                var output = window.checkBakuMutu(value, min, max, equal, 'default', null, '', numberFormat);
                $('#badge_' + index).html(output || '');
            }
        },

        /**
         * Update result preview with baku mutu check (HANYA badge baku mutu, TANPA perbandingan dengan analis)
         * Mendukung multiple baku mutu seperti di pemeriksa.blade.php
         */
        updateResultPreview: function($preview, newValue, hasilAnalis, min, max, equal, numberFormat, parameterId, offsetBakuMutu, selectedBakuMutu) {
            if (!$preview || $preview.length === 0) {
                return;
            }

            numberFormat = numberFormat || 'en';
            offsetBakuMutu = offsetBakuMutu || 'default';
            selectedBakuMutu = selectedBakuMutu || [];
            var output = '';

            // Extract plain text from HTML if needed
            var rawValue = newValue || '';
            var plainValue = rawValue;
            if (typeof rawValue === 'string' && /<[^>]+>/.test(rawValue)) {
                var $temp = $('<div>').html(rawValue);
                plainValue = $temp.text() || $temp.html();
            }

            // Remove spaces for checking
            var delete_space = plainValue ? String(plainValue).replace(/\s/g, '') : '';

            if (delete_space && delete_space !== "" && delete_space !== "-") {
                var melewati_baku_mutu = false;
                var matchedBakuMutu = null;

                // Prioritas: Cek terhadap selected baku mutu terlebih dahulu (jika ada)
                if (selectedBakuMutu && selectedBakuMutu.length > 0) {
                    var checkResult = this.checkAgainstSelectedBakuMutu(plainValue, selectedBakuMutu, numberFormat);
                    melewati_baku_mutu = checkResult.melewati;
                    matchedBakuMutu = checkResult.matched;
                } else {
                    // Fallback ke single baku mutu (min, max, equal) jika tidak ada selected
                    var hasil_clean = plainValue.toString().replace(/&nbsp;/g, ' ').trim();
                    var hasil_numeric = this.extractNumericValue(plainValue, numberFormat);

                    if (this.isValidEqual(equal)) {
                        var equal_clean = String(equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                        var hasil_compare = hasil_clean.replace(/\s/g, '');
                        if (hasil_compare !== equal_clean) {
                            melewati_baku_mutu = true;
                        } else {
                            melewati_baku_mutu = false;
                        }
                    } else if (hasil_numeric !== null && !isNaN(hasil_numeric)) {
                        var dbFormat = numberFormat || 'en';
                        var hasMin = this.isValidNumeric(min, dbFormat);
                        var hasMax = this.isValidNumeric(max, dbFormat);

                        if (hasMin && hasMax) {
                            var minNum = parseNumberInput(min, dbFormat);
                            var maxNum = parseNumberInput(max, dbFormat);
                            if (hasil_numeric < minNum || hasil_numeric > maxNum) {
                                melewati_baku_mutu = true;
                            } else {
                                melewati_baku_mutu = false;
                            }
                        } else if (hasMin) {
                            var minNum = parseNumberInput(min, dbFormat);
                            if (hasil_numeric < minNum) {
                                melewati_baku_mutu = true;
                            } else {
                                melewati_baku_mutu = false;
                            }
                        } else if (hasMax) {
                            var maxNum = parseNumberInput(max, dbFormat);
                            if (/^>\s*[\d.,]+/.test(hasil_clean)) {
                                if (hasil_numeric > maxNum) {
                                    melewati_baku_mutu = true;
                                } else {
                                    melewati_baku_mutu = false;
                                }
                            } else if (hasil_numeric > maxNum) {
                                melewati_baku_mutu = true;
                            } else {
                                melewati_baku_mutu = false;
                            }
                        } else {
                            melewati_baku_mutu = false;
                        }
                    } else {
                        melewati_baku_mutu = false;
                    }
                }

                // Use checkBakuMutu untuk mendapatkan badge dengan format yang benar
                // Jika ada selected baku mutu, gunakan min/max/equal dari matched baku mutu
                var checkMin = min;
                var checkMax = max;
                var checkEqual = equal;
                var kesimpulan = '';
                
                if (matchedBakuMutu) {
                    checkMin = matchedBakuMutu.min || min;
                    checkMax = matchedBakuMutu.max || max;
                    checkEqual = matchedBakuMutu.equal || equal;
                    kesimpulan = matchedBakuMutu.kesimpulan_baku_mutu || '';
                }
                
                if (typeof window.checkBakuMutu === 'function') {
                    var bakuMutuOutput = window.checkBakuMutu(plainValue, checkMin, checkMax, checkEqual, offsetBakuMutu, null, '', numberFormat);
                    if (bakuMutuOutput) {
                        output = bakuMutuOutput;
                        // Tambahkan kesimpulan jika ada
                        if (kesimpulan && kesimpulan.trim() !== '') {
                            var $temp = $('<div>').html(bakuMutuOutput);
                            if ($temp.find('.badge').length > 0) {
                                output = bakuMutuOutput + '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + this.toFormatHtml(kesimpulan) + '</small>';
                            }
                        }
                    }
                } else {
                    // Fallback: create simple badge
                    var badgeClass = melewati_baku_mutu ? 'badge-danger' : 'badge-success';
                    var icon = melewati_baku_mutu ? 'fa-times-circle' : 'fa-check-circle';
                    output = '<span class="badge ' + badgeClass + '" style="font-size: 14px; padding: 8px 12px;">' +
                        '<i class="fa ' + icon + '"></i> ' + this.toFormatHtml(rawValue) +
                        (melewati_baku_mutu ? ' <i class="fa fa-exclamation-triangle"></i>' : '') +
                        '</span>';
                    if (kesimpulan) {
                        output += '<br><small class="text-info mt-1" style="display: block; margin-top: 6px; font-size: 12px;"><i class="fa fa-info-circle"></i> ' + this.toFormatHtml(kesimpulan) + '</small>';
                    }
                }
            }

            $preview.html(output || '<span class="text-muted">-</span>');
        },

        /**
         * Check if value is valid numeric
         */
        isValidNumeric: function(val, format) {
            format = format || 'en';
            var num = parseNumberInput(val, format);
            return num !== null && !isNaN(num) && isFinite(num);
        },

        /**
         * Check if value is valid (for equal check)
         */
        isValidEqual: function(val) {
            return val !== "" && val !== null && val !== undefined && String(val).trim() !== "";
        },

        /**
         * Extract numeric value from string
         */
        extractNumericValue: function(str, format) {
            format = format || 'en';
            if (!str) return null;
            var cleaned = str.toString().replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
            
            // Try to extract number from patterns like "<100", ">50", etc.
            var match = cleaned.match(/^[<>≤≥]?\s*([\d.,]+)/);
            if (match) {
                return parseNumberInput(match[1], format);
            }
            
            // Try direct parsing
            var num = parseNumberInput(cleaned, format);
            return num;
        },

        /**
         * Check result against selected baku mutu (mengikuti logika dari pemeriksa.blade.php)
         */
        checkAgainstSelectedBakuMutu: function(rawValue, selectedBakuMutu, numberFormat) {
            numberFormat = numberFormat || 'en';
            
            if (!selectedBakuMutu || selectedBakuMutu.length === 0) {
                return { melewati: true, matched: null };
            }

            var hasil_clean = rawValue.toString().replace(/&nbsp;/g, ' ').trim();
            var hasil_numeric = this.extractNumericValue(rawValue, numberFormat);
            var isWithinAnyRange = false;
            var matchedBakuMutu = null;

            // Cek apakah hasil masuk dalam salah satu range yang dipilih
            for (var i = 0; i < selectedBakuMutu.length; i++) {
                var bm = selectedBakuMutu[i];
                var isWithinThisRange = false;

                // Cek dengan equal terlebih dahulu
                if (this.isValidEqual(bm.equal)) {
                    var equal_clean = String(bm.equal).replace(/&nbsp;/g, ' ').trim().replace(/\s/g, '');
                    var hasil_compare = hasil_clean.replace(/\s/g, '');
                    if (hasil_compare === equal_clean) {
                        isWithinThisRange = true;
                        matchedBakuMutu = bm;
                    }
                }
                // Jika tidak ada equal, cek dengan min dan max
                else if (hasil_numeric !== null && !isNaN(hasil_numeric)) {
                    var dbFormat = numberFormat || 'en';
                    var hasMin = this.isValidNumeric(bm.min, dbFormat);
                    var hasMax = this.isValidNumeric(bm.max, dbFormat);

                    if (hasMin && hasMax) {
                        var bmMin = parseNumberInput(bm.min, dbFormat);
                        var bmMax = parseNumberInput(bm.max, dbFormat);
                        if (hasil_numeric >= bmMin && hasil_numeric <= bmMax) {
                            isWithinThisRange = true;
                            matchedBakuMutu = bm;
                        }
                    } else if (hasMin) {
                        var bmMin = parseNumberInput(bm.min, dbFormat);
                        if (hasil_numeric >= bmMin) {
                            isWithinThisRange = true;
                            matchedBakuMutu = bm;
                        }
                    } else if (hasMax) {
                        var bmMax = parseNumberInput(bm.max, dbFormat);
                        // Handle format ">100" - jika hasil <= max, maka dalam range
                        if (/^>\s*[\d.,]+/.test(hasil_clean)) {
                            if (hasil_numeric <= bmMax) {
                                isWithinThisRange = true;
                                matchedBakuMutu = bm;
                            }
                        } else if (hasil_numeric <= bmMax) {
                            isWithinThisRange = true;
                            matchedBakuMutu = bm;
                        }
                    } else {
                        // Tidak ada min dan max, anggap dalam range
                        isWithinThisRange = true;
                        matchedBakuMutu = bm;
                    }
                }

                if (isWithinThisRange) {
                    isWithinAnyRange = true;
                    break; // Sudah ketemu yang match, tidak perlu cek yang lain
                }
            }

            return {
                melewati: !isWithinAnyRange, // Melewati jika TIDAK masuk dalam range manapun
                matched: matchedBakuMutu
            };
        },

        /**
         * Format value to HTML (for display)
         */
        toFormatHtml: function(value) {
            if (!value) return '';
            
            // Convert to string
            value = value.toString();
            
            // Remove <p> tags first
            value = value.replace(/<p[^>]*>/gi, '');
            value = value.replace(/<\/p>/gi, '');
            
            // If already contains HTML sup/sub tags, clean and return
            if (/<sup[^>]*>|<sub[^>]*>/.test(value)) {
                // Already has HTML sup/sub tags, just clean <p> tags
                return value;
            }
            
            // Convert superscript notation: ^(1) or ^1 to <sup>1</sup>
            // Pattern: number^(digit)number or number^digit
            value = value.replace(/\^\((\d+)\)/g, '<sup>$1</sup>');
            value = value.replace(/\^(\d+)/g, '<sup>$1</sup>');
            
            // Convert basic formatting
            value = value.replace(/\n/g, '<br>');
            value = value.replace(/ /g, '&nbsp;');
            
            return value;
        },

        /**
         * Escape HTML for safe display
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Auto-initialize when document is ready
    $(document).ready(function() {
        setTimeout(function() {
            MobileInlineEditor.init();
            MobileInlineEditor.setupDropdownEventDelegation();
        }, 500);
    });

    // Make available globally
    window.MobileInlineEditor = MobileInlineEditor;

})(jQuery);

