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

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_data')
    <br>

    {{-- Tabel hasil khusus Air Higiene — nama lama: Air Bersih (layout seperti contoh: hasil & baku mutu) --}}
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._table_result_air')
    <div style="margin-bottom: 5px"></div>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_signature')

</body>

</html>
