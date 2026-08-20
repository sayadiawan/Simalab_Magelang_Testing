<html lang="">

@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 12.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.5;
    $padding = isset($padding) ? (float) $padding : 4.0;
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Makmin-{!! $no_LHU !!}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <style>
        .starter-template {
            text-align: center;
        }

        table>tr>td {
            /* cell-padding: 5px !important; */
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
            font-size: 5px;
            text-align: center
        }

        .result {
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
        }

        @page {
            size: 794px 1248px;
            margin: 5px 30px;
        }

        body {
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
        }

        .page_break {
            page-break-before: always;
        }

        .table-container {
            flex: 2;
            margin-right: 10px;
        }

        .table-container table {
            width: 60%;
            border-collapse: collapse;
            font-size: 16px;
        }

        .tembusan ol {
            padding-left: 16px;
        }
    </style>
</head>

<body style="margin:50px 10px 50px 10px; padding: 0; font-size: {{ $fontsize }}pt; line-height: {{ $lineHeight }};">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._kop')

    <div style="text-align: center; margin-top: 10px;">
        <h3 style="margin: 0; padding: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
            LAPORAN HASIL UJI
        </h3>
    </div>

    <div style="margin-top: 10px; font-size: 10pt;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 15%;">Kode Hasil</td>
                <td style="width: 1%;">:</td>
                <td>{!! $no_LHU !!}</td>
            </tr>
        </table>
    </div>

    @if ($isKuantitatif)
        @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._tabel_kuantitatif_makanan')
    @else
        @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._tabel_kualitatif_makanan')
    @endif
    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._signature_makanan')
</body>

</html>
