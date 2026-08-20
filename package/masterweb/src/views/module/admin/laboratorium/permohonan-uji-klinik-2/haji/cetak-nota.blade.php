<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Haji - {{ $haji->nama_haji }}</title>
</head>
<body>
    @foreach ($permohonanList as $index => $permohonan)
        @if ($index > 0)
            <div style="page-break-before: always;"></div>
        @endif
        @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint.nota', [
            'item_permohonan_uji_klinik' => $permohonan['item_permohonan_uji_klinik'],
            'detail_payment' => $permohonan['detail_payment'],
            'value_items' => $permohonan['value_items'],
            'tanggal_transaksi_lunas' => $permohonan['tanggal_transaksi_lunas'],
            'nama_petugas_registrasi' => $permohonan['nama_petugas_registrasi'],
        ])
    @endforeach
</body>
</html>

