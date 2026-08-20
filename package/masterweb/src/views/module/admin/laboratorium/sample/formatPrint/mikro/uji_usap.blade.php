@php
    $x_baku_mutu = [
        'Baku mutu : Permenkes RI Nomor 2 Tahun 2023',
        'Tentang Peraturan Pelaksanaan Peraturan Pemerintah Nomor 66',
        'Tentang Kesehatan Lingkungan',
    ];
@endphp

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
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_data_uji_usap')
    <br>
    {{-- Tabel hasil uji usap dengan format sama seperti air higiene / air bersih --}}
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._table_result_uji_usap')
    <div style="margin-bottom: 5px"></div>
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_signature')
</body>

</html>
