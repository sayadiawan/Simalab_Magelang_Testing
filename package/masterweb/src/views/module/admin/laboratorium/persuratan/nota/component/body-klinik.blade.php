<table width="100%" cellspacing="0" cellpadding="0" border="1">
    <tr style="text-align: center;">
        <td style="width: 44%" colspan="2">
            <b>{{ $document_title ?? 'NOTA PEMBAYARAN' }}</b>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 0;">
            <b style="font-size: 12pt">{{ $nama_customer ?? "Sutrisno" }}</b>
            <br>
            <br>
            {{ \Smt\Masterweb\Helpers\Smt::formatAlamatCetak($alamat_customer ?? 'Alamat pelanggan') }}
        </td>
        <td rowspan="4" style="vertical-align: top; padding: 0; border-bottom: none !important;">
            <table border="1" cellspacing="0" cellpadding="2" width="100%" style="border-collapse: collapse; border-bottom: none !important;">
                <tr>
                    <td width="20%" style="text-align: center;">Jenis Sampel</td>
                    <td width="22%" style="text-align: center;">Pemeriksaan</td>
                    <td width="20%" style="text-align: center;">Biaya</td>
                    <td width="15%" style="text-align: center;">Jumlah Sampel</td>
                    <td width="18%" style="text-align: center;">Subtotal</td>
                </tr>
                @php
                    $value_items_array = is_array($value_items ?? []) ? $value_items : [];
                    $totalColumns = 5;
                    $compactNota = !empty($compactNota);
                    $minimumDisplayRows = $compactNota ? 2 : 8;
                    $extraFillerRows = $compactNota ? 0 : 4;
                    $noHorizontalBorderStyle = 'border-top: none; border-bottom: none;';

                    // Kelompokkan value_items berdasarkan jenis_sampel + pemeriksaan
                    $groupedItems = [];
                    foreach ($value_items_array as $item) {
                        $currJenis = $item['jenis_sampel'] ?? '';
                        $nameItem = isset($item['name_item']) ? trim($item['name_item']) : '';
                        $groupKey = $currJenis . '|' . $nameItem;
                        $biayaItem = (int) (isset($item['price_item']) ? $item['price_item'] : (isset($item['total']) ? $item['total'] : 0));
                        $jumlahSampelItem = (int) (isset($item['jumlah_sampel']) ? $item['jumlah_sampel'] : 1);

                        if (!isset($groupedItems[$groupKey])) {
                            $groupedItems[$groupKey] = [
                                'jenis_sampel' => $currJenis,
                                'pemeriksaan' => $nameItem,
                                'biaya' => $biayaItem,
                                'jumlah_sampel' => 0,
                                'subtotal' => 0
                            ];
                        }

                        $groupedItems[$groupKey]['jumlah_sampel'] += $jumlahSampelItem;
                        $groupedItems[$groupKey]['subtotal'] += $biayaItem;

                        // Pertahankan biaya satuan pertama yang valid
                        if ($groupedItems[$groupKey]['biaya'] <= 0 && $biayaItem > 0) {
                            $groupedItems[$groupKey]['biaya'] = $biayaItem;
                        }
                    }
                @endphp
                @if (!empty($groupedItems))
                    @foreach ($groupedItems as $groupKey => $groupedItem)
                        <tr>
                            <td style="vertical-align: top; {{ $noHorizontalBorderStyle }}">
                                {!! $groupedItem['jenis_sampel'] ?? '-' !!}
                            </td>
                            <td style="vertical-align: top; {{ $noHorizontalBorderStyle }}">
                                {!! $groupedItem['pemeriksaan'] ?? '-' !!}
                            </td>
                            <td style="text-align: right; vertical-align: top; {{ $noHorizontalBorderStyle }}">
                                {{ ($groupedItem['biaya'] ?? 0) > 0 ? number_format($groupedItem['biaya'], 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: center; vertical-align: top; {{ $noHorizontalBorderStyle }}">
                                {{ $groupedItem['jumlah_sampel'] ?? 1 }}
                            </td>
                            <td style="text-align: right; vertical-align: top; {{ $noHorizontalBorderStyle }}">
                                {{ ($groupedItem['subtotal'] ?? 0) > 0 ? number_format($groupedItem['subtotal'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ $totalColumns }}" style="text-align: center; {{ $noHorizontalBorderStyle }}"></td>
                    </tr>
                @endif
                @php
                    $currentRowCount = count($groupedItems);
                    $fillerRows = max(0, $minimumDisplayRows - $currentRowCount);
                @endphp
                @for ($i = 0; $i < $fillerRows + $extraFillerRows; $i++)
                    <tr>
                        <td style="{{ $noHorizontalBorderStyle }}">&nbsp;</td>
                        <td style="{{ $noHorizontalBorderStyle }}">&nbsp;</td>
                        <td style="{{ $noHorizontalBorderStyle }}">&nbsp;</td>
                        <td style="{{ $noHorizontalBorderStyle }}">&nbsp;</td>
                        <td style="{{ $noHorizontalBorderStyle }}">&nbsp;</td>
                    </tr>
                @endfor
                @php
                    $total_parameter = $total_parameter ?? ($total_harga ?? 0);
                    $biaya_pengambilan_sampel = $biaya_pengambilan_sampel ?? 0;
                    // Label ringkasan di kanan (span 4 kolom), nilai di kolom Subtotal
                    $summaryLabelColspan = 4;
                @endphp
                @if($biaya_pengambilan_sampel > 0)
                    <tr>
                        <td colspan="{{ $summaryLabelColspan }}" style="text-align: right; white-space: nowrap; {{ $noHorizontalBorderStyle }}">Biaya Pengambilan Sampel</td>
                        <td style="text-align: right; {{ $noHorizontalBorderStyle }}">{{ rupiahTanpaRp($biaya_pengambilan_sampel) }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="{{ $summaryLabelColspan }}" style="text-align: right;"><strong>Total</strong></td>
                    <td style="text-align: right;"><strong>{{ rupiahTanpaRp($total_harga ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="{{ $summaryLabelColspan }}" style="text-align: right;">Dibayar</td>
                    <td style="text-align: right;">{{ rupiahTanpaRp($dibayar ?? 0) }}</td>
                </tr>
                <tr style="border-bottom: none !important;">
                    <td colspan="{{ $summaryLabelColspan }}" style="text-align: right; border-bottom: none !important; border-top: none !important;">Sisa</td>
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
                    <td width="50%" style="border: none; vertical-align: center;">Klinis</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Nomor Sampel</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="50%" style="border: none;">{{ $no_sampel ?? "-" }}</td>
                </tr>
                <tr>
                    <td width="25%" style="border: none;">Tanggal Pendaftaran</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="50%" style="border: none;">{{ isset($tanggal_pendaftaran) ? \Carbon\Carbon::parse($tanggal_pendaftaran)->format('d/m/Y') : "-" }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            @unless ($compactNota)
                <br>
            @endunless
            Biaya Rp. <b style="margin-left: 100px; font-size: 14px;">{{ rupiahTanpaRp($total_harga ?? 0) }}</b>
            @unless ($compactNota)
                <br>
                <br>
            @endunless
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">
            Keterangan:
            <ol>
                <li>Parameter yang telah didaftarkan untuk<br>
                    pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                <li>Pelanggan menyetujui semua metode uji yang <br>
                    digunakan di Laboratorium SIMLAB</li>
            </ol>
        </td>
    </tr>
</table>