<table width="100%" cellspacing="0" cellpadding="0" border="1">
    <tr style="text-align: center;">
        <td style="width: 44%">
            KONTRAK PERMOHONAN PEMERIKSAAN
        </td>
        <td style="width: 56%">
            PENERIMAAN SAMPEL
        </td>
    </tr>
    <tr>
        <td>
            No. Rekaman: {{ $no_rekaman ?? 'F/labkesKabMgl/04/01/Rev00' }}
        </td>
        <td>
            No. Rekaman: {{ $no_rekaman ?? 'F/labkesKabMgl/04/01/Rev00' }}
        </td>
    </tr>
    <tr>
        <td style="text-align: center; padding: 0;">
            <b>{{ $nama_customer ?? "Sutrisno" }}</b>
            <br>
            <br>
            <br>
            {{ $alamat_customer ?? "Jl. Raya Kedungwuni, RT.01/RW.01, Kedungwuni, Kec. Mungkid, Kabupaten Magelang, Jawa Tengah 56151" }}
        </td>
        <td rowspan="2" style="vertical-align: top; padding: 0;">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="20%" style="border: none;">Pengirim/Asal Sampel</td>
                    <td width="2%" style="border: none;">:</td>
                    <td width="60%" style="border: none;">{{ $nama_customer ?? "-" }}</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Tanggal diambil/diterima</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="60%" style="border: none;">{{ ($tanggal_diambil ?? '-') . ' / ' . ($tanggal_diterima ?? '-') }}</td>
                </tr>
                <tr>
                    <td style="border: none;">Jam pendaftaran</td>
                    <td width="1%" style="border: none;">:</td>
                    <td style="border: none;">{{ $jam_diterima ?? "" }}:00</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Jumlah Sampel</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="60%" style="border: none;">{{ $jumlah_sampel ?? "" }}</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Keterangan</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="60%" style="border: none;">{{ $keterangan ?? "" }}</td>
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
                    <td width="50%" style="border: none; vertical-align: center;">PEMERIKSAAN {{ $unit_pemeriksaan ?? "KIMIA/FISIKA" }}</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none;">Jenis<br>Sampel</td>
                    <td width="1%" style="border: none;">:</td>
                    <td width="50%" style="border: none;">{{ $jenis_sampel ?? "Makanan, Makanan" }}</td>
                </tr>
                <tr>
                    <td width="15%" style="border: none; vertical-align: top;">parameter</td>
                    <td width="1%" style="border: none; vertical-align: top;">:</td>
                    <td width="50%" style="border: none;">{{ $parameter ?? "Formalin, Ketengikan" }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td><br></td>
        <td rowspan="4" style="vertical-align: top; padding: 0;">
            <table border="1" cellspacing="0" cellpadding="2" width="100%">
                <tr>
                    <td width="8%" style="text-align: center;"><b>No. Lab</b></td>
                    <td width="15%" style="text-align: center;"><b>Jenis Sampel</b></td>
                    @if ($unit_pemeriksaan !== 'KLINIK')
                        <td width="25%" style="text-align: center;"><b>Lokasi/titik pengambilan</b></td>
                    @endif
                    <td width="15%" style="text-align: center;"><b>Parameter</b></td>
                    <td width="8%" style="text-align: center;"><b>Biaya</b></td>
                </tr>
                @php
                    $no = 1;
                    $value_items_array = is_array($value_items ?? []) ? $value_items : [];
                    $rowCount = !empty($value_items_array) ? count($value_items_array) : 0;
                    $allUniqueParameters = [];
                    $parameterShown = false;
                    
                    // Kumpulkan semua parameter unik dari semua value_items
                    foreach ($value_items_array as $item) {
                        if (isset($item['name_item']) && !empty(trim($item['name_item']))) {
                            $params = explode(',', $item['name_item']);
                            foreach ($params as $param) {
                                $param = trim($param);
                                if (!empty($param) && !in_array($param, $allUniqueParameters)) {
                                    $allUniqueParameters[] = $param;
                                }
                            }
                        }
                    }
                    $allParametersString = implode(', ', $allUniqueParameters);
                    
                    // Kelompokkan value_items berdasarkan jenis_sampel + lokasi
                    $groupedItems = [];
                    foreach ($value_items_array as $item) {
                        $currJenis = $item['jenis_sampel'] ?? '';
                        $lokasiRaw = isset($item['lokasi']) ? $item['lokasi'] : '';
                        $lokasiKey = trim(preg_replace('/\s+/', ' ', strip_tags($lokasiRaw)));
                        $groupKey = $currJenis . '|' . $lokasiKey;
                        
                        if (!isset($groupedItems[$groupKey])) {
                            $groupedItems[$groupKey] = [
                                'jenis_sampel' => $currJenis,
                                'lokasi' => $lokasiRaw,
                                'lokasiKey' => $lokasiKey,
                                'total_biaya' => 0
                            ];
                        }
                        
                        // Jumlahkan biaya
                        $biaya = isset($item['price_item']) ? $item['price_item'] : (isset($item['total']) ? $item['total'] : 0);
                        $groupedItems[$groupKey]['total_biaya'] += $biaya;
                    }
                @endphp
                @if (!empty($groupedItems))
                    @foreach ($groupedItems as $groupKey => $groupedItem)
                        @php
                            $showParameter = !$parameterShown && !empty($allParametersString);
                            if ($showParameter) {
                                $parameterShown = true;
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center; border-bottom: none !important;">{{ $no_lab }}</td>
                            <td style="border-bottom: none !important;">
                                {!! $groupedItem['jenis_sampel'] ?? '' !!}
                            </td>
                            @if ($unit_pemeriksaan !== 'KLINIK')
                                <td style="border-bottom: none !important;">
                                    @php
                                        $lokasiRaw = $groupedItem['lokasi'] ?? '';
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
                                    {!! $lokasi !== '' ? $lokasi : '' !!}
                                </td>
                            @endif
                            <td style="border: 0 !important; border-left: 1px solid #000 !important; border-right: 1px solid #000 !important;">{!! $showParameter ? $allParametersString : '' !!}</td>
                            <td style="text-align: right; border-bottom: none !important;">
                                {{ number_format($groupedItem['total_biaya'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @php
                            $no++;
                        @endphp
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center;"></td>
                    </tr>
                @endif
                @php
                    $total_parameter = $total_parameter ?? ($total_harga ?? 0);
                    $biaya_pengambilan_sampel = $biaya_pengambilan_sampel ?? 0;
                @endphp
                <tr>
                    <td colspan="{{ $unit_pemeriksaan !== 'KLINIK' ? 4 : 3 }}" style="text-align: right;">Total Parameter</td>
                    <td style="text-align: right;">{{ rupiahTanpaRp($total_parameter) }}</td>
                </tr>
                @if($biaya_pengambilan_sampel > 0)
                <tr>
                    <td colspan="{{ $unit_pemeriksaan !== 'KLINIK' ? 4 : 3 }}" style="text-align: right;">Biaya Pengambilan Sampel</td>
                    <td style="text-align: right;">{{ rupiahTanpaRp($biaya_pengambilan_sampel) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="{{ $unit_pemeriksaan !== 'KLINIK' ? 4 : 3 }}" style="text-align: right;"><strong>Total</strong></td>
                    <td style="text-align: right;"><strong>{{ rupiahTanpaRp($total_harga ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="{{ $unit_pemeriksaan !== 'KLINIK' ? 4 : 3 }}" style="text-align: right;">Dibayar</td>
                    <td style="text-align: right;">{{ rupiahTanpaRp($dibayar ?? 0) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $unit_pemeriksaan !== 'KLINIK' ? 4 : 3 }}" style="text-align: right;">Sisa</td>
                    <td style="text-align: right;">{{ rupiahTanpaRp($sisa ?? 0) }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="text-align: center;">Kesimpulan Permohonan Pemeriksaan <br> Diterima / ditolak *)</td>
    </tr>
    <tr>
        <td>
            <br>
                Biaya (Rp.) <b style="margin-left: 100px; font-size: 14px;">{{ rupiahTanpaRp($total_harga ?? 0) }}</b>
            <br>
            <br>
        </td>
    </tr>
    <tr>
        <td>
            Keterangan:
            <ol>
                <li>Parameter yang telah didaftarkan untuk <br>
                    pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                <li>Pelanggan menyetujui semua metode saji yang <br>
                    digunakan di Labkesmas Kab. Magelang</li>
            </ol>
        </td>
    </tr>
</table>