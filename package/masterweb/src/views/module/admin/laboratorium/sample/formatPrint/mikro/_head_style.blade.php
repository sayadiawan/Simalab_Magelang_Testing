@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 12.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.5;
    $padding = isset($padding) ? (float) $padding : 4.0;
@endphp

<style>
    .starter-template {
        text-align: center;
    }

    @media print {
        #cetak {
            display: none;
        }
    }

    @font-face {
        font-family: 'DejaVu Sans', sans-serif !important;
        src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
        font-size: 13px;
    }


    .garis {
        border: 1px solid
    }

    .table2 {
        font-size: 12pt;
        text-align: center
    }

    .result {
        border-collapse: collapse;
    }

    .result td {
        border: 0.7px solid #3a3939;
        text-align: start;
        padding: {{ $padding }}px;
        font-size: {{ $fontsize }}pt;
    }

    .result tr:not([valign="top"]) td,
    .result td.td-result {
        vertical-align: middle !important;
        text-align: center;
    }

    .result tr[valign="top"] td,
    .result td.table_tr,
    .result td.table_tr-param {
        vertical-align: top !important;
    }

    @page {
        size: 1030px 1448px;
        margin: 50px 30px 50px 30px;
    }

    body,
    table {
        font-size: {{ $fontsize }}pt;
        line-height: {{ $lineHeight }};
        font-family: sans-serif;
        border-color: black;
    }

    table td {
        vertical-align: top;
        padding: {{ $padding }}px;
    }

    table.head-data td {
        vertical-align: top !important;
        padding-top: 0 !important;
        line-height: 1.3;
    }

    .page_break {
        page-break-before: always;
    }

    /* .page-break {
    page-break-before: auto;
  } */

    .clearfix {
        overflow: auto;
    }

    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }

    .page-title {
        font-size: {{ $fontsize }}px;
    }

    .keterangan {
        font-size: 11pt;
        list-style: none;
        margin: 0;
        padding: 0;
        margin-left: 4.5em;
        line-height: 1.8;
    }
</style>
