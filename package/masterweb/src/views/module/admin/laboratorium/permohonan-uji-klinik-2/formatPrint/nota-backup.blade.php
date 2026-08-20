<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nota Klinik">
    <meta name="author" content="Klinik Kota Mungkid">
    <title>Nota BOYOLALI-KLINIK</title>
    <link rel="shortcut icon" href="favicon.ico">

    @php
        if (count($value_items) < 10) {
            # code...
            $size = 7;
        } elseif (count($value_items) >= 10 && count($value_items) <= 16) {
            # code...
            $size = 6;
        } else {
            $size = 5;
        }

        // dd(count($value_items));

    @endphp
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        hr {
            border: none;
            border-top: 2px dashed black;
        }

        .nota {
            width: 100%;
            height: 31%;
        }

        .nota-kop {
            width: 15%;
            height: 31%;
            padding: 0;
        }



        .nota-body {
            width: 85%;
            font-size: 200pt;
            padding-left: 20px;
            padding-right: 20px;
            padding-top: 30px;
            padding-bottom: 10px;
            vertical-align: top;
        }

        .nota-body-2 {
            background-color: #FFE3F0;
        }

        .nota-body-3 {
            background-color: #C1EDFF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr td {
            padding: 2px;
        }

        @media print {
            #cetak {
                display: none;
            }
        }



        .result {
            border: 1px solid black;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
            font-size: 10px;
        }

        .result2 {
            margin-top: 2px;
        }

        .result2 td {
            font-weight: bold;
            font-size: 10px;
        }

        .parallelogram {
            width: 90%;
            padding: 2px;
            height: 10px;
            border: 1px solid black;
            transform: skew(-20deg);
            display: flex;
            align-items: center;
        }



        .font_dinamic {
            margin-top: 2px;
            font-weight: bold;
            font-size: {{ $size }}pt !important;
        }
    </style>
</head>


