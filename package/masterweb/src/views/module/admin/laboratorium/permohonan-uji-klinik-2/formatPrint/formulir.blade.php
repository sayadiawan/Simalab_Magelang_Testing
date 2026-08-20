<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>INFORMED CONSENT</title>
    <link rel="shortcut icon" href="">
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
            margin: 20px 75px 20px 75px;
        }

        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 11px;
        }

        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: 11px;
            text-align: justify;
            text-justify: inter-word;
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

        .border {
            border: 1.5px solid black;
        }

        .v-align-top {
            vertical-align: top;
        }

        .checkbox {
            height: 10px;
            position: relative;
            bottom: 5px;
        }

        .blue-header {
            background-color: #3a95b5;
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
            padding-left: 4px;
            height: 10px;
        }

        .text-center {
            text-align: center;
        }

        .td-header {
            font-family: "Times New Roman", Times, serif !important;
            font-weight: bold;
            text-align: center;
        }

        .table-consent td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 4px 2px 4px 2px;
            font-size: 12.4px;
        }

        .table-clear td {
            border: 0px;
            padding: 0px;
        }

        .td-form-no {
            font-family: "Times New Roman", Times, serif !important;
            text-align: right;
        }
    </style>
</head>

<body>

    {{-- Bagian Persetujuan WhatsApp sudah dipindahkan ke file terpisah: lembar-persetujuan.blade.php --}}
    {{-- @endif --}}

    @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint.data-formulir')
    @if (isset($signOption) and $signOption == 1)
        <div style="position: fixed; bottom: 0px; text-align: left;">
            <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara</i></p>
        </div>
    @endif
</body>

</html>
