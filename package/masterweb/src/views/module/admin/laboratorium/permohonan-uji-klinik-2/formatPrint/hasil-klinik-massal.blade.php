@php
    // Definisikan fungsi-fungsi helper sekali di awal untuk menghindari redeclare error
    if (!function_exists('formatHasilAbnormal')) {
        function formatHasilAbnormal($hasil, $nilai_baku_mutu)
        {
            if ($hasil === null || $hasil === '' || $hasil === '-') {
                return $hasil ?? '-';
            }

            // Cek apakah ada flag yang menunjukkan abnormal
            if (isset($nilai_baku_mutu['flag']) && $nilai_baku_mutu['flag'] == 1) {
                return '<strong>' . $hasil . '</strong> *';
            }

            // Cek berdasarkan nilai baku mutu
            $min = isset($nilai_baku_mutu['min']) ? $nilai_baku_mutu['min'] : null;
            $max = isset($nilai_baku_mutu['max']) ? $nilai_baku_mutu['max'] : null;
            $equal = isset($nilai_baku_mutu['equal']) ? $nilai_baku_mutu['equal'] : null;

            $hasil_numeric = is_numeric($hasil) ? (float) $hasil : null;

            if ($hasil_numeric === null) {
                return $hasil;
            }

            // Cek dengan equal
            if ($equal !== null && $equal !== '' && $equal !== '0') {
                $equal_numeric = is_numeric($equal) ? (float) $equal : null;
                if ($equal_numeric !== null && $hasil_numeric != $equal_numeric) {
                    return '<strong>' . $hasil . '</strong> *';
                }
            }

            // Cek dengan min dan max
            if ($min !== null && $max !== null && $min !== '' && $max !== '') {
                $min_numeric = is_numeric($min) ? (float) $min : null;
                $max_numeric = is_numeric($max) ? (float) $max : null;
                if ($min_numeric !== null && $max_numeric !== null) {
                    if ($hasil_numeric < $min_numeric || $hasil_numeric > $max_numeric) {
                        return '<strong>' . $hasil . '</strong> *';
                    }
                }
            }

            return $hasil;
        }
    }
    
    if (!function_exists('formatHasilSubAbnormal')) {
        function formatHasilSubAbnormal($hasil, $min, $max, $equal, $flag = null)
        {
            if ($hasil === null || $hasil === '' || $hasil === '-') {
                return $hasil ?? '-';
            }

            // Cek flag terlebih dahulu
            if ($flag !== null && $flag != '' && $flag != '0' && $flag != 0) {
                return '<strong>' . $hasil . '</strong> *';
            }

            $hasil_numeric = is_numeric($hasil) ? (float) $hasil : null;

            if ($hasil_numeric === null) {
                return $hasil;
            }

            // Cek dengan equal
            if ($equal !== null && $equal !== '' && $equal !== '0') {
                $equal_numeric = is_numeric($equal) ? (float) $equal : null;
                if ($equal_numeric !== null && $hasil_numeric != $equal_numeric) {
                    return '<strong>' . $hasil . '</strong> *';
                }
            }

            // Cek dengan min dan max
            if ($min !== null && $max !== null && $min !== '' && $max !== '') {
                $min_numeric = is_numeric($min) ? (float) $min : null;
                $max_numeric = is_numeric($max) ? (float) $max : null;
                if ($min_numeric !== null && $max_numeric !== null) {
                    if ($hasil_numeric < $min_numeric || $hasil_numeric > $max_numeric) {
                        return '<strong>' . $hasil . '</strong> *';
                    }
                }
            }

            return $hasil;
        }
    }
    
    if (!function_exists('formatHasilMultipleBakuMutu')) {
        function formatHasilMultipleBakuMutu($hasil, $item_satuan_klinik, $item_permohonan_uji_klinik = null)
        {
            $context = is_array($item_satuan_klinik) ? $item_satuan_klinik : [];
            if ($item_permohonan_uji_klinik) {
                if (!array_key_exists('pasien_umur', $context) || $context['pasien_umur'] === null || $context['pasien_umur'] === '') {
                    $context['pasien_umur'] = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null;
                }
                if (!array_key_exists('pasien_gender', $context) || $context['pasien_gender'] === null || $context['pasien_gender'] === '') {
                    $context['pasien_gender'] = optional($item_permohonan_uji_klinik->pasien)->gender_pasien ?? null;
                }
            }

            return \Smt\Masterweb\Helpers\Smt::formatHasilForKlinikPrint($hasil, $context);
        }
    }
    
    if (!function_exists('formatHasilSubMultipleBakuMutu')) {
        function formatHasilSubMultipleBakuMutu($hasil, $item_subsatuan_klinik, $item_satuan_klinik = [], $item_permohonan_uji_klinik = null)
        {
            $parentContext = [
                'nama_parameter_satuan_klinik' => $item_satuan_klinik['nama_parameter_satuan_klinik'] ?? null,
                'number_format' => $item_satuan_klinik['number_format'] ?? 'en',
                'kesimpulan_baku_mutu' => $item_satuan_klinik['kesimpulan_baku_mutu'] ?? '',
                'is_normal' => $item_satuan_klinik['is_normal'] ?? null,
                'pasien_umur' => $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? null,
                'pasien_gender' => $item_permohonan_uji_klinik->pasien->gender_pasien ?? null,
            ];

            return \Smt\Masterweb\Helpers\Smt::formatHasilSubForKlinikPrint(
                $hasil,
                $item_subsatuan_klinik,
                $parentContext
            );
        }
    }
    
    // Panggil controller untuk prepare data setiap permohonan
    $klinikController = new \Smt\Masterweb\Http\Controllers\LaboratoriumPermohonanUjiKlinikManagement2(
        app(\Smt\Masterweb\Helpers\SatuSehatHelper::class)
    );
    
    $allResultsData = [];
    foreach ($permohonanIds as $permohonanId) {
        $request = new \Illuminate\Http\Request(['signoption' => 0]);
        $resultData = $klinikController->preparePrintDataHasil($request, $permohonanId);
        
        if ($resultData) {
            $allResultsData[] = $resultData;
        }
    }
    
    // Ambil style dari view hasil-klinik untuk digunakan di wrapper
    $firstResult = !empty($allResultsData) ? $allResultsData[0] : null;
