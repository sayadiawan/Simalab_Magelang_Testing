<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Print-Label-Pasien.BYLALI-2002006-AP</title>
  <link rel="shortcut icon" href="">
  <link rel="stylesheet" href="{{asset('assets/admin/cdn-local/css/jquery-ui.min.css')}}">
  <style>
    .starter-template {
      padding: 0px 0px;
      text-align: center;
    }

    .label-container {
      display: inline-block;
      width: 50mm; /* Default width - ukuran tabung darah */
      height: 30mm; /* Default height */
      padding: 2mm; /* Optional padding */
      margin: 0; /* Default margin set to 0 (horizontal diatur di .label-row) */
      cursor: move; /* Mengubah kursor saat di atas label */
      position: relative; /* Required for draggable */
      font-size: 8px; /* Set font size */
      vertical-align: top; /* Align items to the top */
      margin-bottom: 0.3mm; /* Jarak vertikal antar kotak label */
    }

    .label-row {
      display: flex; /* Use flexbox to arrange labels */
      flex-wrap: wrap; /* Allow wrapping to the next line */
    }

    /* Hilangkan padding/margin bawaan .container agar area cetak mulai dari pojok kiri atas */
    #printable.container {
      max-width: 100%;
      width: 100%;
      margin: 0;
      padding: 0;
    }

    /* ===== Desain label klinik (barcode style) ===== */
    .clinic-label {
      width: 100%;
      height: 100%;
      border-radius: 4px;
      border: 1px solid #000;
      box-sizing: border-box;
      padding: 1.5mm;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      font-family: Arial, sans-serif;
      overflow: hidden;
    }

    .clinic-label-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: bold;
      margin-bottom: 0.3mm;
      flex-shrink: 0;
      max-height: 6.5mm;
      overflow: hidden;
      position: relative;
      z-index: 2; /* nama selalu di atas, tidak tertabrak kode vertikal */
    }

    .clinic-label-name {
      font-size: 8px;
      flex: 1;
      min-width: 0;
      word-wrap: break-word;
      line-height: 1.1;
      white-space: normal;
      overflow: hidden;
    }

    .clinic-label-dob {
      font-size: 6.5px;
      text-align: right;
      white-space: nowrap;
      flex-shrink: 0;
      margin-left: 1mm;
    }

    /* Middle: kolom kiri khusus kode vertikal, kanan barcode — tidak saling menabrak */
    .clinic-label-middle {
      flex: 1;
      min-height: 0;
      display: flex;
      flex-direction: row;
      align-items: stretch;
      margin: 0;
      overflow: hidden;
      position: relative;
      z-index: 1;
    }

    .clinic-label-side-code {
      writing-mode: vertical-rl;
      text-orientation: mixed;
      font-size: 8px;
      font-weight: 700;
      line-height: 1;
      letter-spacing: 0.2px;
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
      flex: 0 0 3mm;
      width: 3mm;
      max-width: 3mm;
      height: 100%;
      max-height: 100%;
      overflow: hidden;
      box-sizing: border-box;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      margin: 0 0.3mm 0 0;
    }

    .clinic-label-barcode-wrapper {
      flex: 1 1 auto;
      min-width: 0;
      min-height: 0;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      justify-content: center;
      overflow: hidden;
    }

    .clinic-label-barcode-text {
      font-size: 8px;
      font-weight: 700;
      letter-spacing: 0.2px;
      line-height: 1;
      text-align: left;
      margin: 0 0 0.2mm 0;
      flex-shrink: 0;
      font-variant-numeric: tabular-nums;
      width: 100%;
    }

    .clinic-label-barcode {
      display: block;
      width: 100%;
      max-width: 100%;
      max-height: 100%;
      height: auto;
      margin: 0;
      align-self: stretch;
      /* Jaga tepi batang tajam saat cetak thermal */
      image-rendering: crisp-edges;
      shape-rendering: crispEdges;
    }

    .clinic-label-regdate {
      text-align: center;
      font-size: 6px;
      margin-top: 0.2mm;
      letter-spacing: 0.2px;
      flex-shrink: 0;
      line-height: 1;
      overflow: hidden;
      position: relative;
      z-index: 2;
    }

    .clinic-label-bottom {
      display: flex;
      justify-content: space-between;
      align-items: stretch;
      gap: 1mm;
      font-size: 7px;
      margin-top: 0.3mm;
      flex-shrink: 0;
      line-height: 1.1;
      overflow: hidden;
      position: relative;
      z-index: 2;
    }

    .clinic-label-sample {
      max-width: 28%;
      word-wrap: break-word;
      display: flex;
      align-items: center;
      font-size: 7px;
      font-weight: 600;
      overflow: hidden;
    }

    /* Badge untuk jenis sampel (disamakan konsepnya dengan penerima-sampel) */
    .clinic-label-sample .badge-custom {
      background: #0b3a5c;
      color: #ffffff;
      padding: 1px 3px;
      border-radius: 3px;
      font-size: 6px;
      font-weight: 600;
      display: inline-block;
      margin-right: 1px;
      margin-bottom: 1px;
    }

    .clinic-label-params {
      max-width: 70%;
      flex: 1;
      min-width: 0;
      text-align: right;
      word-wrap: break-word;
      font-size: 7px;
      line-height: 1.15;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-end;
    }

    /* Jarak horizontal antar kotak label: 0.3mm antara kotak, bukan dari tulisan ke kotak */
    .label-row .label-container {
      margin-right: 0.3mm;
    }

    .label-row .label-container:last-child {
      margin-right: 0;
    }

    /* Settings Panel */
    .settings-panel {
      position: fixed;
      bottom: 80px; /* Positioned above the buttons */
      right: 20px;
      background: white;
      border: 2px solid #28a745;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      font-family: Arial, sans-serif;
      width: 320px;
      max-height: calc(100vh - 120px);
      max-height: calc(100dvh - 120px);
      overflow-y: auto;
      overscroll-behavior: contain;
      -webkit-overflow-scrolling: touch;
      touch-action: pan-y;
    }

    .settings-panel h3 {
      margin: 0 0 10px 0;
      color: #28a745;
      font-size: 16px;
    }

    .settings-panel h4 {
      margin: 12px 0 8px 0;
      color: #333;
      font-size: 13px;
      border-top: 1px solid #e0e0e0;
      padding-top: 10px;
    }

    .setting-group {
      margin-bottom: 10px;
    }

    .setting-group label {
      display: inline-block;
      width: 110px;
      font-size: 12px;
      font-weight: bold;
      vertical-align: middle;
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

    .setting-group select#barcode-format,
    .setting-group select#paper-size {
      width: 140px;
    }

    .setting-group .unit-hint {
      font-size: 11px;
      color: #666;
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
      /* Sembunyikan tombol & panel saat print */
      #print-button, .settings-panel, .toggle-settings {
        display: none;
      }

      #cetak {
        display: none;
      }

      /* Paksa desain 50 × 30 mm — jangan ikut melebar ke kertas driver 58 × 210 mm */
      html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 50mm !important;
        min-height: 0 !important;
      }

      /* display:contents supaya grouping 4-per-baris tidak menahan page-break */
      body.thermal-print .label-row {
        display: contents !important;
      }

      body.thermal-print #printable,
      body.thermal-print #printable.container {
        width: 50mm !important;
        max-width: 50mm !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
      }

      body.thermal-print .thermal-page,
      body.thermal-print .label-container,
      body.thermal-print .label-barcode {
        display: block !important;
        width: 50mm !important;
        height: 30mm !important;
        max-width: 50mm !important;
        max-height: 30mm !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-after: always;
        break-after: page;
        page-break-inside: avoid;
        break-inside: avoid;
        box-sizing: border-box !important;
        overflow: hidden !important;
      }

      body.thermal-print .thermal-page:not(.thermal-page-first) {
        page-break-before: always;
        break-before: page;
      }

      body.thermal-print .thermal-page-last,
      body.thermal-print .label-container:last-child,
      body.thermal-print .label-barcode:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
      }

      body.thermal-print .thermal-gap-spacer {
        display: none !important;
      }

      body.thermal-print .label-container .clinic-label {
        width: 50mm !important;
        height: 30mm !important;
        max-width: 50mm !important;
        max-height: 30mm !important;
        box-sizing: border-box !important;
      }
    }

    /* Paksa halaman cetak 50 × 30 mm (driver Windows tetap bisa 58 × 210 mm) */
    @page {
      size: 50mm 30mm;
      margin: 0;
    }

    /* Simulasi area kertas di layar (tidak mempengaruhi hasil print) */
    @media screen {
      body {
        background: #e5e5e5;
      }

      #printable {
        /* Ukuran lebar/tinggi akan diatur dinamis via JS berdasarkan pilihan paper (A4 / Folio) */
        margin: 10px auto;
        background: #ffffff;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        padding-top: 5mm; /* sedikit jarak visual dari tepi atas simulasi */
      }
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

    /* HP / layar sempit: panel diikat atas-bawah agar scroll sampai ke bagian paling atas */
    @media screen and (max-width: 640px) {
      .settings-panel {
        top: max(8px, env(safe-area-inset-top, 0px));
        bottom: calc(64px + env(safe-area-inset-bottom, 0px));
        left: 8px;
        right: 8px;
        width: auto;
        max-height: none;
      }

      .toggle-settings {
        left: 12px;
        right: auto;
        bottom: max(12px, env(safe-area-inset-bottom, 0px));
        padding: 10px 14px;
        font-size: 13px;
        z-index: 1003;
      }

      #print-button {
        right: 12px;
        bottom: max(12px, env(safe-area-inset-bottom, 0px));
        z-index: 1003;
      }
    }
  </style>

  <script src="{{asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js')}}"></script>
  <script src="{{asset('assets/admin/cdn-local/js/jquery-ui.min.js')}}"></script>
  {{-- JsBarcode untuk QR 1D (Code 128) - gunakan file lokal --}}
  {{-- <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script> --}}
  <script src="{{ asset('assets/admin/cdn-local/js/JsBarcode.all.min.js') }}"></script>
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
        <input type="number" id="label-width" value="50" min="10" max="200">
    <select id="width-unit">
      <option value="mm">mm</option>
      <option value="cm">cm</option>
      <option value="px">px</option>
    </select>
  </div>

  <div class="setting-group">
    <label for="label-height">Tinggi:</label>
        <input type="number" id="label-height" value="30" min="10" max="200">
    <select id="height-unit">
      <option value="mm">mm</option>
      <option value="cm">cm</option>
      <option value="px">px</option>
    </select>
  </div>

  <div class="setting-group">
    <label for="font-size">Font dasar:</label>
    <input type="number" id="font-size" value="8" min="6" max="20" step="0.5">
    <select id="font-unit">
      <option value="px">px</option>
      <option value="pt">pt</option>
    </select>
  </div>

  <h4>Ukuran font elemen</h4>
  <div class="setting-group">
    <label for="font-name">Nama:</label>
    <input type="number" id="font-name" value="8" min="5" max="16" step="0.5">
    <span class="unit-hint">px</span>
  </div>
  <div class="setting-group">
    <label for="font-dob">Tgl lahir:</label>
    <input type="number" id="font-dob" value="6.5" min="5" max="12" step="0.5">
    <span class="unit-hint">px</span>
  </div>
  <div class="setting-group">
    <label for="font-regdate">Tgl register:</label>
    <input type="number" id="font-regdate" value="6" min="4" max="12" step="0.5">
    <span class="unit-hint">px</span>
  </div>
  <div class="setting-group">
    <label for="font-noreg">Kode barcode / no. spesimen:</label>
    <input type="number" id="font-noreg" value="8" min="3.5" max="10" step="0.5">
    <span class="unit-hint">px</span>
  </div>
  <div class="setting-group">
    <label for="font-sample">Jenis sampel:</label>
    <input type="number" id="font-sample" value="7.5" min="4" max="16" step="0.5">
    <span class="unit-hint">px</span>
  </div>
  <div class="setting-group">
    <label for="font-params">Paket parameter:</label>
    <input type="number" id="font-params" value="7.5" min="4" max="12" step="0.5">
    <span class="unit-hint">px</span>
  </div>

  <h4>Ukuran barcode</h4>
  <div class="setting-group" style="display:block; font-size:10px; color:#555; line-height:1.35; margin-bottom:8px;">
    Default label tabung: <strong>Codabar (NW-7)</strong>, 10 digit (DDMM lahir + 5 digit nomor spesimen + 1 digit jenis).
    Digit jenis: 1 Darah, 2 Serum, <strong>3 Plasma</strong>, <strong>8 Plasma NaF</strong>, 4 Urine, 5 Feses, 6 Swab, 7 Blood Cell, 9 Lainnya.
    Batang <strong>lebar mengisi area</strong> dan <strong>tebal</strong> (module ≥ 0.25&nbsp;mm, tinggi bar lebih besar) agar terbaca scanner analyzer.
    Isi kode barcode tidak diubah. Start/stop Codabar <code>A…A</code> ditambah otomatis saat encode.
  </div>
  <div class="setting-group">
    <label for="barcode-format">Tipe:</label>
    <select id="barcode-format">
      <option value="CODE39">CODE39</option>
      <option value="CODE128">CODE128</option>
      <option value="ITF">ITF (2 of 5)</option>
      <option value="codabar" selected>Codabar (NW-7)</option>
    </select>
  </div>
  <div class="setting-group">
    <label for="barcode-height-pct">Tinggi:</label>
    <input type="number" id="barcode-height-pct" value="62" min="20" max="75" step="1">
    <span class="unit-hint">% area</span>
  </div>
  <div class="setting-group">
    <label for="barcode-width-pct">Lebar:</label>
    <input type="number" id="barcode-width-pct" value="100" min="40" max="100" step="5">
    <span class="unit-hint">% area</span>
  </div>
  <div class="setting-group">
    <label for="barcode-bar-width">Tebal garis:</label>
    <input type="number" id="barcode-bar-width" value="3" min="2" max="5" step="0.5">
    <span class="unit-hint">≥0.25mm</span>
  </div>
  <div class="setting-group">
    <label for="barcode-spacing">Jarak garis:</label>
    <input type="number" id="barcode-spacing" value="0" min="0" max="8" step="1">
    <span class="unit-hint">antar batang</span>
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
    <label for="paper-size">Paper:</label>
    <select id="paper-size">
      <option value="A4">A4</option>
      <option value="F4">Folio (F4)</option>
      <option value="thermal-50x30" selected>50x30mm (dipaksa)</option>
      <option value="thermal-57x30">Thermal 57x30mm</option>
      <option value="thermal-57x40">Thermal 57x40mm</option>
      <option value="thermal-58x30">Thermal 58x30mm (roll 58)</option>
      <option value="thermal-58x40">Thermal 58x40mm (roll 58)</option>
      <option value="thermal-80x80">Thermal 80x80mm</option>
    </select>
  </div>

  <div class="setting-group" id="thermal-gap-group" style="display:none;">
    <label for="label-gap">Gap pemisah:</label>
    <input type="number" id="label-gap" value="3" min="0" max="10" step="0.5">
    <span class="unit-hint">mm</span>
  </div>
  <div id="thermal-gap-hint" style="display:none; font-size:10px; color:#555; line-height:1.35; margin:-4px 0 8px 0;">
    Dibagi rata atas dan bawah. Naikkan jika cetakan masih bergeser ke pemisah.
  </div>

