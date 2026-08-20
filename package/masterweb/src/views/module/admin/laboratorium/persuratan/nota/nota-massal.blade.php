<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nota {{ $lab_name ?? 'KLINIK' }}">
    <meta name="author" content="Labkes Magelang">
    <title>Nota MAGELANG-{{ $lab_name ?? 'KLINIK' }}</title>
    <link rel="shortcut icon" href="favicon.ico">
    <style>
        @page {
            margin-top: 10mm;
            margin-bottom: 10mm;
            margin-left: 12mm;
            margin-right: 12mm;
        }

        html, body {
            font-family: Calibri, sans-serif !important;
            font-size: 10px !important;
            font-weight: normal;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .content-wrapper {
            padding: 0 8mm;
            box-sizing: border-box;
        }

        .nota-page {
            width: 100%;
        }

        .page-break {
            page-break-before: always;
        }

        .nota-slot {
            width: 100%;
            page-break-inside: avoid;
        }

        .cut-line {
            border-top: 1px dashed #777;
            margin: 8px 0 6px 0;
            height: 1px;
            font-size: 0;
            line-height: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            max-width: 100%;
        }

        td {
            padding: 1px 2px;
            line-height: 1.15;
            word-wrap: break-word;
            overflow: hidden;
        }

        table[border="1"] {
            border: 1px solid #000;
        }

        table[border="1"] td {
            border: 1px solid #000;
            padding: 1px 3px;
        }

        table[border="1"] td[style*="border-top: none"],
        table[border="1"] td[style*="border-top: 0"],
        table[border="1"] td[style*="border-bottom: none"],
        table[border="1"] td[style*="border-bottom: 0"] {
            border-top: 0 !important;
            border-bottom: 0 !important;
        }

        ol {
            margin: 1px 0;
            padding-left: 16px;
        }

        ol li {
            margin: 0;
        }

        @font-face {
            font-family: 'Calibri', sans-serif !important;
            src: local("Calibri"), local("Calibri Regular");
            font-weight: normal;
            font-style: normal;
        }
    </style>
</head>

<body>
    @php
        // Dua nota per halaman (atas-bawah); sisa ganjil tampil sendiri di halaman terakhir.
        $notaPerPage = 2;
        $notaPages = array_chunk($notas, $notaPerPage);
    @endphp

    <div class="content-wrapper">
    @foreach ($notaPages as $indexPage => $notaPage)
        <div class="nota-page {{ $indexPage > 0 ? 'page-break' : '' }}">
            @foreach ($notaPage as $indexNota => $nota)
                @if ($indexNota > 0)
                    <div class="cut-line"></div>
                @endif
                @php
                    $notaData = array_merge($nota, [
                        'compactNota' => true,
                        'kopWidth' => '70%',
                    ]);
                @endphp
                <div class="nota-slot">
                    @include('masterweb::module.admin.laboratorium.persuratan.nota.component.kop', $notaData)
                    @include('masterweb::module.admin.laboratorium.persuratan.nota.component.body-klinik', $notaData)
                    @include('masterweb::module.admin.laboratorium.persuratan.nota.component.footer', $notaData)
                </div>
            @endforeach
        </div>
    @endforeach
    </div>
</body>

</html>
