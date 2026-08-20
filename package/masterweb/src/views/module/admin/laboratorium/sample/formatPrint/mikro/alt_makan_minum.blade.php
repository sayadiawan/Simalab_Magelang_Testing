<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MIKRO-{!! $no_LHU !!}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_style')
</head>

<body style="margin: 0 10px; padding: 0">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_kop')

    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_data_makanan')



    <br>

    @if ($isKuantitatif)
        @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._table_kuantitatif_makanan')
    @else
        @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._table_kualitatif_makanan')
    @endif

    <br>
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._magelang_signature_makmin')

</html>
