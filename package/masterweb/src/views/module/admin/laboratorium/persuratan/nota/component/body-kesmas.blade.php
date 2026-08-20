<table width="100%" cellspacing="0" cellpadding="0" border="1">
    <tr style="text-align: center;">
        <td style="width: 44%" colspan="2">
            <b>KONTRAK/ PERMOHONAN PEMERIKSAAN</b>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 0;">
            <b style="font-size: 12pt">{{ $nama_customer ?? "Sutrisno" }}</b>
            <br>
            <br>
            {{ ucwords(strtolower($alamat_customer ?? 'Jl. Raya Kedungwuni, RT.01/RW.01, Kedungwuni, Kec. Mungkid, Kabupaten Magelang, Jawa Tengah 56151')) }}
        </td>
        <td rowspan="4" style="vertical-align: top; padding: 0; border-bottom: none !important;">
            <table border="1" cellspacing="0" cellpadding="2" width="100%" style="border-collapse: collapse; border-bottom: none !important;">
                <tr>
                    <td width="10%" style="text-align: center; white-space: nowrap; font-size: 9px; padding: 2px 4px;"><b>No. Lab</b></td>
                    <td width="19%" style="text-align: center;">Jenis Sampel</td>
                    <td width="19%" style="text-align: center;">Lokasi/titik <br>pengambilan</td>
                    <td width="32%" style="text-align: center;">Parameter</td>
                    <td width="18%" style="text-align: center;">Harga (Rp)</td>
                </tr>
                @php
                    $value_items_array = is_array($value_items ?? []) ? $value_items : [];
                    $totalColumns = 5;
                    $minimumDisplayRows = 8;
                    $noHorizontalBorderStyle = 'border-top: none; border-bottom: none;';

                    $formatLokasiHtml = function ($lokasiRaw) {
                        $lokasi = str_replace('"""', '', (string) $lokasiRaw);
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

                        return $lokasi;
                    };

                    $displayRows = [];
                    foreach ($value_items_array as $item) {
                        $lokasiRaw = $item['lokasi'] ?? '';
                        $lokasiHtml = $formatLokasiHtml($lokasiRaw);
                        $lokasiPlain = trim(preg_replace('/\s+/', ' ', strip_tags($lokasiRaw)));
                        $jenisPlain = trim(strip_tags($item['jenis_sampel'] ?? '-'));

                        $displayRows[] = [
                            'jenis_sampel' => $item['jenis_sampel'] ?? '-',
                            'jenis_plain' => $jenisPlain !== '' ? $jenisPlain : '-',
                            'lokasi_html' => $lokasiHtml !== '' ? $lokasiHtml : '-',
                            'lokasi_plain' => $lokasiPlain !== '' ? $lokasiPlain : '-',
                            'name_item' => $item['name_item'] ?? '-',
                            'harga' => (int) ($item['total'] ?? ($item['price_item'] ?? 0)),
                            'no_lab' => $item['no_lab'] ?? null,
                        ];
                    }

                    usort($displayRows, function ($a, $b) {
                        $byJenis = strcmp($a['jenis_plain'], $b['jenis_plain']);
                        if ($byJenis !== 0) {
                            return $byJenis;
                        }

                        return strcmp($a['lokasi_plain'], $b['lokasi_plain']);
                    });

                    $rowCount = count($displayRows);
                    $jenisRowspan = array_fill(0, $rowCount, 0);
                    $lokasiRowspan = array_fill(0, $rowCount, 0);

                    for ($i = 0; $i < $rowCount;) {
                        $j = $i + 1;
                        while ($j < $rowCount && $displayRows[$j]['jenis_plain'] === $displayRows[$i]['jenis_plain']) {
                            $j++;
                        }
                        $jenisRowspan[$i] = $j - $i;
                        $i = $j;
                    }

                    for ($i = 0; $i < $rowCount;) {
                        $j = $i + 1;
                        while ($j < $rowCount && $displayRows[$j]['lokasi_plain'] === $displayRows[$i]['lokasi_plain']) {
                            $j++;
                        }
                        $lokasiRowspan[$i] = $j - $i;
                        $i = $j;
                    }
                @endphp
                @if (!empty($displayRows))
                    @foreach ($displayRows as $rowIndex => $row)
                        <tr>
                            <td style="vertical-align: top; text-align: center; width: 10%; white-space: nowrap; font-size: 8px;">
                                @if(!empty($row['no_lab']))
                                    {{ $row['no_lab'] }}
                                @endif
                            </td>
                            @if ($jenisRowspan[$rowIndex] > 0)
                                <td rowspan="{{ $jenisRowspan[$rowIndex] }}" style="vertical-align: middle; text-align: center;">
                                    {!! $row['jenis_sampel'] !!}
                                </td>
                            @endif
                            @if ($lokasiRowspan[$rowIndex] > 0)
                                <td rowspan="{{ $lokasiRowspan[$rowIndex] }}" style="vertical-align: middle;">
                                    {!! $row['lokasi_html'] !!}
                                </td>
                            @endif
                            <td style="vertical-align: top;">
                                {!! $row['name_item'] !!}
                            </td>
                            <td style="text-align: right; vertical-align: top; white-space: nowrap;">
                                {{ $row['harga'] > 0 ? number_format($row['harga'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ $totalColumns }}" style="text-align: center; {{ $noHorizontalBorderStyle }}"></td>
                    </tr>
                @endif
                @php
                    $currentRowCount = isset($displayRows) ? count($displayRows) : count($value_items_array);
                    $fillerRows = max(0, $minimumDisplayRows - $currentRowCount);
                @endphp
                @for ($i = -4; $i < $fillerRows; $i++)
                    <tr>
                        <td style="text-align: center;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
                @php
                    $total_parameter = $total_parameter ?? ($total_harga ?? 0);
                    $biaya_pengambilan_sampel = $biaya_pengambilan_sampel ?? 0;
                    $summaryLabelColspan = 3;
                @endphp
                @if($biaya_pengambilan_sampel > 0)
                    <tr>
                        <td colspan="{{ $summaryLabelColspan }}" style="{{ $noHorizontalBorderStyle }}"></td>
                        <td style="text-align: right; {{ $noHorizontalBorderStyle }}">Biaya Pengambilan Sampel</td>
                        <td style="text-align: right; {{ $noHorizontalBorderStyle }}">{{ rupiahTanpaRp($biaya_pengambilan_sampel) }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="{{ $summaryLabelColspan }}"></td>
                    <td style="text-align: right;"><strong>Total</strong></td>
                    <td style="text-align: right;"><strong>{{ rupiahTanpaRp($total_harga ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="{{ $summaryLabelColspan }}"></td>
                    <td style="text-align: right;">Dibayar</td>
                    <td style="text-align: right;">{{ rupiahTanpaRp($dibayar ?? 0) }}</td>
                </tr>
                <tr style="border-bottom: none !important;">
                    <td colspan="{{ $summaryLabelColspan }}" style="border-bottom: none !important; border-top: none !important;"></td>
                    <td style="text-align: right; border-bottom: none !important; border-top: none !important;">Sisa</td>
                    <td style="text-align: right; border-bottom: none !important; border-top: none !important;">{{ rupiahTanpaRp($sisa ?? 0) }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="16%" style="border: none;">No. HP</td>
                    <td width="2%" style="border: none;">:</td>
                    <td width="50%" style="border: none;">{{ $no_hp ?? "081234567890" }}</td>
                </tr>
                <tr>
                    <td width="18%" style="border: none;">Unit Pemeriksaan</td>
                    <td width="1%" style="border: none; vertical-align: center;">:</td>
                    <td width="50%" style="border: none; vertical-align: center;">{{ $unit_pemeriksaan ?? '-' }}</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Nomor Sampel</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="50%" style="border: none;">{{ $no_sampel ?? "-" }}</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Tanggal Pengiriman</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="50%" style="border: none;">{{ $tanggal_pengiriman ?? "-" }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <br>
                Biaya Rp. <b style="margin-left: 100px; font-size: 14px;">{{ rupiahTanpaRp($total_harga ?? 0) }}</b>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">
            Keterangan:
            <ol>
                <li>Parameter yang telah didaftarkan untuk<br>
                    pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                <li>Pelanggan menyetujui semua metode uji yang <br>
                    digunakan di Labkesmas Kab. Magelang</li>
            </ol>
        </td>
    </tr>
</table>