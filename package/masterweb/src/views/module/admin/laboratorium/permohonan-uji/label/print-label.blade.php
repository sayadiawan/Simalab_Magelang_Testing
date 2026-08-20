<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Print-Label-Sample</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="{{ asset('assets/admin/cdn-local/css/jquery-ui.min.css') }}">
    <style>
        .starter-template {
            padding: 0px 0px;
            text-align: center;
        }

        .label-container {
            display: inline-block;
            width: 3cm;
            /* Default width for label 3x6.4cm */
            height: 6.4cm;
            /* Default height for label 3x6.4cm */
            border: 1px solid #000000;
            padding: 1mm;
            /* Reduced padding for better fit */
            margin: 0;
            /* No margin - labels should be tightly packed */
            cursor: move;
            /* Mengubah kursor saat di atas label */
            position: relative;
            /* Required for draggable */
            font-size: 7px;
            /* Base font size - will scale with container */
            vertical-align: top;
            /* Align items to the top */
            box-sizing: border-box;
            /* Include border and padding in width/height */
            flex-shrink: 0;
            /* Prevent labels from shrinking */
        }

        .label-content-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .label-row {
            display: flex;
            /* Use flexbox to arrange labels */
            flex-wrap: wrap;
            /* Allow wrapping to the next line */
            gap: 2mm;
            /* Default gap between labels (both row and column) */
            row-gap: 2mm;
            /* Gap between rows */
            column-gap: 2mm;
            /* Gap between columns */
            margin-bottom: 2mm;
            /* Margin bottom for spacing between rows */
        }

        .page-break {
            page-break-after: always;
            break-after: page;
        }

        @media print {
            .page-break {
                page-break-after: always;
                break-after: page;
            }
        }

        /* Specific margins for label placement */
        .label-container:nth-child(1) {
            margin-right: 0mm;
            /* No margin for the first label */
        }

        .label-container:nth-child(2) {
            margin-right: 2mm;
            /* 2mm margin for the second label */
        }

        .label-container:nth-child(3) {
            margin-right: 0mm;
            /* No margin for the third label */
        }

        /* Settings Panel */
        .settings-panel {
            position: fixed;
            bottom: 80px;
            /* Positioned above the buttons */
            right: 20px;
            background: white;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            font-family: Arial, sans-serif;
            width: 280px;
        }

        .settings-panel h3 {
            margin: 0 0 10px 0;
            color: #28a745;
            font-size: 16px;
        }

        .setting-group {
            margin-bottom: 10px;
        }

        .setting-group label {
            display: inline-block;
            width: 60px;
            font-size: 12px;
            font-weight: bold;
        }

        .setting-group input {
            width: 60px;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-right: 5px;
        }

        .setting-group select {
            width: 50px;
            padding: 2px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .apply-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
        }

        .apply-btn:hover {
            background-color: #218838;
        }

        .toggle-settings {
            position: fixed;
            bottom: 18px;
            right: 100px;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1001;
            transition: background-color 0.3s;
        }

        .toggle-settings:hover {
            background-color: #218838;
        }

        @media print {

            #print-button,
            .settings-panel,
            .toggle-settings {
                display: none;
            }

            #cetak {
                display: none;
            }
        }

        @page {
            margin: 5mm;
            /* Minimal margin for label sheets */
            size: A4;
        }

        @page.portrait {
            size: A4 portrait;
        }

        @page.landscape {
            size: A4 landscape;
        }

        #print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        #print-button:hover {
            background-color: #0056b3;
        }

        .qr-code-container {
            text-align: center;
            margin-bottom: 0;
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 0;
            overflow: hidden;
        }

        .qr-code-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
            max-height: 100%;
        }

        .qr-code-placeholder {
            width: 80%;
            height: 80%;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 0.6em;
        }

        .qr-code-container svg {
            width: 100% !important;
            height: auto !important;
            max-width: 100% !important;
            max-height: 75% !important;
            object-fit: contain;
        }

        .qr-code-container img {
            max-width: 100% !important;
            max-height: 75% !important;
            width: auto !important;
            height: auto !important;
            object-fit: contain;
        }

        .sample-code {
            text-align: center;
            font-size: 0.75em;
            font-weight: bold;
            word-break: break-all;
            margin-top: 1mm;
            padding: 0 2px;
            line-height: 1.2;
            flex-shrink: 0;
            width: 100%;
            overflow: hidden;
        }
    </style>

    <script src="{{ asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/admin/cdn-local/js/jquery-ui.min.js') }}"></script>
