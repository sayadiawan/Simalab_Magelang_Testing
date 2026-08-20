/**
 * Analis Inline Editing - Simplified Input Form
 * 
 * Mengubah tampilan form analis dari modal-based menjadi inline editing dengan:
 * - Input langsung di tabel
 * - TinyMCE inline mode untuk keterangan
 * - Keyboard navigation (Enter/Arrow keys)
 * - Real-time badge update
 */

(function($) {
    'use strict';

    // Helper function to decode HTML entities and normalize whitespace
    function decodeHtmlEntities(str) {
        if (!str) return '';
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = str.toString();
        var decoded = tempDiv.textContent || tempDiv.innerText || '';
        // Normalize all whitespace (multiple spaces, tabs, newlines) to single space
        decoded = decoded.replace(/\s+/g, ' ').trim();
        return decoded;
    }
    
    // Normalize legacy "?" comparison operator to "≥" before digit (encoding mismatch in old data)
    function normalizeComparisonOperatorDisplay(str) {
        if (!str) return str;
        return String(str).replace(/(^|[\s,(;])\?\s*(?=\d)/g, '$1≥ ');
    }

    // Helper function to normalize string for comparison (decode HTML, remove all whitespace)
    function normalizeForComparison(str) {
        if (!str) return '';
        str = normalizeComparisonOperatorDisplay(str.toString());
        // Decode HTML entities first
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = str;
        var decoded = tempDiv.textContent || tempDiv.innerText || '';
        // Remove ALL whitespace (spaces, tabs, newlines, etc.) for comparison
        decoded = decoded.replace(/\s+/g, '');
        return decoded;
    }

    var AnalisInlineEditor = {
        settings: {
            hasilInputClass: 'inline-hasil-input',
            keteranganEditorClass: 'inline-keterangan-editor',
            badgeContainerClass: 'result-badge-inline'
        },
        
        initialized: false,

        init: function() {
            // Prevent double initialization
            if (this.initialized) {
                console.log('AnalisInlineEditor already initialized, skipping...');
                return;
            }
            
            console.log('Initializing Analis Inline Editor...');
            this.convertToInlineEditing();
            
            // Wait for DOM elements to be created before initializing TinyMCE
            var self = this;
            var retryCount = 0;
            var maxRetries = 50; // 5 seconds max wait (increased for TinyMCE to fully load)
            
            function tryInitTinyMCE() {
                var $hasilEditors = $('.inline-hasil-editor');
                var $keteranganEditors = $('.' + self.settings.keteranganEditorClass);
                
                // Check if TinyMCE is fully ready (not just loaded, but also initialized)
                // Wait for TinyMCE to be completely ready, including all internal modules
                var isTinyMCEReady = typeof tinymce !== 'undefined' && 
                                     typeof tinymce.init === 'function' &&
                                     typeof tinymce.util === 'object' &&
                                     typeof tinymce.EditorManager !== 'undefined';
                
                // Also check if TinyMCE has finished initializing any previous instances
                // Wait a bit more if TinyMCE is still initializing
                if (isTinyMCEReady && typeof tinymce.EditorManager !== 'undefined') {
                    try {
                        // Try to access a TinyMCE internal property to ensure it's fully loaded
                        var testInit = tinymce.init;
                        if (typeof testInit !== 'function') {
                            isTinyMCEReady = false;
                        }
                    } catch(e) {
                        isTinyMCEReady = false;
                    }
                }
                
                if (($hasilEditors.length > 0 || $keteranganEditors.length > 0) && isTinyMCEReady) {
                    console.log('Elements found and TinyMCE is ready, initializing TinyMCE...');
                    // Add small delay to ensure TinyMCE is completely ready
                    setTimeout(function() {
                        self.initTinyMCEInline();
                        self.initKeyboardNavigation();
                        self.initialized = true;
                        console.log('Analis Inline Editor initialized successfully');
                        // Notify blade that editor is ready → sembunyikan loading overlay
                        window.analisEditorReady = true;
                        $(document).trigger('analisEditorReady');
                        // Run initial validation after TinyMCE is initialized
                        setTimeout(function() {
                            self.runInitialValidation();
                        }, 500);
                    }, 200);
                } else if (retryCount < maxRetries) {
                    retryCount++;
                    if (!isTinyMCEReady) {
                        console.log('Waiting for TinyMCE to be ready... (attempt ' + retryCount + '/' + maxRetries + ')');
                    } else {
                        console.log('Waiting for elements to be created... (attempt ' + retryCount + '/' + maxRetries + ')');
                    }
                    setTimeout(tryInitTinyMCE, 100);
                } else {
                    if (!isTinyMCEReady) {
                        console.error('TinyMCE not ready after ' + maxRetries + ' attempts. Cannot initialize.');
                        console.error('TinyMCE state:', {
                            defined: typeof tinymce !== 'undefined',
                            hasInit: typeof tinymce !== 'undefined' && typeof tinymce.init === 'function',
                            hasUtil: typeof tinymce !== 'undefined' && typeof tinymce.util === 'object',
                            hasEditorManager: typeof tinymce !== 'undefined' && typeof tinymce.EditorManager !== 'undefined'
                        });
                        return;
                    }
                    console.warn('Elements not found after ' + maxRetries + ' attempts. Initializing anyway...');
                    self.initTinyMCEInline();
                    self.initKeyboardNavigation();
                    self.initialized = true;
                    window.analisEditorReady = true;
                    $(document).trigger('analisEditorReady');
                }
            }
            
            // Start trying after a longer delay to ensure TinyMCE from template admin is fully loaded
            setTimeout(tryInitTinyMCE, 500);
        },

        /**
         * Convert hidden textareas to visible inputs
         */
        convertToInlineEditing: function() {
            var self = this;
            var processedCount = 0;
            var skippedCount = 0;
            
            // Check if already converted - but allow re-processing if there are missing editors
            var existingKeteranganEditors = $('.' + this.settings.keteranganEditorClass).length;
            var existingHasilEditors = $('.inline-hasil-editor').length;
            var totalKeteranganTextareas = $('#table-parameter textarea').filter(function() {
                var $this = $(this);
                var id = $this.attr('id') || '';
                var name = $this.attr('name') || '';
                var isKeterangan = (id.indexOf('keterangan_') !== -1 || name.indexOf('keterangan_') !== -1);
                var isNotResultMethod = !$this.hasClass('result_method_klinik') && 
                                       id.indexOf('result_method') === -1 &&
                                       name.indexOf('result_method') === -1;
                return isKeterangan && isNotResultMethod;
            }).length;
            
            // Only skip FULL conversion if hasil + keterangan editors already match textareas
            var totalHasilTextareas = $('textarea.result_method_klinik').length;
            if (existingKeteranganEditors >= totalKeteranganTextareas && existingHasilEditors >= totalHasilTextareas && totalKeteranganTextareas > 0) {
                console.log('Inline editors already exist and count matches, skipping convertToInlineEditing');
                // Tetap pastikan event placeholder/empty terpasang pada editor yang sudah ada
                self.bindExistingKeteranganEditors();
                return;
            }
            
            if (existingKeteranganEditors > 0 && existingKeteranganEditors < totalKeteranganTextareas) {
                console.log('Some keterangan editors missing (' + existingKeteranganEditors + ' < ' + totalKeteranganTextareas + '), continuing with convertToInlineEditing...');
            }
            
            console.log('Starting convertToInlineEditing...');
            var $table = $('#table-parameter');
            if ($table.length === 0) {
                console.warn('Table #table-parameter not found!');
                return;
            }
            
            var $rows = $('#table-parameter tbody tr').not('[class*="group-header"]');
            console.log('Found ' + $rows.length + ' rows to process');

            // Process each parameter row
            $rows.each(function() {
                var $row = $(this);
                
                // Skip header rows
                if ($row.find('th[colspan]').length > 0) {
                    return;
                }

                // Process "Hasil" column - Find textarea first, then locate its parent TD
                // This makes it flexible for different table structures (analis vs baca-hasil)
                var $hasilTextarea = $row.find('textarea.result_method_klinik');
                
                if ($hasilTextarea.length > 0) {
                    // Find the parent TD that contains this textarea
                    var $hasilTd = $hasilTextarea.closest('td');
                    // If not found in closest, try to find by searching all TDs
                    if ($hasilTd.length === 0 || !$hasilTd.is('td')) {
                        $row.find('td').each(function() {
                            if ($(this).find('textarea.result_method_klinik').length > 0) {
                                $hasilTd = $(this);
                                return false; // break
                            }
                        });
                    }
                    
                    if ($hasilTd.length > 0) {
                        self.createHasilInput($hasilTd, $hasilTextarea);
                        processedCount++;
                    } else {
                        skippedCount++;
                        console.warn('Hasil TD not found for textarea:', $hasilTextarea.attr('id'));
                    }
                } else {
                    skippedCount++;
                }

                // Process "Keterangan" column - Find textarea first, then locate its parent TD
                // Look for textarea with id starting with "keterangan_" but not "result_method_klinik"
                // IMPORTANT: Include hidden textareas (display:none) as they might be in the DOM but hidden
                var $keteranganTextarea = $row.find('textarea').filter(function() {
                    var $this = $(this);
                    var id = $this.attr('id') || '';
                    var name = $this.attr('name') || '';
                    // Match textarea with keterangan in ID or name, but exclude result_method_klinik
                    var isKeterangan = (id.indexOf('keterangan_') !== -1 || name.indexOf('keterangan_') !== -1);
                    var isNotResultMethod = !$this.hasClass('result_method_klinik') && 
                                           id.indexOf('result_method') === -1 &&
                                           name.indexOf('result_method') === -1;
                    return isKeterangan && isNotResultMethod;
                });
                
                // Also search in all TDs of the row, including hidden ones
                if ($keteranganTextarea.length === 0) {
                    $row.find('td').each(function() {
                        var $td = $(this);
                        // Search for textarea in this TD, including hidden ones
                        var $tdTextarea = $td.find('textarea').filter(function() {
                            var $this = $(this);
                            var id = $this.attr('id') || '';
                            var name = $this.attr('name') || '';
                            var isKeterangan = (id.indexOf('keterangan_') !== -1 || name.indexOf('keterangan_') !== -1);
                            var isNotResultMethod = !$this.hasClass('result_method_klinik') && 
                                                   id.indexOf('result_method') === -1 &&
                                                   name.indexOf('result_method') === -1;
                            return isKeterangan && isNotResultMethod;
                        });
                        if ($tdTextarea.length > 0) {
                            $keteranganTextarea = $tdTextarea.first();
                            console.log('Found hidden keterangan textarea in TD:', $tdTextarea.first().attr('id'));
                            return false; // break
                        }
                    });
                }
                
                // If not found in row, try searching in all TDs of the row
                if ($keteranganTextarea.length === 0) {
                    $row.find('td').each(function() {
                        var $td = $(this);
                        var $textarea = $td.find('textarea').filter(function() {
                            var $this = $(this);
                            var id = $this.attr('id') || '';
                            var name = $this.attr('name') || '';
                            var isKeterangan = (id.indexOf('keterangan_') !== -1 || name.indexOf('keterangan_') !== -1);
                            var isNotResultMethod = !$this.hasClass('result_method_klinik') && 
                                                   id.indexOf('result_method') === -1 &&
                                                   name.indexOf('result_method') === -1;
                            return isKeterangan && isNotResultMethod;
                        });
                        if ($textarea.length > 0) {
                            $keteranganTextarea = $textarea.first();
                            return false; // break
                        }
                    });
                }
                
                if ($keteranganTextarea.length > 0) {
                    // Find the parent TD that contains this textarea
                    var $keteranganTd = $keteranganTextarea.closest('td');
                    // If not found in closest, try to find by searching all TDs
                    if ($keteranganTd.length === 0 || !$keteranganTd.is('td')) {
                        $row.find('td').each(function() {
                            var $td = $(this);
                            var $foundTextarea = $td.find('textarea').filter(function() {
                                var $this = $(this);
                                var id = $this.attr('id') || '';
                                var name = $this.attr('name') || '';
                                var isKeterangan = (id.indexOf('keterangan_') !== -1 || name.indexOf('keterangan_') !== -1);
                                var isNotResultMethod = !$this.hasClass('result_method_klinik') && 
                                                       id.indexOf('result_method') === -1 &&
                                                       name.indexOf('result_method') === -1;
                                return isKeterangan && isNotResultMethod;
                            });
                            if ($foundTextarea.length > 0 && $foundTextarea.attr('id') === $keteranganTextarea.attr('id')) {
                                $keteranganTd = $td;
                                return false; // break
                            }
                        });
                    }
                    
                    if ($keteranganTd.length > 0 && $keteranganTd.is('td')) {
                        console.log('Found keterangan textarea:', $keteranganTextarea.attr('id'), 'in TD:', $keteranganTd.length);
                        self.createKeteranganEditor($keteranganTd, $keteranganTextarea);
                    } else {
                        console.warn('Keterangan TD not found for textarea:', $keteranganTextarea.attr('id'), 'Row:', $row.index());
                        // Try to create editor anyway by finding any TD that might contain keterangan display
                        $row.find('td').each(function() {
                            var $td = $(this);
                            // Check if this TD has keterangan-display or might be the keterangan column
                            if ($td.find('.keterangan-display').length > 0 || 
                                $td.text().indexOf('keterangan') !== -1 ||
                                $td.find('textarea[id*="keterangan"]').length > 0) {
                                console.log('Attempting to create keterangan editor in TD with keterangan-display or textarea');
                                self.createKeteranganEditor($td, $keteranganTextarea);
                                return false; // break
                            }
                        });
                    }
                } else {
                    // Log when no keterangan textarea is found for debugging
                    var rowIndex = $row.index();
                    var hasKeteranganDisplay = $row.find('.keterangan-display').length > 0;
                    if (hasKeteranganDisplay) {
                        console.warn('Row', rowIndex, 'has keterangan-display but no textarea found');
                        // Try to find textarea in the entire page that might match this row
                        var $keteranganDisplay = $row.find('.keterangan-display').first();
                        if ($keteranganDisplay.length > 0) {
                            var displayId = $keteranganDisplay.attr('id') || '';
                            var $keteranganTd = $keteranganDisplay.closest('td');
                            
                            // Try multiple strategies to find matching textarea
                            var $foundTextarea = null;
                            
                            // Strategy 1: Extract UUID or ID from display ID
                            // Pattern: keterangan_display_param_UUID or keterangan_display_sub_UUID
                            var uuidMatch = displayId.match(/keterangan_display_(?:param|sub)_(.+)$/);
                            if (uuidMatch && uuidMatch[1]) {
                                var extractedId = uuidMatch[1];
                                // Try to find textarea with this ID in the name or id attribute
                                $foundTextarea = $('textarea[id*="' + extractedId + '"], textarea[name*="' + extractedId + '"]');
                                if ($foundTextarea.length === 0) {
                                    // Try with full pattern
                                    $foundTextarea = $('textarea[id*="keterangan_param_' + extractedId + '"], textarea[name*="keterangan_param_' + extractedId + '"]');
                                }
                                if ($foundTextarea.length === 0) {
                                    $foundTextarea = $('textarea[id*="keterangan_sub_' + extractedId + '"], textarea[name*="keterangan_sub_' + extractedId + '"]');
                                }
                            }
                            
                            // Strategy 2: Extract numeric index (for backward compatibility)
                            if (!$foundTextarea || $foundTextarea.length === 0) {
                                var match = displayId.match(/keterangan_display_(?:param|sub)_(\d+)/);
                                if (match && match[1]) {
                                    var extractedIndex = match[1];
                                    $foundTextarea = $('textarea[id*="keterangan_' + extractedIndex + '"], textarea[name*="keterangan_' + extractedIndex + '"]');
                                    if ($foundTextarea.length === 0) {
                                        $foundTextarea = $('textarea[id*="keterangan_param_' + extractedIndex + '"], textarea[name*="keterangan_param_' + extractedIndex + '"]');
                                    }
                                    if ($foundTextarea.length === 0) {
                                        $foundTextarea = $('textarea[id*="keterangan_sub_' + extractedIndex + '"], textarea[name*="keterangan_sub_' + extractedIndex + '"]');
                                    }
                                }
                            }
                            
                            // Strategy 3: Find any textarea in the same TD or row
                            if (!$foundTextarea || $foundTextarea.length === 0) {
                                $foundTextarea = $keteranganTd.find('textarea[id*="keterangan"], textarea[name*="keterangan"]');
                                if ($foundTextarea.length === 0) {
                                    $foundTextarea = $row.find('textarea[id*="keterangan"], textarea[name*="keterangan"]');
                                }
                            }
                            
                            if ($foundTextarea && $foundTextarea.length > 0 && $keteranganTd.length > 0) {
                                console.log('Found textarea and TD for keterangan display:', displayId, 'Textarea ID:', $foundTextarea.first().attr('id'));
                                self.createKeteranganEditor($keteranganTd, $foundTextarea.first());
                            } else {
                                console.warn('Could not find matching textarea for keterangan display:', displayId);
                                // Last resort: create editor in TD with keterangan-display even without textarea
                                // We'll create a dummy textarea or use the display element itself
                                if ($keteranganTd.length > 0) {
                                    // Try to find any textarea in the row that might be the keterangan textarea
                                    var $anyKeteranganTextarea = $row.find('textarea').filter(function() {
                                        var $this = $(this);
                                        var id = $this.attr('id') || '';
                                        var name = $this.attr('name') || '';
                                        return (id.indexOf('keterangan') !== -1 || name.indexOf('keterangan') !== -1) &&
                                               id.indexOf('result_method') === -1;
                                    });
                                    
                                    if ($anyKeteranganTextarea.length > 0) {
                                        console.log('Found alternative keterangan textarea in row:', $anyKeteranganTextarea.first().attr('id'));
                                        self.createKeteranganEditor($keteranganTd, $anyKeteranganTextarea.first());
                                    } else {
                                        // Create a temporary textarea element for the editor
                                        var tempId = 'keterangan_temp_' + Date.now();
                                        var $tempTextarea = $('<textarea>').attr({
                                            'id': tempId,
                                            'name': tempId,
                                            'style': 'display: none;'
                                        });
                                        $keteranganTd.append($tempTextarea);
                                        console.log('Created temporary textarea for keterangan editor:', tempId);
                                        self.createKeteranganEditor($keteranganTd, $tempTextarea);
                                    }
                                }
                            }
                        } else {
                            // No keterangan-display found, but try to find textarea anyway
                            var $anyKeteranganTextarea = $row.find('textarea').filter(function() {
                                var $this = $(this);
                                var id = $this.attr('id') || '';
                                var name = $this.attr('name') || '';
                                return (id.indexOf('keterangan') !== -1 || name.indexOf('keterangan') !== -1) &&
                                       id.indexOf('result_method') === -1;
                            });
                            
                            if ($anyKeteranganTextarea.length > 0) {
                                // Find TD that might contain keterangan column (usually last TD or TD with specific class)
                                $row.find('td').each(function() {
                                    var $td = $(this);
                                    // Check if this looks like a keterangan column (empty or has placeholder text)
                                    if ($td.find('textarea[id*="keterangan"]').length > 0 || 
                                        $td.find('.keterangan-display').length > 0 ||
                                        ($td.text().trim() === '' || $td.text().trim() === '-')) {
                                        console.log('Found potential keterangan TD in row:', rowIndex);
                                        self.createKeteranganEditor($td, $anyKeteranganTextarea.first());
                                        return false; // break
                                    }
                                });
                            } else {
                                // Last resort: search entire page for textarea that might match this row
                                // Try to find by row index or by looking for textarea with similar ID pattern
                                console.warn('No keterangan textarea found in row', rowIndex, '- searching entire page');
                                
                                // Try to find TD in this row that might be the keterangan column (usually one of the last TDs)
                                var $tds = $row.find('td');
                                if ($tds.length > 0) {
                                    // Try the last TD first (keterangan is usually the last column)
                                    var $lastTd = $tds.last();
                                    if ($lastTd.length > 0 && !$lastTd.find('.' + self.settings.keteranganEditorClass).length) {
                                        // Check if this TD has a hidden textarea
                                        var $hiddenTextarea = $lastTd.find('textarea[style*="display: none"], textarea[style*="display:none"], textarea').filter(function() {
                                            var $this = $(this);
                                            var id = $this.attr('id') || '';
                                            var name = $this.attr('name') || '';
                                            return (id.indexOf('keterangan') !== -1 || name.indexOf('keterangan') !== -1) &&
                                                   id.indexOf('result_method') === -1;
                                        });
                                        
                                        if ($hiddenTextarea.length > 0) {
                                            console.log('Found hidden keterangan textarea in last TD of row:', rowIndex, 'ID:', $hiddenTextarea.first().attr('id'));
                                            self.createKeteranganEditor($lastTd, $hiddenTextarea.first());
                                        } else {
                                            // Search all TDs in row for any textarea with keterangan
                                            $row.find('td').each(function() {
                                                var $td = $(this);
                                                var $tdTextarea = $td.find('textarea').filter(function() {
                                                    var $this = $(this);
                                                    var id = $this.attr('id') || '';
                                                    var name = $this.attr('name') || '';
                                                    return (id.indexOf('keterangan') !== -1 || name.indexOf('keterangan') !== -1) &&
                                                           id.indexOf('result_method') === -1;
                                                });
                                                if ($tdTextarea.length > 0) {
                                                    console.log('Found keterangan textarea in TD of row:', rowIndex, 'ID:', $tdTextarea.first().attr('id'));
                                                    self.createKeteranganEditor($td, $tdTextarea.first());
                                                    return false; // break
                                                }
                                            });
                                            
                                            // If still not found, try to find by method_id from hasil textarea
                                            var $hasilTextarea = $row.find('textarea.result_method_klinik');
                                            if ($hasilTextarea.length > 0) {
                                                var methodId = $hasilTextarea.data('method-id') || $hasilTextarea.attr('data-method-id');
                                                if (methodId) {
                                                    var $matchingTextarea = $('textarea[id*="keterangan_' + methodId + '"], textarea[name*="keterangan_' + methodId + '"]');
                                                    if ($matchingTextarea.length > 0) {
                                                        console.log('Found keterangan textarea by method_id:', methodId);
                                                        self.createKeteranganEditor($lastTd, $matchingTextarea.first());
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
            
            // Count all keterangan textareas found in the page
            var self = this;
            var totalKeteranganTextareas = $('textarea[id*="keterangan"], textarea[name*="keterangan"]').filter(function() {
                var $this = $(this);
                var id = $this.attr('id') || '';
                var name = $this.attr('name') || '';
                return id.indexOf('result_method') === -1 && name.indexOf('result_method') === -1;
            }).length;
            
            var createdKeteranganEditors = $('.' + this.settings.keteranganEditorClass).length;
            
            // Final pass: Find ALL textarea keterangan in the entire page and ensure editors are created
            // This catches any textareas that might have been missed in the row-by-row processing
            console.log('Final pass: Searching for all keterangan textareas in the page...');
            var $allKeteranganTextareas = $('textarea').filter(function() {
                var $this = $(this);
                var id = $this.attr('id') || '';
                var name = $this.attr('name') || '';
                var isKeterangan = (id.indexOf('keterangan_') !== -1 || name.indexOf('keterangan_') !== -1);
                var isNotResultMethod = !$this.hasClass('result_method_klinik') && 
                                       id.indexOf('result_method') === -1 &&
                                       name.indexOf('result_method') === -1;
                return isKeterangan && isNotResultMethod;
            });
            
            console.log('Found ' + $allKeteranganTextareas.length + ' total keterangan textareas in page');
            
            var createdCount = 0;
            $allKeteranganTextareas.each(function() {
                var $textarea = $(this);
                var textareaId = $textarea.attr('id') || '';
                
                // Check if editor already exists for this textarea
                var index = '';
                if (textareaId) {
                    var match = textareaId.match(/keterangan_(?:param|sub)_(.+)$/);
                    if (match && match[1]) {
                        index = match[1];
                    } else {
                        match = textareaId.match(/keterangan_([a-f0-9\-]+|\d+)/i);
                        if (match && match[1]) {
                            index = match[1];
                        }
                    }
                }
                
                if (index) {
                    var editorId = 'keterangan_editor_' + index;
                    var $existingEditor = $('#' + editorId);
                    
                    // If editor doesn't exist, create it
                    if ($existingEditor.length === 0) {
                        // Find the TD that contains this textarea
                        var $td = $textarea.closest('td');
                        
                        // If not found in closest, try to find by searching in the row
                        if ($td.length === 0 || !$td.is('td')) {
                            var $row = $textarea.closest('tr');
                            if ($row.length > 0) {
                                // Try to find TD that might be the keterangan column (usually last TD or empty TD)
                                $row.find('td').each(function() {
                                    var $candidateTd = $(this);
                                    // Check if this TD contains the textarea or looks like keterangan column
                                    // IMPORTANT: Use find with exact ID match, including hidden textareas
                                    if ($candidateTd.find('textarea[id="' + textareaId + '"]').length > 0 ||
                                        $candidateTd.find('textarea').filter(function() {
                                            return $(this).attr('id') === textareaId;
                                        }).length > 0 ||
                                        $candidateTd.find('.keterangan-display').length > 0 ||
                                        ($candidateTd.text().trim() === '' || $candidateTd.text().trim() === '-')) {
                                        $td = $candidateTd;
                                        return false; // break
                                    }
                                });
                                
                                // If still not found, try searching all TDs in the row for any textarea with matching ID
                                if ($td.length === 0 || !$td.is('td')) {
                                    $row.find('td').each(function() {
                                        var $candidateTd = $(this);
                                        // Search for textarea with matching ID in this TD (including hidden ones)
                                        var $foundTextarea = $candidateTd.find('textarea').filter(function() {
                                            return $(this).attr('id') === textareaId;
                                        });
                                        if ($foundTextarea.length > 0) {
                                            $td = $candidateTd;
                                            return false; // break
                                        }
                                    });
                                }
                            }
                        }
                        
                        // If TD found, create editor
                        if ($td.length > 0 && $td.is('td')) {
                            console.log('Creating missing keterangan editor for textarea:', textareaId, 'in TD (display:', $textarea.css('display'), ')');
                            self.createKeteranganEditor($td, $textarea);
                            createdCount++;
                        } else {
                            console.warn('Could not find TD for keterangan textarea:', textareaId, 'Searching for fallback TD...');
                            // Last resort: find any TD in the row that might be the keterangan column
                            var $row = $textarea.closest('tr');
                            if ($row.length > 0) {
                                // Try the last TD in the row (keterangan is usually last)
                                var $lastTd = $row.find('td').last();
                                if ($lastTd.length > 0) {
                                    console.log('Using last TD as fallback for keterangan editor:', textareaId);
                                    self.createKeteranganEditor($lastTd, $textarea);
                                    createdCount++;
                                } else {
                                    console.error('Could not find any TD for keterangan textarea:', textareaId);
                                }
                            } else {
                                console.error('Could not find row for keterangan textarea:', textareaId);
                            }
                        }
                    } else {
                        // Editor exists, but make sure it's visible
                        if (!$existingEditor.is(':visible') || $existingEditor.css('display') === 'none') {
                            console.warn('Keterangan editor exists but not visible, forcing visibility:', editorId);
                            $existingEditor.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'opacity': '1'
                            }).show();
                            var styleAttr = $existingEditor.attr('style') || '';
                            styleAttr = styleAttr.replace(/display\s*:\s*none[^;]*;?/gi, '');
                            $existingEditor.attr('style', styleAttr + '; display: block !important; visibility: visible !important;');
                        }
                    }
                }
            });
            
            if (createdCount > 0) {
                console.log('Created ' + createdCount + ' additional keterangan editors in final pass');
                // IMPORTANT: Re-initialize TinyMCE for newly created editors
                // Wait a bit to ensure DOM is updated and editor elements are fully rendered
                setTimeout(function() {
                    console.log('Re-initializing TinyMCE for newly created keterangan editors...');
                    // Check if TinyMCE is ready
                    if (typeof tinymce !== 'undefined' && typeof tinymce.init === 'function') {
                        // Find all uninitialized keterangan editors
                        var $newKeteranganEditors = $('.' + self.settings.keteranganEditorClass).filter(function() {
                            var $editor = $(this);
                            var editorId = $editor.attr('id');
                            if (!editorId) return false;
                            // Check if TinyMCE instance already exists
                            return !tinymce.get(editorId);
                        });
                        
                        if ($newKeteranganEditors.length > 0) {
                            console.log('Found ' + $newKeteranganEditors.length + ' uninitialized keterangan editors, initializing TinyMCE...');
                            // Create selector for uninitialized editors
                            var uninitializedIds = $newKeteranganEditors.map(function() {
                                return '#' + $(this).attr('id');
                            }).get().join(',');
                            
                            if (uninitializedIds) {
                                try {
                                    tinymce.init({
                                        selector: uninitializedIds,
                                        inline: true,
                                        menubar: false,
                                        theme: 'modern',
                                        content_css: false,
                                        document_base_url: window.location.origin,
                                        plugins: [
                                            'lists charmap',
                                            'searchreplace',
                                            'paste'
                                        ],
                                        toolbar: 'bold italic underline | superscript subscript | charmap | ' +
                                            'bullist numlist | removeformat',
                                        toolbar_mode: 'floating',
                                        toolbar_location: 'auto',
                                        paste_as_text: true,
                                        content_style: 'body { font-size: 13px; font-family: Arial, sans-serif; }',
                                        charmap_append: [
                                            [0x00B1, 'plus-minus sign'],
                                            [0x00B2, 'superscript two'],
                                            [0x00B3, 'superscript three'],
                                            [0x00B5, 'micro sign'],
                                            [0x2264, 'less-than or equal to'],
                                            [0x2265, 'greater-than or equal to'],
                                            [0x2248, 'almost equal to'],
                                            [0x2260, 'not equal to'],
                                            [0x00B0, 'degree sign'],
                                            [0x2103, 'degree celsius'],
                                            [0x00D7, 'multiplication sign'],
                                            [0x00F7, 'division sign'],
                                            [0x03B1, 'greek small letter alpha'],
                                            [0x03B2, 'greek small letter beta'],
                                            [0x03B3, 'greek small letter gamma'],
                                            [0x03BC, 'greek small letter mu']
                                        ],
                                        setup: function(editor) {
                                            editor.on('init', function() {
                                                console.log('TinyMCE initialized for keterangan editor:', editor.id);
                                            });
                                        }
                                    });
                                    console.log('TinyMCE initialization triggered for:', uninitializedIds);
                                } catch(e) {
                                    console.error('Error initializing TinyMCE for new keterangan editors:', e);
                                }
                            }
                        } else {
                            console.log('All keterangan editors already initialized by TinyMCE');
                        }
                    } else {
                        console.warn('TinyMCE not ready, cannot initialize new keterangan editors');
                    }
                }, 500); // Increased delay to ensure DOM is fully updated
            }
            
            console.log('convertToInlineEditing completed. Processed: ' + processedCount + ', Skipped: ' + skippedCount + ', Final pass created: ' + createdCount);
            console.log('Created ' + $('.inline-hasil-editor').length + ' hasil editors and ' + createdKeteranganEditors + ' keterangan editors');
            console.log('Total keterangan textareas found in page: ' + totalKeteranganTextareas);
            self.bindExistingKeteranganEditors();
            
            // If there's a mismatch, log details
            if (totalKeteranganTextareas > createdKeteranganEditors) {
                console.warn('Mismatch detected: Found ' + totalKeteranganTextareas + ' keterangan textareas but only created ' + createdKeteranganEditors + ' editors');
                // List all keterangan textareas
                $('textarea[id*="keterangan"], textarea[name*="keterangan"]').filter(function() {
                    var $this = $(this);
                    var id = $this.attr('id') || '';
                    var name = $this.attr('name') || '';
                    return id.indexOf('result_method') === -1 && name.indexOf('result_method') === -1;
                }).each(function() {
                    var $this = $(this);
                    var id = $this.attr('id') || '';
                    var name = $this.attr('name') || '';
                    var hasEditor = $this.closest('td').find('.' + self.settings.keteranganEditorClass).length > 0;
                    if (!hasEditor) {
                        console.warn('Missing editor for textarea:', id || name, 'in row:', $this.closest('tr').index());
                    }
                });
            }

            // Reorder columns - DISABLED to prevent column scrambling
            // this.reorderColumns();

            // Setup event delegation for dropdowns (backup method)
            this.setupDropdownEventDelegation();

            // Run initial validation after DOM is ready and TinyMCE is initialized
            // Wait longer to ensure TinyMCE editors are fully initialized
            setTimeout(function() {
                self.runInitialValidation();
            }, 1000);
        },

        /**
         * Setup event delegation for dropdown changes (backup method)
         */
        setupDropdownEventDelegation: function() {
            var self = this;
            console.log('Setting up dropdown event delegation...');
            
            // Use event delegation on document for dropdowns
            // Note: This is a fallback handler. The main handler is bound directly on the select element
            // to prevent double handling, we check if the event was already handled
            $(document).on('change', 'select.' + this.settings.hasilInputClass, function(e) {
                // Skip if this event was already handled by the direct handler
                if ($(this).data('change-handled')) {
                    $(this).removeData('change-handled');
                    return;
                }
                
                console.log('=== DROPDOWN CHANGE (Event Delegation) ===');
                var $dropdown = $(this);
                var newValue = $dropdown.val(); // This is the original value from dropdown option
                
                // Store options before any operation
                var optionsHtml = $dropdown.html();
                var selectedValue = newValue;
                
                // Normalize value for comparison - but keep original for display
                var normalizedNewValue = normalizeForComparison(newValue);
                var index = $dropdown.data('index');
                var paramId = $dropdown.data('param-id');
                var min = $dropdown.data('min') || '';
                var max = $dropdown.data('max') || '';
                var equal = decodeHtmlEntities($dropdown.data('equal') || '');
                var numberFormat = $dropdown.data('number-format') || 'en';
                
                console.log('Dropdown changed:', {
                    index: index,
                    value: newValue,
                    valueNormalized: normalizedNewValue,
                    paramId: paramId,
                    min: min,
                    max: max,
                    equal: equal
                });
                
                // Update hidden textarea with original value (for form submission)
                // NOTE: do NOT use normalizedNewValue here - it removes spaces and breaks re-selection on reload
                var $textarea = $dropdown.closest('td').find('textarea.result_method_klinik');
                if ($textarea.length > 0) {
                    console.log('Updating textarea:', $textarea.attr('id'));
                    $textarea.val(newValue);
                } else {
                    console.warn('Textarea not found for dropdown index:', index);
                }
                
                // Update badge with original value (will be normalized inside function for comparison)
                self.updateResultBadgeForOption(index, newValue, min, max, equal, numberFormat);
                
                // Safety check: restore options if they were cleared
                setTimeout(function() {
                    if ($dropdown.length > 0 && $dropdown.find('option').length === 0 && optionsHtml) {
                        console.warn('Dropdown options cleared in delegation handler! Restoring...');
                        $dropdown.html(optionsHtml);
                        if (selectedValue) {
                            $dropdown.val(selectedValue);
                        }
                    }
                }, 50);
            });
        },

        /**
         * Create inline input for "Hasil"
         */
        createHasilInput: function($td, $textarea) {
            // ALWAYS get value from textarea (most reliable source)
            var currentValue = $textarea.val() || '';
            // Clean and normalize: remove whitespace, check for empty/dash
            if (currentValue) {
                currentValue = currentValue.trim();
                if (currentValue === '-' || currentValue === '') {
                    currentValue = '';
                }
            }
            
            var id = $textarea.attr('id');
            // Prioritaskan data-index jika ada, jika tidak gunakan dari ID
            var index = $textarea.data('index') || (id ? id.match(/\d+/)[0] : '');
            var min = $textarea.data('min');
            var max = $textarea.data('max');
            var equal = decodeHtmlEntities($textarea.data('equal') || '');
            var numberFormat = $textarea.data('number-format') || 'en';
            var nilaiBakuMutuPlain = $textarea.attr('data-nilai-baku-mutu') || '';
            
            // Debug: log if we're creating editor with unexpected value
            if (currentValue && currentValue.toLowerCase().includes('tidakberbau') && !equal || (equal && equal.toLowerCase() !== 'tidakberbau')) {
                console.warn('Creating editor with unexpected value:', {
                    textareaId: id,
                    index: index,
                    currentValue: currentValue,
                    equal: equal,
                    min: min,
                    max: max
                });
            }


            // Check if this is a dropdown (option-based) - READ FROM TEXTAREA DATA ATTRIBUTES
            var isOption = $textarea.data('is-option') == 1 || $textarea.data('is-option') == '1';
            var isUrinalisaDual = $textarea.data('urinalisa-dual') == 1 || $textarea.data('urinalisa-dual') == '1'
                || $td.find('.urinalisa-dual-input').length > 0;
            var optionValues = [];
            
            if (isOption) {
                try {
                    var optionData = $textarea.data('option-values');
                    if (typeof optionData === 'string') {
                        optionValues = JSON.parse(optionData);
                    } else if (Array.isArray(optionData)) {
                        optionValues = optionData;
                    }
                } catch(e) {
                    console.error('Error parsing option values:', e);
                    optionValues = [];
                }
            }

            console.log('Creating hasil input - Index:', index, 'isOption:', isOption, 'isUrinalisaDual:', isUrinalisaDual, 'optionValues:', optionValues);

            // Urinalisa dual (Silinder/Kristal) tidak memakai .hasil-input-container —
            // tombol dipasang di .urinalisa-dual-actions. Guard khusus agar Enter/navigasi
            // tidak memanggil ulang createHasilInput dan menggandakan tombol Baku Mutu.
            if (isUrinalisaDual) {
                var $existingDualActions = $td.find('.urinalisa-dual-actions .hasil-action-buttons, .urinalisa-badge-buttons-row, .urinalisa-dual-actions .badge-buttons-row');
                if ($existingDualActions.length > 0) {
                    console.log('Urinalisa dual action buttons already exist for index ' + index + ', skipping creation');
                    return;
                }
            }

            // Check if hasil-input-container already exists - prevent double creation
            var $existingContainer = $td.find('.hasil-input-container');
            if ($existingContainer.length > 0) {
                console.log('hasil-input-container already exists for index ' + index + ', skipping creation');
                return;
            }

            // Clear existing display
            $td.find('.result-display, .result-output').remove();

            var $inputContainer = $('<div class="hasil-input-container"></div>');

            if (isOption && optionValues.length > 0 && !isUrinalisaDual) {
                console.log('Creating dropdown for options:', optionValues);
                
                // Create dropdown for option-based parameters
                var $select = $('<select>').addClass('form-control ' + this.settings.hasilInputClass)
                    .attr({
                        'data-index': index,
                        'data-param-id': $textarea.attr('name'),
                        'data-min': min,
                        'data-max': max,
                        'data-equal': equal,
                        'data-number-format': numberFormat,
                        'data-nilai-baku-mutu': nilaiBakuMutuPlain
                    })
                    .css({
                        'width': '100%',
                        'padding': '8px 12px',
                        'border': '2px solid #e9ecef',
                        'border-radius': '6px',
                        'font-size': '14px',
                        'background': 'white',
                        'cursor': 'pointer'
                    });

                $select.append('<option value="">- Pilih -</option>');
                var matchedOptionVal = null; // track the actual option value that matched
                var normalizedCurrentValue = normalizeComparisonOperatorDisplay(currentValue);
                $.each(optionValues, function(i, opt) {
                    var displayOpt = normalizeComparisonOperatorDisplay(opt);
                    var $option = $('<option>').val(displayOpt).text(displayOpt);
                    // Match with trimmed, case-insensitive and normalized comparison
                    var trimmedOpt = displayOpt.trim().toLowerCase();
                    var trimmedCurrent = normalizedCurrentValue.trim().toLowerCase();
                    var normalizedOpt = normalizeForComparison(displayOpt).toLowerCase();
                    var normalizedCurrent = normalizeForComparison(normalizedCurrentValue).toLowerCase();
                    if (displayOpt == normalizedCurrentValue ||
                        trimmedOpt === trimmedCurrent ||
                        (normalizedOpt && normalizedCurrent && normalizedOpt === normalizedCurrent)) {
                        $option.attr('selected', 'selected');
                        matchedOptionVal = displayOpt;
                    }
                    $select.append($option);
                });
                // Store matched value on $select so we can apply it after DOM insertion
                if (matchedOptionVal) {
                    $select.data('pending-selection', matchedOptionVal);
                }

                // Add badge container
                var $badgeDiv = $('<div>').addClass(this.settings.badgeContainerClass)
                    .attr('id', 'badge_' + index)
                    .css({
                        'min-height': '20px',
                        'flex': '1', // Allow badge to take available space
                        'display': 'flex',
                        'align-items': 'center'
                    });
                
                // Create a container for badge and action buttons (horizontal layout)
                var $badgeAndButtonsRow = $('<div class="badge-buttons-row">').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'margin-top': '8px',
                    'flex-wrap': 'wrap',
                    'width': '100%'
                });
                
                // Append select to container first
                $inputContainer.append($select);
                
                // Append badge to the row
                $badgeAndButtonsRow.append($badgeDiv);
                
                // Move action buttons (history and repeat) from Aksi column to container
                // But append to the row instead of directly to inputContainer
                this.moveActionButtonsToContainer($td, $badgeAndButtonsRow, index);
                
                // Append the row to inputContainer
                $inputContainer.append($badgeAndButtonsRow);

                // Show initial badge if has value (for dropdown/option-based)
                if (currentValue) {
                    // Use original value from textarea (may already contain HTML)
                    var htmlValue = $textarea.val() || currentValue;
                    // If value doesn't contain HTML tags but contains ^( notation, convert it
                    if (htmlValue && !htmlValue.includes('<sup') && !htmlValue.includes('<sub') && 
                        (htmlValue.includes('^(') || htmlValue.includes('^'))) {
                        htmlValue = this.convertSuperscriptToHtml(htmlValue);
                    }
                    // Update badge with HTML value to preserve formatting
                    this.updateResultBadgeForOption(index, htmlValue, min, max, equal, numberFormat);
                }

                // Store data for later validation after DOM ready
                // Normalize currentValue before storing
                var normalizedCurrentValue = normalizeForComparison(currentValue);
                $select.data('initialValidation', {
                    index: index,
                    currentValue: normalizedCurrentValue,
                    originalCurrentValue: currentValue,
                    min: min,
                    max: max,
                    equal: equal,
                    numberFormat: numberFormat
                });

                // Bind change event for dropdown AFTER appending to container
                var self = this;
                
                // CRITICAL: Prevent any click/mousedown events from causing form submit
                $select.on('mousedown click', function(e) {
                    // Prevent form submission from click events
                    e.stopPropagation();
                    // Don't prevent default to allow dropdown to open normally
                    console.log('Select clicked, preventing form submit');
                });
                
                $select.on('change', function(e) {
                    // CRITICAL: Prevent form submission from change event
                    // But DON'T prevent default on change event as it will prevent the value from being set
                    e.stopPropagation();
                    
                    // Mark as handled to prevent event delegation handler from processing
                    $(this).data('change-handled', true);
                    
                    console.log('=== DROPDOWN CHANGE EVENT TRIGGERED ===');
                    var newValue = $(this).val();
                    console.log('New value selected:', newValue);
                    console.log('Event target:', e.target);
                    
                    // Store current options HTML before any operations - make a copy
                    var currentOptionsHtml = $select.html();
                    var currentSelectedValue = newValue;
                    
                    // If dropdown is being focused, be extra careful
                    var isFocusing = $select.data('focusing');
                    if (isFocusing) {
                        console.log('Dropdown is being focused, being extra careful with options...');
                    }
                    
                    // Update textarea but don't trigger change to avoid side effects
                    $textarea.val(newValue);
                    
                    // Update badge with full baku mutu validation
                    self.updateResultBadgeForOption(index, newValue, min, max, equal, numberFormat);
                    
                    // Restore options if they were cleared (safety check) - check multiple times
                    var checkCount = 0;
                    var maxChecks = 3;
                    
                    function checkAndRestore() {
                        checkCount++;
                        if ($select.length > 0) {
                            var currentOptionsCount = $select.find('option').length;
                            var currentHtml = $select.html();
                            
                            // If options were cleared, restore them
                            if ((currentOptionsCount === 0 || (currentOptionsHtml.length > 50 && currentHtml.length < currentOptionsHtml.length * 0.3)) && currentOptionsHtml) {
                                console.warn('Dropdown options were cleared! Restoring... (check ' + checkCount + ')', {
                                    expected: { count: currentOptionsHtml.match(/<option/g) ? currentOptionsHtml.match(/<option/g).length : 0, htmlLength: currentOptionsHtml.length },
                                    actual: { count: currentOptionsCount, htmlLength: currentHtml.length }
                                });
                                $select.html(currentOptionsHtml);
                                if (currentSelectedValue) {
                                    $select.val(currentSelectedValue);
                                }
                            }
                            
                            // Continue checking if not last check
                            if (checkCount < maxChecks) {
                                setTimeout(checkAndRestore, 100);
                            } else {
                                // Clear the flag after final check
                                $select.removeData('change-handled');
                            }
                        } else {
                            // Element removed, stop checking
                            $select.removeData('change-handled');
                        }
                    }
                    
                    // Start checking
                    setTimeout(checkAndRestore, 50);
                });
                
                // Handle Enter key untuk dropdown: setelah memilih option, Enter akan pindah ke parameter berikutnya
                // Use capture phase to ensure this handler runs before form submit
                $select.on('keydown', function(e) {
                    // Enter tanpa Shift = pindah ke parameter berikutnya
                    if (e.key === 'Enter' && !e.shiftKey) {
                        // CRITICAL: Prevent form submission and stop all event propagation
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        
                        console.log('Enter key pressed on dropdown, preventing form submit');
                        
                        // Check if dropdown is open (size > 1 means it's open)
                        var isOpen = $select[0].size > 1;
                        
                        if (isOpen) {
                            // If dropdown is open, let browser close it first, then navigate
                        var $selectElement = $(this);
                            var selectedValue = $selectElement.val();
                            
                            // Ensure value is set
                            if (selectedValue) {
                                $selectElement.val(selectedValue);
                                // Trigger change manually since we prevented default
                                $selectElement.trigger('change');
                            }
                            
                            // Wait for dropdown to close and change event to complete
                        setTimeout(function() {
                                // Store options before navigation
                                var optionsHtml = $selectElement.html();
                                var value = $selectElement.val();
                                
                                // Navigate to next
                            self.navigateToNextHasil($selectElement);
                                
                                // Safety check: restore options if they were cleared during navigation
                                setTimeout(function() {
                                    if ($selectElement.length > 0 && $selectElement.find('option').length === 0 && optionsHtml) {
                                        console.warn('Dropdown options cleared during navigation! Restoring...');
                                        $selectElement.html(optionsHtml);
                                        if (value) {
                                            $selectElement.val(value);
                                        }
                                    }
                                }, 100);
                            }, 100);
                        } else {
                            // Dropdown is closed, just navigate
                            var $selectElement = $(this);
                            var optionsHtml = $selectElement.html();
                            var value = $selectElement.val();
                            
                            self.navigateToNextHasil($selectElement);
                            
                            // Safety check: restore options if they were cleared
                            setTimeout(function() {
                                if ($selectElement.length > 0 && $selectElement.find('option').length === 0 && optionsHtml) {
                                    console.warn('Dropdown options cleared during navigation! Restoring...');
                                    $selectElement.html(optionsHtml);
                                    if (value) {
                                        $selectElement.val(value);
                                    }
                                }
                            }, 100);
                        }
                        
                        // Return false to ensure event is completely stopped
                        return false;
                    }
                    // Shift+Enter tidak berlaku untuk dropdown (dropdown tidak bisa new line)
                });
            } else if (isUrinalisaDual) {
                var $dualInput = $td.find('.urinalisa-dual-input');
                var $badgeDiv = $('<div>').addClass(this.settings.badgeContainerClass)
                    .attr('id', 'badge_' + index)
                    .css({
                        'min-height': '20px',
                        'flex': '1',
                        'display': 'flex',
                        'align-items': 'flex-start'
                    });

                var $badgeAndButtonsRow = $('<div class="badge-buttons-row urinalisa-badge-buttons-row">').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'margin-top': '8px',
                    'flex-wrap': 'wrap',
                    'width': '100%'
                });

                $badgeAndButtonsRow.append($badgeDiv);
                this.moveActionButtonsToContainer($td, $badgeAndButtonsRow, index);

                if ($dualInput.length) {
                    var $actionsSlot = $dualInput.find('.urinalisa-dual-actions[data-param-no="' + $dualInput.data('param-no') + '"]');
                    if ($actionsSlot.length) {
                        $actionsSlot.append($badgeAndButtonsRow);
                    } else {
                        $dualInput.append($badgeAndButtonsRow);
                    }
                } else {
                    $inputContainer.append($badgeAndButtonsRow);
                }

                if (currentValue) {
                    var shouldShowBadge = true;

                    if (isUrinalisaDual) {
                        var $dualInput = $td.find('.urinalisa-dual-input');
                        if ($dualInput.length) {
                            var positivity = ($dualInput.find('.urinalisa-positivity-select').val()
                                || $('#urinalisa_positivity_' + $dualInput.data('param-no')).val()
                                || '').trim();
                            var detail = ($dualInput.find('.urinalisa-detail-input').first().val() || '').trim();
                            var hasName = $dualInput.find('.urinalisa-names .urinalisa-name-input').filter(function() {
                                return ($(this).val() || '').trim() !== '';
                            }).length > 0;
                            var hasAnyDetail = $dualInput.find('.urinalisa-names .urinalisa-detail-input').filter(function() {
                                return ($(this).val() || '').trim() !== '';
                            }).length > 0;
                            if (positivity.toLowerCase() !== 'negatif' && !detail && !hasName && !hasAnyDetail) {
                                shouldShowBadge = false;
                            }
                        } else if ((currentValue || '').trim().toLowerCase() === 'positif') {
                            shouldShowBadge = false;
                        }
                    }

                    if (shouldShowBadge) {
                        this.updateResultBadgeForOption(index, currentValue, min, max, equal, numberFormat);
                    }
                }
            } else {
                // Convert currentValue to HTML format if it contains superscript notation
                var convertedValue = currentValue;
                if (currentValue && (currentValue.includes('^(') || currentValue.includes('^'))) {
                    // Convert superscript notation to HTML: ^(1) to <sup>1</sup>
                    convertedValue = this.convertSuperscriptToHtml(currentValue);
                }
                // Textarea menyimpan baris sebagai "\n" (rubahNilaikeForm); di HTML "\n"
                // hanya jadi spasi, jadi harus dijadikan <br> agar baris tidak hilang.
                convertedValue = this.newlinesToBr(convertedValue);
                
                // Create contenteditable div with TinyMCE support for rich text (pangkat, simbol, dll)
                var $editor = $('<div>').addClass('inline-hasil-editor')
                    .attr({
                        'id': 'hasil_editor_' + index,
                        'data-index': index,
                        'data-textarea-id': id,
                        'data-min': min,
                        'data-max': max,
                        'data-equal': equal,
                        'data-number-format': numberFormat,
                        'data-nilai-baku-mutu': nilaiBakuMutuPlain,
                        'contenteditable': 'true'
                    })
                    .html(convertedValue || '')
                    .css({
                        'min-height': '40px',
                        'padding': '8px 12px',
                        'border': '2px solid #e9ecef',
                        'border-radius': '6px',
                        'background': 'white',
                        'cursor': 'text'
                    });

                if (!currentValue) {
                    $editor.attr('data-placeholder', 'Masukkan hasil...');
                    $editor.css('color', '#999');
                }

                $inputContainer.append($editor);

                // Add badge container
                var $badgeDiv = $('<div>').addClass(this.settings.badgeContainerClass)
                    .attr('id', 'badge_' + index)
                    .css({
                        'min-height': '20px',
                        'flex': '1', // Allow badge to take available space
                        'display': 'flex',
                        'align-items': 'center'
                    });
                
                // Create a container for badge and action buttons (horizontal layout)
                var $badgeAndButtonsRow = $('<div class="badge-buttons-row">').css({
                    'display': 'flex',
                    'align-items': 'center',
                    'gap': '8px',
                    'margin-top': '8px',
                    'flex-wrap': 'wrap',
                    'width': '100%'
                });
                
                // Append badge to the row
                $badgeAndButtonsRow.append($badgeDiv);
                
                // Move action buttons (history and repeat) from Aksi column to container
                // But append to the row instead of directly to inputContainer
                this.moveActionButtonsToContainer($td, $badgeAndButtonsRow, index);
                
                // Append the row to inputContainer
                $inputContainer.append($badgeAndButtonsRow);

                // Show initial badge if has value
                // Use HTML content from textarea if available (preserves superscript/subscript)
                if (currentValue) {
                    // Get HTML value from textarea (may already contain <sup> or <sub> tags)
                    var htmlValue = $textarea.val() || currentValue;
                    // If value doesn't contain HTML tags but contains ^( notation, convert it
                    if (htmlValue && !htmlValue.includes('<sup') && !htmlValue.includes('<sub') && 
                        (htmlValue.includes('^(') || htmlValue.includes('^'))) {
                        htmlValue = this.convertSuperscriptToHtml(htmlValue);
                    }
                    htmlValue = this.newlinesToBr(htmlValue);
                    // Update badge with HTML value to preserve formatting
                    this.updateResultBadge(index, htmlValue, min, max, equal, numberFormat);
                }

                // Bind change event
                var self = this;
                $editor.on('blur input', function() {
                    var rawValue = $(this).html();
                    // Convert superscript notation to HTML: ^(1) or ^1 to <sup>1</sup>
                    var htmlValue = self.convertSuperscriptToHtml(rawValue);
                    // Save converted HTML to textarea
                    $textarea.val(htmlValue).trigger('change');
                    // Pass HTML value to updateResultBadge so it can handle superscripts
                    self.updateResultBadge(index, htmlValue, min, max, equal, numberFormat);
                });

                // Fallback tanpa TinyMCE: Enter = pindah parameter,
                // Shift+Enter dibiarkan default (browser menyisipkan baris baru)
                $editor.on('keydown', function(e) {
                    if (e.key !== 'Enter' || e.shiftKey) {
                        return;
                    }

                    e.preventDefault();
                    self.navigateToNextHasil($editor);
                    return false;
                });

                // Handle placeholder
                $editor.on('focus', function() {
                    if ($(this).text() === '') {
                        $(this).css('color', '#000');
                    }
                });

                $editor.on('blur', function() {
                    if ($(this).text() === '') {
                        $(this).css('color', '#999');
                    }
                });
            }

            if ($inputContainer.children().length > 0) {
                $td.append($inputContainer);
            }
            
            // After DOM insertion, apply the pending dropdown selection
            // (browser may reset .val() set on detached elements; must set AFTER insertion)
            var $pendingSelect = $td.find('select.inline-hasil-input[data-pending-selection]');
            if ($pendingSelect.length > 0) {
                var pendingVal = $pendingSelect.data('pending-selection');
                $pendingSelect.val(pendingVal);
                $pendingSelect.removeData('pending-selection');
                console.log('Applied post-DOM-insertion selection:', pendingVal);
            }
        },

        /**
         * Create and add action buttons (history, repeat, and edit) to hasil-input-container
         */
        moveActionButtonsToContainer: function($hasilTd, $inputContainer, index) {
            console.log('moveActionButtonsToContainer called for index:', index);
            console.log('$hasilTd:', $hasilTd.length, 'elements');
            console.log('$inputContainer:', $inputContainer.length, 'elements');
            
            // Find the textarea to get parameter data
            // Try multiple methods: in $hasilTd, in row, or by ID
            var $textarea = $hasilTd.find('textarea.result_method_klinik');
            if ($textarea.length === 0) {
                // Try to find textarea in the row instead
                var $row = $hasilTd.closest('tr');
                $textarea = $row.find('textarea.result_method_klinik');
                if ($textarea.length === 0) {
                    // Try to find by ID pattern (result_method_{index})
                    $textarea = $('textarea#result_method_' + index + ', textarea[id*="result_method_' + index + '"]');
                    if ($textarea.length === 0) {
                        console.error('Textarea.result_method_klinik not found for index:', index);
                        // Still continue to create Baku Mutu button even without textarea
                        // We can get data from $inputContainer or use index
                        console.log('Continuing without textarea - will use index for Baku Mutu button');
                    } else {
                        console.log('Found textarea by ID pattern');
                    }
                } else {
                    console.log('Found textarea in row instead');
                }
            } else {
                console.log('Found textarea in $hasilTd');
            }
            
            if ($textarea.length > 0) {
                console.log('Found textarea:', $textarea.attr('id'));
            }
            
            // Get parameter data from textarea name attribute (if textarea exists)
            var textareaName = '';
            var textareaId = '';
            var isSub = false;
            
            if ($textarea.length > 0) {
                textareaName = $textarea.attr('name') || '';
                textareaId = $textarea.attr('id') || '';
                // Determine if this is sub-parameter or main parameter
                isSub = textareaId.includes('sub_parameter') || textareaId.includes('detail');
            } else {
                // If no textarea, try to determine from index or row structure
                var $row = $hasilTd.closest('tr');
                // Check if row contains detail-related elements
                isSub = $row.find('input[id*="detail"], input[name*="detail"]').length > 0;
            }
            
            var parameterId = null;
            var parameterName = '';
            
            // Extract parameter ID from name or find it in the row
            var $row = $hasilTd.closest('tr');
            
            if (isSub) {
                // For sub-parameter, get ID from hidden input
                var $subParamInput = $row.find('input[id^="parameter_sub_satuan_klinik_id_"]');
                if ($subParamInput.length > 0) {
                    parameterId = $subParamInput.val();
                }
                // Get parameter name from second td (Jenis Parameter column for baca-hasil)
                var $paramTd = $row.find('td').eq(1);
                if ($paramTd.length > 0) {
                    parameterName = $paramTd.clone().children().remove().end().text().trim();
                    if (!parameterName) {
                        parameterName = $paramTd.find('b, strong').first().text().trim();
                    }
                    if (!parameterName) {
                        parameterName = $paramTd.text().trim();
                    }
                }
                // Fallback: get from first td
                if (!parameterName) {
                    parameterName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
                }
            } else {
                // For main parameter, get ID from hidden input
                var $paramInput = $row.find('input[id^="permohonan_uji_parameter_klinik_"]');
                if ($paramInput.length > 0) {
                    parameterId = $paramInput.val();
                }
                // Get parameter name from second td (Jenis Parameter column for baca-hasil)
                var $paramTd = $row.find('td').eq(1);
                if ($paramTd.length > 0) {
                    parameterName = $paramTd.clone().children().remove().end().text().trim();
                    if (!parameterName) {
                        parameterName = $paramTd.find('b, strong').first().text().trim();
                    }
                    if (!parameterName) {
                        parameterName = $paramTd.text().trim();
                    }
                }
                // Fallback: get from first td
                if (!parameterName) {
                    parameterName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
                }
            }
            
            // Check if action buttons already exist - prevent double creation.
            // Cek di container target DAN di seluruh sel Hasil (urinalisa dual
            // memasang tombol di .urinalisa-dual-actions, bukan di $inputContainer baru).
            var $existingActionButtons = $inputContainer.find('.hasil-action-buttons');
            if ($existingActionButtons.length === 0 && $hasilTd && $hasilTd.length) {
                $existingActionButtons = $hasilTd.find('.hasil-action-buttons');
            }
            if ($existingActionButtons.length > 0) {
                console.log('Action buttons already exist for index ' + index + ', skipping creation');
                return;
            }
            
            // Create container for action buttons (no margin-top since it's in the same row as badge)
            var $actionButtonsContainer = $('<div class="hasil-action-buttons"></div>')
                .css({
                    'display': 'flex',
                    'gap': '5px',
                    'justify-content': 'flex-start',
                    'align-items': 'center',
                    'flex-shrink': '0' // Prevent buttons from shrinking
                });
            
            // Helper to attach visual badge on repeat button
            function attachRepeatBadge($btn, historyCount) {
                try {
                    historyCount = parseInt(historyCount || 0);
                    if (!$btn || !$btn.length || !historyCount || historyCount <= 0) return;

                    // Set data attribute (digunakan juga oleh CSS jika perlu)
                    $btn.attr('data-repeat-count', historyCount);

                    // Tambahkan span badge di dalam tombol (lebih eksplisit daripada pseudo-element saja)
                    var $existingBadge = $btn.find('.repeat-count-badge').first();
                    if ($existingBadge.length) {
                        $existingBadge.text(historyCount);
                    } else {
                        $btn.append('<span class="repeat-count-badge">' + historyCount + '</span>');
                    }
                } catch (e) {
                    console.warn('attachRepeatBadge failed:', e);
                }
            }

            // Create Repeat button (only if parameterId exists from klinik format)
            var $repeatBtn = null;
            if (parameterId) {
                $repeatBtn = $('<button type="button" class="btn btn-sm btn-info btn-repeat-parameter"></button>')
                    .attr({
                        'data-parameter-id': parameterId,
                        'title': 'Ulangi Pemeriksaan (Simpan ke History)'
                    })
                    .css({
                        'padding': '4px 8px',
                        'font-size': '12px'
                    })
                    .html('<i class="fa fa-redo"></i>');

                if (isSub) {
                    $repeatBtn.attr('data-is-sub', '1');
                }

                // Tambahkan badge kecil jumlah pengulangan langsung ke tombol (jika ada history)
                try {
                    var $resultOutputDiv = $row.find('[id^="result_output_sub_"], [id^="result_output_param_"]').first();
                    if ($resultOutputDiv && $resultOutputDiv.length) {
                        var historyCount = parseInt($resultOutputDiv.data('history-count') || 0);
                        attachRepeatBadge($repeatBtn, historyCount);
                    }
                } catch (e) {
                    console.warn('Failed to set repeat-count badge on repeat button:', e);
                }
            }
            
            // Create History button (only if parameterId exists from klinik format)
            var $historyBtn = null;
            if (parameterId) {
                $historyBtn = $('<button type="button" class="btn btn-sm btn-secondary btn-view-history"></button>')
                    .attr({
                        'data-parameter-id': parameterId,
                        'data-parameter-name': parameterName,
                        'title': 'Lihat History'
                    })
                    .css({
                        'padding': '4px 8px',
                        'font-size': '12px'
                    })
                    .html('<i class="fa fa-history"></i>');
                
                if (isSub) {
                    $historyBtn.attr('data-is-sub', '1');
                }
            }
            
            // Get current offset_baku_mutu value
            // Try multiple formats: klinik format (offset_baku_mutu_sub_/param_) and baca-hasil format (offset_baku_mutu_{id})
            var currentOffset = 'default';
            var $offsetInput = null;
            
            if (isSub) {
                // Try klinik format first
                $offsetInput = $row.find('input[id^="offset_baku_mutu_sub_"]');
                if ($offsetInput.length === 0) {
                    // Try baca-hasil format (offset_baku_mutu_{detail_id})
                    $offsetInput = $row.find('input[id="offset_baku_mutu_' + index + '"]');
                }
            } else {
                // Try klinik format first
                $offsetInput = $row.find('input[id^="offset_baku_mutu_param_"]');
                if ($offsetInput.length === 0) {
                    // Try baca-hasil format (offset_baku_mutu_{method_id})
                    $offsetInput = $row.find('input[id="offset_baku_mutu_' + index + '"]');
                }
            }
            
            if ($offsetInput.length > 0) {
                currentOffset = $offsetInput.val() || 'default';
            }
            
            // Create Baku Mutu Override button
            var $bakuMutuBtn = $('<button type="button" class="btn btn-sm btn-warning btn-baku-mutu-override"></button>')
                .attr({
                    'data-index': index,
                    'data-is-sub': isSub ? '1' : '0',
                    'data-current-offset': currentOffset,
                    'title': 'Atur Status Baku Mutu'
                })
                .css({
                    'padding': '4px 8px',
                    'font-size': '12px'
                });
            
            // Set button text and icon based on current offset
            if (currentOffset === 'true') {
                $bakuMutuBtn.html('<i class="fa fa-exclamation-triangle"></i> Melewati');
                $bakuMutuBtn.removeClass('btn-warning').addClass('btn-danger');
            } else if (currentOffset === 'false') {
                $bakuMutuBtn.html('<i class="fa fa-check-circle"></i> Normal');
                $bakuMutuBtn.removeClass('btn-warning').addClass('btn-success');
            } else {
                $bakuMutuBtn.html('<i class="fa fa-cog"></i> Baku Mutu');
            }
            
            // Append buttons to container (only append if they exist)
            if ($repeatBtn) {
                $actionButtonsContainer.append($repeatBtn);
            }
            if ($historyBtn) {
                $actionButtonsContainer.append($historyBtn);
            }
            // Always append Baku Mutu button (works for both klinik and baca-hasil)
            $actionButtonsContainer.append($bakuMutuBtn);
            
            // Append action buttons container to hasil-input-container
            $inputContainer.append($actionButtonsContainer);
        },

        /**
         * Create inline TinyMCE editor for "Keterangan"
         */
        /**
         * Apakah konten keterangan dianggap kosong (untuk class .empty / placeholder).
         */
        isKeteranganVisuallyEmpty: function($el) {
            if (!$el || !$el.length) {
                return true;
            }
            var text = ($el.text() || '').replace(/\u00a0/g, ' ').trim();
            if (!text || text === '-') {
                return true;
            }
            var html = ($el.html() || '')
                .replace(/&nbsp;/gi, ' ')
                .replace(/<br\s*\/?>/gi, '')
                .replace(/<p>\s*<\/p>/gi, '')
                .replace(/<p><br[^>]*><\/p>/gi, '')
                .replace(/<p><br data-mce-bogus="1"><\/p>/gi, '')
                .trim();
            return html === '';
        },

        syncKeteranganEmptyClass: function($el) {
            if (!$el || !$el.length) {
                return;
            }
            if (this.isKeteranganVisuallyEmpty($el)) {
                $el.addClass('empty');
            } else {
                $el.removeClass('empty');
            }
        },

        bindKeteranganEmptyHandlers: function($editor, $textarea) {
            var self = this;
            if (!$editor || !$editor.length) {
                return;
            }
            $editor.off('.keteranganEmpty');

            $editor.on('focus.keteranganEmpty', function() {
                var $this = $(this);
                if (self.isKeteranganVisuallyEmpty($this)) {
                    $this.addClass('empty');
                }
            });

            $editor.on('input.keteranganEmpty keyup.keteranganEmpty paste.keteranganEmpty', function() {
                self.syncKeteranganEmptyClass($(this));
            });

            $editor.on('blur.keteranganEmpty', function() {
                var $this = $(this);
                var newValue = $this.html();
                var editorId = $this.attr('id');
                if (editorId && typeof tinymce !== 'undefined') {
                    try {
                        var editor = tinymce.get(editorId);
                        if (editor && typeof editor.getContent === 'function' && !editor.removed) {
                            newValue = editor.getContent();
                        }
                    } catch (e) { /* ignore */ }
                }
                if ($textarea && $textarea.length) {
                    $textarea.val(newValue).trigger('change');
                }
                self.syncKeteranganEmptyClass($this);
            });
        },

        bindExistingKeteranganEditors: function() {
            var self = this;
            $('.' + this.settings.keteranganEditorClass).each(function() {
                var $editor = $(this);
                var textareaId = $editor.attr('data-textarea-id') || '';
                var $textarea = textareaId ? $('#' + textareaId) : $();
                if (!$textarea.length) {
                    var idx = $editor.attr('data-index') || '';
                    if (idx) {
                        $textarea = $('#keterangan_param_' + idx);
                    }
                }
                if (!$editor.attr('data-placeholder')) {
                    $editor.attr('data-placeholder', 'Klik untuk mengisi keterangan...');
                }
                self.syncKeteranganEmptyClass($editor);
                self.bindKeteranganEmptyHandlers($editor, $textarea);
            });
        },

        createKeteranganEditor: function($td, $textarea) {
            var currentValue = $textarea.val() || '';
            var id = $textarea.attr('id') || '';
            var name = $textarea.attr('name') || '';
            var self = this;

            if (!$td || $td.length === 0) {
                console.error('Invalid TD provided to createKeteranganEditor');
                return;
            }
            if (!$textarea || $textarea.length === 0) {
                console.error('Invalid textarea provided to createKeteranganEditor');
                return;
            }

            var index = '';
            if (id) {
                index = $textarea.data('index') || '';
                if (!index) {
                    var match = id.match(/keterangan_(?:param|sub)_(\d+)/);
                    if (match && match[1]) {
                        index = match[1];
                    } else {
                        match = id.match(/keterangan_(?:param|sub)_([a-f0-9\-]+)/i);
                        if (match && match[1]) {
                            index = match[1];
                        } else {
                            match = id.match(/keterangan_([a-f0-9\-]+|\d+)/i);
                            if (match && match[1]) {
                                index = match[1];
                            } else {
                                match = id.match(/keterangan[^_]*_(.+)$/);
                                if (match && match[1]) {
                                    index = match[1];
                                }
                            }
                        }
                    }
                }
            }

            if (!index && name) {
                var nameMatch = name.match(/keterangan[^_]*_([a-f0-9\-]+|\d+)/i);
                if (nameMatch && nameMatch[1]) {
                    index = nameMatch[1];
                }
            }

            if (!index) {
                if (id) {
                    var idParts = id.split('_');
                    if (idParts.length > 1) {
                        index = idParts.slice(1).join('_');
                    } else {
                        index = 'unknown_' + $textarea.index() + '_' + Date.now();
                    }
                } else {
                    index = 'unknown_' + $textarea.index() + '_' + Date.now();
                }
                console.warn('Could not extract index from keterangan textarea, using fallback:', index, 'ID:', id, 'Name:', name);
            }

            var editorId = 'keterangan_editor_' + index;

            // Reuse editor dari blade jika sudah ada
            var $existingEditor = $td.find('.' + this.settings.keteranganEditorClass).first();
            if (!$existingEditor.length) {
                $existingEditor = $('#' + editorId);
            }
            if ($existingEditor.length > 0) {
                if ($existingEditor.closest('td')[0] !== $td[0]) {
                    $td.append($existingEditor);
                }
                $td.find('.keterangan-display').remove();
                if (!$existingEditor.attr('id')) {
                    $existingEditor.attr('id', editorId);
                }
                if (!$existingEditor.attr('data-textarea-id')) {
                    $existingEditor.attr('data-textarea-id', id);
                }
                if (!$existingEditor.attr('data-index')) {
                    $existingEditor.attr('data-index', index);
                }
                if (!$existingEditor.attr('data-placeholder')) {
                    $existingEditor.attr('data-placeholder', 'Klik untuk mengisi keterangan...');
                }
                if (!$existingEditor.attr('contenteditable')) {
                    $existingEditor.attr('contenteditable', 'true');
                }
                if (this.isKeteranganVisuallyEmpty($existingEditor) && currentValue && String(currentValue).trim() !== '' && String(currentValue).trim() !== '-') {
                    $existingEditor.html(currentValue);
                }
                this.syncKeteranganEmptyClass($existingEditor);
                this.bindKeteranganEmptyHandlers($existingEditor, $textarea);
                $existingEditor.css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1',
                    'width': '100%',
                    'min-width': '160px'
                }).show();
                console.log('Reused existing keterangan editor:', editorId);
                return;
            }

            $td.find('.keterangan-display').remove();
            $td.find('.' + this.settings.keteranganEditorClass).remove();

            var $editor = $('<div>').addClass(this.settings.keteranganEditorClass)
                .attr({
                    'id': editorId,
                    'data-index': index,
                    'data-textarea-id': id,
                    'data-placeholder': 'Klik untuk mengisi keterangan...',
                    'contenteditable': 'true'
                })
                .html(currentValue || '')
                .css({
                    'min-height': '60px',
                    'padding': '8px 12px',
                    'border': '2px solid #e9ecef',
                    'border-radius': '6px',
                    'font-size': '13px',
                    'background': 'white',
                    'cursor': 'text',
                    'transition': 'all 0.3s',
                    'width': '100%',
                    'min-width': '160px',
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                });

            this.syncKeteranganEmptyClass($editor);
            $td.css({
                'display': 'table-cell',
                'visibility': 'visible'
            });
            $td.append($editor);
            this.bindKeteranganEmptyHandlers($editor, $textarea);
            console.log('Created keterangan editor for index:', index, 'ID:', id);
        },

        /**
         * Initialize TinyMCE inline mode
         */
        initTinyMCEInline: function() {
            if (typeof tinymce === 'undefined') {
                console.warn('TinyMCE not loaded, using contenteditable only');
                return;
            }

            try {
                // Check if elements exist before initializing TinyMCE
                var $hasilEditors = $('.inline-hasil-editor');
                var $keteranganEditors = $('.' + this.settings.keteranganEditorClass);
                
                if ($hasilEditors.length === 0 && $keteranganEditors.length === 0) {
                    console.warn('No inline editors found, skipping TinyMCE initialization');
                    return;
                }
                
                console.log('Found ' + $hasilEditors.length + ' hasil editors and ' + $keteranganEditors.length + ' keterangan editors');
                
                // TinyMCE untuk kolom "Hasil" (inline-hasil-editor)
                if ($hasilEditors.length > 0) {
                    console.log('Initializing TinyMCE for ' + $hasilEditors.length + ' hasil editors...');
                    
                    // Verify all editor elements exist in DOM before initializing
                    var validEditors = [];
                    $hasilEditors.each(function() {
                        var $editor = $(this);
                        if ($editor.length > 0 && $editor.is(':visible')) {
                            validEditors.push($editor[0]);
                        } else {
                            console.warn('Skipping invalid editor element:', $editor.attr('id'));
                        }
                    });
                    
                    if (validEditors.length === 0) {
                        console.warn('No valid editor elements found, skipping TinyMCE initialization');
                        return;
                    }
                    
                    // Use TinyMCE from local assets (loaded in template admin scripts.blade.php)
                    // Force baseURL to local assets to prevent CDN loading
                    var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
                    if (typeof tinymce !== 'undefined') {
                        // Prevent CDN loading by explicitly setting baseURL
                        if (tinymce.baseURL === undefined || 
                            tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                            tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                            tinymce.baseURL = tinymceBasePath;
                            console.log('TinyMCE baseURL forced to local assets:', tinymce.baseURL);
                        }
                    }
                    
                    // Double check TinyMCE is ready before init
                    if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                        console.error('TinyMCE is not ready, cannot initialize hasil editors');
                        return;
                    }
                    
                    // Wait a bit more to ensure TinyMCE is completely ready (especially theme loading)
                    setTimeout(function() {
                        try {
                            // Final check before init
                            if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                                console.error('TinyMCE became unavailable, cannot initialize hasil editors');
                                return;
                            }
                            
                            // Ensure baseURL is still set correctly
                            if (tinymce.baseURL && (tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1)) {
                                tinymce.baseURL = tinymceBasePath;
                                console.log('TinyMCE baseURL corrected to local assets:', tinymce.baseURL);
                            }
                            
                            tinymce.init({
                            selector: '.inline-hasil-editor',
                            inline: true,
                            menubar: false,
                            theme: 'modern', // Use theme available in local assets (not 'silver' which requires CDN)
                            // Use TinyMCE from local assets - don't override base_url, theme_url, skin_url
                            content_css: false,
                            document_base_url: window.location.origin,
                        plugins: [
                            'lists charmap',
                            'searchreplace',
                            'paste'
                        ],
                    toolbar: 'bold italic underline | superscript subscript | charmap | removeformat',
                    toolbar_mode: 'floating',
                    toolbar_location: 'auto',
                    paste_as_text: true,
                    content_style: 'body { font-size: 14px; font-family: Arial, sans-serif; } sup { vertical-align: super; font-size: 0.8em; } sub { vertical-align: sub; font-size: 0.8em; }',
                    // Allow sup and sub tags
                    valid_elements: '*[*]',
                    extended_valid_elements: 'sup[*],sub[*]',
                    // Ensure superscript/subscript commands are available
                    formats: {
                        superscript: {inline: 'sup', styles: {verticalAlign: 'super'}},
                        subscript: {inline: 'sub', styles: {verticalAlign: 'sub'}}
                    },
                    // Prevent automatic <p> tag creation
                    forced_root_block: false,
                    force_br_newlines: true,
                    force_p_newlines: false,
                    charmap_append: [
                        // Simbol matematika
                        [0x00B1, 'plus-minus sign'],
                        [0x00B2, 'superscript two'],
                        [0x00B3, 'superscript three'],
                        [0x00B5, 'micro sign'],
                        [0x00BC, 'vulgar fraction one quarter'],
                        [0x00BD, 'vulgar fraction one half'],
                        [0x00BE, 'vulgar fraction three quarters'],
                        [0x2264, 'less-than or equal to'],
                        [0x2265, 'greater-than or equal to'],
                        [0x2248, 'almost equal to'],
                        [0x2260, 'not equal to'],
                        // Simbol kimia
                        [0x00B0, 'degree sign'],
                        [0x2103, 'degree celsius'],
                        [0x00D7, 'multiplication sign'],
                        [0x00F7, 'division sign'],
                        // Greek letters (untuk notasi)
                        [0x03B1, 'greek small letter alpha'],
                        [0x03B2, 'greek small letter beta'],
                        [0x03B3, 'greek small letter gamma'],
                        [0x03BC, 'greek small letter mu']
                    ],
                    setup: function(editor) {
                        // Ensure editor is available
                        if (!editor || !editor.id) {
                            console.error('Editor not properly initialized');
                            return;
                        }
                        
                        // Verify the editor element exists in DOM
                        var $editorElement = $('#' + editor.id);
                        if ($editorElement.length === 0) {
                            console.error('Editor element not found in DOM:', editor.id);
                            return;
                        }
                        
                        var self = this;
                        
                        // Convert initial content when editor is initialized
                        editor.on('init', function() {
                            try {
                                // Double check editor is still valid
                                if (!editor || !editor.id) {
                                    console.error('Editor became invalid during init');
                                    return;
                                }
                                
                                var $editorEl = $('#' + editor.id);
                                if ($editorEl.length === 0) {
                                    console.error('Editor element disappeared during init:', editor.id);
                                    return;
                                }
                                
                            var initialContent = editor.getContent();
                            if (initialContent && (initialContent.includes('^(') || initialContent.includes('^'))) {
                                // Convert superscript notation to HTML: ^(1) to <sup>1</sup>
                                var convertedContent = AnalisInlineEditor.convertSuperscriptToHtml(initialContent);
                                if (convertedContent !== initialContent) {
                                    editor.setContent(convertedContent);
                                }
                            }
                            
                            // Clean empty <p> tags on init
                            var content = editor.getContent();
                            if (content && (content.trim() === '<p><br data-mce-bogus="1"></p>' || content.trim() === '<p><br></p>' || content.trim() === '<p></p>')) {
                                editor.setContent('');
                            }
                            } catch(e) {
                                console.error('Error in editor init handler:', e);
                            }
                        });
                        
                        // Handle focus: clean empty <p> tags when editor is focused
                        editor.on('focus', function() {
                            try {
                                // Verify editor is still valid
                                if (!editor || !editor.id || typeof editor.getContent !== 'function' || editor.removed) {
                                    return;
                                }
                                
                                var $editorEl = $('#' + editor.id);
                                if ($editorEl.length === 0) {
                                    return;
                                }
                                
                                var content = editor.getContent();
                                // Remove empty <p> tags with bogus br
                                if (content && (content.trim() === '<p><br data-mce-bogus="1"></p>' || content.trim() === '<p><br></p>' || content.trim() === '<p></p>')) {
                                    editor.setContent('');
                                }
                            } catch(e) {
                                console.error('Error in editor focus handler:', e);
                            }
                        });
                        
                        // Enter = pindah parameter berikutnya; Shift+Enter = baris baru
                        editor.on('keydown', function(e) {
                            try {
                                if (!editor || !editor.id) {
                                    return;
                                }

                                var $editorEl = $('#' + editor.id);
                                if ($editorEl.length === 0) {
                                    return;
                                }

                                if (e.keyCode !== 13) {
                                    return;
                                }

                                // Shift+Enter = baris baru: biarkan TinyMCE yang menangani
                                // supaya kursor ikut pindah ke baris baru.
                                if (e.shiftKey) {
                                    return;
                                }

                                e.preventDefault();
                                e.stopPropagation();
                                e.stopImmediatePropagation();

                                if (editor && editor.save) {
                                    editor.save();
                                }

                                var $editor = $('#' + editor.id);
                                setTimeout(function() {
                                    AnalisInlineEditor.navigateToNextHasil($editor);
                                }, 10);

                                return false;
                            } catch(e) {
                                console.error('Error in editor keydown handler:', e);
                            }
                        });
                        
                        // Preview badge real-time (debounced) — supaya line break langsung terlihat
                        var previewTimeout;
                        editor.on('input keyup NodeChange', function() {
                            try {
                                if (!editor || !editor.id || typeof editor.getContent !== 'function' || editor.removed) {
                                    return;
                                }

                                var $editorEl = $('#' + editor.id);
                                if ($editorEl.length === 0 || !$editorEl.hasClass('inline-hasil-editor')) {
                                    return;
                                }

                                clearTimeout(previewTimeout);
                                previewTimeout = setTimeout(function() {
                                    try {
                                        var rawPreview = editor.getContent();
                                        if (rawPreview && (rawPreview.trim() === '<p><br data-mce-bogus="1"></p>' || rawPreview.trim() === '<p><br></p>' || rawPreview.trim() === '<p></p>')) {
                                            rawPreview = '';
                                        }

                                        var htmlPreview = AnalisInlineEditor.convertSuperscriptToHtml(rawPreview);
                                        var textareaId = $editorEl.data('textarea-id');
                                        if (textareaId) {
                                            $('#' + textareaId).val(htmlPreview);
                                        }

                                        AnalisInlineEditor.updateResultBadge(
                                            $editorEl.data('index'),
                                            htmlPreview,
                                            $editorEl.data('min'),
                                            $editorEl.data('max'),
                                            decodeHtmlEntities($editorEl.data('equal') || ''),
                                            $editorEl.data('number-format')
                                        );
                                    } catch (previewErr) {
                                        // abaikan — blur tetap sync
                                    }
                                }, 150);
                            } catch(e) {
                                // abaikan
                            }
                        });

                        // Handle input/change to convert ^( notation in real-time (debounced)
                        var conversionTimeout;
                        editor.on('input keyup', function() {
                            try {
                                if (!editor || !editor.id || typeof editor.getContent !== 'function' || editor.removed) {
                                    return;
                                }
                                
                                // Debounce conversion to avoid disrupting typing
                                clearTimeout(conversionTimeout);
                                conversionTimeout = setTimeout(function() {
                                    try {
                                        var rawContent = editor.getContent();
                                        
                                        // Check if content contains ^( or _( notation that needs conversion
                                        // Only convert if there's a complete pattern like ^(2) or _(2)
                                        if (rawContent && (rawContent.match(/\^\([^\)]+\)/) || rawContent.match(/\_\([^\)]+\)/))) {
                                            // Convert superscript/subscript notation to HTML
                                            var htmlContent = AnalisInlineEditor.convertSuperscriptToHtml(rawContent);
                                            
                                            // Only update if conversion changed the content
                                            if (htmlContent !== rawContent) {
                                                // Get current cursor position
                                                var bookmark = editor.selection.getBookmark(2, true);
                                                
                                                // Set converted content
                                                editor.setContent(htmlContent, {format: 'raw'});
                                                
                                                // Restore cursor position
                                                editor.selection.moveToBookmark(bookmark);
                                            }
                                        }
                                    } catch(e) {
                                        // Silently ignore errors during input (to avoid disrupting typing)
                                    }
                                }, 500); // Wait 500ms after user stops typing
                            } catch(e) {
                                // Silently ignore errors during input (to avoid disrupting typing)
                            }
                        });
                        
                        editor.on('blur', function() {
                            try {
                                // Verify editor is still valid
                                if (!editor || !editor.id || typeof editor.getContent !== 'function' || editor.removed) {
                                    return;
                                }
                                
                                var $editorEl = $('#' + editor.id);
                                if ($editorEl.length === 0) {
                                    return;
                                }
                                
                                var rawContent = editor.getContent();
                                
                                // Clean empty <p> tags before saving
                                if (rawContent && (rawContent.trim() === '<p><br data-mce-bogus="1"></p>' || rawContent.trim() === '<p><br></p>' || rawContent.trim() === '<p></p>')) {
                                    rawContent = '';
                                }
                                
                                // Convert superscript notation to HTML: ^(1) or ^1 to <sup>1</sup>
                                // This ensures any remaining ^( notation is converted before saving
                                var htmlContent = AnalisInlineEditor.convertSuperscriptToHtml(rawContent);
                                
                                // If conversion changed content, update editor
                                if (htmlContent !== rawContent) {
                                    editor.setContent(htmlContent, {format: 'raw'});
                                    // Get updated content after conversion
                                    htmlContent = editor.getContent();
                                }
                                
                                var $editor = $('#' + editor.id);
                                var textareaId = $editor.data('textarea-id');
                                // Save converted HTML to textarea (without parentheses)
                                $('#' + textareaId).val(htmlContent).trigger('change');
                                
                                // Update badge for hasil
                                if ($editor.hasClass('inline-hasil-editor')) {
                                    var index = $editor.data('index');
                                    var min = $editor.data('min');
                                    var max = $editor.data('max');
                                    var equal = decodeHtmlEntities($editor.data('equal') || '');
                                    var numberFormat = $editor.data('number-format');
                                    
                                    // Pass HTML value to updateResultBadge so it can handle superscripts
                                    AnalisInlineEditor.updateResultBadge(index, htmlContent, min, max, equal, numberFormat);
                                }
                            } catch(e) {
                                console.error('Error in editor blur handler:', e);
                            }
                        });
                    }
                            }); // End tinymce.init
                        } catch(e) {
                            console.error('Error initializing TinyMCE hasil editors:', e);
                            console.error('TinyMCE state:', {
                                defined: typeof tinymce !== 'undefined',
                                hasInit: typeof tinymce !== 'undefined' && typeof tinymce.init === 'function',
                                hasUtil: typeof tinymce !== 'undefined' && typeof tinymce.util === 'object'
                            });
                        }
                    }, 300); // Wait 300ms to ensure TinyMCE theme is loaded
                }

                // TinyMCE untuk kolom "Keterangan" (inline-keterangan-editor)
                if ($keteranganEditors.length > 0) {
                    // Filter out editors that are already initialized
                    // IMPORTANT: Don't filter by visibility - TinyMCE can initialize hidden editors
                    var $uninitializedKeteranganEditors = $keteranganEditors.filter(function() {
                        var $editor = $(this);
                        var editorId = $editor.attr('id');
                        if (!editorId) {
                            console.warn('Keterangan editor without ID found, skipping TinyMCE init');
                            return false; // Skip editors without ID
                        }
                        
                        // Check if TinyMCE instance already exists for this editor
                        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                            return false; // Already initialized, skip
                        }
                        
                        // Force visibility before initialization to ensure TinyMCE can initialize
                        if (!$editor.is(':visible') || $editor.css('display') === 'none') {
                            console.warn('Keterangan editor not visible, forcing visibility before TinyMCE init:', editorId);
                            // Remove display:none
                            var currentStyle = $editor.attr('style') || '';
                            currentStyle = currentStyle.replace(/display\s*:\s*none[^;]*;?/gi, '');
                            $editor.attr('style', currentStyle);
                            $editor.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'opacity': '1'
                            }).show();
                            
                            // Also ensure parent TD and TR are visible
                            var $td = $editor.closest('td');
                            if ($td.length > 0) {
                                var tdStyle = $td.attr('style') || '';
                                tdStyle = tdStyle.replace(/display\s*:\s*none[^;]*;?/gi, '');
                                $td.attr('style', tdStyle);
                                $td.css({
                                    'display': 'table-cell',
                                    'visibility': 'visible'
                                }).show();
                                
                                var $tr = $td.parent('tr');
                                if ($tr.length > 0) {
                                    var trStyle = $tr.attr('style') || '';
                                    trStyle = trStyle.replace(/display\s*:\s*none[^;]*;?/gi, '');
                                    $tr.attr('style', trStyle);
                                    $tr.css({
                                        'display': 'table-row',
                                        'visibility': 'visible'
                                    }).show();
                                }
                            }
                        }
                        
                        return true; // Not initialized yet, include it
                    });
                    
                    if ($uninitializedKeteranganEditors.length === 0) {
                        console.log('All keterangan editors already initialized, skipping...');
                    } else {
                        console.log('Initializing TinyMCE for ' + $uninitializedKeteranganEditors.length + ' keterangan editors (out of ' + $keteranganEditors.length + ' total)...');
                        // Use TinyMCE from local assets (loaded in template admin scripts.blade.php)
                        // Force baseURL to local assets to prevent CDN loading
                        var tinymceBasePath = window.location.origin + '/assets/admin/vendors/tinymce';
                        if (typeof tinymce !== 'undefined') {
                            // Prevent CDN loading by explicitly setting baseURL
                            if (tinymce.baseURL === undefined || 
                                tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || 
                                tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1) {
                                tinymce.baseURL = tinymceBasePath;
                                console.log('TinyMCE baseURL forced to local assets:', tinymce.baseURL);
                            }
                        }
                        
                        // Double check TinyMCE is ready before init
                        if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                            console.error('TinyMCE is not ready, cannot initialize keterangan editors');
                            return;
                        }
                        
                        // Create a unique selector for uninitialized editors only
                        // Also verify each editor exists in DOM and is accessible
                        var uninitializedIds = [];
                        $uninitializedKeteranganEditors.each(function() {
                            var $editor = $(this);
                            var editorId = $editor.attr('id');
                            if (editorId && $editor.length > 0) {
                                // Double check editor is in DOM
                                if ($('#' + editorId).length > 0) {
                                    uninitializedIds.push('#' + editorId);
                                    console.log('Including keterangan editor for TinyMCE init:', editorId, 'Visible:', $editor.is(':visible'), 'Display:', $editor.css('display'));
                                } else {
                                    console.warn('Keterangan editor not found in DOM:', editorId);
                                }
                            }
                        });
                        
                        var uninitializedIdsString = uninitializedIds.join(',');
                        
                        if (uninitializedIdsString) {
                            // Wait a bit more to ensure TinyMCE is completely ready (especially theme loading)
                            setTimeout(function() {
                                try {
                                    // Final check before init
                                    if (typeof tinymce === 'undefined' || typeof tinymce.init !== 'function') {
                                        console.error('TinyMCE became unavailable, cannot initialize keterangan editors');
                                        return;
                                    }
                                    
                                    // Ensure baseURL is still set correctly
                                    if (tinymce.baseURL && (tinymce.baseURL.indexOf('cdn.jsdelivr.net') !== -1 || tinymce.baseURL.indexOf('cdnjs.cloudflare.com') !== -1)) {
                                        tinymce.baseURL = tinymceBasePath;
                                        console.log('TinyMCE baseURL corrected to local assets:', tinymce.baseURL);
                                    }
                                    
                                    console.log('Initializing TinyMCE for keterangan editors with selector:', uninitializedIdsString);
                                    
                                    tinymce.init({
                                    selector: uninitializedIdsString,
                            inline: true,
                            menubar: false,
                            theme: 'modern', // Use theme available in local assets (not 'silver' which requires CDN)
                            // Use TinyMCE from local assets - don't override base_url, theme_url, skin_url
                            content_css: false,
                            document_base_url: window.location.origin,
                        plugins: [
                            'lists charmap',
                            'searchreplace',
                            'paste'
                        ],
                    toolbar: 'bold italic underline | superscript subscript | charmap | ' +
                        'bullist numlist | removeformat',
                    toolbar_mode: 'floating',
                    toolbar_location: 'auto',
                    paste_as_text: true,
                    content_style: 'body { font-size: 13px; font-family: Arial, sans-serif; }',
                    charmap_append: [
                        // Simbol matematika
                        [0x00B1, 'plus-minus sign'],
                        [0x00B2, 'superscript two'],
                        [0x00B3, 'superscript three'],
                        [0x00B5, 'micro sign'],
                        [0x2264, 'less-than or equal to'],
                        [0x2265, 'greater-than or equal to'],
                        [0x2248, 'almost equal to'],
                        [0x2260, 'not equal to'],
                        // Simbol umum
                        [0x00B0, 'degree sign'],
                        [0x2103, 'degree celsius'],
                        [0x00D7, 'multiplication sign'],
                        [0x00F7, 'division sign'],
                        // Greek letters
                        [0x03B1, 'greek small letter alpha'],
                        [0x03B2, 'greek small letter beta'],
                        [0x03B3, 'greek small letter gamma'],
                        [0x03BC, 'greek small letter mu']
                    ],
                    setup: function(editor) {
                        // Ensure editor is available
                        if (!editor || !editor.id) {
                            console.error('Keterangan editor not properly initialized');
                            return;
                        }

                        editor.on('init', function() {
                            try {
                                var $el = $('#' + editor.id);
                                AnalisInlineEditor.syncKeteranganEmptyClass($el);
                            } catch (e) { /* ignore */ }
                        });

                        editor.on('input keyup change SetContent NodeChange', function() {
                            try {
                                var $el = $('#' + editor.id);
                                AnalisInlineEditor.syncKeteranganEmptyClass($el);
                            } catch (e) { /* ignore */ }
                        });
                        
                        // Handle Enter key: Enter = pindah parameter, Shift+Enter = new line
                        editor.on('keydown', function(e) {
                            try {
                                // Verify editor is still valid
                                if (!editor || !editor.id) {
                                    return;
                                }
                                
                                var $editorEl = $('#' + editor.id);
                                if ($editorEl.length === 0) {
                                    return;
                                }

                                // Hapus placeholder segera saat mulai mengetik
                                if (!e.ctrlKey && !e.metaKey && !e.altKey && e.key && e.key.length === 1) {
                                    $editorEl.removeClass('empty');
                                }
                                
                                // Enter tanpa Shift = pindah ke keterangan berikutnya
                                if (e.keyCode === 13 && !e.shiftKey) {
                                    // CRITICAL: Prevent form submission
                                    e.preventDefault();
                                    e.stopPropagation();
                                    e.stopImmediatePropagation();
                                    
                                    // Blur editor untuk save content
                                    if (editor && editor.save) {
                                        editor.save();
                                    }
                                    
                                    // Navigate to next keterangan
                                    var $editor = $('#' + editor.id);
                                    setTimeout(function() {
                                        AnalisInlineEditor.navigateToNextKeterangan($editor);
                                    }, 10);
                                    
                                    return false;
                                }
                                // Shift+Enter = new line (biarkan default behavior)
                            } catch(e) {
                                console.error('Error in keterangan editor keydown handler:', e);
                            }
                        });
                        
                        editor.on('blur', function() {
                            try {
                                // Verify editor is still valid
                                if (!editor || !editor.id || typeof editor.getContent !== 'function' || editor.removed) {
                                    return;
                                }
                                
                                var $editor = $('#' + editor.id);
                                if ($editor.length === 0) {
                                    return;
                                }
                                
                                var content = editor.getContent();
                                var textareaId = $editor.data('textarea-id');
                                if (textareaId) {
                                    // Save editor content to textarea (TinyMCE's save method)
                                    if (editor && typeof editor.save === 'function') {
                                        editor.save();
                                    }
                                    // Also manually update textarea to ensure it's saved
                                    var $textarea = $('#' + textareaId);
                                    if ($textarea.length > 0) {
                                        $textarea.val(content).trigger('change');
                                        console.log('Saved keterangan from TinyMCE editor:', editor.id, 'to textarea:', textareaId);
                                    } else {
                                        console.warn('Textarea not found for TinyMCE editor:', textareaId);
                                    }
                                }
                                AnalisInlineEditor.syncKeteranganEmptyClass($editor);
                            } catch(e) {
                                console.error('Error in keterangan editor blur handler:', e);
                            }
                        });
                    }
                                    }); // End tinymce.init
                                } catch(e) {
                                    console.error('Error initializing TinyMCE keterangan editors:', e);
                                    console.error('TinyMCE state:', {
                                        defined: typeof tinymce !== 'undefined',
                                        hasInit: typeof tinymce !== 'undefined' && typeof tinymce.init === 'function',
                                        hasUtil: typeof tinymce !== 'undefined' && typeof tinymce.util === 'object'
                                    });
                                }
                            }, 300); // Wait 300ms to ensure TinyMCE theme is loaded
                        } // End if (uninitializedIds)
                    } // End else (if uninitialized editors exist)
                } // End if ($keteranganEditors.length > 0)

                console.log('TinyMCE inline initialized for Hasil and Keterangan');
            } catch(e) {
                console.error('Error initializing TinyMCE inline:', e);
                console.error('Error details:', e.message, e.stack);
            }
        },

        /**
         * Initialize keyboard navigation
         */
        initKeyboardNavigation: function() {
            var self = this;
            var hasilSelector = '.' + this.settings.hasilInputClass + ', .inline-hasil-editor';
            var keteranganSelector = '.' + this.settings.keteranganEditorClass;

            // CRITICAL: Prevent form submission when Enter is pressed on ANY form input
            // This covers ALL cases including text inputs (date field), selects, and inline editors.
            // Note: the form only has 1 <input type="text"> (date), so HTML5 spec would auto-submit
            // the form when Enter is pressed in that field. We prevent that here.
            // We use a broad selector to catch all potentially problematic inputs.
            $(document).on('keydown', 'form input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]), form select, .inline-hasil-input, .inline-hasil-editor', function(e) {
                // If Enter is pressed (without Shift), prevent form submission
                if (e.key === 'Enter' && !e.shiftKey) {
                    var $target = $(e.target);
                    var $form = $target.closest('form');
                    if ($form.length > 0) {
                        // Prevent native form submission for ALL form inputs
                        e.preventDefault();
                        // Do NOT stopPropagation - let specific handlers still run for navigation
                        console.log('Preventing form submit from Enter key on', $target[0] ? $target[0].tagName : 'unknown', $target.attr('class') || '');
                    }
                }
            });

            // CRITICAL: Prevent form submission when clicking on select/dropdown
            // This prevents accidental form submission from click events
            $(document).on('mousedown click', 'form select.inline-hasil-input, select.inline-hasil-input', function(e) {
                // Stop propagation to prevent form submit, but allow dropdown to work normally
                e.stopPropagation();
                console.log('Preventing form submit from click on select dropdown');
            });

            // Also prevent form submit on form element itself when Enter is pressed or select is clicked
            // Use capture phase to catch submit events early
            $(document).on('submit', 'form', function(e) {
                // Check if the submit was triggered by Enter key on input/dropdown
                var $activeElement = $(document.activeElement);
                var isInputOrDropdown = $activeElement.is('input, select, textarea') || 
                                       $activeElement.hasClass('inline-hasil-input') || 
                                       $activeElement.hasClass('inline-hasil-editor');
                
                // Check if active element is a submit button - if so, allow it
                var isSubmitButton = $activeElement.is('button[type="submit"], input[type="submit"]');
                
                // Also check event target
                var $eventTarget = $(e.target);
                var targetIsSelect = $eventTarget.is('select') || $eventTarget.closest('select.inline-hasil-input').length > 0;
                
                // If submit was triggered by select or input/dropdown (but not submit button), prevent it
                if ((isInputOrDropdown && !isSubmitButton) || targetIsSelect) {
                    // This submit was likely triggered by Enter key or select click, prevent it
                    console.log('Preventing form submit triggered by Enter key or select click', {
                        isInputOrDropdown: isInputOrDropdown,
                        isSubmitButton: isSubmitButton,
                        targetIsSelect: targetIsSelect,
                        activeElement: $activeElement[0] ? $activeElement[0].tagName : 'none',
                        eventTarget: $eventTarget[0] ? $eventTarget[0].tagName : 'none'
                    });
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }
            });

            // Enter pada dropdown/input Urinalisa dual (Silinder/Kristal/dll):
            // - Kolom 1 Positif → fokus ke kolom jenis
            // - Kolom jenis → fokus ke nama jenis (jika terlihat)
            // - Selain itu → pindah ke parameter berikutnya (tanpa recreate tombol)
            $(document).on('keydown', '.urinalisa-positivity-select, .urinalisa-detail-input, .urinalisa-name-input', function(e) {
                if (e.key !== 'Enter' || e.shiftKey) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                var $current = $(this);
                var $dual = $current.closest('.urinalisa-dual-input');
                var paramNo = $dual.data('param-no');

                if ($current.hasClass('urinalisa-positivity-select')) {
                    var positivity = ($current.val() || '').trim().toLowerCase();
                    var requiresNama = String($dual.data('requires-nama-jenis') || '0') === '1';

                    if (positivity !== 'negatif') {
                        if (requiresNama) {
                            var $firstGrade = $dual.find('.urinalisa-names .urinalisa-detail-input').first();
                            var $namesWrap = $dual.find('.urinalisa-names');
                            if ($firstGrade.length && $namesWrap.is(':visible')) {
                                $firstGrade.focus({ preventScroll: true });
                                return false;
                            }
                        }

                        var $detail = $dual.find('.urinalisa-detail-wrap .urinalisa-detail-input').first();
                        var $detailWrap = $dual.find('.urinalisa-detail-wrap').first();
                        if ($detail.length && $detailWrap.is(':visible')) {
                            $detail.focus({ preventScroll: true });
                            return false;
                        }
                    }
                }

                if ($current.hasClass('urinalisa-detail-input')) {
                    var $row = $current.closest('.urinalisa-name-row');
                    if ($row.length) {
                        var $rowName = $row.find('.urinalisa-name-input');
                        if ($rowName.length && $rowName.is(':visible')) {
                            $rowName.focus({ preventScroll: true });
                            return false;
                        }
                    }

                    var $name = $dual.find('.urinalisa-names .urinalisa-name-input').first();
                    var $namesWrap = $dual.find('.urinalisa-names');
                    if ($name.length && $namesWrap.is(':visible')) {
                        $name.focus({ preventScroll: true });
                        return false;
                    }
                }

                if ($current.hasClass('urinalisa-name-input')) {
                    var $nameRow = $current.closest('.urinalisa-name-row');
                    var $nextRow = $nameRow.nextAll('.urinalisa-name-row:visible').first();
                    if ($nextRow.length) {
                        var $nextGrade = $nextRow.find('.urinalisa-detail-input').first();
                        if ($nextGrade.length) {
                            $nextGrade.focus({ preventScroll: true });
                            return false;
                        }
                        $nextRow.find('.urinalisa-name-input').focus({ preventScroll: true });
                        return false;
                    }
                }

                // Anchor navigasi ke textarea parameter agar index terdeteksi benar
                var $textarea = $('#hasil_permohonan_uji_parameter_klinik_' + paramNo);
                if ($textarea.length) {
                    self.navigateToNextHasil($textarea);
                } else {
                    self.navigateToNextHasil($current);
                }

                return false;
            });

            // Keyboard navigation untuk kolom "Hasil"
            $(document).on('keydown', hasilSelector, function(e) {
                var $current = $(this);

                // Enter = pindah ke kolom "Hasil" baris berikutnya
                // Shift+Enter = baris baru di dalam editor (ditangani editor, jangan diprevent)
                if (e.key === 'Enter' && !e.shiftKey) {
                    // CRITICAL: Prevent form submission
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    // Untuk dropdown, handler khusus di createHasilInput sudah handle pindah
                    if (!$current.is('select')) {
                        self.navigateToNextHasil($current);
                    }

                    return false;
                }

                // Arrow Down - move to same column, next row
                if (e.key === 'ArrowDown' && e.ctrlKey) {
                    e.preventDefault();
                    self.navigateVertical($current, 'down');
                }

                // Arrow Up - move to same column, previous row
                if (e.key === 'ArrowUp' && e.ctrlKey) {
                    e.preventDefault();
                    self.navigateVertical($current, 'up');
                }

                // Tab - pindah ke kolom "Keterangan" di baris yang sama
                if (e.key === 'Tab' && !e.shiftKey) {
                    e.preventDefault();
                    var $currentTd = $current.closest('td');
                    var $currentTr = $currentTd.closest('tr');
                    // Keterangan ada di kolom ke-4 (index 3), hasil di kolom ke-2 (index 1)
                    var $keteranganTd = $currentTr.find('td').eq(3);
                    var $keteranganInput = $keteranganTd.find(keteranganSelector);
                    if ($keteranganInput.length) {
                        $keteranganInput.focus();
                        if ($keteranganInput.attr('contenteditable') === 'true') {
                            self.setCursorAtEnd($keteranganInput[0]);
                        }
                    }
                }
            });

            // Keyboard navigation untuk kolom "Keterangan"
            $(document).on('keydown', keteranganSelector, function(e) {
                var $current = $(this);

                // Enter key - pindah ke kolom "Keterangan" di baris berikutnya
                // Shift+Enter - new line di dalam editor (default behavior, tidak prevent)
                if (e.key === 'Enter' && !e.shiftKey) {
                    // CRITICAL: Prevent form submission
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    self.navigateToNextKeterangan($current);
                    // Return false to ensure event is completely stopped
                    return false;
                }
                // Shift+Enter dibiarkan default (new line di TinyMCE)

                // Arrow Down - move to same column, next row
                if (e.key === 'ArrowDown' && e.ctrlKey) {
                    e.preventDefault();
                    self.navigateVertical($current, 'down');
                }

                // Arrow Up - move to same column, previous row
                if (e.key === 'ArrowUp' && e.ctrlKey) {
                    e.preventDefault();
                    self.navigateVertical($current, 'up');
                }

                // Tab - pindah ke hasil di baris berikutnya
                if (e.key === 'Tab' && !e.shiftKey) {
                    e.preventDefault();
                    self.navigateToNextHasilFromKeterangan($current);
                }

                // Shift+Tab - kembali ke hasil di baris yang sama
                if (e.key === 'Tab' && e.shiftKey) {
                    e.preventDefault();
                    var $currentTd = $current.closest('td');
                    var $currentTr = $currentTd.closest('tr');
                    var $hasilTd = $currentTr.find('td').eq(1); // Hasil di kolom ke-2 (index 1)
                    var $hasilInput = $hasilTd.find(hasilSelector);
                    if ($hasilInput.length) {
                        $hasilInput.focus();
                        if ($hasilInput.attr('contenteditable') === 'true') {
                            self.setCursorAtEnd($hasilInput[0]);
                        }
                    }
                }
            });
        },

        /**
         * Navigate to next "Hasil" field (same column, next row)
         * Skip header rows (th, colspan rows) and find next valid input field
         */
        navigateToNextHasil: function($current) {
            // Get current index from data-index attribute (prioritas utama)
            var currentIndex = null;
            
            // Priority 1: Try to get from data-index attribute (most reliable)
            currentIndex = $current.data('index');
            if (currentIndex !== undefined && currentIndex !== null) {
                currentIndex = parseInt(currentIndex);
            }
            
            // Priority 2: Try to get index from ID (hasil_editor_X or hasil_permohonan_uji_parameter_klinik_X)
            if (currentIndex === null) {
                var currentId = $current.attr('id') || '';
                if (currentId) {
                    var match = currentId.match(/\d+/);
                    if (match) {
                        currentIndex = parseInt(match[0]);
                    }
                }
            }
            
            // Priority 3: Try to get from textarea
            if (currentIndex === null) {
                var $textarea = $current.closest('td').find('textarea.result_method_klinik');
                if ($textarea.length > 0) {
                    // Prioritaskan data-index dari textarea
                    var textareaIndex = $textarea.data('index');
                    if (textareaIndex !== undefined && textareaIndex !== null) {
                        currentIndex = parseInt(textareaIndex);
                    } else {
                        // Fallback ke ID jika data-index tidak ada
                        var textareaId = $textarea.attr('id') || '';
                        if (textareaId) {
                            var match = textareaId.match(/\d+/);
                            if (match) {
                                currentIndex = parseInt(match[0]);
                            }
                        }
                    }
                }
            }
            
            // If we still don't have an index, return
            if (currentIndex === null) {
                console.warn('Could not determine current index from data-index, ID, or textarea');
                return;
            }
            
            console.log('Navigating from index:', currentIndex);
            
            // Find the next editor by incrementing the index
            var nextIndex = currentIndex + 1;
            var maxAttempts = 100; // Prevent infinite loop
            var attempts = 0;
            var $nextHasilInput = null;
            
            // Try to find next editor by data-index first (most reliable)
            while (attempts < maxAttempts && (!$nextHasilInput || $nextHasilInput.length === 0)) {
                // Priority 1: Try to find by data-index attribute (most reliable)
                // Include both editor and dropdown (select) with inline-hasil-input class
                $nextHasilInput = $('[data-index="' + nextIndex + '"].inline-hasil-editor, [data-index="' + nextIndex + '"].inline-hasil-input, select[data-index="' + nextIndex + '"].inline-hasil-input');
                
                console.log('Searching for next input with index:', nextIndex, 'Found:', $nextHasilInput.length, 'elements');
                
                // Priority 2: Try to find editor by ID: hasil_editor_X
                if ($nextHasilInput.length === 0) {
                    $nextHasilInput = $('#hasil_editor_' + nextIndex);
                    console.log('Trying by ID hasil_editor_' + nextIndex + ':', $nextHasilInput.length);
                }

                // Priority 2b: Urinalisa dual (Silinder/Kristal) — kolom 1 positivity
                if ($nextHasilInput.length === 0) {
                    $nextHasilInput = $('#urinalisa_positivity_' + nextIndex);
                    if ($nextHasilInput.length > 0 && !$nextHasilInput.is(':visible')) {
                        $nextHasilInput = $();
                    }
                    console.log('Trying urinalisa positivity #' + nextIndex + ':', $nextHasilInput.length);
                }
                
                // Priority 3: Try to find dropdown by searching in container (more comprehensive search)
                if ($nextHasilInput.length === 0) {
                    // Search for select dropdown with data-index anywhere in the document
                    $nextHasilInput = $('select.inline-hasil-input[data-index="' + nextIndex + '"]');
                    console.log('Trying select dropdown with data-index=' + nextIndex + ':', $nextHasilInput.length);
                    
                    // Also try searching in table container
                    if ($nextHasilInput.length === 0) {
                        var $table = $('#table-parameter, #tableParameterResponsive');
                        if ($table.length > 0) {
                            $nextHasilInput = $table.find('select.inline-hasil-input[data-index="' + nextIndex + '"]');
                            console.log('Trying in table container:', $nextHasilInput.length);
                        }
                    }
                }
                
                // Priority 4: Try to find by textarea ID and create editor if needed
                if ($nextHasilInput.length === 0) {
                    var $nextTextarea = $('#hasil_permohonan_uji_parameter_klinik_' + nextIndex + ', #hasil_permohonan_uji_sub_parameter_klinik_' + nextIndex + ', #result_method_' + nextIndex);
                    if ($nextTextarea.length > 0) {
                        // Found textarea, try to find or create editor
                        var $nextTd = $nextTextarea.closest('td');
                        if ($nextTd.length > 0) {
                            // Check if editor already exists (search more broadly, including urinalisa dual)
                            $nextHasilInput = $nextTd.find('.inline-hasil-input, .inline-hasil-editor, [contenteditable="true"], select.inline-hasil-input, .urinalisa-positivity-select');
                            
                            // If not found, try to create it
                            if ($nextHasilInput.length === 0) {
                                try {
                                    this.createHasilInput($nextTd, $nextTextarea);
                                    // Wait a bit for editor to be created
                                    var self = this;
                                    setTimeout(function() {
                                        $nextHasilInput = $nextTd.find('.inline-hasil-input, .inline-hasil-editor, [contenteditable="true"], select.inline-hasil-input, .urinalisa-positivity-select');
                                        if ($nextHasilInput.length > 0) {
                                            console.log('Found next input after creation, index:', nextIndex);
                                            self.focusHasilInput($nextHasilInput.first());
                                        }
                                    }, 100);
                                    // Try to find immediately as well
                                    $nextHasilInput = $nextTd.find('.inline-hasil-input, .inline-hasil-editor, [contenteditable="true"], select.inline-hasil-input, .urinalisa-positivity-select');
                                } catch(e) {
                                    console.error('Error creating hasil input:', e);
                                }
                            }
                        }
                    }
                }
                
                // If we found an input, break (ambil elemen pertama bila selector match banyak)
                if ($nextHasilInput && $nextHasilInput.length > 0) {
                    $nextHasilInput = $nextHasilInput.first();
                    console.log('Found next input with index:', nextIndex, 'Type:', $nextHasilInput.is('select') ? 'dropdown' : ($nextHasilInput.is('[contenteditable="true"]') ? 'editor' : 'input'));
                    break;
                }
                
                // Try next index
                nextIndex++;
                attempts++;
            }
            
            // If we found an input, focus it
            if ($nextHasilInput && $nextHasilInput.length > 0) {
                console.log('Focusing on next input, index:', nextIndex, 'Type:', $nextHasilInput.is('select') ? 'dropdown' : 'editor/input');

                var isUrinalisaSelect = $nextHasilInput.hasClass('urinalisa-positivity-select')
                    || $nextHasilInput.hasClass('urinalisa-detail-input');
                
                // For dropdown, ensure it has options before focusing
                // Skip recovery/recreate untuk urinalisa dual (opsi sudah di Blade, recreate malah dobel tombol)
                if ($nextHasilInput.is('select') && !isUrinalisaSelect) {
                    var optionsCount = $nextHasilInput.find('option').length;
                    var optionsHtml = $nextHasilInput.html();
                    console.log('Dropdown options count before focus:', optionsCount, 'HTML length:', optionsHtml.length);
                    
                    // Store options HTML for recovery
                    $nextHasilInput.data('original-options-html', optionsHtml);
                    
                    if (optionsCount === 0 || optionsHtml.length < 50) { // Less than 50 chars means probably empty
                        console.warn('Dropdown has no options or options seem incomplete! Waiting for options to be populated...');
                        // Wait a bit and try again
                        var self = this;
                        var retryCount = 0;
                        var maxRetries = 5;
                        
                        function retryFocus() {
                            retryCount++;
                            var retryOptions = $nextHasilInput.find('option').length;
                            var retryHtml = $nextHasilInput.html();
                            console.log('Retry', retryCount, '- Options count:', retryOptions, 'HTML length:', retryHtml.length);
                            
                            if (retryOptions > 0 && retryHtml.length > 50) {
                                console.log('Dropdown options now available:', retryOptions);
                                $nextHasilInput.data('original-options-html', retryHtml);
                                self.focusHasilInput($nextHasilInput);
                            } else if (retryCount < maxRetries) {
                                setTimeout(retryFocus, 200);
                            } else {
                                console.error('Dropdown still has no options after', maxRetries, 'retries. Attempting to recreate...');
                                // Try to recreate dropdown
                                var index = $nextHasilInput.data('index');
                                var $td = $nextHasilInput.closest('td');
                                var $textarea = $('#hasil_permohonan_uji_parameter_klinik_' + index + ', #hasil_permohonan_uji_sub_parameter_klinik_' + index + ', #result_method_' + index);
                                if ($textarea.length > 0 && $td.length > 0) {
                                    $nextHasilInput.remove();
                                    self.createHasilInput($td, $textarea);
                                    setTimeout(function() {
                                        var $newSelect = $td.find('select.inline-hasil-input[data-index="' + index + '"]');
                                        if ($newSelect.length > 0 && $newSelect.find('option').length > 0) {
                                            $newSelect.data('original-options-html', $newSelect.html());
                                            self.focusHasilInput($newSelect);
                                        }
                                    }, 300);
                                }
                            }
                        }
                        
                        setTimeout(retryFocus, 200);
                        return;
                    }
                }
                
                this.focusHasilInput($nextHasilInput);
            } else {
                console.warn('Could not find next hasil input after index', currentIndex, 'tried up to index', nextIndex - 1);
            }
        },
        
        /**
         * Focus on hasil input field
         */
        focusHasilInput: function($nextHasilInput) {
            if (!$nextHasilInput || $nextHasilInput.length === 0) {
                return;
            }
            
            // Scroll ke elemen di dalam container tabel dengan smooth behavior
            var $container = $('#tableParameterResponsive');
            if ($container.length && $nextHasilInput[0]) {
                var currentScroll = $container.scrollTop();
                var targetTop = $nextHasilInput.offset().top - $container.offset().top + currentScroll - 100;
                // Native smooth scroll lebih halus dari jQuery animate
                $container[0].scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
            }
            
            // Dropdown perlu delay untuk memastikan options sudah ter-render
            if ($nextHasilInput.is('select')) {
                var self = this;

                // Urinalisa dual: fokus langsung, jangan recreate (bisa menggandakan Baku Mutu)
                if ($nextHasilInput.hasClass('urinalisa-positivity-select')
                    || $nextHasilInput.hasClass('urinalisa-detail-input')) {
                    try {
                        $nextHasilInput.focus({ preventScroll: true });
                    } catch (e) {
                        $nextHasilInput.focus();
                    }
                    return;
                }

                setTimeout(function() {
                    try {
                        var $select = $nextHasilInput;
                        
                        // Re-check if dropdown still exists and is visible
                        if ($select.length === 0 || !$select.is(':visible')) {
                            console.warn('Dropdown disappeared or not visible');
                            return;
                        }
                        
                        // Check options again (in case they were populated after initial check)
                        var optionsCount = $select.find('option').length;
                        console.log('Focusing dropdown with index:', $select.data('index'), 'Options count:', optionsCount);
                        
                        if (optionsCount === 0) {
                            console.error('Dropdown still has no options after wait!');
                            // Try to find the textarea and recreate dropdown
                            var index = $select.data('index');
                            var $textarea = $('#hasil_permohonan_uji_parameter_klinik_' + index + ', #hasil_permohonan_uji_sub_parameter_klinik_' + index + ', #result_method_' + index);
                            if ($textarea.length > 0) {
                                var $td = $select.closest('td');
                                if ($td.length > 0) {
                                    console.log('Attempting to recreate dropdown for index:', index);
                                    // Remove existing select
                                    $select.remove();
                                    // Recreate
                                    self.createHasilInput($td, $textarea);
                                    // Wait and try to focus again
                                    setTimeout(function() {
                                        var $newSelect = $td.find('select.inline-hasil-input[data-index="' + index + '"]');
                                        if ($newSelect.length > 0 && $newSelect.find('option').length > 0) {
                                            console.log('Recreated dropdown, focusing...');
                                            $newSelect.focus({ preventScroll: true });
                                        }
                                    }, 200);
                                }
                            }
                            return;
                        }
                        
                        // Store complete options HTML and current value before focusing
                        var optionsHtml = $select.html();
                        var currentValue = $select.val();
                        var selectIndex = $select.data('index');
                        
                        console.log('Storing dropdown state before focus:', {
                            index: selectIndex,
                            value: currentValue,
                            optionsCount: optionsCount,
                            optionsHtmlLength: optionsHtml.length
                        });
                        
                        // Store options data for recovery - make a deep copy to prevent reference issues
                        var optionsData = {
                            html: optionsHtml, // Store the full HTML
                            value: currentValue,
                            count: optionsCount,
                            timestamp: Date.now() // Add timestamp to track when backup was made
                        };
                        
                        // Also store in original-options-html for compatibility
                        if (!$select.data('original-options-html')) {
                            $select.data('original-options-html', optionsHtml);
                        }
                        
                        // Set a flag on the select element to track if we're monitoring it
                        $select.data('options-backup', optionsData);
                        
                        // Prevent any change events from clearing options during focus
                        $select.data('focusing', true);
                        
                        // Use requestAnimationFrame to ensure DOM is ready before focus
                        requestAnimationFrame(function() {
                            // Double-check options are still there before focusing
                            var optionsBeforeFocus = $select.find('option').length;
                            var htmlBeforeFocus = $select.html();
                            
                            if (optionsBeforeFocus === 0 || htmlBeforeFocus.length < optionsData.html.length * 0.5) {
                                console.error('Options disappeared before focus! Restoring...', {
                                    expected: { count: optionsData.count, htmlLength: optionsData.html.length },
                                    actual: { count: optionsBeforeFocus, htmlLength: htmlBeforeFocus.length }
                                });
                                $select.html(optionsData.html);
                                if (optionsData.value) {
                                    $select.val(optionsData.value);
                                }
                            }
                            
                            // Focus using native method to avoid jQuery side effects
                            // preventScroll: true agar tidak memicu scroll tambahan (scroll sudah ditangani di atas)
                            try {
                            if ($select[0] && typeof $select[0].focus === 'function') {
                                $select[0].focus({ preventScroll: true });
                                console.log('Focused dropdown (native method)');
                            } else {
                                $select.focus();
                                console.log('Focused dropdown (jQuery method)');
                                }
                            } catch(e) {
                                console.error('Error focusing dropdown:', e);
                            }
                            
                            // Set up monitoring to restore options if they disappear
                            var monitorInterval = setInterval(function() {
                                if ($select.length === 0 || !$select.is(':visible')) {
                                    clearInterval(monitorInterval);
                                    $select.removeData('focusing');
                                    return;
                                }
                                
                                var currentOptionsCount = $select.find('option').length;
                                var currentHtml = $select.html();
                                var backup = $select.data('options-backup');
                                var originalHtml = $select.data('original-options-html');
                                
                                // Use backup HTML if available, otherwise use original
                                var restoreHtml = backup ? backup.html : (originalHtml || '');
                                
                                // Check if options were cleared (more lenient threshold)
                                if (restoreHtml && restoreHtml.length > 50 && 
                                    (currentOptionsCount === 0 || currentHtml.length < restoreHtml.length * 0.3)) {
                                    console.error('Dropdown options disappeared during monitoring! Restoring...', {
                                        current: { count: currentOptionsCount, htmlLength: currentHtml.length },
                                        backup: { count: backup ? backup.count : 'N/A', htmlLength: restoreHtml.length }
                                    });
                                    // Save current selection before restoring HTML
                                    var currentSelVal = $select.val();
                                    $select.html(restoreHtml);
                                    // Prefer current selection over backup (user may have already selected)
                                    if (currentSelVal) {
                                        $select.val(currentSelVal);
                                    } else if (backup && backup.value) {
                                        $select.val(backup.value);
                                    }
                                }
                            }, 50); // Check every 50ms for faster recovery
                            
                            // Stop monitoring after 5 seconds (increased from 3)
                            setTimeout(function() {
                                clearInterval(monitorInterval);
                                $select.removeData('options-backup');
                                $select.removeData('focusing');
                            }, 5000);
                            
                            // Verify immediately after focus
                            requestAnimationFrame(function() {
                                var valueAfter = $select.val();
                                var optionsAfter = $select.find('option').length;
                                var optionsHtmlAfter = $select.html();
                                
                                if (optionsAfter === 0 || (optionsData.html.length > 50 && optionsHtmlAfter.length < optionsData.html.length * 0.3)) {
                                    console.error('Dropdown options disappeared after focus! Restoring...', {
                                        before: { count: optionsData.count, htmlLength: optionsData.html.length },
                                        after: { count: optionsAfter, htmlLength: optionsHtmlAfter.length }
                                    });
                                    // Restore options HTML
                                    $select.html(optionsData.html);
                                    // Restore value - prefer current value (user may have selected)
                                    if (valueAfter) {
                                        $select.val(valueAfter);
                                    } else if (optionsData.value) {
                                        $select.val(optionsData.value);
                                    }
                                } else {
                                    // Options intact - do NOT restore value, let user's selection stand
                                    console.log('Dropdown focus successful, options intact');
                                }
                            });
                        });
                    } catch(e) {
                        console.error('Error in dropdown focus handler:', e);
                    }
                }, 200); // Wait 200ms to ensure dropdown is ready
            } else {
                // Non-dropdown: focus segera di frame berikutnya (tidak ada delay nyata)
                // Ini mencegah "blink" jeda highlight antar baris saat navigasi Enter
                var self = this;
                requestAnimationFrame(function() {
                    if ($nextHasilInput.attr('contenteditable') === 'true') {
                        // Check if it's a TinyMCE editor
                        var editorId = $nextHasilInput.attr('id');
                        if (editorId && typeof tinymce !== 'undefined') {
                            try {
                                var editor = tinymce.get(editorId);
                                // More thorough check: ensure editor exists, is initialized, and has required methods
                                if (editor && 
                                    typeof editor.getContent === 'function' && 
                                    typeof editor.setContent === 'function' && 
                                    typeof editor.focus === 'function' &&
                                    !editor.removed) {
                                    // Clean empty <p> tags before focusing
                                    try {
                                        var content = editor.getContent();
                                        if (content && (content.trim() === '<p><br data-mce-bogus="1"></p>' || content.trim() === '<p><br></p>' || content.trim() === '<p></p>')) {
                                            editor.setContent('');
                                        }
                                        // Focus editor
                                        editor.focus();
                                        // Don't call setCursorAtEnd for TinyMCE as it has its own cursor management
                                    } catch(e) {
                                        console.warn('Error accessing TinyMCE editor methods:', e);
                                        // Editor might be in invalid state, fallback to regular contenteditable
                                        self.setCursorAtEnd($nextHasilInput[0]);
                                    }
                                } else {
                                    // TinyMCE not initialized yet or editor is invalid, use regular contenteditable
                                    self.setCursorAtEnd($nextHasilInput[0]);
                                }
                            } catch(e) {
                                console.warn('Error accessing TinyMCE editor:', e);
                                // Fallback to regular contenteditable
                                self.setCursorAtEnd($nextHasilInput[0]);
                            }
                        } else {
                            // Regular contenteditable - preventScroll agar tidak memicu scroll ganda
                            if ($nextHasilInput[0]) {
                                $nextHasilInput[0].focus({ preventScroll: true });
                            } else {
                                $nextHasilInput.focus();
                            }
                            self.setCursorAtEnd($nextHasilInput[0]);
                        }
                    } else {
                        // For input/select, select all text - preventScroll agar tidak memicu scroll ganda
                        if ($nextHasilInput.is('input[type="text"]')) {
                            if ($nextHasilInput[0]) {
                                $nextHasilInput[0].focus({ preventScroll: true });
                            } else {
                                $nextHasilInput.focus();
                            }
                            $nextHasilInput.select();
                        } else {
                            // Fallback: just focus
                            if ($nextHasilInput[0]) {
                                $nextHasilInput[0].focus({ preventScroll: true });
                            } else {
                                $nextHasilInput.focus();
                            }
                        }
                    }
                });
            }
        },

        /**
         * Navigate to next "Keterangan" field (same column, next row)
         * Skip header rows (th, colspan rows) and find next valid input field
         */
        navigateToNextKeterangan: function($current) {
            var $currentTd = $current.closest('td');
            var $currentTr = $currentTd.closest('tr');
            var $allRows = $currentTr.nextAll('tr');
            var $nextTr = null;
            
            // Find next row that has a valid keterangan input field
            // Skip rows with th (header jenis sampel) or colspan (header parameter dengan sub-parameter)
            for (var i = 0; i < $allRows.length; i++) {
                var $row = $($allRows[i]);
                
                // Skip header rows: rows with <th> or <td colspan>
                var hasTh = $row.find('th').length > 0;
                var hasColspan = $row.find('td[colspan]').length > 0;
                
                if (hasTh || hasColspan) {
                    continue; // Skip this row
                }
                
                // Check if this row has a valid keterangan input field
                var $keteranganTd = $row.find('td').eq(3); // Keterangan di kolom ke-4 (index 3)
                var $keteranganInput = $keteranganTd.find('.' + this.settings.keteranganEditorClass);
                
                if ($keteranganInput.length > 0) {
                    $nextTr = $row;
                    break; // Found valid row
                }
            }

            if ($nextTr && $nextTr.length) {
                // Keterangan ada di kolom ke-4 (index 3)
                var $nextKeteranganTd = $nextTr.find('td').eq(3);
                var $nextKeteranganInput = $nextKeteranganTd.find('.' + this.settings.keteranganEditorClass);
                if ($nextKeteranganInput.length) {
                    // Scroll ke elemen di dalam container tabel dengan smooth behavior
                    var $container = $('#tableParameterResponsive');
                    if ($container.length && $nextKeteranganInput[0]) {
                        var currentScroll = $container.scrollTop();
                        var targetTop = $nextKeteranganInput.offset().top - $container.offset().top + currentScroll - 100;
                        $container[0].scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                    }
                    
                    // Focus segera di frame berikutnya untuk menghindari blink jeda highlight antar baris
                    var selfKet = this;
                    requestAnimationFrame(function() {
                        if ($nextKeteranganInput[0]) {
                            $nextKeteranganInput[0].focus({ preventScroll: true });
                        } else {
                            $nextKeteranganInput.focus();
                        }
                        if ($nextKeteranganInput.attr('contenteditable') === 'true') {
                            selfKet.setCursorAtEnd($nextKeteranganInput[0]);
                        }
                    });
                }
            }
        },

        /**
         * Navigate to next "Hasil" from "Keterangan" (next row, hasil column)
         * Skip header rows (th, colspan rows) and find next valid input field
         */
        navigateToNextHasilFromKeterangan: function($current) {
            var $currentTd = $current.closest('td');
            var $currentTr = $currentTd.closest('tr');
            var $allRows = $currentTr.nextAll('tr');
            var $nextTr = null;
            
            // Find next row that has a valid hasil input field
            // Skip rows with th (header jenis sampel) or colspan (header parameter dengan sub-parameter)
            for (var i = 0; i < $allRows.length; i++) {
                var $row = $($allRows[i]);
                
                // Skip header rows: rows with <th> or <td colspan>
                var hasTh = $row.find('th').length > 0;
                var hasColspan = $row.find('td[colspan]').length > 0;
                
                if (hasTh || hasColspan) {
                    continue; // Skip this row
                }
                
                // Check if this row has a valid hasil input field
                var $hasilTd = $row.find('td').eq(1); // Hasil di kolom ke-2 (index 1)
                var $hasilInput = $hasilTd.find('.inline-hasil-input, .inline-hasil-editor');
                
                if ($hasilInput.length > 0) {
                    $nextTr = $row;
                    break; // Found valid row
                }
            }

            if ($nextTr && $nextTr.length) {
                // Hasil ada di kolom ke-2 (index 1)
                var $nextHasilTd = $nextTr.find('td').eq(1);
                var $nextHasilInput = $nextHasilTd.find('.inline-hasil-input, .inline-hasil-editor');
                if ($nextHasilInput.length) {
                    // Scroll ke elemen di dalam container tabel dengan smooth behavior
                    var $container = $('#tableParameterResponsive');
                    if ($container.length && $nextHasilInput[0]) {
                        var currentScroll = $container.scrollTop();
                        var targetTop = $nextHasilInput.offset().top - $container.offset().top + currentScroll - 100;
                        $container[0].scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                    }
                    
                    // Focus segera di frame berikutnya untuk menghindari blink jeda highlight antar baris
                    var selfFromKet = this;
                    requestAnimationFrame(function() {
                        if ($nextHasilInput[0]) {
                            $nextHasilInput[0].focus({ preventScroll: true });
                        } else {
                            $nextHasilInput.focus();
                        }
                        if ($nextHasilInput.attr('contenteditable') === 'true') {
                            selfFromKet.setCursorAtEnd($nextHasilInput[0]);
                        } else {
                            // For input/select, select all text
                            if ($nextHasilInput.is('input[type="text"]')) {
                                $nextHasilInput.select();
                            }
                        }
                    });
                }
            }
        },

        /**
         * Set cursor at end of contenteditable element
         */
        setCursorAtEnd: function(element) {
            if (!element) return;
            try {
                var range, selection;
                if (document.createRange) {
                    range = document.createRange();
                    range.selectNodeContents(element);
                    range.collapse(false);
                    selection = window.getSelection();
                    if (selection) {
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                }
            } catch(e) {
                console.warn('Error setting cursor at end:', e);
            }
        },

        /**
         * Navigate vertically (up/down) in same column
         */
        navigateVertical: function($current, direction) {
            var $currentTd = $current.closest('td');
            var colIndex = $currentTd.index();
            var $currentTr = $currentTd.closest('tr');
            var $allRows;
            var $targetTr = null;

            if (direction === 'down') {
                $allRows = $currentTr.nextAll('tr');
            } else {
                $allRows = $currentTr.prevAll('tr');
            }

            // Find next/previous row that has a valid input field
            // Skip rows with th (header jenis sampel) or colspan (header parameter dengan sub-parameter)
            for (var i = 0; i < $allRows.length; i++) {
                var $row = $($allRows[i]);
                
                // Skip header rows: rows with <th> or <td colspan>
                var hasTh = $row.find('th').length > 0;
                var hasColspan = $row.find('td[colspan]').length > 0;
                
                if (hasTh || hasColspan) {
                    continue; // Skip this row
                }
                
                // Check if this row has a valid input field in the same column
                var $targetTd = $row.find('td').eq(colIndex);
                var $targetInput = $targetTd.find('.' + this.settings.hasilInputClass + ', .inline-hasil-editor, .' + this.settings.keteranganEditorClass);
                
                if ($targetInput.length > 0) {
                    $targetTr = $row;
                    break; // Found valid row
                }
            }

            if ($targetTr && $targetTr.length) {
                var $targetTd = $targetTr.find('td').eq(colIndex);
                var $targetInput = $targetTd.find('.' + this.settings.hasilInputClass + ', .inline-hasil-editor, .' + this.settings.keteranganEditorClass);
                if ($targetInput.length) {
                    // Scroll di dalam container tabel dengan smooth behavior
                    var $containerV = $('#tableParameterResponsive');
                    if ($containerV.length && $targetInput[0]) {
                        var currentScrollV = $containerV.scrollTop();
                        var targetTopV = $targetInput.offset().top - $containerV.offset().top + currentScrollV - 100;
                        $containerV[0].scrollTo({ top: Math.max(0, targetTopV), behavior: 'smooth' });
                    }
                    
                    // Focus segera di frame berikutnya untuk menghindari blink jeda highlight antar baris
                    var selfV = this;
                    requestAnimationFrame(function() {
                        if ($targetInput[0]) {
                            $targetInput[0].focus({ preventScroll: true });
                        } else {
                            $targetInput.focus();
                        }
                        // Set cursor at end for contenteditable
                        if ($targetInput.attr('contenteditable') === 'true') {
                            selfV.setCursorAtEnd($targetInput[0]);
                        } else if ($targetInput.is('input[type="text"]')) {
                            $targetInput.select();
                        }
                    });
                }
            }
        },

        /**
         * Convert superscript notation to HTML superscript tags
         * Uses the same logic as convertToTinyMCE from baku-mutu/add.blade.php
         * Converts ^(1) to <sup>1</sup> and _(1) to <sub>1</sub>
         * Also removes <p> tags and converts special characters
         */
        /**
         * Textarea menyimpan baris sebagai "\n"; di HTML itu hanya jadi spasi.
         */
        newlinesToBr: function(value) {
            if (value === null || value === undefined) return '';
            return String(value).replace(/\r\n|\r|\n/g, '<br>');
        },

        convertSuperscriptToHtml: function(value) {
            if (!value) return '';
            
            var str = String(value);
            
            // Blok <p>/<div> adalah pindah baris — jadikan <br> dulu supaya tidak hilang
            str = str.replace(/<\/p>\s*<p[^>]*>/gi, '<br>');
            str = str.replace(/<\/div>\s*<div[^>]*>/gi, '<br>');
            str = str.replace(/<p[^>]*>/gi, '');
            str = str.replace(/<\/p>/gi, '');
            str = str.replace(/<div[^>]*>/gi, '');
            str = str.replace(/<\/div>/gi, '');
            
            // Convert Unicode superscript characters to <sup> tags FIRST
            // This handles characters like ³, ², ¹, etc.
            str = str.replace(/¹/g, '<sup>1</sup>');
            str = str.replace(/²/g, '<sup>2</sup>');
            str = str.replace(/³/g, '<sup>3</sup>');
            str = str.replace(/⁴/g, '<sup>4</sup>');
            str = str.replace(/⁵/g, '<sup>5</sup>');
            str = str.replace(/⁶/g, '<sup>6</sup>');
            str = str.replace(/⁷/g, '<sup>7</sup>');
            str = str.replace(/⁸/g, '<sup>8</sup>');
            str = str.replace(/⁹/g, '<sup>9</sup>');
            str = str.replace(/⁰/g, '<sup>0</sup>');
            
            // If already contains HTML sup/sub tags, return as is (but cleaned of <p> tags)
            // But still check for any remaining ^( or _( notation that might be mixed in
            if (str.includes('<sup') || str.includes('<sub')) {
                // Still convert any remaining ^( or _( notation that might be in the text
                str = str.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
                str = str.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
                // Also handle format without parentheses
                str = str.replace(/\^(\d+)/g, '<sup>$1</sup>');
                str = str.replace(/\_(\d+)/g, '<sub>$1</sub>');
                return str;
            }
            
            // Use the same conversion logic as convertToTinyMCE from add.blade.php
            // Convert special characters
            str = str.replace(/≤/g, '&le;');
            str = str.replace(/≥/g, '&ge;');
            str = str.replace(/±/g, '&plusmn;');
            
            // Convert superscript notation: ^(content) to <sup>content</sup>
            // IMPORTANT: $1 captures only the content inside parentheses, not the parentheses themselves
            str = str.replace(/\^\(([^\)]*)\)/g, '<sup>$1</sup>');
            
            // Also handle format without parentheses: ^2 to <sup>2</sup>
            // But only if not already part of ^( format
            str = str.replace(/\^(\d+)/g, '<sup>$1</sup>');
            
            // Convert subscript notation: _(content) to <sub>content</sub>
            str = str.replace(/\_\(([^\)]*)\)/g, '<sub>$1</sub>');
            
            // Also handle format without parentheses: _2 to <sub>2</sub>
            str = str.replace(/\_(\d+)/g, '<sub>$1</sub>');
            
            return str;
        },

        /**
         * Extract numeric value from string, handling superscripts
         * For validation, we extract only the base numeric value before superscript
         * e.g., "12¹2" -> extract "12" (not "1212")
         * e.g., "12^(1)2" -> extract "12" (not "1212")
         */
        extractNumericWithSuperscript: function(value) {
            if (!value) return null;
            
            // Convert to string
            var str = String(value);
            
            // Remove <p> tags first
            str = str.replace(/<p[^>]*>/gi, '');
            str = str.replace(/<\/p>/gi, '');
            
            // Handle HTML superscript tags: extract number before <sup>
            // Pattern: "12<sup>1</sup>2" -> extract "12"
            // Also handle cases with whitespace: "12 <sup>1</sup>2"
            var htmlMatch = str.match(/^(\d+[.,]?\d*)\s*<sup[^>]*>/i);
            if (htmlMatch) {
                var numStr = htmlMatch[1].replace(/,/g, '').replace(/\./g, '.');
                var result = parseFloat(numStr);
                console.log('HTML superscript match - Input:', str, 'Extracted:', result);
                return result;
            }
            
            // Handle superscript notation: ^(1) or ^1
            // Pattern: "12^(1)2" or "12^12" -> extract "12" (number before ^)
            // Also handle cases like "12^(1)2" where we want to extract "12"
            var caretMatch = str.match(/^(\d+[.,]?\d*)\s*\^/);
            if (caretMatch) {
                var numStr = caretMatch[1].replace(/,/g, '');
                return parseFloat(numStr);
            }
            
            // Handle Unicode superscript characters: ¹²³⁴⁵⁶⁷⁸⁹⁰
            // Pattern: "12¹2" -> extract "12" (number before superscript)
            var unicodeMatch = str.match(/^(\d+[.,]?\d*)\s*[¹²³⁴⁵⁶⁷⁸⁹⁰]/);
            if (unicodeMatch) {
                var numStr = unicodeMatch[1].replace(/,/g, '');
                return parseFloat(numStr);
            }
            
            // If no superscript pattern found, try normal parsing
            // Remove HTML tags first
            var cleaned = str.replace(/<[^>]*>/g, '');
            // Remove superscript unicode characters
            cleaned = cleaned.replace(/[¹²³⁴⁵⁶⁷⁸⁹⁰]/g, '');
            // Remove superscript notation ^(digit) or ^digit
            cleaned = cleaned.replace(/\^\(?\d+\)?/g, '');
            // Extract first numeric sequence
            var numericMatch = cleaned.match(/^(\d+[.,]?\d*)/);
            if (numericMatch) {
                var numStr = numericMatch[1].replace(/,/g, '');
                return parseFloat(numStr);
            }
            
            return null;
        },

        /**
         * Update result badge based on baku mutu
         */
        updateResultBadge: function(index, value, min, max, equal, numberFormat) {
            if (!value) {
                $('#badge_' + index).html('');
                return;
            }

            // Try to get original value from editor or textarea for display
            var valueOriginal = value;
            var normalizedValueForMatch = normalizeForComparison(value);
            var $badgeContainer = $('#badge_' + index);
            var $row = $badgeContainer.closest('tr');
            
            // Try to get from TinyMCE editor first
            var $editor = $row.find('.inline-hasil-editor[data-index="' + index + '"]');
            if ($editor.length > 0) {
                var editorId = $editor.attr('id');
                if (editorId && typeof tinymce !== 'undefined') {
                    try {
                        var editor = tinymce.get(editorId);
                        if (editor && typeof editor.getContent === 'function' && !editor.removed) {
                            // Get HTML content to preserve superscript/subscript formatting
                            var editorContent = editor.getContent();
                            if (editorContent && editorContent.trim() !== '') {
                                valueOriginal = editorContent;
                            } else {
                                // Editor is ready but empty, fallback to textarea
                                var $textarea = $row.find('textarea.result_method_klinik[data-index="' + index + '"], textarea#result_method_' + index);
                                if ($textarea.length > 0) {
                                    var textareaValue = $textarea.val();
                                    if (textareaValue && textareaValue.trim() !== '') {
                                        valueOriginal = textareaValue;
                                        // Convert ^( notation to HTML
                                        if (!valueOriginal.includes('<sup') && !valueOriginal.includes('<sub') && 
                                            (valueOriginal.includes('^(') || valueOriginal.includes('^'))) {
                                            valueOriginal = this.convertSuperscriptToHtml(valueOriginal);
                                        }
                                    }
                                }
                            }
                        } else {
                            // Editor not ready yet, fallback to textarea
                            var $textarea = $row.find('textarea.result_method_klinik[data-index="' + index + '"], textarea#result_method_' + index);
                            if ($textarea.length > 0) {
                                var textareaValue = $textarea.val();
                                if (textareaValue && textareaValue.trim() !== '') {
                                    valueOriginal = textareaValue;
                                    // Convert ^( notation to HTML
                                    if (!valueOriginal.includes('<sup') && !valueOriginal.includes('<sub') && 
                                        (valueOriginal.includes('^(') || valueOriginal.includes('^'))) {
                                        valueOriginal = this.convertSuperscriptToHtml(valueOriginal);
                                    }
                                } else {
                                    // Fallback to editor HTML
                                    valueOriginal = $editor.html() || value;
                                }
                            } else {
                                // Fallback to editor HTML
                                valueOriginal = $editor.html() || value;
                            }
                        }
                    } catch(e) {
                        // Error getting editor, fallback to textarea
                        var $textarea = $row.find('textarea.result_method_klinik[data-index="' + index + '"], textarea#result_method_' + index);
                        if ($textarea.length > 0) {
                            var textareaValue = $textarea.val();
                            if (textareaValue && textareaValue.trim() !== '') {
                                valueOriginal = textareaValue;
                                // Convert ^( notation to HTML
                                if (!valueOriginal.includes('<sup') && !valueOriginal.includes('<sub') && 
                                    (valueOriginal.includes('^(') || valueOriginal.includes('^'))) {
                                    valueOriginal = this.convertSuperscriptToHtml(valueOriginal);
                                }
                            } else {
                                // Fallback to editor HTML
                        valueOriginal = $editor.html() || value;
                    }
                } else {
                            // Fallback to editor HTML
                    valueOriginal = $editor.html() || value;
                        }
                    }
                } else {
                    // TinyMCE not loaded yet, use textarea
                    var $textarea = $row.find('textarea.result_method_klinik[data-index="' + index + '"], textarea#result_method_' + index);
                    if ($textarea.length > 0) {
                        var textareaValue = $textarea.val();
                        if (textareaValue && textareaValue.trim() !== '') {
                            valueOriginal = textareaValue;
                            // Convert ^( notation to HTML
                            if (!valueOriginal.includes('<sup') && !valueOriginal.includes('<sub') && 
                                (valueOriginal.includes('^(') || valueOriginal.includes('^'))) {
                                valueOriginal = this.convertSuperscriptToHtml(valueOriginal);
                            }
                        } else {
                            // Fallback to editor HTML
                            valueOriginal = $editor.html() || value;
                        }
                    } else {
                        // Fallback to editor HTML
                        valueOriginal = $editor.html() || value;
                    }
                }
            } else {
                // Try to get from dropdown
                var $dropdown = $row.find('.inline-hasil-input[data-index="' + index + '"]');
                if ($dropdown.length > 0 && $dropdown.is('select')) {
                    // Find option where normalized value matches
                    var foundOption = null;
                    $dropdown.find('option').each(function() {
                        var optValue = $(this).val();
                        var optText = $(this).text();
                        if (optValue && normalizeForComparison(optValue) === normalizedValueForMatch) {
                            foundOption = optText; // Use text (original with spaces)
                            return false; // break
                        }
                    });
                    
                    if (foundOption && foundOption.trim() !== '- Pilih -') {
                        valueOriginal = foundOption.trim();
                    } else {
                        // Fallback: get selected option text
                        var selectedOption = $dropdown.find('option:selected');
                        if (selectedOption.length > 0) {
                            var optionText = selectedOption.text().trim();
                            if (optionText && optionText !== '- Pilih -') {
                                valueOriginal = optionText;
                            }
                        }
                    }
                } else {
                    // Try to get from textarea
                    var $textarea = $row.find('textarea.result_method_klinik[data-index="' + index + '"], textarea#result_method_' + index);
                    if ($textarea.length > 0) {
                        var textareaValue = $textarea.val();
                        if (textareaValue && textareaValue.trim() !== '') {
                            // Use textarea value directly (may already contain HTML from rubahNilaikeForm)
                            valueOriginal = textareaValue;
                            // If value doesn't contain HTML tags but contains ^( notation, convert it
                            if (!valueOriginal.includes('<sup') && !valueOriginal.includes('<sub') && 
                                (valueOriginal.includes('^(') || valueOriginal.includes('^'))) {
                                valueOriginal = this.convertSuperscriptToHtml(valueOriginal);
                            }
                        }
                    }
                }
            }
            
            // Final fallback: if value contains spaces, use it as original
            if ((valueOriginal === value || !valueOriginal) && value && value.toString().indexOf(' ') !== -1) {
                valueOriginal = value;
            }
            
            // CRITICAL: Final conversion check - ensure valueOriginal is in HTML format
            // This catches any edge cases where conversion might have been missed
            if (valueOriginal && !valueOriginal.includes('<sup') && !valueOriginal.includes('<sub') && 
                (valueOriginal.includes('^(') || valueOriginal.includes('^'))) {
                valueOriginal = this.convertSuperscriptToHtml(valueOriginal);
            }
            
            // Store original equal for display
            var equalOriginal = equal;

            // Normalize equal - remove all whitespace (spaces, newlines, tabs, etc.) and decode HTML entities
            if (equal && equal !== '') {
                equal = normalizeForComparison(equal);
            }

            // Get offset_baku_mutu value (manual override)
            var offsetBakuMutu = 'default';
            var $badgeContainer = $('#badge_' + index);
            var $row = $badgeContainer.closest('tr');
            
            // Determine if sub or param first to get correct ID
            var textareaId = $row.find('textarea.result_method_klinik').attr('id') || '';
            var isSub = textareaId.includes('sub_parameter');
            
            // Try to find offset input with correct ID based on isSub
            var offsetInputId;
            if (isSub) {
                offsetInputId = 'offset_baku_mutu_sub_' + index;
            } else {
                offsetInputId = 'offset_baku_mutu_param_' + index;
            }
            
            var $offsetInput = $('#' + offsetInputId);
            if ($offsetInput.length === 0) {
                // Try alternative: search in row
                $offsetInput = $row.find('input[id="' + offsetInputId + '"]');
            }
            if ($offsetInput.length === 0) {
                // Try with starts with selector
                $offsetInput = $row.find('input[id^="offset_baku_mutu_sub_' + index + '"], input[id^="offset_baku_mutu_param_' + index + '"]');
            }
            if ($offsetInput.length === 0) {
                // Try baca-hasil format: offset_baku_mutu_{index}
                $offsetInput = $row.find('input[id="offset_baku_mutu_' + index + '"]');
            }
            if ($offsetInput.length === 0) {
                // Try alternative search by name
                $offsetInput = $row.find('input[name*="offset_baku_mutu"]');
            }
            if ($offsetInput.length > 0) {
                offsetBakuMutu = String($offsetInput.val() || 'default').trim();
            }

            // Normalize offset value - ensure it's a string and lowercase for consistent comparison
            offsetBakuMutu = String(offsetBakuMutu || 'default').trim().toLowerCase();
            if (offsetBakuMutu !== 'true' && offsetBakuMutu !== 'false') {
                offsetBakuMutu = 'default';
            }

            // Get multiple baku mutu and kesimpulan if available
            var multipleBakuMutu = null;
            var kesimpulanBakuMutu = '';
            
            if (isSub) {
                var $multipleDiv = $row.find('#result_output_sub_' + index);
                if ($multipleDiv.length > 0) {
                    var multipleData = $multipleDiv.data('multiple-baku-mutu');
                    if (multipleData) {
                        try {
                            multipleBakuMutu = typeof multipleData === 'string' ? JSON.parse(multipleData) : multipleData;
                        } catch(e) {
                            multipleBakuMutu = null;
                        }
                    }
                }
                var $kesimpulanInput = $row.find('#kesimpulan_baku_mutu_sub_' + index);
                if ($kesimpulanInput.length > 0) {
                    kesimpulanBakuMutu = $kesimpulanInput.val() || '';
                }
            } else {
                var $multipleDiv = $row.find('#result_output_param_' + index);
                if ($multipleDiv.length > 0) {
                    var multipleData = $multipleDiv.data('multiple-baku-mutu');
                    if (multipleData) {
                        try {
                            multipleBakuMutu = typeof multipleData === 'string' ? JSON.parse(multipleData) : multipleData;
                        } catch(e) {
                            multipleBakuMutu = null;
                        }
                    }
                }
                var $kesimpulanInput = $row.find('#kesimpulan_baku_mutu_param_' + index);
                if ($kesimpulanInput.length > 0) {
                    kesimpulanBakuMutu = $kesimpulanInput.val() || '';
                }
            }

            // Use global checkBakuMutu function if available (more complete)
            if (typeof window.checkBakuMutu === 'function') {
                // Ensure offset is properly normalized (already normalized above)
                var normalizedOffset = offsetBakuMutu;
                // Ensure kesimpulanBakuMutu is not undefined
                var kesimpulanValue = kesimpulanBakuMutu || '';
                if (kesimpulanValue === undefined || kesimpulanValue === null) {
                    kesimpulanValue = '';
                }
                
                // CRITICAL: Ensure valueOriginal is in HTML format before sending to checkBakuMutu
                // Convert ^( notation to HTML if not already converted
                var valueForBadge = valueOriginal || value;
                
                // Always check and convert if needed (defensive programming)
                if (valueForBadge) {
                    // If value doesn't contain HTML tags but contains ^( notation, convert it
                    if (!valueForBadge.includes('<sup') && !valueForBadge.includes('<sub') && 
                        (valueForBadge.includes('^(') || valueForBadge.includes('^'))) {
                        valueForBadge = this.convertSuperscriptToHtml(valueForBadge);
                    }
                    // Also check if value parameter needs conversion (fallback)
                    if (valueForBadge === value && !value.includes('<sup') && !value.includes('<sub') && 
                        (value.includes('^(') || value.includes('^'))) {
                        valueForBadge = this.convertSuperscriptToHtml(value);
                    }
                }
                
                // Use valueForBadge (guaranteed to be HTML format) for display in badge
                // checkBakuMutu will normalize internally for comparison but use HTML for display
                var $nilaiBmTa = $row.find('textarea.result_method_klinik[data-index="' + index + '"]');
                var $taForBm = ($nilaiBmTa.length ? $nilaiBmTa : $row.find('textarea.result_method').first());
                var nilaiBmForCheck = ($taForBm.attr('data-nilai-baku-mutu') || '').trim();
                var nilaiBmFromDisplay = '';
                if ($taForBm.length) {
                    var _tid = $taForBm.attr('id') || '';
                    if (_tid.indexOf('result_method_') === 0) {
                        var _mid = _tid.replace('result_method_', '');
                        var $disp = $('#nilai_baku_mutu_display_' + _mid);
                        if ($disp.length) {
                            nilaiBmFromDisplay = $disp.text().replace(/\s+/g, ' ').trim();
                        }
                    }
                }
                if (nilaiBmFromDisplay && /[<>≤≥]/.test(nilaiBmForCheck) === false && /[<>≤≥]/.test(nilaiBmFromDisplay)) {
                    nilaiBmForCheck = nilaiBmFromDisplay;
                } else if (!nilaiBmForCheck && nilaiBmFromDisplay) {
                    nilaiBmForCheck = nilaiBmFromDisplay;
                }
                var badgeHtml = window.checkBakuMutu(valueForBadge, min, max, equalOriginal || equal, normalizedOffset, multipleBakuMutu, kesimpulanValue, numberFormat, nilaiBmForCheck);
                if (badgeHtml && badgeHtml !== 'undefined' && badgeHtml !== '') {
                    // Clean up any "undefined" strings in the HTML
                    badgeHtml = badgeHtml.replace(/undefined/g, '');
                    
                    // Tambahkan notifikasi pengulangan jika ada
                    var historyCount = 0;
                    if (isSub) {
                        var $multipleDiv = $row.find('#result_output_sub_' + index);
                        if ($multipleDiv.length > 0) {
                            historyCount = parseInt($multipleDiv.data('history-count') || 0);
                        }
                    } else {
                        var $multipleDiv = $row.find('#result_output_param_' + index);
                        if ($multipleDiv.length > 0) {
                            historyCount = parseInt($multipleDiv.data('history-count') || 0);
                        }
                    }
                    
                    // Untuk tampilan analis, indikator pengulangan cukup melalui badge kecil di tombol repeat
                    // (melalui data-history-count -> data-repeat-count). Tidak perlu menambah badge teks di bawah hasil.
                    
                    // Update badge container if exists
                    var $badgeContainer = $('#badge_' + index);
                    if ($badgeContainer.length > 0) {
                        $badgeContainer.html(badgeHtml);
                    }
                    
                    // Also update result_display to ensure consistency (for verification page)
                    var $resultDisplay;
                    if (isSub) {
                        $resultDisplay = $('#result_display_sub_' + index);
                    } else {
                        $resultDisplay = $('#result_display_param_' + index);
                    }
                    if ($resultDisplay.length > 0 && badgeHtml) {
                        $resultDisplay.html(badgeHtml).removeClass('empty');
                    }
                    
                    return;
                }
            }

            // Fallback: Manual badge creation if checkBakuMutu not available
            // Check manual override FIRST (before automatic check)
            var isNormal = true;
            var message = '';
            
            if (offsetBakuMutu === 'false') {
                // Manual override: Tidak melewati (Memenuhi syarat)
                isNormal = true;
                message = 'Memenuhi syarat (Manual)';
            } else if (offsetBakuMutu === 'true') {
                // Manual override: Melewati (Tidak memenuhi syarat)
                isNormal = false;
                message = 'Tidak memenuhi syarat (Manual)';
                } else {
                    // Default: Check against baku mutu automatically
                    // Parse number if numeric
                    // First try to extract numeric value handling superscripts
                    var numValue = null;
                    var cleanedValue = value;
                    var dbFormat = numberFormat || 'en';
                    var hasilRange = null;
                    
                    // If value contains HTML or superscript, extract numeric value properly
                    var valueStr = value.toString();
                    if (valueStr.includes('<sup') || valueStr.includes('<sub') || 
                        /[¹²³⁴⁵⁶⁷⁸⁹⁰]/.test(valueStr) || /\^\(?\d+\)?/.test(valueStr)) {
                        // Extract numeric value handling superscripts
                        numValue = this.extractNumericWithSuperscript(value);
                        console.log('Superscript detected - Original value:', value, 'Extracted numeric:', numValue);
                        // For display, keep the original value with HTML
                        cleanedValue = value;
                    } else if (typeof parseResultRange === 'function') {
                        hasilRange = parseResultRange(value, dbFormat);
                        if (hasilRange) {
                            numValue = hasilRange.high;
                        }
                    } else if (typeof parseNumberInput === 'function') {
                        numValue = parseNumberInput(value, numberFormat);
                    }

                    // Check against baku mutu
                    if (equal && equal != '') {
                        // equal sudah dinormalisasi di awal fungsi, jadi langsung gunakan
                        // Normalize value for comparison
                        var normalizedValue = normalizeForComparison(value);
                        // equal sudah dinormalisasi di awal fungsi
                        var normalizedEqual = equal;
                        
                        var valueUpper = normalizedValue.toUpperCase();
                        var equalUpper = normalizedEqual.toUpperCase();
                        isNormal = (valueUpper === equalUpper);
                        
                        console.log('Equal check in updateResultBadge:');
                        console.log('  - value (original):', value, 'length:', (value || '').toString().length);
                        console.log('  - equal (original):', equalOriginal, 'length:', (equalOriginal || '').toString().length);
                        console.log('  - value (normalized):', normalizedValue, 'length:', normalizedValue.length);
                        console.log('  - equal (normalized):', normalizedEqual, 'length:', normalizedEqual.length);
                        console.log('  - valueUpper:', valueUpper);
                        console.log('  - equalUpper:', equalUpper);
                        console.log('  - isNormal:', isNormal);
                        
                        // Use original equal for display in message
                        message = isNormal ? 'Sesuai standar' : 'Tidak sesuai standar (Expected: ' + equalOriginal + ')';
                    } else if (hasilRange && hasilRange.isRange && typeof evaluateBakuMutuRange === 'function') {
                        // Hasil tipe range (mis. "0-1"): abnormal hanya jika batas atas > max
                        var evalRange = evaluateBakuMutuRange(value, min, max, dbFormat);
                        if (evalRange !== null) {
                            isNormal = !evalRange;
                            message = isNormal ? 'Sesuai standar (range)' : 'Melewati batas maksimum (range)';
                        }
                    } else if (numValue !== null) {
                    // Treat 0 as a valid threshold (do NOT rely on truthy checks for min/max)
                    if (min !== undefined && min !== null && min !== '' && numValue < parseFloat(min)) {
                        isNormal = false;
                        message = 'Di bawah batas minimum';
                    }
                    if (max !== undefined && max !== null && max !== '' && numValue > parseFloat(max)) {
                        isNormal = false;
                        message = 'Melewati batas maksimum';
                    }
                    if (
                        isNormal &&
                        (
                            (min !== undefined && min !== null && min !== '') ||
                            (max !== undefined && max !== null && max !== '')
                        )
                    ) {
                        message = 'Dalam rentang normal';
                    }
                }
            }

            var badgeClass = isNormal ? 'badge-success' : 'badge-danger';
            var icon = isNormal ? 'fa-check-circle' : 'fa-times-circle';
            var star = isNormal ? '' : ' <span class="bintang-baku-mutu">*</span>';

            // Display value: convert to HTML format (use original value for display)
            var displayValue = valueOriginal.toString();
            
            // Remove <p> tags first
            displayValue = displayValue.replace(/<p[^>]*>/gi, '');
            displayValue = displayValue.replace(/<\/p>/gi, '');
            
            // If already contains HTML sup/sub tags, use as is (but clean <p> tags)
            if (displayValue.includes('<sup') || displayValue.includes('<sub')) {
                // Already has HTML sup/sub tags, just ensure <p> tags are removed
                displayValue = displayValue;
            } else {
                // Convert superscript notation: ^(1) or ^1 to <sup>1</sup>
                // Pattern: number^(digit)number or number^digit
                // First, convert ^(digit) format
                displayValue = displayValue.replace(/\^\((\d+)\)/g, '<sup>$1</sup>');
                // Then, convert ^digit format (but not if already converted)
                displayValue = displayValue.replace(/(\d+)\^(\d+)/g, function(match, base, exp) {
                    // Only convert if not already inside <sup> tags
                    return base + '<sup>' + exp + '</sup>';
                });
            }

            // Ensure displayValue and message are not undefined
            displayValue = displayValue || '';
            message = message || '';
            
            // Clean up any "undefined" strings
            displayValue = String(displayValue).replace(/undefined/g, '');
            message = String(message).replace(/undefined/g, '');
            
            var badgeHtml = '<span class="badge ' + badgeClass + '">' +
                '<i class="fa ' + icon + '"></i> ' + displayValue + star +
                (message ? '<br><small>' + message + '</small>' : '') +
                '</span>';

            // Clean up any remaining "undefined" in the HTML
            badgeHtml = badgeHtml.replace(/undefined/g, '');
            
            $('#badge_' + index).html(badgeHtml);
        },

        /**
         * Update result badge for option-based parameters
         */
        updateResultBadgeForOption: function(index, value, min, max, equal, numberFormat) {
            if (!value) {
                $('#badge_' + index).html('');
                return;
            }

            numberFormat = numberFormat || 'en';
            
            // Store original value for display
            // Try to get original value from dropdown option text first (most reliable for option-based)
            var valueOriginal = value;
            var $dropdown = $('.inline-hasil-input[data-index="' + index + '"]');
            
            if ($dropdown.length > 0 && $dropdown.is('select')) {
                // Get the selected option - try by value first, then by selected
                var selectedOption = $dropdown.find('option:selected');
                if (selectedOption.length > 0) {
                    var optionText = selectedOption.text().trim();
                    if (optionText && optionText !== '- Pilih -') {
                        valueOriginal = optionText;
                    } else {
                        // If selected option is "- Pilih -", try to find option by value match
                        var normalizedValue = normalizeForComparison(value);
                        $dropdown.find('option').each(function() {
                            var optValue = $(this).val();
                            var optText = $(this).text().trim();
                            if (optValue && normalizeForComparison(optValue) === normalizedValue && optText !== '- Pilih -') {
                                valueOriginal = optText;
                                return false; // break
                            }
                        });
                    }
                } else {
                    // No option selected, try to find by value match
                    var normalizedValue = normalizeForComparison(value);
                    $dropdown.find('option').each(function() {
                        var optValue = $(this).val();
                        var optText = $(this).text().trim();
                        if (optValue && normalizeForComparison(optValue) === normalizedValue && optText !== '- Pilih -') {
                            valueOriginal = optText;
                            return false; // break
                        }
                    });
                }
            } else {
                // Not a dropdown, check if value contains spaces (then it's original)
                if (value && value.toString().indexOf(' ') !== -1) {
                    valueOriginal = value;
                } else {
                    // Value might be normalized, try to get from textarea
                    var $textarea = $('textarea[data-index="' + index + '"], textarea#result_method_' + index);
                    if ($textarea.length > 0) {
                        var textareaValue = $textarea.val();
                        if (textareaValue && textareaValue.trim() !== '') {
                            valueOriginal = textareaValue;
                        }
                    }
                }
            }
            
            // Store original equal for display
            var equalOriginal = equal;
            
            // Normalize equal - remove all whitespace (spaces, newlines, tabs, etc.) and decode HTML entities
            if (equal && equal !== '') {
                equal = normalizeForComparison(equal);
            }
            
            // Log after normalization
            console.log('updateResultBadgeForOption called:', {index: index, value: value, min: min, max: max, equal: equal, equalOriginal: equalOriginal, format: numberFormat});
            
            // Try to extract numeric value from the option (e.g. "< 5", "> 10", "15")
            var numericValue = null;
            var hasOperator = false;
            var operator = '';
            
            // Normalize value first - remove all whitespace (spaces, newlines, tabs, etc.)
            var cleanValue = normalizeForComparison(value);
            var operatorMatch = cleanValue.match(/^([<>≤≥]+)([\d.,]+)/);
            
            if (operatorMatch) {
                hasOperator = true;
                operator = operatorMatch[1];
                numericValue = parseNumberInput(operatorMatch[2], numberFormat);
            } else {
                // Try to parse the whole value as number
                numericValue = parseNumberInput(cleanValue, numberFormat);
            }

            var isNormal = true;
            var message = '';
            var validationReason = '';

            // If we have numeric value and baku mutu thresholds, perform range check
            if (numericValue !== null && !isNaN(numericValue)) {
                var minVal = parseNumberInput(min, 'en');
                var maxVal = parseNumberInput(max, 'en');
                var equalVal = parseNumberInput(equal, 'en');

                console.log('Dropdown validation - Value:', numericValue, 'Min:', minVal, 'Max:', maxVal, 'Equal:', equalVal);

                // Check equal first (exact match)
                if (equalVal !== null && !isNaN(equalVal)) {
                    isNormal = (Math.abs(numericValue - equalVal) < 0.0001);
                    validationReason = isNormal ? 'Sesuai nilai baku mutu' : 'Tidak sesuai nilai baku mutu (Expected: ' + equal + ')';
                }
                // Check range (min-max)
                else if (minVal !== null && !isNaN(minVal) && maxVal !== null && !isNaN(maxVal)) {
                    isNormal = (numericValue >= minVal && numericValue <= maxVal);
                    validationReason = isNormal ? 
                        'Dalam rentang baku mutu (' + min + ' - ' + max + ')' : 
                        'Di luar rentang baku mutu (' + min + ' - ' + max + ')';
                }
                // Check min only
                else if (minVal !== null && !isNaN(minVal)) {
                    isNormal = (numericValue >= minVal);
                    validationReason = isNormal ? 
                        'Di atas batas minimum (' + min + ')' : 
                        'Di bawah batas minimum (' + min + ')';
                }
                // Check max only
                else if (maxVal !== null && !isNaN(maxVal)) {
                    isNormal = (numericValue <= maxVal);
                    validationReason = isNormal ? 
                        'Di bawah batas maksimum (' + max + ')' : 
                        'Melebihi batas maksimum (' + max + ')';
                }
                
                message = validationReason;
            } else {
                // Non-numeric option, check exact match with 'equal' field (case-insensitive dengan trim)
                if (equal && equal != '') {
                    // equal sudah dinormalisasi di awal fungsi, jadi langsung gunakan
                    // Normalize value for comparison
                    var normalizedValue = normalizeForComparison(value);
                    // equal sudah dinormalisasi di awal fungsi
                    var normalizedEqual = equal;
                    
                    var valueUpper = normalizedValue.toUpperCase();
                    var equalUpper = normalizedEqual.toUpperCase();
                    isNormal = (valueUpper === equalUpper);
                    
                    console.log('Dropdown equal check:');
                    console.log('  - value (original):', value, 'length:', (value || '').toString().length);
                    console.log('  - equal (original):', equalOriginal, 'length:', (equalOriginal || '').toString().length);
                    console.log('  - value (normalized):', normalizedValue, 'length:', normalizedValue.length);
                    console.log('  - equal (normalized):', normalizedEqual, 'length:', normalizedEqual.length);
                    console.log('  - valueUpper:', valueUpper);
                    console.log('  - equalUpper:', equalUpper);
                    console.log('  - isNormal:', isNormal);
                    console.log('  - char codes value:', Array.from(normalizedValue).map(c => c.charCodeAt(0)));
                    console.log('  - char codes equal:', Array.from(normalizedEqual).map(c => c.charCodeAt(0)));
                    
                    // Use original equal for display in message
                    message = isNormal ? 'Sesuai standar' : 'Tidak sesuai standar (Expected: ' + equalOriginal + ')';
                } else {
                    // No validation criteria, just show selected
                    message = 'Terpilih';
                }
            }

            var badgeClass = isNormal ? 'badge-success' : 'badge-danger';
            var icon = isNormal ? 'fa-check-circle' : 'fa-times-circle';
            var star = isNormal ? '' : ' <span class="bintang-baku-mutu">*</span>';

            // Process valueOriginal to ensure HTML formatting is preserved
            var displayValue = valueOriginal;
            // If value doesn't contain HTML tags but contains ^( notation, convert it
            if (displayValue && !displayValue.includes('<sup') && !displayValue.includes('<sub') && 
                (displayValue.includes('^(') || displayValue.includes('^'))) {
                displayValue = this.convertSuperscriptToHtml(displayValue);
            }
            // Ensure sup/sub tags have proper styling
            displayValue = String(displayValue || '').replace(/<sup>/g, '<sup style="vertical-align: super; font-size: 0.75em; line-height: 0; position: relative; top: -0.4em;">');
            displayValue = displayValue.replace(/<sub>/g, '<sub style="vertical-align: sub; font-size: 0.75em; line-height: 0; position: relative; bottom: -0.25em;">');

            // Gunakan checkBakuMutu global jika ada (lebih akurat)
            var badgeHtml = null;
            if (typeof window.checkBakuMutu === 'function') {
                var $ta = $('textarea#hasil_permohonan_uji_parameter_klinik_' + index);
                if (!$ta.length) {
                    $ta = $('textarea[id*="' + index + '"]').first();
                }
                var paramName = $ta.attr('data-name') || '';
                var nilaiBm = $ta.attr('data-nilai-baku-mutu') || '';
                badgeHtml = window.checkBakuMutu(
                    valueOriginal || value,
                    min,
                    max,
                    equalOriginal || equal,
                    'default',
                    null,
                    '',
                    numberFormat,
                    paramName || nilaiBm
                );
            }

            if (!badgeHtml) {
                badgeHtml = '<span class="badge ' + badgeClass + '" style="font-size: 14px; padding: 8px 12px; line-height: 1.4;">' +
                    '<i class="fa ' + icon + ' mr-1"></i> ' + displayValue + star +
                    (message && message !== 'Terpilih' ? '<br><small class="bm-kesimpulan-hasil">' + message + '</small>' : '') +
                    '</span>';
            }

            var $badgeContainer = $('#badge_' + index);
            console.log('Updating badge for index:', index, 'Container exists:', $badgeContainer.length > 0, 'isNormal:', isNormal);
            $badgeContainer.html(badgeHtml);
        },

        /**
         * Reorder table columns
         */
        reorderColumns: function() {
            // Current order: Name, Baku Mutu, Satuan, Hasil, Keterangan, Aksi
            // New order: Name, Hasil, Satuan, Keterangan, Baku Mutu, Aksi

            var processedCount = 0;
            var skippedCount = 0;

            $('#table-parameter tbody tr').each(function() {
                var $row = $(this);
                var $cells = $row.find('td, th');

                // Skip header rows (rows with colspan in th or td)
                if ($row.find('th[colspan]').length > 0 || $row.find('td[colspan]').length > 0) {
                    skippedCount++;
                    return;
                }

                // Skip if not enough cells (should have at least 5: Name, Baku Mutu, Satuan, Hasil, Keterangan)
                if ($cells.length < 5) {
                    console.warn('Row skipped - not enough cells:', $cells.length, 'Expected: at least 5', $row.find('td').first().text().trim());
                    skippedCount++;
                    return;
                }

                // First, try to find Aksi column by looking for action-buttons (most reliable method)
                var $aksi = $row.find('td .action-buttons').closest('td');
                
                // If not found by content, try by position (6th column)
                if ($aksi.length === 0 && $cells.length >= 6) {
                    $aksi = $cells.eq(5);
                }
                
                // Get other cells by position
                var $name = $cells.eq(0);      // Keep: Name
                var $bakuMutu = $cells.eq(1);  // Move to 5th: Baku Mutu
                var $satuan = $cells.eq(2);    // Keep position: Satuan
                var $hasil = $cells.eq(3);     // Move to 2nd: Hasil
                var $keterangan = $cells.eq(4); // Move to 4th: Keterangan

                // If Aksi column still doesn't exist, create it
                if ($aksi.length === 0 || $aksi.find('.action-buttons').length === 0) {
                    console.warn('Aksi column missing for row:', $row.find('td').first().text().trim(), '- Creating Aksi column');
                    $aksi = $('<td class="text-center align-middle"></td>');
                    // Try to find parameter ID from the row to create buttons
                    var paramId = $row.find('input[name*="permohonan_uji_parameter_klinik"]').val() || 
                                  $row.find('input[name*="parameter_sub_satuan_klinik_id"]').val() ||
                                  $row.find('input[id*="permohonan_uji_parameter_klinik"]').val() ||
                                  $row.find('input[id*="parameter_sub_satuan_klinik_id"]').val();
                    var paramName = $row.find('td').first().text().trim().replace(/^[-~]\s*/, '');
                    var isSub = $row.find('input[name*="parameter_sub_satuan_klinik_id"]').length > 0;
                    
                    if (paramId) {
                        var $actionButtons = $('<div class="action-buttons"></div>');
                        $actionButtons.append('<button type="button" class="btn btn-sm btn-info btn-sm-popup btn-repeat-parameter" data-parameter-id="' + paramId + '" data-is-sub="' + (isSub ? 1 : 0) + '" title="Ulangi Pemeriksaan (Simpan ke History)"><i class="fa fa-redo"></i></button>');
                        $actionButtons.append('<button type="button" class="btn btn-sm btn-secondary btn-sm-popup btn-view-history" data-parameter-id="' + paramId + '" data-parameter-name="' + paramName + '" data-is-sub="' + (isSub ? 1 : 0) + '" title="Lihat History"><i class="fa fa-history"></i></button>');
                        $aksi.append($actionButtons);
                    }
                }

                // Reorder
                $row.empty();
                $row.append($name);      // 1. Name
                $row.append($hasil);     // 2. Hasil
                $row.append($satuan);    // 3. Satuan
                $row.append($keterangan); // 4. Keterangan
                $row.append($bakuMutu);  // 5. Baku Mutu
                $row.append($aksi);      // 6. Aksi (History and Repeat buttons)
                
                processedCount++;
            });

            console.log('Columns reordered:', {
                processed: processedCount,
                skipped: skippedCount,
                total: processedCount + skippedCount
            });
        },

        /**
         * Run initial validation for all fields with values (dropdowns and TinyMCE editors)
         * This ensures badges are displayed immediately on page load
         */
        runInitialValidation: function() {
            var self = this;
            console.log('Running initial validation for all fields...');
            
            // Ensure checkBakuMutu is available
            if (typeof window.checkBakuMutu === 'undefined') {
                console.warn('checkBakuMutu not available yet, retrying in 200ms...');
                setTimeout(function() {
                    self.runInitialValidation();
                }, 200);
                return;
            }
            
            // Process all dropdowns
            $('select.' + this.settings.hasilInputClass).each(function() {
                var $dropdown = $(this);
                var validationData = $dropdown.data('initialValidation');
                
                if (validationData && validationData.currentValue) {
                    // Normalize value before validation
                    var normalizedValue = normalizeForComparison(validationData.currentValue);
                    console.log('Initial validation for dropdown index:', validationData.index, 'value (original):', validationData.currentValue, 'value (normalized):', normalizedValue);
                    
                    // Also visually restore the dropdown selection if it's currently showing "- Pilih -"
                    if (!$dropdown.val()) {
                        // Search options by normalized/case-insensitive comparison to find the REAL option value
                        var matchedOptionValue = null;
                        var originalVal = (validationData.originalCurrentValue || '').trim();
                        $dropdown.find('option').each(function() {
                            var optVal = $(this).val();
                            if (!optVal) return; // skip "- Pilih -"
                            var optNorm = normalizeForComparison(optVal).toLowerCase();
                            // Compare against both normalized stored value and original value
                            if (optNorm === normalizedValue.toLowerCase() ||
                                (originalVal && optNorm === normalizeForComparison(originalVal).toLowerCase()) ||
                                optVal.trim().toLowerCase() === originalVal.toLowerCase()) {
                                matchedOptionValue = optVal;
                                return false; // break
                            }
                        });
                        if (matchedOptionValue) {
                            $dropdown.val(matchedOptionValue);
                            console.log('Restored dropdown selection to:', matchedOptionValue);
                        }
                    }
                    
                    // Use original value (may contain HTML) for display, but normalized for comparison
                    var originalValue = validationData.originalCurrentValue || validationData.currentValue;
                    // Get HTML value from textarea if available
                    var $textarea = $('textarea[data-index="' + validationData.index + '"], textarea#result_method_' + validationData.index);
                    if ($textarea.length > 0) {
                        var textareaValue = $textarea.val();
                        if (textareaValue && textareaValue.trim() !== '') {
                            originalValue = textareaValue;
                        }
                    }
                    // If value doesn't contain HTML tags but contains ^( notation, convert it
                    if (originalValue && !originalValue.includes('<sup') && !originalValue.includes('<sub') && 
                        (originalValue.includes('^(') || originalValue.includes('^'))) {
                        originalValue = self.convertSuperscriptToHtml(originalValue);
                    }
                    
                    self.updateResultBadgeForOption(
                        validationData.index,
                        originalValue, // Use HTML value for display
                        validationData.min,
                        validationData.max,
                        validationData.equal,
                        validationData.numberFormat
                    );
                } else {
                    // Also check if dropdown has a value even without initialValidation data
                    var currentValue = $dropdown.val();
                    if (currentValue) {
                        // Normalize value before validation
                        var normalizedValue = normalizeForComparison(currentValue);
                        var index = $dropdown.data('index');
                        var textareaId = $dropdown.data('textarea-id');
                        if (index && textareaId) {
                            var $textarea = $('#' + textareaId);
                            if ($textarea.length > 0) {
                                // Do NOT use "|| ''" here – it will turn 0 into '' (falsy).
                                // Keep raw values so that 0 is treated as a valid threshold.
                                var min = $textarea.data('min');
                                var max = $textarea.data('max');
                                var equal = decodeHtmlEntities($textarea.data('equal') != null ? $textarea.data('equal') : '');
                                var numberFormat = $textarea.data('number-format') || 'en';
                                console.log('Initial validation for dropdown (no initialValidation data) index:', index, 'value (original):', currentValue, 'value (normalized):', normalizedValue);
                                self.updateResultBadgeForOption(index, normalizedValue, min, max, equal, numberFormat);
                            }
                        }
                    }
                }
            });
            
            // Process all TinyMCE editors and contenteditable divs
            $('.inline-hasil-editor').each(function() {
                var $editor = $(this);
                var textareaId = $editor.data('textarea-id');
                if (!textareaId) {
                    console.warn('Editor missing textarea-id:', $editor.attr('id'));
                    return;
                }
                
                var $textarea = $('#' + textareaId);
                if ($textarea.length === 0) {
                    console.warn('Textarea not found for editor:', textareaId);
                    return;
                }
                
                // ALWAYS get value from textarea first (most reliable source)
                // Textarea value is from rubahNilaikeForm which converts HTML <sup> to ^( format
                var textareaValue = $textarea.val() || '';
                
                // Convert ^( notation to HTML FIRST (rubahNilaikeForm converts <sup> to ^()
                var currentValue = textareaValue;
                if (currentValue && !currentValue.includes('<sup') && !currentValue.includes('<sub') && 
                    (currentValue.includes('^(') || currentValue.includes('^'))) {
                    currentValue = self.convertSuperscriptToHtml(currentValue);
                }
                
                // Only use TinyMCE content if textarea is empty AND TinyMCE has content
                // This ensures we don't use stale/wrong values from editor HTML
                if (!currentValue || currentValue.trim() === '') {
                    var editorId = $editor.attr('id');
                    if (editorId && typeof tinymce !== 'undefined') {
                        try {
                            var editor = tinymce.get(editorId);
                            if (editor && 
                                typeof editor.getContent === 'function' && 
                                !editor.removed) {
                                var editorContent = editor.getContent();
                                // Use HTML content directly (preserves superscript/subscript)
                                if (editorContent && editorContent.trim() !== '') {
                                    currentValue = editorContent;
                                }
                            }
                        } catch(e) {
                            console.warn('Error getting TinyMCE content:', e);
                        }
                    }
                }
                
                // Only proceed if we have a valid value
                // Don't use editor HTML as fallback because it may contain values from other parameters
                if (!currentValue || currentValue.trim() === '' || currentValue === '-') {
                    console.log('Skipping validation for empty editor:', textareaId, 'index:', $editor.data('index'));
                    return;
                }
                
                // Final check: if value still doesn't contain HTML tags but contains ^( notation, convert it
                if (currentValue && !currentValue.includes('<sup') && !currentValue.includes('<sub') && 
                    (currentValue.includes('^(') || currentValue.includes('^'))) {
                    currentValue = self.convertSuperscriptToHtml(currentValue);
                }
                
                var index = $editor.data('index');
                // Prefer editor data-* values, fall back to textarea, but NEVER collapse 0 to ''.
                var min = $editor.data('min');
                if (min === undefined) min = $textarea.data('min');
                var max = $editor.data('max');
                if (max === undefined) max = $textarea.data('max');
                var equalRaw = $editor.data('equal');
                if (equalRaw === undefined || equalRaw === null) equalRaw = $textarea.data('equal');
                var equal = decodeHtmlEntities(equalRaw != null ? equalRaw : '');
                var numberFormat = $editor.data('number-format') || $textarea.data('number-format') || 'en';
                
                // Get additional baku mutu data from result_output div (if exists) or textarea
                var $resultOutput = $('#result_output_param_' + index);
                if ($resultOutput.length === 0) {
                    $resultOutput = $('#result_output_sub_' + index);
                }
                
                var offsetBakuMutu = 'default';
                var multipleBakuMutu = null;
                var kesimpulanBakuMutu = '';
                
                // Get offset from hidden input first
                var isSub = textareaId.includes('sub_parameter');
                var offsetInputId;
                if (isSub) {
                    offsetInputId = 'offset_baku_mutu_sub_' + index;
                } else {
                    offsetInputId = 'offset_baku_mutu_param_' + index;
                }
                var $offsetInput = $('#' + offsetInputId);
                if ($offsetInput.length > 0) {
                    offsetBakuMutu = String($offsetInput.val() || 'default').trim();
                }
                
                // Try to get from result_output div first
                if ($resultOutput.length > 0) {
                    if (offsetBakuMutu === 'default') {
                        offsetBakuMutu = $resultOutput.data('offset-baku-mutu') || $textarea.data('offset-baku-mutu') || 'default';
                    }
                    var multipleBakuMutuData = $resultOutput.attr('data-multiple-baku-mutu');
                    if (multipleBakuMutuData) {
                        try {
                            multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                        } catch(e) {
                            multipleBakuMutu = null;
                        }
                    }
                    kesimpulanBakuMutu = $resultOutput.data('kesimpulan-baku-mutu') || $textarea.data('kesimpulan-baku-mutu') || '';
                } else {
                    // Fallback to textarea
                    if (offsetBakuMutu === 'default') {
                        offsetBakuMutu = $textarea.data('offset-baku-mutu') || 'default';
                    }
                    var multipleBakuMutuData = $textarea.data('multiple-baku-mutu');
                    if (multipleBakuMutuData) {
                        if (typeof multipleBakuMutuData === 'string') {
                            try {
                                multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                            } catch(e) {
                                multipleBakuMutu = null;
                            }
                        } else {
                            multipleBakuMutu = multipleBakuMutuData;
                        }
                    }
                    kesimpulanBakuMutu = $textarea.data('kesimpulan-baku-mutu') || '';
                }
                
                // Get kesimpulan from hidden input if exists
                var kesimpulanInputId;
                if (isSub) {
                    kesimpulanInputId = 'kesimpulan_baku_mutu_sub_' + index;
                } else {
                    kesimpulanInputId = 'kesimpulan_baku_mutu_param_' + index;
                }
                var $kesimpulanInput = $('#' + kesimpulanInputId);
                if ($kesimpulanInput.length > 0 && $kesimpulanInput.val()) {
                    kesimpulanBakuMutu = $kesimpulanInput.val();
                }
                
                // Get history count
                var historyCount = 0;
                if ($resultOutput.length > 0) {
                    historyCount = parseInt($resultOutput.data('history-count') || 0);
                }
                
                // Normalize value - remove all whitespace (spaces, newlines, tabs, etc.) - for comparison only
                var normalizedValue = normalizeForComparison(currentValue);
                
                // Final check: ensure currentValue is in HTML format before sending to updateResultBadge
                // This is critical to prevent race conditions where value might not be converted yet
                if (currentValue && !currentValue.includes('<sup') && !currentValue.includes('<sub') && 
                    (currentValue.includes('^(') || currentValue.includes('^'))) {
                    currentValue = self.convertSuperscriptToHtml(currentValue);
                }
                
                console.log('Initial validation for editor index:', index, 'value (original HTML):', currentValue, 'value (normalized):', normalizedValue, 'offset:', offsetBakuMutu, 'historyCount:', historyCount);
                
                // Update badge using updateResultBadge (which will call checkBakuMutu) with HTML value (not normalized)
                // This ensures superscript/subscript formatting is preserved in the badge
                self.updateResultBadge(index, currentValue, min, max, equal, numberFormat);
            });
            
            // Also process all textareas that might not have inline editors yet
            $('textarea.result_method_klinik').each(function() {
                var $textarea = $(this);
                var textareaId = $textarea.attr('id');
                if (!textareaId) return;
                
                // Skip if already processed by inline editor
                var index = textareaId.match(/\d+/);
                if (!index || !index[0]) return;
                index = index[0];
                
                // Check if inline editor exists for this textarea
                var $editor = $('.inline-hasil-editor[data-textarea-id="' + textareaId + '"]');
                if ($editor.length > 0) return; // Already processed
                
                var currentValue = $textarea.val();
                if (!currentValue || currentValue.trim() === '') return;
                
                // Normalize value - remove all whitespace (spaces, newlines, tabs, etc.)
                var normalizedValue = normalizeForComparison(currentValue);
                
                // Keep raw min/max so that 0 is not converted to ''.
                var min = $textarea.data('min');
                var max = $textarea.data('max');
                var equal = decodeHtmlEntities($textarea.data('equal') != null ? $textarea.data('equal') : '');
                var numberFormat = $textarea.data('number-format') || 'en';
                
                // Get offset from hidden input
                var isSub = textareaId.includes('sub_parameter');
                var offsetInputId;
                if (isSub) {
                    offsetInputId = 'offset_baku_mutu_sub_' + index;
                } else {
                    offsetInputId = 'offset_baku_mutu_param_' + index;
                }
                var $offsetInput = $('#' + offsetInputId);
                var offsetBakuMutu = 'default';
                if ($offsetInput.length > 0) {
                    offsetBakuMutu = String($offsetInput.val() || 'default').trim();
                }
                
                // Get result output div
                var $resultOutput = $('#result_output_param_' + index);
                if ($resultOutput.length === 0) {
                    $resultOutput = $('#result_output_sub_' + index);
                }
                
                var multipleBakuMutu = null;
                var kesimpulanBakuMutu = '';
                var historyCount = 0;
                
                if ($resultOutput.length > 0) {
                    var multipleBakuMutuData = $resultOutput.attr('data-multiple-baku-mutu');
                    if (multipleBakuMutuData) {
                        try {
                            multipleBakuMutu = JSON.parse(multipleBakuMutuData);
                        } catch(e) {
                            multipleBakuMutu = null;
                        }
                    }
                    kesimpulanBakuMutu = $resultOutput.data('kesimpulan-baku-mutu') || '';
                    historyCount = parseInt($resultOutput.data('history-count') || 0);
                }
                
                // Get kesimpulan from hidden input if exists
                var kesimpulanInputId;
                if (isSub) {
                    kesimpulanInputId = 'kesimpulan_baku_mutu_sub_' + index;
                } else {
                    kesimpulanInputId = 'kesimpulan_baku_mutu_param_' + index;
                }
                var $kesimpulanInput = $('#' + kesimpulanInputId);
                if ($kesimpulanInput.length > 0 && $kesimpulanInput.val()) {
                    kesimpulanBakuMutu = $kesimpulanInput.val();
                }
                
                // Final check: ensure currentValue is in HTML format before sending to updateResultBadge
                // This is critical to prevent race conditions where value might not be converted yet
                if (currentValue && !currentValue.includes('<sup') && !currentValue.includes('<sub') && 
                    (currentValue.includes('^(') || currentValue.includes('^'))) {
                    currentValue = self.convertSuperscriptToHtml(currentValue);
                }
                
                console.log('Initial validation for textarea index:', index, 'value (original HTML):', currentValue, 'value (normalized):', normalizedValue, 'offset:', offsetBakuMutu);
                
                // Update badge using updateResultBadge with HTML value (not normalized)
                // This ensures superscript/subscript formatting is preserved in the badge
                self.updateResultBadge(index, currentValue, min, max, equal, numberFormat);
            });
            
            console.log('Initial validation completed');
        }
    };

    // Export AnalisInlineEditor to window for global access
    window.AnalisInlineEditor = AnalisInlineEditor;

    /**
     * Sinkronisasi badge pengulangan pada tombol .btn-repeat-parameter
     * berdasarkan atribut data-history-count di elemen result_output_*.
     * Dipakai agar halaman verifikasi/analis yang sudah ter-render penuh
     * tetap mendapatkan angka kecil di pojok kanan tombol ulangi.
     */
    function syncRepeatButtonBadgesFromHistory() {
        try {
            console.log('syncRepeatButtonBadgesFromHistory: start scanning .btn-repeat-parameter ...');
            var processed = 0;
            var withHistory = 0;
            $('.btn-repeat-parameter').each(function () {
                var $btn = $(this);
                var $row = $btn.closest('tr');
                if (!$row.length) {
                    console.log('syncRepeatButtonBadgesFromHistory: skip, no <tr> for button', $btn.get(0));
                    return;
                }

                var $resultOutput = $row.find('[id^="result_output_sub_"], [id^="result_output_param_"]').first();
                if (!$resultOutput.length) {
                    console.log('syncRepeatButtonBadgesFromHistory: skip, no result_output_* found in row index', $row.index());
                    return;
                }

                var historyCount = parseInt($resultOutput.data('history-count') || 0);
                console.log('syncRepeatButtonBadgesFromHistory: row index', $row.index(), 'historyCount =', historyCount, 'resultOutputId =', $resultOutput.attr('id'));
                processed++;
                if (historyCount > 0) {
                    withHistory++;
                    // Gunakan helper yang sama agar span badge selalu konsisten
                    attachRepeatBadge($btn, historyCount);
                }
            });
            console.log('syncRepeatButtonBadgesFromHistory: finished. processed rows =', processed, 'withHistory =', withHistory);
        } catch (e) {
            console.warn('syncRepeatButtonBadgesFromHistory failed:', e);
        }
    }

    // Expose to global scope so Blade views can call it
    window.syncRepeatButtonBadgesFromHistory = syncRepeatButtonBadgesFromHistory;

    // Auto-initialize when document is ready (unless explicitly skipped)
    $(document).ready(function() {
        // Check if auto-initialization should be skipped
        if (window.skipAnalisInlineEditorAutoInit === true) {
            console.log('Skipping auto-initialization of AnalisInlineEditor (will be initialized manually)');
            return;
        }
        
        // Wait for page to be fully loaded
        setTimeout(function() {
            if (typeof AnalisInlineEditor !== 'undefined') {
                AnalisInlineEditor.init();
                // Setelah inline editor dan tombol repeat terbentuk,
                // sinkronkan badge pengulangan dari data-history-count.
                setTimeout(syncRepeatButtonBadgesFromHistory, 400);
            }
        }, 500);
    });

})(jQuery);


