<html lang="">

@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 12.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.0;
    $padding = isset($padding) ? (float) $padding : 1.0;
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>LHU.ELIT-2002006-AP </title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <style>
        .starter-template {
            text-align: center;
        }

        table>tr>td {
            cell-padding: 5px !important;
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

        .result td,
        .result th {
            border: 1px solid black;
        }

        .result td {
            text-align: center;
        }

        @page {
            size: 794px 1248px;
            margin: 20px 20px 20px 20px;
        }

        body {
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
        }

        .page_break {
            page-break-before: always;
        }

        .judul-surat {
                text-align: center;
                margin-bottom: 10px;
                font-size: 14px;
            }

        .result th,
        .result td {
            padding: {{ $padding }}px;
            line-height: {{ $lineHeight }};
        }
    </style>
</head>

<body style="margin: 10px; padding:0">
    @if ($showKop)
        <div class="row text-center" id="header">
            <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="730px" class="img-fluid">
        </div>
    @else
        <div style="height: 140px;"></div>
    @endif

    <div class="container">
        <div class="judul-surat">
            <h3><u>LAPORAN HASIL UJI</u></h3>
        </div>

        <div class="row batas">
            <div class="col-md-2"></div>
            <div class="col-md-8">

                @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._head_data_lhu')

                <br>

                @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._tabel_hasil_lhu')
                <br>
                @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._signature_makanan')

            </div>
        </div>
</body>

</html>