</head>

<body>
    <!-- Settings Button -->
    <button class="toggle-settings" id="toggle-settings">⚙️ Settings</button>

    <button id="print-button">Print</button>

    <!-- Settings Panel -->
    <div class="settings-panel" id="settings-panel" style="display: none;">
        <h3>Label Settings</h3>

        <div class="setting-group">
            <label for="label-width">Lebar:</label>
            <input type="number" id="label-width" value="30" min="10" max="200" step="0.1">
            <select id="width-unit">
                <option value="mm" selected>mm</option>
                <option value="cm">cm</option>
                <option value="px">px</option>
            </select>
        </div>

        <div class="setting-group">
            <label for="label-height">Tinggi:</label>
            <input type="number" id="label-height" value="64" min="10" max="200" step="0.1">
            <select id="height-unit">
                <option value="mm" selected>mm</option>
                <option value="cm">cm</option>
                <option value="px">px</option>
            </select>
        </div>

        <div class="setting-group">
            <label for="font-size">Font:</label>
            <input type="number" id="font-size" value="8" min="6" max="20">
            <select id="font-unit">
                <option value="px">px</option>
                <option value="pt">pt</option>
            </select>
        </div>

        <div class="setting-group">
            <label for="padding">Padding:</label>
            <input type="number" id="padding" value="2" min="0" max="10">
            <select id="padding-unit">
                <option value="mm">mm</option>
                <option value="px">px</option>
            </select>
        </div>

        <div class="setting-group">
            <label for="columns">Kolom:</label>
            <input type="number" id="columns" value="4" min="1" max="10">
            <span style="font-size: 11px; margin-left: 5px;">per baris</span>
        </div>

        <div class="setting-group">
            <label for="rows">Baris:</label>
            <input type="number" id="rows" value="3" min="0" max="50">
            <span style="font-size: 11px; margin-left: 5px;">per halaman (0 = auto)</span>
        </div>

        <div class="setting-group">
            <label for="gap">Jarak Antar Label:</label>
            <input type="number" id="gap" value="2" min="0" max="10" step="0.1">
            <select id="gap-unit">
                <option value="mm" selected>mm</option>
                <option value="cm">cm</option>
                <option value="px">px</option>
            </select>
        </div>

        <div class="setting-group">
            <label for="paper-size">Ukuran Kertas:</label>
            <select id="paper-size" style="width: 120px;">
                <option value="A4" selected>A4</option>
                <option value="A3">A3</option>
                <option value="A5">A5</option>
                <option value="Letter">Letter</option>
                <option value="Legal">Legal</option>
            </select>
        </div>

        <div class="setting-group">
            <label for="orientation">Orientasi:</label>
            <select id="orientation" style="width: 120px;">
                <option value="portrait" selected>Portrait</option>
                <option value="landscape">Landscape</option>
            </select>
        </div>

        <button class="apply-btn" onclick="applySettings()">Terapkan</button>
        <button class="apply-btn" onclick="resetSettings()" style="background-color: #dc3545;">Reset</button>
    </div>

    <div id="printable" class="container">
        @php
            $label_count = count($samples_data);
        @endphp

        @if ($label_count > 0)
            <div id="label-container-wrapper">
                <div class="label-row">
                    @for ($n = 0; $n < $label_count; $n++)
                        <div class="label-container" id="label-{{ $n }}">
                            <div class="label-content-wrapper">
                                <div class="qr-code-container">
                                    @if (isset($samples_data[$n]['qr_code']) && $samples_data[$n]['qr_code'])
                                        <div class="qr-code-wrapper">
                                            {!! $samples_data[$n]['qr_code'] !!}
                                        </div>
                                    @else
                                        <div class="qr-code-placeholder">
                                            <span>QR Code</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="sample-code">
                                    {{ $samples_data[$n]['codesample_samples'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    </div>

    <script>
        $(function() {
            // Print button functionality
            $('#print-button').on('click', function() {
                // Get current paper size and orientation settings
                const paperSize = $('#paper-size').val() || 'A4';
                const orientation = $('#orientation').val() || 'portrait';

                // Apply orientation to body before printing
                $('body').removeClass('print-portrait print-landscape');
                $('body').addClass('print-' + orientation);

                // Apply @page size and orientation via style tag
                let styleTag = $('#print-orientation-style');
                if (styleTag.length === 0) {
                    styleTag = $('<style id="print-orientation-style"></style>');
                    $('head').append(styleTag);
                }
                styleTag.html('@page { size: ' + paperSize + ' ' + orientation + '; }');

                // Trigger print
                window.print();
            });

            // Toggle settings panel
            $('#toggle-settings').on('click', function() {
                $('#settings-panel').toggle();
            });
        });

        function applySettings() {
            const width = $('#label-width').val();
            const widthUnit = $('#width-unit').val();
            const height = $('#label-height').val();
            const heightUnit = $('#height-unit').val();
            const fontSize = $('#font-size').val();
            const fontUnit = $('#font-unit').val();
            const padding = $('#padding').val();
            const paddingUnit = $('#padding-unit').val();
            const gap = $('#gap').val() || 2;
            const gapUnit = $('#gap-unit').val() || 'mm';
            const paperSize = $('#paper-size').val() || 'A4';
            const orientation = $('#orientation').val() || 'portrait';
            const columns = parseInt($('#columns').val()) || 4;
            const rows = parseInt($('#rows').val()) || 3;

            // Apply column layout FIRST (this will recreate the DOM structure)
            applyColumnLayout(columns, rows, gap, gapUnit);

            // Then apply dimensions AFTER layout is created
            setTimeout(function() {
                $('.label-container').css({
                    'width': width + widthUnit,
                    'height': height + heightUnit,
                    'font-size': fontSize + fontUnit,
                    'padding': padding + paddingUnit,
                    'margin': '0',
                    'box-sizing': 'border-box'
                });

                // Calculate responsive font size based on label dimensions
                // Convert to pixels for calculation (approximate: 1mm ≈ 3.7795px at 96dpi)
                let labelWidthPx = parseFloat(width);
                let labelHeightPx = parseFloat(height);

                if (widthUnit === 'cm') {
                    labelWidthPx = labelWidthPx * 10 * 3.7795;
                } else if (widthUnit === 'mm') {
                    labelWidthPx = labelWidthPx * 3.7795;
                }

                if (heightUnit === 'cm') {
                    labelHeightPx = labelHeightPx * 10 * 3.7795;
                } else if (heightUnit === 'mm') {
                    labelHeightPx = labelHeightPx * 3.7795;
                }

                // Use smaller dimension to ensure text fits
                const minDimension = Math.min(labelWidthPx, labelHeightPx);
                // Font size should be about 8-10% of the smaller dimension
                const responsiveFontSize = Math.max(6, Math.min(14, minDimension * 0.09)) + 'px';

                // Apply responsive font size to sample code
                $('.sample-code').css({
                    'font-size': responsiveFontSize
                });

                // Make QR code responsive - scale to fit container
                $('.qr-code-container svg').css({
                    'width': '100%',
                    'height': 'auto',
                    'max-width': '100%',
                    'max-height': '75%'
                });

                // Apply gap to label-row (both column and row gap)
                $('.label-row').css({
                    'gap': gap + gapUnit,
                    'row-gap': gap + gapUnit,
                    'column-gap': gap + gapUnit,
                    'margin-bottom': gap + gapUnit
                });

                // Re-initialize draggable after layout and dimensions are applied
                reinitializeDraggable();
            }, 150);

            // Save settings to localStorage (if supported)
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem('labelSettings', JSON.stringify({
                    width: width,
                    widthUnit: widthUnit,
                    height: height,
                    heightUnit: heightUnit,
                    fontSize: fontSize,
                    fontUnit: fontUnit,
                    padding: padding,
                    paddingUnit: paddingUnit,
                    gap: gap,
                    gapUnit: gapUnit,
                    paperSize: paperSize,
                    orientation: orientation,
                    columns: columns,
                    rows: rows
                }));
            }

            // Show alert after all settings are applied
            setTimeout(function() {
                alert('Pengaturan berhasil diterapkan!');
            }, 200);
        }

        function applyColumnLayout(columns, rows, gap = 2, gapUnit = 'mm') {
            const $wrapper = $('#label-container-wrapper');
            const $labels = $('.label-container');
            const totalLabels = $labels.length;

            if (totalLabels === 0) return;

            // Calculate labels per page
            const labelsPerPage = rows > 0 ? (columns * rows) : totalLabels;

            // Detach all labels first to preserve them
            const labelsArray = [];
            $labels.each(function() {
                labelsArray.push($(this).detach());
            });

            // Clear wrapper completely to ensure clean start
            $wrapper.empty();

            // Group labels into rows and pages
            let currentRow = null;
            let labelsInCurrentRow = 0;
            let labelsInCurrentPage = 0;

            labelsArray.forEach(function($label, index) {
                // Check if we need a new page (before creating new row)
                if (rows > 0 && labelsInCurrentPage > 0 && labelsInCurrentPage % labelsPerPage === 0) {
                    // Add page break before starting new page
                    if (currentRow) {
                        currentRow.addClass('page-break');
                    }
                    labelsInCurrentPage = 0;
                    labelsInCurrentRow = 0;
                    currentRow = null; // Reset current row to force new row creation
                }

                // Check if we need a new row (when starting or when current row is full)
                if (currentRow === null || labelsInCurrentRow >= columns) {
                    currentRow = $('<div class="label-row"></div>');
                    $wrapper.append(currentRow);
                    labelsInCurrentRow = 0;
                }

                // Add label to current row
                currentRow.append($label);
                labelsInCurrentRow++;
                labelsInCurrentPage++;
            });

            // Set max-width for label-row to control column width
            const labelWidth = parseFloat($('#label-width').val()) || 30;
            const widthUnit = $('#width-unit').val() || 'mm';

            // Ensure we're using mm for calculation
            let labelWidthInMm = labelWidth;
            if (widthUnit === 'cm') {
                labelWidthInMm = labelWidth * 10;
            } else if (widthUnit === 'px') {
                // Approximate conversion: 1px ≈ 0.264583mm at 96dpi
                labelWidthInMm = labelWidth * 0.264583;
            }

            // Calculate gap in mm for container width calculation
            let gapInMm = parseFloat(gap) || 2;
            if (gapUnit === 'cm') {
                gapInMm = gapInMm * 10;
            } else if (gapUnit === 'px') {
                gapInMm = gapInMm * 0.264583;
            }

            // Calculate container width based on columns with gap
            // Container width = (label width × columns) + (gap × (columns - 1))
            const containerWidth = (labelWidthInMm * columns) + (gapInMm * (columns - 1));
            $wrapper.find('.label-row').css({
                'max-width': containerWidth + 'mm',
                'width': containerWidth + 'mm',
                'margin': '0 auto', // Center the row
                'gap': gap + gapUnit, // Apply gap between labels
                'row-gap': gap + gapUnit, // Gap between rows
                'column-gap': gap + gapUnit, // Gap between columns
                'margin-bottom': gap + gapUnit // Margin bottom for spacing between rows
            });
        }

        function resetSettings() {
            $('#label-width').val(30);
            $('#width-unit').val('mm');
            $('#label-height').val(64);
            $('#height-unit').val('mm');
            $('#font-size').val(7);
            $('#font-unit').val('px');
            $('#padding').val(1);
            $('#padding-unit').val('mm');
            $('#columns').val(4);
            $('#rows').val(3);
            $('#gap').val(2);
            $('#gap-unit').val('mm');
            $('#paper-size').val('A4');
            $('#orientation').val('portrait');

            applySettings();
        }

        // Enhanced draggable initialization for dynamic labels
        function initializeDraggable() {
            $(".label-container").draggable({
                containment: "#printable",
                cursor: "move",
                opacity: 0.7,
                revert: false
            });
        }

        // Re-initialize draggable after settings change
        function reinitializeDraggable() {
            // Check if draggable is already initialized before destroying
            if ($(".label-container").hasClass('ui-draggable')) {
                $(".label-container").draggable("destroy");
            }
            initializeDraggable();
        }

        // Load saved settings on page load
        $(document).ready(function() {
            // Set default values first
            $('#columns').val(4);
            $('#rows').val(3);

            // Function to apply default layout
            function applyDefaultLayout() {
                // Set default values first
                $('#label-width').val(30);
                $('#width-unit').val('mm');
                $('#label-height').val(64);
                $('#height-unit').val('mm');
                $('#columns').val(4);
                $('#rows').val(3);
                $('#padding').val(1);
                $('#padding-unit').val('mm');
                $('#font-size').val(7);
                $('#font-unit').val('px');
                $('#gap').val(2);
                $('#gap-unit').val('mm');
                $('#paper-size').val('A4');
                $('#orientation').val('portrait');

                // Apply layout immediately
                applyColumnLayout(4, 3, 2, 'mm');

                // Apply dimensions to all labels after layout is created
                setTimeout(function() {
                    // Force apply dimensions using !important equivalent by using inline styles
                    $('.label-container').each(function() {
                        $(this).css({
                            'width': '30mm',
                            'height': '64mm',
                            'padding': '1mm',
                            'margin': '0',
                            'font-size': '7px',
                            'box-sizing': 'border-box'
                        });
                    });

                    // Calculate responsive font size for 30mm x 64mm label
                    const labelWidthPx = 30 * 3.7795; // 30mm to px
                    const labelHeightPx = 64 * 3.7795; // 64mm to px
                    const minDimension = Math.min(labelWidthPx, labelHeightPx);
                    const responsiveFontSize = Math.max(6, Math.min(14, minDimension * 0.09)) + 'px';

                    // Apply responsive font size to sample code
                    $('.sample-code').css({
                        'font-size': responsiveFontSize
                    });

                    // Make QR code responsive - scale to fit container
                    $('.qr-code-container svg').css({
                        'width': '100%',
                        'height': 'auto',
                        'max-width': '100%',
                        'max-height': '75%'
                    });

                    // Apply gap to label-row (both row and column gap)
                    $('.label-row').css({
                        'gap': '2mm',
                        'row-gap': '2mm',
                        'column-gap': '2mm',
                        'margin-bottom': '2mm'
                    });

                    // Initialize draggable after layout is applied
                    setTimeout(function() {
                        initializeDraggable();
                    }, 100);
                }, 200);
            }

            if (typeof(Storage) !== "undefined") {
                const savedSettings = localStorage.getItem('labelSettings');
                if (savedSettings) {
                    const settings = JSON.parse(savedSettings);

                    // Check if saved settings have old values (38mm) and override with defaults
                    const width = parseFloat(settings.width) || 30;
                    const height = parseFloat(settings.height) || 64;
                    const widthUnit = settings.widthUnit || 'mm';
                    const heightUnit = settings.heightUnit || 'mm';

                    // If width is 38mm (old default), reset to new default (30mm)
                    if (width === 38 && widthUnit === 'mm') {
                        $('#label-width').val(30);
                        $('#width-unit').val('mm');
                    } else {
                        $('#label-width').val(width);
                        $('#width-unit').val(widthUnit);
                    }

                    // If height is 38mm (old default), reset to new default (64mm)
                    if (height === 38 && heightUnit === 'mm') {
                        $('#label-height').val(64);
                        $('#height-unit').val('mm');
                    } else {
                        $('#label-height').val(height);
                        $('#height-unit').val(heightUnit);
                    }

                    $('#font-size').val(settings.fontSize || 7);
                    $('#font-unit').val(settings.fontUnit || 'px');
                    $('#padding').val(settings.padding || 1);
                    $('#padding-unit').val(settings.paddingUnit || 'mm');
                    $('#gap').val(settings.gap || 2);
                    $('#gap-unit').val(settings.gapUnit || 'mm');
                    $('#paper-size').val(settings.paperSize || 'A4');
                    $('#orientation').val(settings.orientation || 'portrait');
                    $('#columns').val(settings.columns || 4);
                    $('#rows').val(settings.rows || 3);

                    // Apply the loaded settings (with corrected values if needed)
                    applySettings();
                } else {
                    // Apply default column layout on first load (4 columns x 3 rows)
                    setTimeout(function() {
                        applyDefaultLayout();
                    }, 100);
                }
            } else {
                // Apply default column layout if localStorage not supported (4 columns x 3 rows)
                setTimeout(function() {
                    applyDefaultLayout();
                }, 100);
            }
        });
    </script>

</body>

</html>
