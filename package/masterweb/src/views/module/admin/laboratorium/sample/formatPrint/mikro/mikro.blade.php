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

<body style="margin: 10px; padding: 0">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_kop')
    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_data')
    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._table_result')
    <br>


    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._magelang_signature')
</body>


</html>
