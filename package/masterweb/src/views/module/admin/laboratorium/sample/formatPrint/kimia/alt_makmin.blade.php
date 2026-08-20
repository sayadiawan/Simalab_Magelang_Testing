<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>KIMIA-{!! $no_LHU !!}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_style')
</head>

<body style="margin: 0 10px; padding: 0">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._kop')

    <br>

    <div style="text-align: center; margin-top: 10px;">
        <h3 style="margin: 0; padding: 0; font-size: 14pt; font-weight: bold; text-decoration: underline;">
            LAPORAN HASIL UJI
        </h3>
    </div>

    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._head_data_makmin')

    <br>
    @if ($isKuantitatif)
        @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._tabel_kuantitatif_makanan')
    @else
        @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._tabel_kualitatif_makanan')
    @endif

    <br>
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._signature_makanan')

</html>