<body>
    <table class="nota">
        <tr>
            <td class="nota-kop">
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang_nota.png') }}" width="100%"
                    height="31%">
            </td>
            <td class="nota-body">
                <table class="border result2 fixed">
                    <tr>
                        <td width="30%">TELAH DITERIMA DARI</td>
                        <td width="2%">:</td>
                        <td width="68%">{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
                    </tr>
                    <tr>
                        <td>TANGGAL PEMERIKSAAN</td>
                        <td>:</td>
                        <td>
                            @if ($tanggal_transaksi_lunas)
                                {{ Carbon\Carbon::parse($tanggal_transaksi_lunas)->isoFormat('DD MMMM YYYY') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>ALAMAT</td>
                        <td>:</td>
                        <td>{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}</td>
                    </tr>
                    <tr>
                        <td>UANG SEJUMLAH</td>
                        <td>:</td>
                        <td>
                            <div>
                                <span>{{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik) }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>GUNA MEMBAYAR</td>
                        <td>:</td>
                        <td>JENIS PEMERIKSAAN LABORATORIUM KLINIK, terdiri dari:</td>
                    </tr>
                </table>

                @php
                    $no = 1;
                    $total = 0;
                    $value_count = count($value_items);
                @endphp

                @if ($value_count < 5)
                    <!-- Tampilkan satu tabel jika jumlah data < 5 -->
                    <table class="font_dinamic" style="width: 100%; margin-bottom: 20px;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="35%" class="result">PEMERIKSAAN</td>
                            <td width="25%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($value_items as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                @else
                    <!-- Bagi tabel menjadi dua bagian jika jumlah data >= 5 -->
                    @php
                        $half = ceil($value_count / 2); // Bagian setengah data
                        $first_half = array_slice($value_items, 0, $half); // Bagian pertama
                        $second_half = array_slice($value_items, $half); // Bagian kedua
                    @endphp

                    <table class="font_dinamic" style="float: left; width: 49%; margin-right: 1%;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="45%" class="result">PEMERIKSAAN</td>
                            <td width="15%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($first_half as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>

                    <table class="font_dinamic" style="float: right; width: 49%; margin-left: 1%;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="45%" class="result">PEMERIKSAAN</td>
                            <td width="15%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($second_half as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                @endif

                <table class="result2" style="clear: both;">
                    <tr>
                        <td width="70%"></td>
                        <td width="15%">JUMLAH</td>
                        <td width="15%">
                            {{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ?: $total) }}</td>
                    </tr>
                    <tr>
                        <td>
                            <span>TERBILANG : </span><br><br>
                            <div class="parallelogram">
                                <span><i>{{ terbilang($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ?: $total) }}</i></span>
                            </div>
                        </td>
                        <td></td>
                        <td>
                            <p>Kota Mungkid, @if ($item_permohonan_uji_klinik->status_pembayaran == '1')
                                    {{ \Smt\Masterweb\Helpers\DateHelper::formatDateIndo(date('d F, Y')) }}
                                @endif
                            </p>
                            Yang menerima<br>
                            Petugas,<br><br><br>
                            @if ($item_permohonan_uji_klinik->status_pembayaran == '1')
                                @if ($detail_payment->nota_petugas_permohonan_uji_payment_klinik)
                                    Pitoyo
                                @else
                                    ..................
                                @endif
                            @else
                                ..................
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr>
    <table class="nota">
        <tr>
            <td class="nota-kop nota-body-2">
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang_nota.png') }}" width="100%"
                    height="31%">
            </td>
            <td class="nota-body nota-body-2">
                <table class="border result2 fixed">
                    <tr>
                        <td width="30%">TELAH DITERIMA DARI</td>
                        <td width="2%">:</td>
                        <td width="68%">{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
                    </tr>
                    <tr>
                        <td>TANGGAL PEMERIKSAAN</td>
                        <td>:</td>
                        <td>
                            @if ($tanggal_transaksi_lunas)
                                {{ Carbon\Carbon::parse($tanggal_transaksi_lunas)->isoFormat('DD MMMM YYYY') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>ALAMAT</td>
                        <td>:</td>
                        <td>{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}</td>
                    </tr>
                    <tr>
                        <td>UANG SEJUMLAH</td>
                        <td>:</td>
                        <td>
                            <div>
                                <span>
                                    {{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>GUNA MEMBAYAR</td>
                        <td>:</td>
                        <td>JENIS PEMERIKSAAN LABORATORIUM KLINIK, terdiri dari:</td>
                    </tr>
                </table>

                @php
                    $no = 1;
                    $total = 0;
                    $value_count = count($value_items);
                @endphp

                @if ($value_count < 5)
                    <!-- Tampilkan satu tabel jika jumlah data < 5 -->
                    <table class="font_dinamic" style="width: 100%; margin-bottom: 20px;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="35%" class="result">PEMERIKSAAN</td>
                            <td width="25%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($value_items as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                @else
                    <!-- Bagi tabel menjadi dua bagian jika jumlah data >= 5 -->
                    @php
                        $half = ceil($value_count / 2); // Bagian setengah data
                        $first_half = array_slice($value_items, 0, $half); // Bagian pertama
                        $second_half = array_slice($value_items, $half); // Bagian kedua
                    @endphp

                    <table class="font_dinamic" style="float: left; width: 49%; margin-right: 1%;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="45%" class="result">PEMERIKSAAN</td>
                            <td width="15%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($first_half as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>

                    <table class="font_dinamic" style="float: right; width: 49%; margin-left: 1%;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="45%" class="result">PEMERIKSAAN</td>
                            <td width="15%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($second_half as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                @endif

                <table class="result2" style="clear: both;">
                    <tr>
                        <td width="70%"></td>
                        <td width="15%">JUMLAH</td>
                        <td width="15%">
                            {{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ?: $total) }}</td>
                    </tr>
                    <tr>
                        <td>
                            <span>TERBILANG : </span><br><br>
                            <div class="parallelogram">
                                <span>
                                    <i>
                                        {{ terbilang($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ?: $total) }}
                                    </i>
                                </span>
                            </div>
                        </td>
                        <td></td>
                        <td>
                            <p>Kota Mungkid, @if ($item_permohonan_uji_klinik->status_pembayaran == '1')
                                    {{ \Smt\Masterweb\Helpers\DateHelper::formatDateIndo(date('d F, Y')) }}
                                @endif
                            </p>
                            Yang menerima<br>
                            Petugas,<br><br><br>
                            @if ($item_permohonan_uji_klinik->status_pembayaran == '1')
                                @if ($detail_payment->nota_petugas_permohonan_uji_payment_klinik)
                                    Pitoyo
                                @else
                                    ..................
                                @endif
                            @else
                                ..................
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <hr>
    <table class="nota">
        <tr>
            <td class="nota-kop nota-body-3">
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang_nota.png') }}" width="100%"
                    height="31%">
            </td>
            <td class="nota-body nota-body-3">
                <table class="border result2 fixed">
                    <tr>
                        <td width="30%">TELAH DITERIMA DARI</td>
                        <td width="2%">:</td>
                        <td width="68%">{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
                    </tr>
                    <tr>
                        <td>TANGGAL PEMERIKSAAN</td>
                        <td>:</td>
                        <td>
                            @if ($tanggal_transaksi_lunas)
                                {{ Carbon\Carbon::parse($tanggal_transaksi_lunas)->isoFormat('DD MMMM YYYY') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>ALAMAT</td>
                        <td>:</td>
                        <td>{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}</td>
                    </tr>
                    <tr>
                        <td>UANG SEJUMLAH</td>
                        <td>:</td>
                        <td>
                            <div>
                                <span>{{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik) }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>GUNA MEMBAYAR</td>
                        <td>:</td>
                        <td>JENIS PEMERIKSAAN LABORATORIUM KLINIK, terdiri dari:</td>
                    </tr>
                </table>

                @php
                    $no = 1;
                    $total = 0;
                    $value_count = count($value_items);
                @endphp

                @if ($value_count < 5)
                    <!-- Tampilkan satu tabel jika jumlah data < 5 -->
                    <table class="font_dinamic" style="width: 100%; margin-bottom: 20px;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="35%" class="result">PEMERIKSAAN</td>
                            <td width="25%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($value_items as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                @else
                    <!-- Bagi tabel menjadi dua bagian jika jumlah data >= 5 -->
                    @php
                        $half = ceil($value_count / 2); // Bagian setengah data
                        $first_half = array_slice($value_items, 0, $half); // Bagian pertama
                        $second_half = array_slice($value_items, $half); // Bagian kedua
                    @endphp

                    <table class="font_dinamic" style="float: left; width: 49%; margin-right: 1%;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="45%" class="result">PEMERIKSAAN</td>
                            <td width="15%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($first_half as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>

                    <table class="font_dinamic" style="float: right; width: 49%; margin-left: 51%;">
                        <tr>
                            <td width="5%" class="result">NO.</td>
                            <td width="45%" class="result">PEMERIKSAAN</td>
                            <td width="15%" class="result">JUMLAH (Rp)</td>
                        </tr>
                        @foreach ($second_half as $key => $value)
                            @if ($value['name_item'] != 'Sedimen')
                                <tr>
                                    <td class="result">{{ $no }}.</td>
                                    <td class="result">{{ $value['name_item'] }}</td>
                                    <td class="result">{{ rupiah($value['total']) }}</td>
                                </tr>
                            @endif
                            @php
                                $total += $value['total'];
                                $no++;
                            @endphp
                        @endforeach
                    </table>
                @endif

                <table class="result2" style="clear: both;">
                    <tr>
                        <td width="70%"></td>
                        <td width="15%">JUMLAH</td>
                        <td width="15%">
                            {{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ?: $total) }}</td>
                    </tr>
                    <tr>
                        <td>
                            <span>TERBILANG : </span><br><br>
                            <div class="parallelogram">
                                <span><i>{{ terbilang($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ?: $total) }}</i></span>
                            </div>
                        </td>
                        <td></td>
                        <td>
                            <p>Kota Mungkid, @if ($item_permohonan_uji_klinik->status_pembayaran == '1')
                                    {{ \Smt\Masterweb\Helpers\DateHelper::formatDateIndo(date('d F, Y')) }}
                                @endif
                            </p>
                            Yang menerima<br>
                            Petugas,<br><br><br>
                            @if ($item_permohonan_uji_klinik->status_pembayaran == '1')
                                @if ($detail_payment->nota_petugas_permohonan_uji_payment_klinik)
                                    Pitoyo
                                @else
                                    ..................
                                @endif
                            @else
                                ..................
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <hr>
</body>

</html>
