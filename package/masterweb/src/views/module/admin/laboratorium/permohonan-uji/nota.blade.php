<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nota Kesmas">
    <meta name="author" content="Labkes Magelang">
    <title>Nota MAGELANG-KESMAS</title>
    <link rel="shortcut icon" href="favicon.ico">
    <style>
        body {
            font-size: 11px !important;
            font-weight: normal;
            margin: 0;
            padding: 2px;
            font-family: Arial, sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        td {
            padding: 1px 3px;
            line-height: 1.2;
        }

        /* Spacing untuk tabel utama */
        table[border="1"] td {
            padding: 2px 3px;
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
            font-family: 'DejaVu Sans', sans-serif !important;
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
    </style>
</head>

<body>
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 2px;">
        <tr>
            <td style="padding: 0;">
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" height="80px" width="100%"
                    style="display: block;">
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1">
        <tr style="text-align: center;">
            <td style="text-align: center;" width="40%">KONTRAK PERMOHONAN PEMERIKSAAN</td>
            <td style="text-align: center;" width="60%">PENERIMAAN SAMPEL</td>
        </tr>
        <tr style="text-align: center;">
            <td style="text-align: center; padding: 3px;">
                <b>{{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}</b>
                <br>
                {{ $permohonan_uji->customer->address_customer }}
            </td>
            <td rowspan="2" style="vertical-align: top; padding-top: 3px;">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding-left: 4px" width="30%">Pengirim/Asal Sampel</td>
                        <td width="5%">:</td>
                        <td width="65%">
                            {{ $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->customer->name_customer }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 4px">Tanggal diambil/diterima</td>
                        <td>:</td>
                        <td>
                            @if ($tanggalPemeriksaan)
                                {{ Carbon\Carbon::parse($tanggalPemeriksaan)->locale('id')->translatedFormat('d-F-Y') }}
                                /
                                {{ Carbon\Carbon::parse($tanggalPemeriksaan)->locale('id')->translatedFormat('d-F-Y') }}
                            @else
                                - / -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 4px">Pengambil sampel</td>
                        <td>:</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <table width="100%">
                                <tr>
                                    <td width="50%">
                                        <table>
                                            <tr>
                                                <td>Wadah sampel</td>
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                                                </td>
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td>Jam diambil</td>
                                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                                                </td>
                                                <td>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    @if ($tanggalPemeriksaan)
                                                        {{ Carbon\Carbon::parse($tanggalPemeriksaan)->format('H:i:s') }}
                                                    @else
                                                        10:38:00
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="50%">
                                        <table>
                                            <tr>
                                                <td>Volume</td>
                                                <td>:</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>Jam diterima</td>
                                                <td>:</td>
                                                <td>
                                                    @if ($tanggalPemeriksaan)
                                                        {{ Carbon\Carbon::parse($tanggalPemeriksaan)->format('H:i:s') }}
                                                    @else
                                                        10:38:00
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-left: 4px">Jumlah Sampel</td>
                        <td>:</td>
                        <td>{{ count($value_items) }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 4px">Keterangan</td>
                        <td>:</td>
                        <td>-</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="text-align: center;">
            <td style="vertical-align: top; padding-top: 5px;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0">
                    <tr>
                        <td width="35%">No. HP</td>
                        <td width="5%">:</td>
                        <td width="60%">{{ $permohonan_uji->customer->phone_customer ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Unit Pemeriksaan</td>
                        <td>:</td>
                        <td>PEMERIKSAAN MIKROBIOLOGI</td>
                    </tr>
                    <tr>
                        <td>Jenis Sampel</td>
                        <td>:</td>
                        <td>
                            @php
                                $jenis_samples = [];
                                foreach ($value_items as $item) {
                                    if (
                                        isset($item['jenis_sampel']) &&
                                        !in_array($item['jenis_sampel'], $jenis_samples)
                                    ) {
                                        $jenis_samples[] = $item['jenis_sampel'];
                                    }
                                }
                                echo !empty($jenis_samples) ? implode(', ', $jenis_samples) : '-';
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <td>Parameter</td>
                        <td>:</td>
                        <td>
                            @php
                                $parameters = [];
                                foreach ($value_items as $item) {
                                    $parameters[] = $item['name_item'];
                                }
                                echo implode(', ', $parameters);
                            @endphp
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td></td>
            <td rowspan="4" style="vertical-align: top; padding-top: 5px;">
                <table border="1" cellspacing="0" cellpadding="2" width="100%">
                    <tr>
                        <td width="10%" style="text-align: center;"><b>No. Lab</b></td>
                        <td width="25%" style="text-align: center;"><b>Jenis sampel</b></td>
                        <td width="20%" style="text-align: center;"><b>Lokasi/titik pengambilan</b></td>
                        <td width="25%" style="text-align: center;"><b>Parameter</b></td>
                        <td width="20%" style="text-align: center;"><b>Harga (Rp)</b></td>
                    </tr>
                    @php
                        $no = 1;
                        $rowCount = is_array($value_items) ? count($value_items) : 0;
                        $grand_total =
                            $permohonan_uji->total_harga ?:
                            array_sum(
                                array_map(
                                    function ($i) {
                                        return (int) ($i['total'] ?? 0);
                                    },
                                    is_array($value_items) ? $value_items : [],
                                ),
                            );
                        // Kelompokkan baris berurutan dengan Jenis sampel yang sama untuk rowspan
                        $jenisRowspans = [];
                        $prevJenis = null;
                        $startIndex = null;
                        $groupLen = 0;
                        foreach ($value_items as $idx => $it) {
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
                    @foreach ($value_items as $item)
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
                                <td rowspan="{{ $rowCount }}" style="text-align: right; vertical-align: top;">
                                    {{ number_format($grand_total, 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                        @php
                            $no++;
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="4" style="text-align: right; padding-right: 5px;"><b>Total</b></td>
                        <td style="text-align: right;">
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
        <tr>
            <td>Kesimpulan Permohonan Pemeriksaan Diterima / ditolak *)</td>
        </tr>
        <tr>
            <td>Biaya (Rp.)
                <b>{{ rupiah($permohonan_uji->total_harga ? (int) $permohonan_uji->total_harga : (int) '0') }}</b>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                Keterangan:
                <ol>
                    <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                    <li>Pelanggan menyetujui semua metode saji yang digunakan di Laboratorium Kab. Magelang</li>
                </ol>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 3px;">
        <tr>
            <td width="33%" style="vertical-align: top;">
                *) Kritik dan Saran
                <br>089 538 499 0489
            </td>
            <td width="33%" style="text-align: center; padding-top: 5px;">
                Pelanggan
                <br>
                @if (!empty($permohonan_uji->signature_nota_pasien))
                    <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                        <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_pasien) }}"
                            alt="TTD Pelanggan" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
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
                    <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                        <img src="data:image/png;base64,{{ base64_encode($permohonan_uji->signature_nota_petugas) }}"
                            alt="TTD Petugas" style="max-width: 150px; max-height: 55px; object-fit: contain;" />
                    </div>
                @else
                    <div style="height: 60px;"></div>
                @endif
                <br>
                {{ $permohonan_uji->petugas_penerima ?? '-' }}
            </td>
        </tr>
    </table>
</body>

</html>
