<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nota {{ $lab_name ?? 'KESMAS' }}">
    <meta name="author" content="SIMLAB">
    <title>Nota MAGELANG-{{ $lab_name ?? 'KESMAS' }}</title>
    <link rel="shortcut icon" href="favicon.ico">
    <style>
        html, body {
            font-family: Calibri, sans-serif !important;
            font-size: 10px !important;
            font-weight: normal;
            margin-top: 0px;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }

        .content-wrapper {
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            max-width: 100%;
        }

        td {
            padding: 1px 2px;
            line-height: 1.2;
            word-wrap: break-word;
            overflow: hidden;
        }

        /* Spacing untuk tabel utama */
        table[border="1"] {
            border: 1px solid #000;
        }

        table[border="1"] td {
            border: 1px solid #000;
            padding: 2px 3px;
        }

        /* Override untuk border spesifik */
        table[border="1"] td[style*="border-top: none"],
        table[border="1"] td[style*="border-top: 0"],
        table[border="1"] td[style*="border-bottom: none"],
        table[border="1"] td[style*="border-bottom: 0"] {
            border-top: 0 !important;
            border-bottom: 0 !important;
        }

        /* Spacing untuk list */
        ol {
            margin: 2px 0;
            padding-left: 18px;
        }

        ol li {
            margin: 1px 0;
        }

        @font-face {
            font-family: 'Calibri', sans-serif !important;
            src: local("Calibri"), local("Calibri Regular");
            font-weight: normal;
            font-style: normal;
        }

        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            .content-wrapper {
                max-width: 100%;
                overflow: hidden;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        @include('masterweb::module.admin.laboratorium.persuratan.nota.component.kop')
        @if ($is_klinik)
            @include('masterweb::module.admin.laboratorium.persuratan.nota.component.body-klinik')
        @else
            @include('masterweb::module.admin.laboratorium.persuratan.nota.component.body-kesmas')
        @endif
        @include('masterweb::module.admin.laboratorium.persuratan.nota.component.footer')
    </div>
</body>

</html>
