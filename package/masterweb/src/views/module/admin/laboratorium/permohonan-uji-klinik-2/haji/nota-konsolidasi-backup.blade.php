<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nota Klinik Haji Konsolidasi">
    <meta name="author" content="Klinik Magelang">
    <title>Nota Magelang-KLINIK - {{ $customer->name_customer }}</title>
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
            padding: 2px 40px;
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
    </style>
</head>

<body>
    @php
        // Define variables yang digunakan di semua halaman
        $jenis_sampel_grouped = [];
        foreach ($value_items as $item) {
            $jenis_sampel = $item['jenis_sampel'] ?? '';
            if (!isset($jenis_sampel_grouped[$jenis_sampel])) {
                $jenis_sampel_grouped[$jenis_sampel] = [];
            }
            $jenis_sampel_grouped[$jenis_sampel][] = $item;
        }
        $biaya_pengambilan = 0; // Tidak ada biaya pengambilan untuk haji konsolidasi
        $total_parameter = $total_harga;
        $total_keseluruhan = $total_parameter + $biaya_pengambilan;
    @endphp
    {{-- Page 1 --}}
    <div class="section-page section-page-1">
    <div class="section-content">
    <!-- Header -->
    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0;">
        <tr>
            <td style="padding: 0;"><img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}"
                    height="80px" width="100%" style="display: block;">
            </td>
        </tr>
    </table>
    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin: 0;">
        <tr>
            <td colspan="2" style="text-align: center;"><strong>KONTRAK/ PERMOHONAN PEMERIKSAAN</strong></td>
        </tr>
        <tr>
            <td style="padding: 2px 3px;" colspan="2">
                <strong>No. Nota:</strong> {{ $haji->nama_haji }} - {{ Carbon\Carbon::parse($haji->tgl_haji)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td style="text-align: center; width: 40%; vertical-align: top;">
                <b style="font-size: 16px">{{ $customer->name_customer }}</b><br>
                {{ $customer->address_customer ?? '-' }}
            </td>
            <td rowspan="3" style="width: 60%; padding: 0 !important; border-left: none !important; border-right: none !important; vertical-align: top; border-bottom: none !important;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0" style="border: none;">
                    <tr>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000; border-left: none;">Jenis sampel</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Pemeriksaan</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Biaya</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Jumlah Sampel</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000; border-right: none; width: 40px !important;">Subtotal</td>
                    </tr>

                    @foreach ($jenis_sampel_grouped as $jenis_sampel => $items)
                        @foreach ($items as $index => $item)
                            <tr>
                                @if ($index == 0)
                                    <td rowspan="{{ count($items) }}" style="vertical-align: top; padding: 2px; border: 1px solid #000; border-left: none;">
                                        @php
                                            $jenis_sampel_display = $jenis_sampel;
                                            if (is_string($jenis_sampel_display)) {
                                                $decoded = json_decode($jenis_sampel_display, true);
                                                if (is_array($decoded)) {
                                                    echo implode(', ', $decoded);
                                                } else {
                                                    echo $jenis_sampel_display ?: '-';
                                                }
                                            } elseif (is_array($jenis_sampel_display)) {
                                                echo implode(', ', $jenis_sampel_display);
                                            } else {
                                                echo '-';
                                            }
                                        @endphp
                                    </td>
                                @endif
                                <td style="padding: 2px; border: 1px solid #000;">{{ $item['name_item'] }}</td>
                                <td style="padding: 2px; text-align: right; border: 1px solid #000;">{{ number_format($item['price_item'], 0, ',', '.') }}</td>
                                <td style="padding: 2px; text-align: center; border: 1px solid #000;">{{ $item['jumlah_sampel'] }}</td>
                                <td style="padding: 2px; text-align: right; border: 1px solid #000; border-right: none;">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                    <tr>
                        <td width="110px" style="border: none; padding: 1px 3px;">No. Hp</td>
                        <td width="5px" style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ $customer->cp_customer ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 3px;">Unit Pemeriksaan</td>
                        <td style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">Klinis</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 3px;">Jumlah Pasien</td>
                        <td style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ $jumlah_pasien }} orang</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                    <tr>
                        <td width="110px" style="border: none; padding: 1px 3px;">Biaya</td>
                        <td width="5px" style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ number_format($total_harga, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <b>Keterangan</b>
                <ol style="margin-left: -20px !Important">
                    <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                    <li>Pelanggan menyetujui semua metode uji yang digunakan di Labkesmas Kab. Magelang</li>
                </ol>
            </td>
            <td style="padding: 0; vertical-align: bottom; border-top-color: transparent !important;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0" style="border: none; margin-top: 2px;">
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Total Parameter</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none; width: 40px !important;">{{ number_format($total_parameter, 0, ',', '.') }}</td>
                    </tr>
                    @if($biaya_pengambilan > 0)
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Biaya Pengambilan Sampel</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">{{ number_format($biaya_pengambilan, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;"><strong>Total</strong></td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;"><strong>{{ number_format($total_keseluruhan, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Dibayar</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">{{ number_format($total_keseluruhan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Sisa</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">0</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Footer dengan tanda tangan -->
    <table width="100%" cellspacing="0" cellpadding="2" border="0" style="margin-top: 0;">
        <tr>
            <td></td>
            <td style="text-align: center; vertical-align: top; padding: 3px;">
                Dibayar pada: {{ $tanggal_transaksi_lunas ? Carbon\Carbon::parse($tanggal_transaksi_lunas)->format('d/m/Y') : date('d/m/Y') }}
            </td>
            <td></td>
        </tr>
        <tr>
            <td width="35%" style="vertical-align: top; padding: 3px;">
                <div style="font-size: 9px;">*) Kritik Dan Saran :</div>
                <div style="font-size: 16px; font-weight: bold; margin-top: 1px; margin-left: 20px;">089 538 499 0489</div>
                <div style="margin-top: 5px; padding-top: 5px;">
                    <div style="font-size: 16px; font-weight: bold;">
                        <span style="color: #28a745;">Lunas</span>
                    </div>
                </div>
            </td>
            <td width="32%" style="text-align: center; vertical-align: top; padding: 3px;">
                <div style="font-size: 10px; margin-bottom: 2px;">Pelanggan</div>
                <div style="height: 50px;"></div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $customer->name_customer }}</div>
            </td>
            <td width="33%" style="text-align: center; vertical-align: top; padding: 3px;">
                <div style="font-size: 10px; margin-bottom: 2px;">Pendaftar</div>
                <div style="height: 50px;"></div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $nama_petugas_registrasi }}</div>
            </td>
        </tr>
    </table>
    </div>
    </div>

    <hr class="divider">

    {{-- Page 2 of 3 --}}
    <div class="section-page section-page-2">
    <div class="section-content">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 2px;">
        <tr>
            <td style="padding: 0;"><img src="{{ public_path('assets/admin/images/logo/kop_magelang-no-bg.png') }}"
                    height="80px" width="100%" style="display: block;">
            </td>
        </tr>
    </table>
    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 0;">
        <tr>
            <td colspan="2" style="text-align: center;"><strong>KONTRAK/ PERMOHONAN PEMERIKSAAN</strong></td>
        </tr>
        <tr>
            <td style="padding: 2px 3px;" colspan="2">
                <strong>No. Nota:</strong> {{ $haji->nama_haji }} - {{ Carbon\Carbon::parse($haji->tgl_haji)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td style="text-align: center; width: 40%; vertical-align: top;">
                <b style="font-size: 16px">{{ $customer->name_customer }}</b><br>
                {{ $customer->address_customer ?? '-' }}
            </td>
            <td rowspan="3" style="width: 60%; padding: 0 !important; border-left: none !important; border-right: none !important; vertical-align: top; border-bottom: none !important;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0" style="border: none;">
                    <tr>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000; border-left: none;">Jenis sampel</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Pemeriksaan</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Biaya</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Jumlah Sampel</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000; border-right: none; width: 40px !important;">Subtotal</td>
                    </tr>
                    @foreach ($jenis_sampel_grouped as $jenis_sampel => $items)
                        @foreach ($items as $index => $item)
                            <tr>
                                @if ($index == 0)
                                    <td rowspan="{{ count($items) }}" style="vertical-align: top; padding: 2px; border: 1px solid #000; border-left: none;">
                                        @php
                                            $jenis_sampel_display = $jenis_sampel;
                                            if (is_string($jenis_sampel_display)) {
                                                $decoded = json_decode($jenis_sampel_display, true);
                                                if (is_array($decoded)) {
                                                    echo implode(', ', $decoded);
                                                } else {
                                                    echo $jenis_sampel_display ?: '-';
                                                }
                                            } elseif (is_array($jenis_sampel_display)) {
                                                echo implode(', ', $jenis_sampel_display);
                                            } else {
                                                echo '-';
                                            }
                                        @endphp
                                    </td>
                                @endif
                                <td style="padding: 2px; border: 1px solid #000;">{{ $item['name_item'] }}</td>
                                <td style="padding: 2px; text-align: right; border: 1px solid #000;">{{ number_format($item['price_item'], 0, ',', '.') }}</td>
                                <td style="padding: 2px; text-align: center; border: 1px solid #000;">{{ $item['jumlah_sampel'] }}</td>
                                <td style="padding: 2px; text-align: right; border: 1px solid #000; border-right: none;">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                    <tr>
                        <td width="110px" style="border: none; padding: 1px 3px;">No. Hp</td>
                        <td width="5px" style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ $customer->cp_customer ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 3px;">Unit Pemeriksaan</td>
                        <td style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">Klinis</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 3px;">Jumlah Pasien</td>
                        <td style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ $jumlah_pasien }} orang</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                    <tr>
                        <td width="110px" style="border: none; padding: 1px 3px;">Biaya</td>
                        <td width="5px" style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ number_format($total_harga, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <b>Keterangan</b>
                <ol style="margin-left: -20px !Important">
                    <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                    <li>Pelanggan menyetujui semua metode uji yang digunakan di Labkesmas Kab. Magelang</li>
                </ol>
            </td>
            <td style="padding: 0; vertical-align: bottom; border-top-color: transparent !important;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0" style="border: none; margin-top: 2px;">
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Total Parameter</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none; width: 40px !important;">{{ number_format($total_parameter, 0, ',', '.') }}</td>
                    </tr>
                    @if($biaya_pengambilan > 0)
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Biaya Pengambilan Sampel</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">{{ number_format($biaya_pengambilan, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;"><strong>Total</strong></td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;"><strong>{{ number_format($total_keseluruhan, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Dibayar</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">{{ number_format($total_keseluruhan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Sisa</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">0</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Footer dengan tanda tangan -->
    <table width="100%" cellspacing="0" cellpadding="2" border="0" style="margin-top: 0;">
        <tr>
            <td></td>
            <td style="text-align: center; vertical-align: top; padding: 3px;">
                Dibayar pada: {{ $tanggal_transaksi_lunas ? Carbon\Carbon::parse($tanggal_transaksi_lunas)->format('d/m/Y') : date('d/m/Y') }}
            </td>
            <td></td>
        </tr>
        <tr>
            <td width="35%" style="vertical-align: top; padding: 3px;">
                <div style="font-size: 9px;">*) Kritik Dan Saran :</div>
                <div style="font-size: 16px; font-weight: bold; margin-top: 1px; margin-left: 20px;">089 538 499 0489</div>
                <div style="margin-top: 5px; padding-top: 5px;">
                    <div style="font-size: 16px; font-weight: bold;">
                        <span style="color: #28a745;">Lunas</span>
                    </div>
                </div>
            </td>
            <td width="32%" style="text-align: center; vertical-align: top; padding: 3px;">
                <div style="font-size: 10px; margin-bottom: 2px;">Pelanggan</div>
                <div style="height: 50px;"></div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $customer->name_customer }}</div>
            </td>
            <td width="33%" style="text-align: center; vertical-align: top; padding: 3px;">
                <div style="font-size: 10px; margin-bottom: 2px;">Pendaftar</div>
                <div style="height: 50px;"></div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $nama_petugas_registrasi }}</div>
            </td>
        </tr>
    </table>
    </div>
    </div>

    <hr class="divider">

    {{-- Page 3 of 3 --}}
    <div class="section-page section-page-3">
    <div class="section-content">
    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 2px;">
        <tr>
            <td style="padding: 0;"><img src="{{ public_path('assets/admin/images/logo/kop_magelang-no-bg.png') }}"
                    height="80px" width="100%" style="display: block;">
            </td>
        </tr>
    </table>
    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 0;">
        <tr>
            <td colspan="2" style="text-align: center;"><strong>KONTRAK/ PERMOHONAN PEMERIKSAAN</strong></td>
        </tr>
        <tr>
            <td style="padding: 2px 3px;" colspan="2">
                <strong>No. Nota:</strong> {{ $haji->nama_haji }} - {{ Carbon\Carbon::parse($haji->tgl_haji)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td style="text-align: center; width: 40%; vertical-align: top;">
                <b style="font-size: 16px">{{ $customer->name_customer }}</b><br>
                {{ $customer->address_customer ?? '-' }}
            </td>
            <td rowspan="3" style="width: 60%; padding: 0 !important; border-left: none !important; border-right: none !important; vertical-align: top; border-bottom: none !important;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0" style="border: none;">
                    <tr>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000; border-left: none;">Jenis sampel</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Pemeriksaan</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Biaya</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000;">Jumlah Sampel</td>
                        <td style="font-weight: bold; padding: 2px; text-align: center; border: 1px solid #000; border-right: none; width: 40px !important;">Subtotal</td>
                    </tr>
                    @foreach ($jenis_sampel_grouped as $jenis_sampel => $items)
                        @foreach ($items as $index => $item)
                            <tr>
                                @if ($index == 0)
                                    <td rowspan="{{ count($items) }}" style="vertical-align: top; padding: 2px; border: 1px solid #000; border-left: none;">
                                        @php
                                            $jenis_sampel_display = $jenis_sampel;
                                            if (is_string($jenis_sampel_display)) {
                                                $decoded = json_decode($jenis_sampel_display, true);
                                                if (is_array($decoded)) {
                                                    echo implode(', ', $decoded);
                                                } else {
                                                    echo $jenis_sampel_display ?: '-';
                                                }
                                            } elseif (is_array($jenis_sampel_display)) {
                                                echo implode(', ', $jenis_sampel_display);
                                            } else {
                                                echo '-';
                                            }
                                        @endphp
                                    </td>
                                @endif
                                <td style="padding: 2px; border: 1px solid #000;">{{ $item['name_item'] }}</td>
                                <td style="padding: 2px; text-align: right; border: 1px solid #000;">{{ number_format($item['price_item'], 0, ',', '.') }}</td>
                                <td style="padding: 2px; text-align: center; border: 1px solid #000;">{{ $item['jumlah_sampel'] }}</td>
                                <td style="padding: 2px; text-align: right; border: 1px solid #000; border-right: none;">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                    <tr>
                        <td width="110px" style="border: none; padding: 1px 3px;">No. Hp</td>
                        <td width="5px" style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ $customer->cp_customer ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 3px;">Unit Pemeriksaan</td>
                        <td style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">Klinis</td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 1px 3px;">Jumlah Pasien</td>
                        <td style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ $jumlah_pasien }} orang</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" style="border: none;">
                    <tr>
                        <td width="110px" style="border: none; padding: 1px 3px;">Biaya</td>
                        <td width="5px" style="border: none; padding: 1px 3px;">:</td>
                        <td style="border: none; padding: 1px 3px;">{{ number_format($total_harga, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">
                <b>Keterangan</b>
                <ol style="margin-left: -20px !Important">
                    <li>Parameter yang telah didaftarkan untuk pemeriksaan tidak bisa dibatalkan oleh pelanggan</li>
                    <li>Pelanggan menyetujui semua metode uji yang digunakan di Labkesmas Kab. Magelang</li>
                </ol>
            </td>
            <td style="padding: 0; vertical-align: bottom; border-top-color: transparent !important;">
                <table width="100%" cellspacing="0" cellpadding="2" border="0" style="border: none; margin-top: 2px;">
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Total Parameter</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none; width: 40px !important;">{{ number_format($total_parameter, 0, ',', '.') }}</td>
                    </tr>
                    @if($biaya_pengambilan > 0)
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Biaya Pengambilan Sampel</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">{{ number_format($biaya_pengambilan, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;"><strong>Total</strong></td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;"><strong>{{ number_format($total_keseluruhan, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Dibayar</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">{{ number_format($total_keseluruhan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 2px; border: 1px solid #000; border-left: none;">Sisa</td>
                        <td style="text-align: right; padding: 2px; border: 1px solid #000; border-right: none;">0</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Footer dengan tanda tangan -->
    <table width="100%" cellspacing="0" cellpadding="2" border="0" style="margin-top: 0;">
        <tr>
            <td></td>
            <td style="text-align: center; vertical-align: top; padding: 3px;">
                Dibayar pada: {{ $tanggal_transaksi_lunas ? Carbon\Carbon::parse($tanggal_transaksi_lunas)->format('d/m/Y') : date('d/m/Y') }}
            </td>
            <td></td>
        </tr>
        <tr>
            <td width="35%" style="vertical-align: top; padding: 3px;">
                <div style="font-size: 9px;">*) Kritik Dan Saran :</div>
                <div style="font-size: 16px; font-weight: bold; margin-top: 1px; margin-left: 20px;">089 538 499 0489</div>
                <div style="margin-top: 5px; padding-top: 5px;">
                    <div style="font-size: 16px; font-weight: bold;">
                        <span style="color: #28a745;">Lunas</span>
                    </div>
                </div>
            </td>
            <td width="32%" style="text-align: center; vertical-align: top; padding: 3px;">
                <div style="font-size: 10px; margin-bottom: 2px;">Pelanggan</div>
                <div style="height: 50px;"></div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $customer->name_customer }}</div>
            </td>
            <td width="33%" style="text-align: center; vertical-align: top; padding: 3px;">
                <div style="font-size: 10px; margin-bottom: 2px;">Pendaftar</div>
                <div style="height: 50px;"></div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $nama_petugas_registrasi }}</div>
            </td>
        </tr>
    </table>
    </div>
    </div>

</body>

</html>