@endphp

<html lang="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik Massal - {{ $haji->nama_haji ?? '' }}</title>
    <link rel="shortcut icon" href="">
    <style>
    .starter-template {
        text-align: center;
    }

    table {
        table-layout: fixed;
        width: 100%;
    }

    td,
    th {
        word-wrap: break-word;
        white-space: normal;
    }

    @media print {
        #cetak {
            display: none;
        }
    }

    .garis {
        border: 1px solid
    }

    .table2 {
        text-align: center
    }

    .result {
        border-collapse: collapse;
    }

    .result td {
        border: 1px solid black;
        text-align: center;
    }

    @php
        $showKopVal = isset($showKop) ? (int) $showKop : 1;
        $kopPageMargin = $showKopVal ? '3.9cm' : '5.5cm';
    @endphp
    @page {
        size: A4;
        margin: {{ $kopPageMargin }} 14mm 18mm 14mm;
    }

    .kop-repeat {
        position: fixed;
        top: -{{ $kopPageMargin }};
        left: 0;
        right: 0;
        height: {{ $kopPageMargin }};
    }
    .kop-repeat table,
    .kop-repeat td {
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
    }
    .kop-repeat img {
        width: 100%;
        height: auto;
        display: block;
    }

    @font-face {
        font-family: "source_sans_proregular";
        src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
        font-size: 12px;
    }

    body {
        font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
        font-size: 12px;
        text-align: justify;
        text-justify: inter-word;
    }

    .content-wrapper {
        page-break-inside: avoid;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .header-section {
        page-break-inside: avoid;
    }

    .table-with-signature {
        page-break-inside: avoid;
    }

    .signature-section {
        page-break-inside: avoid;
        margin-top: 5px !important;
    }

    .page_break {
        page-break-before: always;
    }

    .flex-container {
        display: flex !important;
        flex-wrap: nowrap !important;
    }

    .flex-container>div {
        width: 100px !important;
        margin: 10px !important;
    }

    th {
        margin-top: 0;
        font-size: 8pt !important;
        padding-top: 2pt !important;
        padding-bottom: 2pt !important;
        padding-left: 3pt !important;
        padding-right: 3pt !important;
    }

    td {
        margin-top: 0;
        font-size: 8pt !important;
        padding-top: 2pt !important;
        padding-bottom: 2pt !important;
        padding-left: 3pt !important;
        padding-right: 3pt !important;
    }

    .table-with-signature + .signature-section {
        margin-top: 3px !important;
    }
    
    .patient-info-table td {
        padding: 3px !important;
    }
    
    .patient-info-table th {
        padding: 3px !important;
    }

    .table-with-signature:empty {
        display: none !important;
    }

    .empty-group {
        display: none !important;
    }

    .keterangan-table table,
    .keterangan-table tr,
    .keterangan-table td,
    .keterangan-table th {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }
    </style>
</head>

<body>
@if ($showKopVal)
<div class="kop-repeat">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%"></td>
        </tr>
    </table>
</div>
@endif
@foreach ($allResultsData as $index => $resultData)
    @if ($index > 0)
        <div style="page-break-before: always;"></div>
    @endif
    
    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._hasil-klinik-body', [
        'item_permohonan_uji_klinik' => $resultData['item_permohonan_uji_klinik'],
        'data_permohonan_uji_parameter_klinik' => $resultData['data_permohonan_uji_parameter_klinik'],
        'tgl_pengujian' => $resultData['tgl_pengujian'],
        'tgl_spesimen_darah' => $resultData['tgl_spesimen_darah'],
        'tgl_spesimen_urine' => $resultData['tgl_spesimen_urine'],
        'data_parameter_satuan' => $resultData['data_parameter_satuan'],
        'arr_permohonan_parameter' => $resultData['arr_permohonan_parameter'] ?? [],
        'no_LHU' => $resultData['no_LHU'],
        'signOption' => $resultData['signOption'],
        'nama_petugas_pengambil_sample' => $resultData['nama_petugas_pengambil_sample'],
        'tanggal_pengambilan_sample' => $resultData['tanggal_pengambilan_sample'],
        'nama_petugas_pemeriksa' => $resultData['nama_petugas_pemeriksa'],
        'tanggal_pemeriksaan_sample' => $resultData['tanggal_pemeriksaan_sample'],
        'nama_petugas_verifikator' => $resultData['nama_petugas_verifikator'],
    ])
@endforeach
</body>
</html>

