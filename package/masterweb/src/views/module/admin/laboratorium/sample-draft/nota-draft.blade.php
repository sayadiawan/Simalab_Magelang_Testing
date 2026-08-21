<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nota Draft Kesmas">
    <meta name="author" content="SIMLAB">
    <title>Nota DRAFT - MAGELANG-KESMAS</title>
    <link rel="shortcut icon" href="favicon.ico">
    <style>
        html, body {
            font-size: 10px !important;
            font-weight: normal;
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            height: 100%;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 2px 3px;
            line-height: 1.3;
        }

        /* Spacing untuk tabel utama */
        table[border="1"] {
            border: 1px solid #000;
        }

        table[border="1"] td {
            border: 1px solid #000;
            padding: 2px 3px;
        }

        /* Section dengan background warna */
        .section-page {
            page-break-inside: avoid;
            padding: 0 !important;
            margin: 0 !important;
            width: 100%;
        }

        .section-content {
            padding: 1px 20px;
        }

        .section-page * {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .section-page table {
            margin: 0 !important;
            background-color: transparent !important;
        }

        .section-page td {
            background-color: transparent !important;
        }

        .section-page-1 {
            background-color: #ffffff;
        }

        .section-page-2 {
            background-color: #ffcce6;
        }

        .section-page-3 {
            background-color: #cce6ff;
        }

        /* Garis putus-putus pemisah */
        .divider {
            border: none;
            border-top: 2px dashed #000;
            margin: 5px 0;
            page-break-after: auto;
        }

        /* Draft watermark */
        .draft-watermark {
            color: #ff0000;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            background-color: #ffeeee;
            border: 2px solid #ff0000;
            margin-bottom: 5px;
        }

        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            .section-page {
                page-break-inside: avoid;
            }
            .divider {
                page-break-after: auto;
            }
        }

        @font-face {
            font-family: 'DejaVu Sans', sans-serif !important;
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
    </style>
</head>

<body>
    @php
        // Ambil data lab pertama saja untuk ditampilkan
        $firstLabData = reset($allLabsData);

        // Kumpulkan semua unit pemeriksaan dari semua lab
        $allUnitPemeriksaan = [];
        foreach ($allLabsData as $lab) {
            if (!in_array($lab['labTypeName'], $allUnitPemeriksaan)) {
                $allUnitPemeriksaan[] = $lab['labTypeName'];
            }
        }

        $no = 1;
        $rowCount = is_array($firstLabData['value_items']) ? count($firstLabData['value_items']) : 0;
        $grand_total = $firstLabData['total'];

        // Kelompokkan baris berurutan dengan Jenis sampel yang sama untuk rowspan
        $jenisRowspans = [];
        $prevJenis = null;
        $startIndex = null;
        $groupLen = 0;
        foreach ($firstLabData['value_items'] as $idx => $it) {
            $currJenis = $it['jenis_sampel'] ?? '-';
            if ($prevJenis === null) {
                $prevJenis = $currJenis;
                $startIndex = 0;
                $groupLen = 1;
                continue;
            }
            if ($currJenis === $prevJenis) {
                $groupLen++;
            } else {
                $jenisRowspans[$startIndex] = $groupLen;
                $prevJenis = $currJenis;
                $startIndex = $idx;
                $groupLen = 1;
            }
        }
        if ($rowCount > 0 && $startIndex !== null) {
            $jenisRowspans[$startIndex] = $groupLen;
        }

        // Kumpulkan jenis sampel dan parameter
        $jenis_samples = [];
        $parameters = [];
        foreach ($firstLabData['value_items'] as $item) {
            $jenisSampelText = strip_tags($item['jenis_sampel'] ?? '-');
            if (!empty($jenisSampelText) && $jenisSampelText != '-' && !in_array($jenisSampelText, $jenis_samples)) {
                $jenis_samples[] = $jenisSampelText;
            }
            $parameters[] = $item['name_item'];
        }
    @endphp

    {{-- Page 1 of 3 --}}
    <div class="section-page section-page-1">
    <div class="section-content">

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 2px;">
            <tr>
                <td style="padding: 0;">
                    <img src="{{ public_path('assets/admin/images/logo/kop_magelang_nota.png') }}" height="80px"
                        width="100%" style="display: block;">
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="1">
            <tr style="text-align: center;">
                <td colspan="2" style="text-align: center;" width="40%"><strong>KONTRAK/PERMOHONAN PEMERIKSAAN (DRAFT)</strong></td>
            </tr>
            <tr>
                <td style="padding: 2px 3px;" colspan="2">
                    <strong>No. Nota:</strong> {{ $permohonan_uji->nomor_nota }}
                </td>
            </tr>
            <tr style="text-align: center;">
                <td style="text-align: center; padding: 3px;">
                    <b>{{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}</b>
                    <br>
                    {{ $permohonan_uji->customer->address_customer }}
                </td>
                <td rowspan="3" style="vertical-align: top; padding: 0; border-bottom: none !important;">
                    <table border="0" cellspacing="0" cellpadding="2" width="100%">
                        <tr>
                            <td width="10%" style="text-align: center;"><b>No. Lab</b></td>
                            <td width="25%" style="text-align: center;"><b>Jenis sampel</b></td>
                            <td width="20%" style="text-align: center;"><b>Lokasi/titik pengambilan</b></td>
                            <td width="25%" style="text-align: center;"><b>Parameter</b></td>
                            <td width="20%" style="text-align: center;"><b>Harga (Rp)</b></td>
                        </tr>
                        @php
                            $no = 1;
                            $rowCount = is_array($firstLabData['value_items']) ? count($firstLabData['value_items']) : 0;
                            $grand_total = $firstLabData['total'];
                            // Kelompokkan baris berurutan dengan Jenis sampel yang sama untuk rowspan
                            $jenisRowspans = [];
                            $prevJenis = null;
                            $startIndex = null;
                            $groupLen = 0;
                            foreach ($firstLabData['value_items'] as $idx => $it) {
                                $currJenis = $it['jenis_sampel'] ?? '-';
                                if ($prevJenis === null) {
                                    $prevJenis = $currJenis;
                                    $startIndex = 0;
                                    $groupLen = 1;
                                    continue;
                                }
                                if ($currJenis === $prevJenis) {
                                    $groupLen++;
                                } else {
                                    $jenisRowspans[$startIndex] = $groupLen;
                                    $prevJenis = $currJenis;
                                    $startIndex = $idx;
                                    $groupLen = 1;
                                }
                            }
                            if ($rowCount > 0 && $startIndex !== null) {
                                $jenisRowspans[$startIndex] = $groupLen;
                            }
                        @endphp
                        @foreach ($firstLabData['value_items'] as $item)
                            <tr>
                                <td style="text-align: center;">{{ $no }}</td>
                                @if (isset($jenisRowspans[$loop->index]))
                                    <td rowspan="{{ $jenisRowspans[$loop->index] }}">{!! $item['jenis_sampel'] ?? '-' !!}
                                    </td>
                                @endif
                                <td>
                                    @php
                                        $lokasiRaw = $item['lokasi'] ?? '';
                                        $lokasi = str_replace('"""', '', $lokasiRaw);
                                        $lokasi = str_replace("\n", '<br>', $lokasi);
                                        $lokasi = str_replace('<p>', '', $lokasi);
                                        $lokasi = str_replace('</p>', '', $lokasi);

                                        if (str_contains($lokasi, 'π')) {
                                            $lokasi = str_replace(
                                                'π',
                                                "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                                $lokasi,
                                            );
                                        }

                                        if (str_contains($lokasi, '&pi;')) {
                                            $lokasi = str_replace(
                                                '&pi;',
                                                "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                                $lokasi,
                                            );
                                        }

                                    @endphp
                                    {!! $lokasi !== '' ? $lokasi : '-' !!}
                                </td>
                                <td>{{ $item['name_item'] }}</td>
                                @if ($loop->first)
                                    <td rowspan="{{ $rowCount }}"
                                        style="text-align: right; vertical-align: top;">
                                        {{ number_format($grand_total, 0, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                            @php
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr style="text-align: center;">
                <td style="vertical-align: top;">
                    <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                        <tr>
                            <td width="35%" style="border: none;">No. HP</td>
                            <td width="5%" style="border: none;">:</td>
                            <td width="60%" style="border: none;">{{ $permohonan_uji->customer->phone_customer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none;">Unit Pemeriksaan</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;"><b>{{ strtoupper(implode(', ', $allUnitPemeriksaan)) }}</b></td>
                        </tr>
                        <tr>
                            <td style="border: none;">Jenis Sampel</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;">
                                {{ !empty($jenis_samples) ? implode(', ', $jenis_samples) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="border: none;">Parameter</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;">
                                {{ implode(', ', $parameters) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>Biaya (Rp.)
                    <b>{{ 'Rp ' . number_format($firstLabData['total'], 0, ',', '.') }}</b>
                </td>
            </tr>
                <tr>
                    <td style="vertical-align: top;">
                        Keterangan:
                        <ol>
                            <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                            <li>Pelanggan menyetujui semua metode uji yang digunakan di Laboratorium SIMLAB</li>
                            <li><strong style="color: red;">Ini adalah dokumen DRAFT, belum final</strong></li>
                        </ol>
                    </td>
                    <td style="vertical-align: bottom; padding: 0; border-top: none !important;">
                        <table width="100%" cellspacing="0" cellpadding="2" border="1">
                            <tr>
                                <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Total</b></td>
                                <td style="text-align: right; width: 55px;">
                                    <b>{{ number_format($grand_total, 0, ',', '.') }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Dibayar</b></td>
                                <td style="text-align: right;">
                                    <b>{{ number_format($grand_total, 0, ',', '.') }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Sisa</b></td>
                                <td style="text-align: right;"><b>0</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 3px;">
            <tr>
                <td></td>
                <td style="text-align: center; vertical-align: top; padding: 3px;">Dibayar pada: {{ date('d/m/Y') }}</td>
                <td></td>
            </tr>
            <tr>
                <td width="33%" style="vertical-align: top;">
                    *) Kritik dan Saran
                    <br>089 538 499 0489
                    <div style="margin-top: 5px; padding-top: 5px;">
                    <div style="font-size: 16px; font-weight: bold;">
                        <span style="color: #dc3545;">DRAFT - Belum Lunas</span>
                    </div>
                </div>
                </td>
                <td width="33%" style="text-align: center; padding-top: 5px;">
                    Pelanggan
                    <br>
                    @if (!empty($permohonan_uji->signature_nota_pasien))
                        <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                            <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_pasien) }}"
                                alt="TTD Pelanggan"
                                style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 60px;"></div>
                    @endif
                    <br>
                    {{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}
                </td>
                <td width="34%" style="text-align: center; padding-top: 5px;">
                    Pendaftar
                    <br>
                    @if (!empty($permohonan_uji->signature_nota_petugas))
                        <div style="height: 40px; display: flex; align-items: center; justify-content: center;">
                            <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_petugas) }}"
                                alt="TTD Petugas"
                                style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <br>
                    {{ $permohonan_uji->petugas_penerima ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
    </div>

    <hr class="divider">

    {{-- Page 2 of 3 --}}
    <div class="section-page section-page-2">
    <div class="section-content">

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 2px;">
            <tr>
                <td style="padding: 0;">
                    <img src="{{ public_path('assets/admin/images/logo/kop_magelang-no-bg.png') }}" height="80px"
                        width="100%" style="display: block;">
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="1">
            <tr style="text-align: center;">
                <td colspan="2" style="text-align: center;" width="40%"><strong>KONTRAK/PERMOHONAN PEMERIKSAAN (DRAFT)</strong></td>
            </tr>
            <tr>
                <td style="padding: 2px 3px;" colspan="2">
                    <strong>No. Nota:</strong> {{ $permohonan_uji->nomor_nota }}
                </td>
            </tr>
            <tr style="text-align: center;">
                <td style="text-align: center; padding: 3px;">
                    <b>{{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}</b>
                    <br>
                    {{ $permohonan_uji->customer->address_customer }}
                </td>
                <td rowspan="3" style="vertical-align: top; padding: 0; border-bottom: none !important;">
                    <table border="1" cellspacing="0" cellpadding="2" width="100%">
                        <tr>
                            <td width="10%" style="text-align: center;"><b>No. Lab</b></td>
                            <td width="25%" style="text-align: center;"><b>Jenis sampel</b></td>
                            <td width="20%" style="text-align: center;"><b>Lokasi/titik pengambilan</b></td>
                            <td width="25%" style="text-align: center;"><b>Parameter</b></td>
                            <td width="20%" style="text-align: center;"><b>Harga (Rp)</b></td>
                        </tr>
                        @php $no = 1; @endphp
                        @foreach ($firstLabData['value_items'] as $item)
                            <tr>
                                <td style="text-align: center;">{{ $no }}</td>
                                @if (isset($jenisRowspans[$loop->index]))
                                    <td rowspan="{{ $jenisRowspans[$loop->index] }}">{!! $item['jenis_sampel'] ?? '-' !!}</td>
                                @endif
                                <td>
                                    @php
                                        $lokasiRaw = $item['lokasi'] ?? '';
                                        $lokasi = str_replace('"""', '', $lokasiRaw);
                                        $lokasi = str_replace("\n", '<br>', $lokasi);
                                        $lokasi = str_replace('<p>', '', $lokasi);
                                        $lokasi = str_replace('</p>', '', $lokasi);
                                        if (str_contains($lokasi, 'π')) {
                                            $lokasi = str_replace('π', "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>", $lokasi);
                                        }
                                        if (str_contains($lokasi, '&pi;')) {
                                            $lokasi = str_replace('&pi;', "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>", $lokasi);
                                        }
                                    @endphp
                                    {!! $lokasi !== '' ? $lokasi : '-' !!}
                                </td>
                                <td>{{ $item['name_item'] }}</td>
                                @if ($loop->first)
                                    <td rowspan="{{ $rowCount }}" style="text-align: right; vertical-align: top;">{{ number_format($grand_total, 0, ',', '.') }}</td>
                                @endif
                            </tr>
                            @php $no++; @endphp
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr style="text-align: center;">
                <td style="vertical-align: top; padding-top: 5px;">
                    <table width="100%" cellspacing="0" cellpadding="2" style="border: none;">
                        <tr>
                            <td width="35%" style="border: none;">No. HP</td>
                            <td width="5%" style="border: none;">:</td>
                            <td width="60%" style="border: none;">{{ $permohonan_uji->customer->phone_customer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none;">Unit Pemeriksaan</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;"><b>{{ strtoupper(implode(', ', $allUnitPemeriksaan)) }}</b></td>
                        </tr>
                        <tr>
                            <td style="border: none;">Jenis Sampel</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;">{{ !empty($jenis_samples) ? implode(', ', $jenis_samples) : '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none;">Parameter</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;">{{ implode(', ', $parameters) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>Biaya (Rp.) <b>{{ 'Rp ' . number_format($firstLabData['total'], 0, ',', '.') }}</b></td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    Keterangan:
                    <ol>
                        <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                        <li>Pelanggan menyetujui semua metode uji yang digunakan di Laboratorium SIMLAB</li>
                        <li><strong style="color: red;">Ini adalah dokumen DRAFT, belum final</strong></li>
                    </ol>
                </td>
                <td style="vertical-align: bottom; padding: 0; border-top: none !important;">
                    <table width="100%" cellspacing="0" cellpadding="2" border="1">
                        <tr>
                            <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Total</b></td>
                            <td style="text-align: right; width: 55px;"><b>{{ number_format($grand_total, 0, ',', '.') }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Dibayar</b></td>
                            <td style="text-align: right;"><b>{{ number_format($grand_total, 0, ',', '.') }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Sisa</b></td>
                            <td style="text-align: right;"><b>0</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 3px;">
            <tr>
                <td></td>
                <td style="text-align: center; vertical-align: top; padding: 3px;">Dibayar pada: {{ date('d/m/Y') }}</td>
                <td></td>
            </tr>
            <tr>
                <td width="33%" style="vertical-align: top;">
                    *) Kritik dan Saran<br>089 538 499 0489
                    <div style="margin-top: 5px; padding-top: 5px;">
                    <div style="font-size: 16px; font-weight: bold;">
                        <span style="color: #dc3545;">DRAFT - Belum Lunas</span>
                    </div>
                </div>
                </td>
                <td width="33%" style="text-align: center; padding-top: 5px;">
                    Pelanggan<br>
                    @if (!empty($permohonan_uji->signature_nota_pasien))
                        <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                            <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_pasien) }}" alt="TTD Pelanggan" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <br>{{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}
                </td>
                <td width="34%" style="text-align: center; padding-top: 5px;">
                    Pendaftar<br>
                    @if (!empty($permohonan_uji->signature_nota_petugas))
                        <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                            <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_petugas) }}" alt="TTD Petugas" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <br>{{ $permohonan_uji->petugas_penerima ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
    </div>

    <hr class="divider">

    {{-- Page 3 of 3 --}}
    <div class="section-page section-page-3">
    <div class="section-content">

        <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 2px;">
            <tr>
                <td style="padding: 0;">
                    <img src="{{ public_path('assets/admin/images/logo/kop_magelang-no-bg.png') }}" height="80px"
                        width="100%" style="display: block;">
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="1">
            <tr style="text-align: center;">
                <td colspan="2" style="text-align: center;" width="40%"><strong>KONTRAK/PERMOHONAN PEMERIKSAAN (DRAFT)</strong></td>
            </tr>
            <tr>
                <td style="padding: 2px 3px;" colspan="2">
                    <strong>No. Nota:</strong> {{ $permohonan_uji->nomor_nota }}
                </td>
            </tr>
            <tr style="text-align: center;">
                <td style="text-align: center; padding: 3px;">
                    <b>{{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}</b>
                    <br>
                    {{ $permohonan_uji->customer->address_customer }}
                </td>
                <td rowspan="3" style="vertical-align: top; padding: 0; border-bottom: none !important;">
                    <table border="1" cellspacing="0" cellpadding="2" width="100%">
                        <tr>
                            <td width="10%" style="text-align: center;"><b>No. Lab</b></td>
                            <td width="25%" style="text-align: center;"><b>Jenis sampel</b></td>
                            <td width="20%" style="text-align: center;"><b>Lokasi/titik pengambilan</b></td>
                            <td width="25%" style="text-align: center;"><b>Parameter</b></td>
                            <td width="20%" style="text-align: center;"><b>Harga (Rp)</b></td>
                        </tr>
                        @php $no = 1; @endphp
                        @foreach ($firstLabData['value_items'] as $item)
                            <tr>
                                <td style="text-align: center;">{{ $no }}</td>
                                @if (isset($jenisRowspans[$loop->index]))
                                    <td rowspan="{{ $jenisRowspans[$loop->index] }}">{!! $item['jenis_sampel'] ?? '-' !!}</td>
                                @endif
                                <td>
                                    @php
                                        $lokasiRaw = $item['lokasi'] ?? '';
                                        $lokasi = str_replace('"""', '', $lokasiRaw);
                                        $lokasi = str_replace("\n", '<br>', $lokasi);
                                        $lokasi = str_replace('<p>', '', $lokasi);
                                        $lokasi = str_replace('</p>', '', $lokasi);
                                        if (str_contains($lokasi, 'π')) {
                                            $lokasi = str_replace('π', "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>", $lokasi);
                                        }
                                        if (str_contains($lokasi, '&pi;')) {
                                            $lokasi = str_replace('&pi;', "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>", $lokasi);
                                        }
                                    @endphp
                                    {!! $lokasi !== '' ? $lokasi : '-' !!}
                                </td>
                                <td>{{ $item['name_item'] }}</td>
                                @if ($loop->first)
                                    <td rowspan="{{ $rowCount }}" style="text-align: right; vertical-align: top;">{{ number_format($grand_total, 0, ',', '.') }}</td>
                                @endif
                            </tr>
                            @php $no++; @endphp
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr style="text-align: center;">
                <td style="vertical-align: top; padding-top: 5px;">
                    <table width="100%" cellspacing="0" cellpadding="2" style="border: none;">
                        <tr>
                            <td width="35%" style="border: none;">No. HP</td>
                            <td width="5%" style="border: none;">:</td>
                            <td width="60%" style="border: none;">{{ $permohonan_uji->customer->phone_customer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none;">Unit Pemeriksaan</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;"><b>{{ strtoupper(implode(', ', $allUnitPemeriksaan)) }}</b></td>
                        </tr>
                        <tr>
                            <td style="border: none;">Jenis Sampel</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;">{{ !empty($jenis_samples) ? implode(', ', $jenis_samples) : '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border: none;">Parameter</td>
                            <td style="border: none;">:</td>
                            <td style="border: none;">{{ implode(', ', $parameters) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>Biaya (Rp.) <b>{{ 'Rp ' . number_format($firstLabData['total'], 0, ',', '.') }}</b></td>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    Keterangan:
                    <ol>
                        <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                        <li>Pelanggan menyetujui semua metode uji yang digunakan di Laboratorium SIMLAB</li>
                        <li><strong style="color: red;">Ini adalah dokumen DRAFT, belum final</strong></li>
                    </ol>
                </td>
                <td style="vertical-align: bottom; padding: 0; border-top: none !important;">
                    <table width="100%" cellspacing="0" cellpadding="2" border="1">
                        <tr>
                            <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Total</b></td>
                            <td style="text-align: right; width: 55px;"><b>{{ number_format($grand_total, 0, ',', '.') }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Dibayar</b></td>
                            <td style="text-align: right;"><b>{{ number_format($grand_total, 0, ',', '.') }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Sisa</b></td>
                            <td style="text-align: right;"><b>0</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 3px;">
            <tr>
                <td></td>
                <td style="text-align: center; vertical-align: top; padding: 3px;">Dibayar pada: {{ date('d/m/Y') }}</td>
                <td></td>
            </tr>
            <tr>
                <td width="33%" style="vertical-align: top;">
                    *) Kritik dan Saran<br>089 538 499 0489
                    <div style="margin-top: 5px; padding-top: 5px;">
                        <div style="font-size: 16px; font-weight: bold;">
                            <span style="color: #dc3545;">DRAFT - Belum Lunas</span>
                        </div>
                    </div>
                </td>
                <td width="33%" style="text-align: center; padding-top: 5px;">
                    Pelanggan<br>
                    @if (!empty($permohonan_uji->signature_nota_pasien))
                        <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                            <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_pasien) }}" alt="TTD Pelanggan" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <br>{{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}
                </td>
                <td width="34%" style="text-align: center; padding-top: 5px;">
                    Pendaftar<br>
                    @if (!empty($permohonan_uji->signature_nota_petugas))
                        <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                            <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_petugas) }}" alt="TTD Petugas" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                        </div>
                    @else
                        <div style="height: 40px;"></div>
                    @endif
                    <br>{{ $permohonan_uji->petugas_penerima ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
    </div>

</body>

</html>