{{--  <div class="setting-group">--}}
{{--    <label for="labels-per-row">Per Baris:</label>--}}
{{--    <input type="number" id="labels-per-row" value="4" min="1" max="10">--}}
{{--  </div>--}}

  <button class="apply-btn" onclick="resetSettings()" style="background-color: #dc3545;">Reset</button>
  <div style="margin-top: 8px; font-size: 11px; color: #666;">Perubahan langsung diterapkan (auto-save).</div>
</div>

<div id="printable" class="container">
  @php
    $label_count = count($get_data);
  @endphp

  @if ($label_count > 0)
    <div class="label-row">
      @for ($n = 0; $n < $label_count; $n++)
        <div class="label-container label-barcode" id="label-{{ $n }}">
          <div class="clinic-label">
            {{-- Bagian atas: Nama + (Gender), dan Tanggal Lahir di kanan --}}
            <div class="clinic-label-top">
              <div class="clinic-label-name">
                {{ mb_strtoupper($get_data[$n]->pasien->nama_pasien, 'UTF-8') }}
                @if(!empty($get_data[$n]->pasien->label_gender_abbr))
                  ({{ $get_data[$n]->pasien->label_gender_abbr }})
                @endif
              </div>
              <div class="clinic-label-dob">
                @if(!empty($get_data[$n]->pasien->tgllahir_pasien))
                  {{ date('d/m/Y', strtotime($get_data[$n]->pasien->tgllahir_pasien)) }}
                @endif
              </div>
            </div>

            {{-- Bagian tengah: kode vertikal + area barcode --}}
            <div class="clinic-label-middle">
              <div class="clinic-label-side-code">
                {{ $get_data[$n]->getSpesimenNumber() }}
              </div>
              <div class="clinic-label-barcode-wrapper">
                <div class="clinic-label-barcode-text">{{ $get_data[$n]->label_register_code }}</div>
                <svg
                  class="clinic-label-barcode"
                  data-code="{{ $get_data[$n]->label_register_code }}"
                  jsbarcode-format="codabar"
                  jsbarcode-height="50"
                  jsbarcode-width="2"
                  jsbarcode-margin="0"
                  jsbarcode-displayValue="false">
                </svg>
              </div>
            </div>

            {{-- Tanggal pendaftaran di bawah barcode --}}
            <div class="clinic-label-regdate">
              {{ $get_data[$n]->tglregister_permohonan_uji_klinik ? \Carbon\Carbon::parse($get_data[$n]->tglregister_permohonan_uji_klinik)->format('d/m/Y') : '-' }}
            </div>

            {{-- Bagian bawah: jenis sampel di kiri (1 jenis sampel per label), parameter di kanan --}}
            <div class="clinic-label-bottom">
              <div class="clinic-label-sample">
                {{-- Tampilkan 1 jenis sampel saja (sudah dipecah di controller) --}}
                @if (!empty($get_data[$n]->label_sample_type))
                  {{ $get_data[$n]->label_sample_type }}
                @endif
              </div>
              <div class="clinic-label-params">
                {{ $get_data[$n]->label_parameters ?? '' }}
              </div>
            </div>
          </div>
        </div>
        @if (($n + 1) % 4 === 0 && ($n + 1) < $label_count)
    </div><div class="label-row">
      @endif
      @endfor
    </div>
  @endif
</div>

<script>
  $(function() {
    // Initialize draggable feature for all labels
    initializeDraggable();

    // Sesuaikan layout & generate barcode setelah DOM siap
    adjustLabelLayout();

    // Print button functionality
    $('#print-button').on('click', function() {
      prepareThermalPrintLayout();
      window.print();
    });

    $(window).on('beforeprint', function() {
      prepareThermalPrintLayout();
    });

    $(window).on('afterprint', function() {
      restoreAfterPrintLayout();
    });

    // Toggle settings panel
    $('#toggle-settings').on('click', function(e) {
      e.stopPropagation();
      toggleSettingsPanel();
    });

    // Tutup panel saat klik di luar (sembarang area halaman)
    $('#settings-panel').on('click', function(e) {
      e.stopPropagation();
    });

    $(document).on('click', function() {
      closeSettingsPanel();
    });

    // Lepas scroll lock body jika orientasi/ukuran layar berubah
    $(window).on('resize orientationchange', function() {
      if (!$('#settings-panel').is(':visible') || !isMobileSettingsLayout()) {
        $('body').css('overflow', '');
      }
    });

    // Semua setting: langsung apply saat berubah (tanpa klik Terapkan)
    bindLiveSettings();
  });

  var settingsApplyTimer = null;
  var settingsLiveEnabled = false;

  function isMobileSettingsLayout() {
    return window.matchMedia('(max-width: 640px)').matches;
  }

  function closeSettingsPanel() {
    $('#settings-panel').hide();
    $('body').css('overflow', '');
  }

  function openSettingsPanel() {
    var $panel = $('#settings-panel');
    $panel.show();
    $panel.scrollTop(0);
    if (isMobileSettingsLayout()) {
      $('body').css('overflow', 'hidden');
    }
  }

  function toggleSettingsPanel() {
    if ($('#settings-panel').is(':visible')) {
      closeSettingsPanel();
    } else {
      openSettingsPanel();
    }
  }

  function scheduleApplySettings() {
    if (!settingsLiveEnabled) {
      return;
    }
    if (settingsApplyTimer) {
      clearTimeout(settingsApplyTimer);
    }
    settingsApplyTimer = setTimeout(function () {
      settingsApplyTimer = null;
      applySettings({ silent: true });
    }, 120);
  }

  function bindLiveSettings() {
    var $panel = $('#settings-panel');

    // Number / text: update saat mengetik / stepper
    $panel.on('input change', 'input[type="number"]', function () {
      scheduleApplySettings();
    });

    // Unit & paper select
    $panel.on('change', 'select', function () {
      var id = $(this).attr('id');
      if (id === 'paper-size') {
        applyPaperPreset($(this).val(), true);
      }
      scheduleApplySettings();
    });
  }
  // Spesifikasi sample barcode: CODE39/CODE128/ITF/Codabar, lebar batang ≥ 0.2 mm.
  // Isi data-code tidak diubah — hanya tipe symbology & parameter fisik cetak.
  var BARCODE_MIN_MODULE_MM = 0.25;
  var BARCODE_DEFAULT_BAR_WIDTH = 3;
  var BARCODE_MIN_BAR_WIDTH = 2;
  var BARCODE_MAX_BAR_WIDTH = 5;
  var BARCODE_DEFAULT_FORMAT = 'codabar';
  var BARCODE_DEFAULT_SPACING = 0;
  var BARCODE_MIN_SPACING = 0;
  var BARCODE_MAX_SPACING = 8;
  var BARCODE_QUIET_ZONE = 8;
  var BARCODE_QUIET_ZONE_LEFT = 8;
  var BARCODE_QUIET_ZONE_RIGHT = 8;
  var BARCODE_DEFAULT_WIDTH_PCT = 100;
  var BARCODE_MIN_WIDTH_PCT = 40;
  var BARCODE_MAX_WIDTH_PCT = 100;
  var BARCODE_DEFAULT_HEIGHT_PCT = 62;
  var BARCODE_STYLE_REV = 5;
  var CSS_PX_PER_MM = 96 / 25.4;
  // Pitch thermal = tinggi stiker + gap pemisah die-cut (bukan tinggi konten).
  var THERMAL_DEFAULT_GAP_MM = 3;
  var THERMAL_MIN_GAP_MM = 0;
  var THERMAL_MAX_GAP_MM = 10;
  var thermalPrintPrepared = false;
  var thermalPrintSnapshot = null;

  var BARCODE_FORMAT_OPTIONS = ['CODE39', 'CODE128', 'ITF', 'codabar'];

  function normalizeBarcodeFormat(format) {
    var f = String(format || BARCODE_DEFAULT_FORMAT).trim();
    if (BARCODE_FORMAT_OPTIONS.indexOf(f) === -1) {
      return BARCODE_DEFAULT_FORMAT;
    }
    return f;
  }

  function getSelectedBarcodeFormat() {
    return normalizeBarcodeFormat($('#barcode-format').val());
  }

  function estimateBarcodeModuleCount(payload, format) {
    payload = String(payload || '');
    format = normalizeBarcodeFormat(format);

    if (format === 'CODE128') {
      var dataSymbols;
      if (/^\d+$/.test(payload) && payload.length % 2 === 0) {
        dataSymbols = payload.length / 2;
      } else if (/^\d+$/.test(payload)) {
        dataSymbols = Math.ceil(payload.length / 2) + 1;
      } else {
        dataSymbols = payload.length;
      }
      // start + data + checksum + stop
      return 11 + (dataSymbols * 11) + 11 + 13;
    }

    if (format === 'CODE39') {
      return (payload.length + 2) * 16;
    }

    if (format === 'ITF') {
      var digits = payload.replace(/\D/g, '');
      if (digits.length % 2 !== 0) {
        digits = '0' + digits;
      }
      return 4 + (digits.length / 2) * 18 + 5;
    }

    if (format === 'codabar') {
      // 4 bar + 3 space per karakter, plus start/stop jika belum ada.
      var n = payload.length;
      if (!/^[A-D]/i.test(payload)) {
        n += 2;
      }
      return Math.max(48, n * 14);
    }

    return Math.max(48, payload.length * 12);
  }

  function fitBarcodeModuleWidth(payload, format, targetWidth, minWidth) {
    var modules = estimateBarcodeModuleCount(payload, format);
    var quiet = BARCODE_QUIET_ZONE_LEFT + BARCODE_QUIET_ZONE_RIGHT;
    var inner = Math.max(10, targetWidth - quiet);
    var fitted = inner / Math.max(1, modules);
    // Isi penuh lebar label agar batang setebal mungkin, tanpa overflow lalu di-scale.
    return Math.max(0.8, Math.min(BARCODE_MAX_BAR_WIDTH, Math.max(minWidth * 0.5, fitted)));
  }
  function isBarcodeCodeCompatible(code, format) {
    code = String(code || '');
    format = normalizeBarcodeFormat(format);

    if (code === '') {
      return false;
    }

    if (format === 'CODE128') {
      return true;
    }

    if (format === 'CODE39') {
      return /^[0-9A-Z\-. $/+%]*$/.test(code.toUpperCase());
    }

    if (format === 'ITF') {
      var digits = code.replace(/\D/g, '');
      return digits.length > 0;
    }

    if (format === 'codabar') {
      return /^[0-9A-D\-$:/.+]+$/i.test(code);
    }

    return true;
  }

  // Nilai yang dikirim ke JsBarcode (data-code di HTML tetap utuh).
  function getBarcodeEncodePayload(code, format) {
    code = String(code || '');
    format = normalizeBarcodeFormat(format);

    if (format === 'ITF') {
      // ITF/Interleaved 2 of 5: hanya digit.
      // Format label tabung: 10 digit = DDMM lahir + nomor spesimen 5 + kode jenis 1 (contoh 2602039022).
      var digits = code.replace(/\D/g, '');
      if (!digits) {
        return '';
      }
      // Syarat ITF: jumlah digit genap — pad 0 di depan jika ganjil.
      if (digits.length % 2 !== 0) {
        digits = '0' + digits;
      }
      return digits;
    }

    if (format === 'codabar') {
      var body = code.toUpperCase().replace(/\s+/g, '');
      if (!body) {
        return '';
      }
      if (/^[A-D].+[A-D]$/.test(body)) {
        return body;
      }
      if (/^[0-9\-$:/.+]+$/.test(body)) {
        return 'A' + body + 'A';
      }
      return '';
    }

    return code;
  }

  // parseFloat lokal: di HP ID sering pakai koma desimal (6,5)
  function parseLocaleNumber(value, fallback) {
    if (value == null || value === '') {
      return fallback;
    }
    var n = parseFloat(String(value).replace(',', '.').trim());
    return isNaN(n) ? fallback : n;
  }

  function getBarcodeSettings() {
    var pct = parseLocaleNumber($('#barcode-height-pct').val(), BARCODE_DEFAULT_HEIGHT_PCT);
    pct = Math.max(20, Math.min(75, pct));

    var barWidth = parseLocaleNumber($('#barcode-bar-width').val(), BARCODE_DEFAULT_BAR_WIDTH);
    barWidth = Math.max(BARCODE_MIN_BAR_WIDTH, Math.min(BARCODE_MAX_BAR_WIDTH, barWidth));

    var minPxForSpec = Math.ceil(BARCODE_MIN_MODULE_MM * CSS_PX_PER_MM * 10) / 10;
    if (barWidth < minPxForSpec) {
      barWidth = Math.min(BARCODE_MAX_BAR_WIDTH, Math.max(BARCODE_MIN_BAR_WIDTH, minPxForSpec));
    }

    var format = getSelectedBarcodeFormat();

    var spacing = parseLocaleNumber($('#barcode-spacing').val(), BARCODE_DEFAULT_SPACING);
    spacing = Math.max(BARCODE_MIN_SPACING, Math.min(BARCODE_MAX_SPACING, spacing));

    var widthPct = parseLocaleNumber($('#barcode-width-pct').val(), BARCODE_DEFAULT_WIDTH_PCT);
    widthPct = Math.max(BARCODE_MIN_WIDTH_PCT, Math.min(BARCODE_MAX_WIDTH_PCT, widthPct));

    return {
      heightPct: pct,
      heightRatio: pct / 100,
      widthPct: widthPct,
      barWidth: barWidth,
      barGap: spacing,
      quietZone: BARCODE_QUIET_ZONE,
      quietZoneLeft: BARCODE_QUIET_ZONE_LEFT,
      quietZoneRight: BARCODE_QUIET_ZONE_RIGHT,
      format: format
    };
  }

  // Perlebar jarak putih antar batang hitam tanpa mengubah tebal batang.
  function expandBarcodeBarGaps(svg, extraGap) {
    extraGap = Number(extraGap) || 0;
    if (extraGap <= 0 || !svg) {
      return;
    }

    var rects = Array.prototype.slice.call(svg.querySelectorAll('rect'));
    if (rects.length < 2) {
      return;
    }

    var parsed = rects.map(function (r) {
      return {
        el: r,
        x: parseFloat(r.getAttribute('x')) || 0,
        w: parseFloat(r.getAttribute('width')) || 0,
        h: parseFloat(r.getAttribute('height')) || 0
      };
    }).filter(function (r) {
      return r.w > 0 && r.h > 0;
    });

    if (parsed.length < 2) {
      return;
    }

    var maxW = 0;
    parsed.forEach(function (r) {
      if (r.w > maxW) {
        maxW = r.w;
      }
    });

    // Rect terlebar biasanya background; batang barcode lebih sempit.
    var bars = parsed
      .filter(function (r) {
        return r.w < maxW * 0.85;
      })
      .sort(function (a, b) {
        return a.x - b.x;
      });

    var backgrounds = parsed.filter(function (r) {
      return r.w >= maxW * 0.85;
    });

    if (bars.length < 2) {
      return;
    }

    var offset = 0;
    for (var i = 1; i < bars.length; i++) {
      offset += extraGap;
      bars[i].el.setAttribute('x', String(bars[i].x + offset));
    }

    var vbParts = String(svg.getAttribute('viewBox') || '').trim().split(/[\s,]+/);
    var vbX = parseFloat(vbParts[0]) || 0;
    var vbY = parseFloat(vbParts[1]) || 0;
    var vbW = parseFloat(vbParts[2]) || 0;
    var vbH = parseFloat(vbParts[3]) || 0;
    if (!vbW) {
      var last = bars[bars.length - 1];
      vbW = last.x + last.w + BARCODE_QUIET_ZONE;
    }
    var newVbW = vbW + offset;
    svg.setAttribute('viewBox', vbX + ' ' + vbY + ' ' + newVbW + ' ' + vbH);

    backgrounds.forEach(function (r) {
      r.el.setAttribute('width', String(newVbW));
    });
  }

  function regenerateBarcodes() {
    if (typeof JsBarcode === 'undefined') {
      return;
    }

    var barcodeSettings = getBarcodeSettings();
    var formatErrors = [];

    $('.clinic-label-barcode').each(function () {
      var code = $(this).data('code') || '';
      var $wrapper = $(this).closest('.clinic-label-barcode-wrapper');
      var $text = $wrapper.find('.clinic-label-barcode-text');
      var wrapperHeight = $wrapper.innerHeight() || $wrapper.height() || 30;
      var wrapperWidth = $wrapper.innerWidth() || $wrapper.width() || 80;
      var textHeight = $text.length ? ($text.outerHeight(true) || 0) : 0;
      var barHeight = Math.max(16, Math.floor(wrapperHeight - textHeight - 1));
      var targetWidth = Math.max(10, Math.floor(wrapperWidth * (barcodeSettings.widthPct / 100)));

      $(this).empty().css({
        width: '',
        height: '',
        maxWidth: '',
        maxHeight: '',
        transform: ''
      });

      if (!code) {
        return;
      }

      if (!isBarcodeCodeCompatible(code, barcodeSettings.format)) {
        formatErrors.push(code + ' → ' + barcodeSettings.format);
        return;
      }

      var encodePayload = getBarcodeEncodePayload(code, barcodeSettings.format);
      if (!encodePayload) {
        formatErrors.push(code + ' → ' + barcodeSettings.format);
        return;
      }

      var moduleWidth = fitBarcodeModuleWidth(
        encodePayload,
        barcodeSettings.format,
        targetWidth,
        barcodeSettings.barWidth
      );

      var jsOptions = {
        format: barcodeSettings.format,
        displayValue: false,
        height: barHeight,
        width: moduleWidth,
        margin: 0,
        marginLeft: barcodeSettings.quietZoneLeft,
        marginRight: barcodeSettings.quietZoneRight,
        marginTop: 0,
        marginBottom: 0
      };

      if (barcodeSettings.format === 'CODE39') {
        jsOptions.mod43 = false;
      }

      try {
        JsBarcode(this, encodePayload, jsOptions);

        // CODE128: jarak antar batang mengikuti symbology asli (jangan re-gap manual).
        if (barcodeSettings.format !== 'CODE128') {
          expandBarcodeBarGaps(this, barcodeSettings.barGap);
        }

        // Isi lebar area; tinggi batang mengikuti slot (tebal). Jangan scale-down module.
        this.setAttribute('width', String(targetWidth));
        this.setAttribute('height', String(barHeight));
        this.setAttribute('preserveAspectRatio', 'none');

        $(this).css({
          'width': targetWidth + 'px',
          'height': barHeight + 'px',
          'max-width': '100%',
          'max-height': '100%',
          'display': 'block',
          'margin-left': '0',
          'margin-right': '0',
          'transform': 'none',
          'transform-origin': 'left center'
        });
      } catch (e) {
        formatErrors.push(code + ' (' + barcodeSettings.format + ')');
        console && console.error && console.error('Error generating barcode', e);
      }
    });

    if (formatErrors.length > 0 && !window.__barcodeFormatWarned) {
      window.__barcodeFormatWarned = true;
      console.warn('Barcode tidak kompatibel dengan tipe ' + barcodeSettings.format + ':', formatErrors);
    } else if (formatErrors.length === 0) {
      window.__barcodeFormatWarned = false;
    }
  }

  // Batas porsi tinggi maksimum untuk baris jenis sampel + parameter.
  var BOTTOM_HEIGHT_MAX_RATIO = 0.36;

  function fitSideCodeToMiddle($label, middleHeight) {
    var $side = $label.find('.clinic-label-side-code');
    if (!$side.length || !middleHeight) {
      return;
    }

    // Preferensi dari Settings; mengecil otomatis jika tidak muat di tinggi area barcode
    var preferred = getElementFontSettings().noreg;
    var fontSize = preferred;
    var minFont = Math.min(3.5, preferred);
    $side.css({
      'font-size': fontSize + 'px',
      'font-weight': '700',
      'letter-spacing': '0.2px',
      'height': middleHeight + 'px',
      'max-height': middleHeight + 'px'
    });

    var safety = 0;
    while ($side[0].scrollHeight > middleHeight + 1 && fontSize > minFont && safety < 24) {
      fontSize -= 0.25;
      $side.css('font-size', fontSize + 'px');
      safety++;
    }
  }

  function adjustLabelLayout() {
    applyElementFonts();
    autoFitLabelName();

    var barcodeRatio = getBarcodeSettings().heightRatio;

    $('.label-container').each(function () {
      var $label = $(this).find('.clinic-label');
      var $top = $label.find('.clinic-label-top');
      var $middle = $label.find('.clinic-label-middle');
      var $regdate = $label.find('.clinic-label-regdate');
      var $bottom = $label.find('.clinic-label-bottom');
      var $barcodeWrapper = $label.find('.clinic-label-barcode-wrapper');

      // Reset dulu supaya pengukuran tinggi alami (natural) bottom tidak terpengaruh nilai sebelumnya.
      $bottom.css({'min-height': '', 'max-height': ''});
      $middle.css({'height': '', 'max-height': '', 'flex': ''});
      $barcodeWrapper.css('height', '');

      var labelInnerHeight = $label.innerHeight();
      var fixedHeight = $top.outerHeight(true) + $regdate.outerHeight(true);
      var bottomNaturalHeight = Math.max(10, $bottom.outerHeight(true));
      var available = Math.max(10, labelInnerHeight - fixedHeight - 1);

      var barcodeHeight = Math.max(12, Math.round(available * barcodeRatio));
      // Sisakan ruang bawah: maksimal (1 - barcodeRatio) atau BOTTOM_HEIGHT_MAX_RATIO, mana yang lebih longgar
      var bottomCap = Math.round(available * Math.max(BOTTOM_HEIGHT_MAX_RATIO, 1 - barcodeRatio));
      var bottomHeight = Math.min(
        bottomCap,
        Math.max(bottomNaturalHeight, available - barcodeHeight)
      );
      // Sisa ruang setelah bottom dipastikan kembali ke barcode agar proporsi tetap seimbang.
      var middleHeight = Math.max(12, available - bottomHeight);

      $middle.css({
        'height': middleHeight + 'px',
        'max-height': middleHeight + 'px',
        'flex': '0 0 ' + middleHeight + 'px'
      });
      $barcodeWrapper.css('height', middleHeight + 'px');
      $bottom.css({
        'min-height': bottomHeight + 'px',
        'max-height': bottomHeight + 'px'
      });

      fitSideCodeToMiddle($label, middleHeight);
    });

    regenerateBarcodes();
    autoFitLabelBottomText();
  }

  function prepareThermalPrintLayout() {
    var paperSize = 'thermal-50x30';
    var preset = getPaperPresets()[paperSize];

    var labelWidth = 50;
    var labelHeight = 30;

    updatePrintPageStyle(paperSize);
    closeSettingsPanel();

    if (!thermalPrintPrepared) {
      thermalPrintSnapshot = $('#printable').html();
      thermalPrintPrepared = true;

      var $labels = $('#printable').find('.label-container').detach();
      $('#printable').empty();
      $labels.each(function (index) {
        var $page = $('<div class="thermal-page"></div>');
        if (index === 0) {
          $page.addClass('thermal-page-first');
        }
        if (index === $labels.length - 1) {
          $page.addClass('thermal-page-last');
        }
        $page.append(this);
        $('#printable').append($page);
      });
    }

    // Paksa 50 × 30 mm — jangan stretch ke kertas driver 58 × 210 mm
    $('#printable').css({
      width: labelWidth + 'mm',
      maxWidth: labelWidth + 'mm',
      minHeight: 0,
      margin: 0,
      padding: 0
    });
    $('.thermal-page').css({
      width: labelWidth + 'mm',
      maxWidth: labelWidth + 'mm',
      height: labelHeight + 'mm',
      minHeight: labelHeight + 'mm',
      maxHeight: labelHeight + 'mm',
      margin: 0,
      padding: 0,
      overflow: 'hidden'
    });
    $('.label-container').css({
      width: labelWidth + 'mm',
      maxWidth: labelWidth + 'mm',
      height: labelHeight + 'mm',
      minHeight: labelHeight + 'mm',
      maxHeight: labelHeight + 'mm',
      padding: 0,
      margin: 0,
      overflow: 'hidden'
    });
    $('.label-container .clinic-label').css({
      width: labelWidth + 'mm',
      height: labelHeight + 'mm',
      maxHeight: labelHeight + 'mm'
    });

    adjustLabelLayout();
  }

  function restoreAfterPrintLayout() {
    if (thermalPrintPrepared && thermalPrintSnapshot != null) {
      $('#printable').html(thermalPrintSnapshot);
      thermalPrintSnapshot = null;
      thermalPrintPrepared = false;
    }
    $('.label-container .clinic-label').css({
      height: '',
      maxHeight: ''
    });
    applySettings({ silent: true });
  }

  function getPaperPresets() {
    return {
      'A4': { width: 50, height: 30, paperWidth: 297, paperHeight: 210, thermal: false, gap: 0 },
      'F4': { width: 50, height: 30, paperWidth: 330, paperHeight: 215, thermal: false, gap: 0 },
      'thermal-50x30': { width: 50, height: 30, paperWidth: 50, paperHeight: 30, thermal: true, gap: 3 },
      'thermal-57x30': { width: 57, height: 30, paperWidth: 57, paperHeight: 30, thermal: true, gap: 3 },
      'thermal-57x40': { width: 57, height: 40, paperWidth: 57, paperHeight: 40, thermal: true, gap: 3 },
      // Roll 58mm (RongTa dll): label & @page = 58 agar full lebar di preview/cetak
      'thermal-58x30': { width: 58, height: 30, paperWidth: 58, paperHeight: 30, thermal: true, gap: 3 },
      'thermal-58x40': { width: 58, height: 40, paperWidth: 58, paperHeight: 40, thermal: true, gap: 3 },
      'thermal-80x80': { width: 80, height: 80, paperWidth: 80, paperHeight: 80, thermal: true, gap: 3 }
    };
  }

  function getThermalGapMm(preset) {
    var fallback = (preset && preset.gap != null) ? preset.gap : THERMAL_DEFAULT_GAP_MM;
    var raw = parseLocaleNumber($('#label-gap').val(), fallback);
    if (isNaN(raw)) {
      raw = fallback;
    }
    return Math.max(THERMAL_MIN_GAP_MM, Math.min(THERMAL_MAX_GAP_MM, raw));
  }

  function getThermalLabelHeightMm(preset) {
    var fallback = preset && preset.height ? preset.height : 30;
    var raw = parseLocaleNumber($('#label-height').val(), fallback);
    if (isNaN(raw) || raw <= 0) {
      raw = fallback;
    }
    return raw;
  }

  function getThermalGapSideMm(preset) {
    return getThermalGapMm(preset) / 2;
  }

  function getThermalPageHeightMm(preset) {
    return getThermalLabelHeightMm(preset) + getThermalGapMm(preset);
  }

  function syncThermalGapVisibility() {
    var paperSize = normalizePaperSize($('#paper-size').val() || 'A4');
    var preset = getPaperPresets()[paperSize];
    var isThermal = !!(preset && preset.thermal);
    $('#thermal-gap-group').toggle(isThermal);
    $('#thermal-gap-hint').toggle(isThermal);
  }

  function normalizePaperSize(value) {
    if (!value) {
      return 'A4';
    }

    var map = {
      '50x30': 'thermal-50x30',
      'thermal-50x30': 'thermal-50x30',
      '57x30': 'thermal-57x30',
      'thermal-57x30': 'thermal-57x30',
      '57x40': 'thermal-57x40',
      'thermal-57x40': 'thermal-57x40',
      '58x30': 'thermal-58x30',
      'thermal-58x30': 'thermal-58x30',
      '58': 'thermal-58x30',
      '58x40': 'thermal-58x40',
      'thermal-58x40': 'thermal-58x40',
      '80x80': 'thermal-80x80',
      'thermal-80x80': 'thermal-80x80',
      'A4': 'A4',
      'F4': 'F4'
    };

    return map[value] || value;
  }

  function updatePrintPageStyle(paperSize) {
    var presets = getPaperPresets();
    var preset = presets[normalizePaperSize(paperSize)] || presets['A4'];
    var pageCss;

    if (preset.thermal) {
      // Selalu paksa 50 × 30 mm. Driver 58 × 210 mm tidak diikuti oleh desain.
      pageCss = [
        '@page { size: 50mm 30mm; margin: 0; }',
        '@media print {',
        '  html, body {',
        '    width: 50mm !important; height: auto !important;',
        '    margin: 0 !important; padding: 0 !important;',
        '  }',
        '  body.thermal-print #printable,',
        '  body.thermal-print #printable.container {',
        '    width: 50mm !important; max-width: 50mm !important;',
        '    margin: 0 !important; padding: 0 !important;',
        '  }',
        '  body.thermal-print .thermal-page,',
        '  body.thermal-print .label-container,',
        '  body.thermal-print .label-barcode {',
        '    width: 50mm !important;',
        '    max-width: 50mm !important;',
        '    height: 30mm !important;',
        '    min-height: 30mm !important;',
        '    max-height: 30mm !important;',
        '    margin: 0 !important; padding: 0 !important;',
        '    overflow: hidden !important;',
        '    page-break-after: always;',
        '    break-after: page;',
        '    box-sizing: border-box !important;',
        '  }',
        '  body.thermal-print .thermal-page-last,',
        '  body.thermal-print .label-container:last-child {',
        '    page-break-after: auto !important;',
        '    break-after: auto !important;',
        '  }',
        '  body.thermal-print .label-container .clinic-label {',
        '    width: 50mm !important;',
        '    height: 30mm !important;',
        '    max-height: 30mm !important;',
        '    box-sizing: border-box !important;',
        '  }',
        '  body.thermal-print .thermal-gap-spacer { display: none !important; }',
        '}'
      ].join('\n');
    } else {
      pageCss = '@page { size: landscape; margin: 0; }';
    }

    $('style[id="dynamic-print-page"]').remove();
    $('<style id="dynamic-print-page">').text(pageCss).appendTo('head');

    $('body').toggleClass('thermal-print', !!preset.thermal);
    syncThermalGapVisibility();
  }

  function applyPaperPreset(paperSize, applyDimensions) {
    var presets = getPaperPresets();
    var normalized = normalizePaperSize(paperSize);
    var preset = presets[normalized] || presets['A4'];

    $('#paper-size').val(normalized);

    if (applyDimensions !== false) {
      $('#label-width').val(preset.width);
      $('#width-unit').val('mm');
      $('#label-height').val(preset.height);
      $('#height-unit').val('mm');
      $('#label-gap').val(preset.gap != null ? preset.gap : THERMAL_DEFAULT_GAP_MM);
    }

    applyPaperSize(normalized);
    updatePrintPageStyle(normalized);
  }

  function getElementFontSettings() {
    return {
      name: parseLocaleNumber($('#font-name').val(), 8),
      dob: parseLocaleNumber($('#font-dob').val(), 6.5),
      regdate: parseLocaleNumber($('#font-regdate').val(), 6),
      noreg: parseLocaleNumber($('#font-noreg').val(), 8),
      sample: parseLocaleNumber($('#font-sample').val(), 7.5),
      params: parseLocaleNumber($('#font-params').val(), 7.5)
    };
  }

  function applyElementFonts() {
    var fonts = getElementFontSettings();
    $('.clinic-label-dob').css('font-size', fonts.dob + 'px');
    $('.clinic-label-regdate').css('font-size', fonts.regdate + 'px');
    $('.clinic-label-barcode-text').css('font-size', fonts.noreg + 'px');
    // Nama / jenis sampel / parameter di-set ulang di adjustLabelLayout (auto-fit memakai nilai ini sebagai preferensi)
  }

  function applySettings(options) {
    options = options || {};
    const width = $('#label-width').val();
    const widthUnit = $('#width-unit').val();
    const height = $('#label-height').val();
    const heightUnit = $('#height-unit').val();
    const fontSize = $('#font-size').val();
    const fontUnit = $('#font-unit').val();
    const padding = $('#padding').val();
    const paddingUnit = $('#padding-unit').val();
    const paperSize = normalizePaperSize($('#paper-size').val() || 'A4');
    const elementFonts = getElementFontSettings();
    const barcodeSettings = getBarcodeSettings();
    $('#paper-size').val(paperSize);
    // Sync clamped values back to inputs
    $('#barcode-height-pct').val(barcodeSettings.heightPct);
    $('#barcode-width-pct').val(barcodeSettings.widthPct);
    $('#barcode-bar-width').val(barcodeSettings.barWidth);
    $('#barcode-spacing').val(barcodeSettings.barGap);
    $('#barcode-format').val(barcodeSettings.format);
    $('#label-gap').val(getThermalGapMm(getPaperPresets()[paperSize]));

    // Apply new dimensions to all labels
    $('.label-container').css({
      'width': width + widthUnit,
      'height': height + heightUnit,
      'font-size': fontSize + fontUnit,
      'padding': padding + paddingUnit
    });

    applyElementFonts();

    // Atur simulasi ukuran kertas berdasarkan pilihan paper
    applyPaperSize(paperSize);
    updatePrintPageStyle(paperSize);

    // Sesuaikan layout internal label setelah ukuran berubah
    adjustLabelLayout();

    // Re-initialize draggable after layout change
    reinitializeDraggable();

    // Save settings to localStorage (if supported)
    if (typeof(Storage) !== "undefined") {
      localStorage.setItem('labelSettings_v2', JSON.stringify({
        width: width,
        widthUnit: widthUnit,
        height: height,
        heightUnit: heightUnit,
        fontSize: fontSize,
        fontUnit: fontUnit,
        padding: padding,
        paddingUnit: paddingUnit,
        paperSize: paperSize,
        fontName: elementFonts.name,
        fontDob: elementFonts.dob,
        fontRegdate: elementFonts.regdate,
        fontNoreg: elementFonts.noreg,
        fontSample: elementFonts.sample,
        fontParams: elementFonts.params,
        barcodeHeightPct: barcodeSettings.heightPct,
        barcodeWidthPct: barcodeSettings.widthPct,
        barcodeBarWidth: barcodeSettings.barWidth,
        barcodeSpacing: barcodeSettings.barGap,
        barcodeBarGap: barcodeSettings.barGap,
        barcodeFormat: barcodeSettings.format,
        barcodeStyleRev: BARCODE_STYLE_REV,
        labelGap: getThermalGapMm(getPaperPresets()[paperSize])
      }));
    }

    if (!options.silent) {
      alert('Pengaturan berhasil diterapkan!');
    }
  }

  function resetSettings() {
    $('#label-width').val(50);
    $('#width-unit').val('mm');
    $('#label-height').val(30);
    $('#height-unit').val('mm');
    $('#font-size').val(8);
    $('#font-unit').val('px');
    $('#font-name').val(8);
    $('#font-dob').val(6.5);
    $('#font-regdate').val(6);
    $('#font-noreg').val(8);
    $('#font-sample').val(7.5);
    $('#font-params').val(7.5);
    $('#barcode-height-pct').val(BARCODE_DEFAULT_HEIGHT_PCT);
    $('#barcode-width-pct').val(BARCODE_DEFAULT_WIDTH_PCT);
    $('#barcode-bar-width').val(BARCODE_DEFAULT_BAR_WIDTH);
    $('#barcode-spacing').val(BARCODE_DEFAULT_SPACING);
    $('#barcode-format').val(BARCODE_DEFAULT_FORMAT);
    $('#padding').val(2);
    $('#padding-unit').val('mm');
    $('#paper-size').val('thermal-50x30');
    $('#label-gap').val(THERMAL_DEFAULT_GAP_MM);

    applySettings({ silent: true });
  }

  function applyPaperSize(paperSize) {
    var presets = getPaperPresets();
    var normalized = normalizePaperSize(paperSize);
    var preset = presets[normalized] || presets['A4'];

    $('#printable').css({
      width: preset.paperWidth + 'mm',
      minHeight: preset.paperHeight + 'mm'
    });
  }

  function updateLabelsPerRow(labelsPerRow) {
    // Remove existing CSS rules for labels per row
    $('style[id="dynamic-label-rules"]').remove();

    // Create new CSS rules for dynamic labels per row
    let css = `
                .label-container:nth-child(${labelsPerRow}n) {
                    margin-right: 0mm !important;
                }
                .label-container:not(:nth-child(${labelsPerRow}n)) {
                    margin-right: 2mm !important;
                }
            `;

    $('<style id="dynamic-label-rules">').html(css).appendTo('head');
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
    $(".label-container").draggable("destroy");
    initializeDraggable();
  }

  // Load saved settings on page load
  $(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var ukuranFromUrl = urlParams.get('ukuran');

    if (ukuranFromUrl) {
      applyPaperPreset(ukuranFromUrl, true);
      $('.label-container').css({
        'width': $('#label-width').val() + 'mm',
        'height': $('#label-height').val() + 'mm'
      });
      adjustLabelLayout();
    }

    // Paper size sudah di-handle live via bindLiveSettings (tanpa alert)

    if (typeof(Storage) !== "undefined") {
      const savedSettings = localStorage.getItem('labelSettings_v2') || localStorage.getItem('labelSettings');
      if (savedSettings) {
        const settings = JSON.parse(savedSettings);

        if (!ukuranFromUrl) {
          $('#label-width').val(settings.width);
          $('#width-unit').val(settings.widthUnit);
          $('#label-height').val(settings.height);
          $('#height-unit').val(settings.heightUnit);
          if (settings.paperSize) {
            $('#paper-size').val(normalizePaperSize(settings.paperSize));
          }
        }
        if (settings.labelGap != null) {
          var savedGap = parseFloat(settings.labelGap);
          if (!isNaN(savedGap)) {
            $('#label-gap').val(Math.max(
              THERMAL_MIN_GAP_MM,
              Math.min(THERMAL_MAX_GAP_MM, savedGap)
            ));
          }
        }

        $('#font-size').val(settings.fontSize);
        $('#font-unit').val(settings.fontUnit);
        $('#padding').val(settings.padding);
        $('#padding-unit').val(settings.paddingUnit);
        // Preferensi font lama yang terlalu besar di-clamp agar layout tetap rapi
        if (settings.fontName != null) { $('#font-name').val(Math.min(16, parseFloat(settings.fontName) || 8)); }
        if (settings.fontDob != null) { $('#font-dob').val(Math.min(12, parseFloat(settings.fontDob) || 6.5)); }
        if (settings.fontRegdate != null) { $('#font-regdate').val(Math.min(12, parseFloat(settings.fontRegdate) || 6)); }
        if (settings.fontNoreg != null) { $('#font-noreg').val(Math.min(10, parseFloat(settings.fontNoreg) || 8)); }
        if (settings.fontSample != null) { $('#font-sample').val(Math.min(16, parseFloat(settings.fontSample) || 7.5)); }
        if (settings.fontParams != null) { $('#font-params').val(Math.min(10, parseFloat(settings.fontParams) || 7.5)); }
        if (settings.barcodeHeightPct != null && Number(settings.barcodeStyleRev) >= BARCODE_STYLE_REV) {
          $('#barcode-height-pct').val(Math.max(20, Math.min(75, parseFloat(settings.barcodeHeightPct) || BARCODE_DEFAULT_HEIGHT_PCT)));
        } else {
          $('#barcode-height-pct').val(BARCODE_DEFAULT_HEIGHT_PCT);
        }
        if (settings.barcodeWidthPct != null) {
          $('#barcode-width-pct').val(Math.max(
            BARCODE_MIN_WIDTH_PCT,
            Math.min(BARCODE_MAX_WIDTH_PCT, parseFloat(settings.barcodeWidthPct) || BARCODE_DEFAULT_WIDTH_PCT)
          ));
        } else {
          $('#barcode-width-pct').val(BARCODE_DEFAULT_WIDTH_PCT);
        }
        if (settings.barcodeBarWidth != null && Number(settings.barcodeStyleRev) >= BARCODE_STYLE_REV) {
          $('#barcode-bar-width').val(Math.max(
            BARCODE_MIN_BAR_WIDTH,
            Math.min(BARCODE_MAX_BAR_WIDTH, parseFloat(settings.barcodeBarWidth) || BARCODE_DEFAULT_BAR_WIDTH)
          ));
        } else {
          $('#barcode-bar-width').val(BARCODE_DEFAULT_BAR_WIDTH);
        }
        if (settings.barcodeBarGap != null || settings.barcodeSpacing != null) {
          var gapVal = settings.barcodeBarGap != null
            ? parseFloat(settings.barcodeBarGap)
            : parseFloat(settings.barcodeSpacing);
          // Nilai lama quiet-zone (biasanya 8–20) di-reset agar tidak membuat jarak batang berlebihan
          if (isNaN(gapVal) || gapVal > BARCODE_MAX_SPACING) {
            gapVal = BARCODE_DEFAULT_SPACING;
          }
          $('#barcode-spacing').val(Math.max(
            BARCODE_MIN_SPACING,
            Math.min(BARCODE_MAX_SPACING, gapVal)
          ));
        } else {
          $('#barcode-spacing').val(BARCODE_DEFAULT_SPACING);
        }
        if (settings.barcodeFormat && Number(settings.barcodeStyleRev) >= BARCODE_STYLE_REV) {
          $('#barcode-format').val(normalizeBarcodeFormat(settings.barcodeFormat));
        } else {
          $('#barcode-format').val(BARCODE_DEFAULT_FORMAT);
        }

        // Apply the loaded settings & simulasi kertas (silent agar tidak alert tiap reload)
        applySettings({ silent: true });
        applyPaperSize(normalizePaperSize($('#paper-size').val() || 'A4'));
        updatePrintPageStyle($('#paper-size').val() || 'A4');
      } else if (!ukuranFromUrl) {
        applyPaperPreset('thermal-50x30', true);
        adjustLabelLayout();
      }
    } else if (!ukuranFromUrl) {
      applyPaperPreset('thermal-50x30', true);
      adjustLabelLayout();
    }

    // Aktifkan live apply setelah load awal selesai
    settingsLiveEnabled = true;
  });

  // Fungsi untuk menyesuaikan font nama pasien: mulai dari setting, kecilkan hanya jika tidak muat
  function autoFitLabelName() {
    var preferred = getElementFontSettings().name;
    var minFontSize = Math.min(5.5, preferred);

    $('.clinic-label-name').each(function () {
      var el = this;
      var $el = $(el);
      var $top = $el.closest('.clinic-label-top');
      var dobWidth = $top.find('.clinic-label-dob').outerWidth(true) || 0;
      var maxWidth = Math.max(20, $top.width() - dobWidth - 2);

      $el.css({
        'max-width': maxWidth + 'px',
        'white-space': 'normal',
        'line-height': '1.1'
      });

      var fontSize = preferred;
      var maxLines = 2;
      $el.css('font-size', fontSize + 'px');

      var safety = 0;
      while ((el.scrollHeight > maxLines * fontSize * 1.15 || el.scrollWidth > maxWidth + 1) && fontSize > minFontSize && safety < 24) {
        fontSize -= 0.25;
        $el.css('font-size', fontSize + 'px');
        safety++;
      }
    });
  }

  // Cari ukuran font terbesar yang masih muat di maxHeight.
  // Ukur via elemen probe (bukan flex stretch) supaya scrollHeight mencerminkan tinggi konten
  // sebenarnya — pada elemen overflow:hidden yang di-stretch, scrollHeight sering = clientHeight
  // meski teks pendek, sehingga binary search di elemen aslinya bisa salah.
  function findFitFontSize(el, maxHeight, minFontSize, maxFontSize) {
    var $el = $(el);
    var text = ($el.text() || '').trim();
    if (!text) {
      return maxFontSize;
    }

    var probe = document.createElement('div');
    probe.style.cssText = [
      'position:absolute',
      'visibility:hidden',
      'left:-9999px',
      'top:0',
      'box-sizing:border-box',
      'width:' + Math.max(8, el.clientWidth || $el.width() || 40) + 'px',
      'white-space:normal',
      'word-wrap:break-word',
      'overflow-wrap:break-word',
      'line-height:1.15',
      'font-family:' + (getComputedStyle(el).fontFamily || 'Arial, sans-serif'),
      'font-weight:' + (getComputedStyle(el).fontWeight || '600')
    ].join(';');
    probe.textContent = text;
    document.body.appendChild(probe);

    var lo = minFontSize;
    var hi = Math.max(minFontSize, maxFontSize);
    var best = minFontSize;
    var iterations = 0;

    while (hi - lo > 0.15 && iterations < 24) {
      var mid = (lo + hi) / 2;
      probe.style.fontSize = mid + 'px';
      if (probe.scrollHeight <= maxHeight + 0.5) {
        best = mid;
        lo = mid;
      } else {
        hi = mid;
      }
      iterations++;
    }

    document.body.removeChild(probe);
    $el.css('font-size', best + 'px');
    return best;
  }

  // Preferensi font dari Settings dipakai mandiri per elemen.
  // Jenis sampel tidak lagi di-paksa ikut mengecil karena daftar parameter panjang.
  var BOTTOM_TEXT_MIN_FONT = 4;

  function autoFitLabelBottomText() {
    var fonts = getElementFontSettings();

    $('.clinic-label-bottom').each(function () {
      var $bottom = $(this);
      var $sample = $bottom.find('.clinic-label-sample');
      var $params = $bottom.find('.clinic-label-params');

      if (!$sample.length && !$params.length) {
        return;
      }

      var maxHeight = $bottom.innerHeight() || 0;
      if (!maxHeight) {
        return;
      }

      // Jenis sampel: hormati nilai Settings; hanya mengecil jika benar-benar tidak muat.
      if ($sample.length) {
        var sampleMax = Math.min(fonts.sample, Math.max(BOTTOM_TEXT_MIN_FONT, maxHeight * 0.9));
        findFitFontSize($sample[0], maxHeight, BOTTOM_TEXT_MIN_FONT, sampleMax);
      }

      // Paket parameter: boleh lebih kecil dari preferensi jika teks panjang (wrapping).
      if ($params.length) {
        var paramsMax = Math.min(fonts.params, Math.max(BOTTOM_TEXT_MIN_FONT, maxHeight * 0.85));
        findFitFontSize($params[0], maxHeight, BOTTOM_TEXT_MIN_FONT, paramsMax);
      }
    });
  }
</script>

</body>

</html>
