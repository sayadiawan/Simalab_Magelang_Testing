<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Nota.KRNGNY-KLINIK </title>
    <link rel="shortcut icon" href="">
    <style>
        .starter-template {
            padding: 0px 0px;
            text-align: center;
        }


        table>tr>td {
            cell-padding: 5px !important;
        }

        @media print {
            #cetak {
                display: none;
            }
        }

        .garis {
            border: 1px solid
        }

        .table2 {
            font-size: 5px;
            text-align: center
        }

        .result {
            border: 1px solid black;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
            font-size: 12px;

        }

        .result2 td {

            font-style: bold;
            font-size: 13px;

        }

        .result3 td {

            font-style: bold;
            font-size: 8px;

        }

        @page {
            margin: 20px 50px 20px 50px;
        }

        body {
            font-size: 12px;
        }

        .page_break {
            page-break-before: always;
        }

        .parallelogram {
            width: 100%;
            height: 25px;
            border: 1px solid black;
            transform: skew(-20deg);
        }

        span.text {
            font-size: 15px;
            display: inline-block;
            -webkit-transform: skew(20deg);
            -moz-transform: skew(20deg);
            -o-transform: skew(20deg);
        }

        table.fixed {
            table-layout: fixed;
        }
    </style>

    <script src="{{asset('assets/admin/cdn-local/js/signature_pad-2.3.2.min.js')}}"></script>

</head>

<body>
    <div id="printable" class="container">
        {{-- print kop --}}
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td style="text-align: center">
                    <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="400px">
                </td>
            </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>
                        <h2>Kartu Rekam Medis</h2>
                    </th>
                </tr>
            </thead>
        </table>

        <table cellpadding="0" cellspacing="5" width="100%" style="border: 1px solid; margin-bottom: 20px">
            <tr>
                <td style="width: 15%">No. Rekam Medis</td>

                <td style="width: 1%">:</td>

                <td>
                    <p style="font-size: 20px">
                        <strong>{{ Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item_permohonan_uji_klinik->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}</strong>
                    </p>
                </td>
            </tr>

            <tr>
                <td style="width: 15%">Laboratorium</td>

                <td style="width: 1%">:</td>

                <td>UPT Estu Lentera Indo</td>
            </tr>

            <tr>
                <td style="width: 15%">Kabupaten/Kota</td>

                <td style="width: 1%">:</td>

                <td>Kab. BOYOLALI</td>
            </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px">
            <tr>
                <td style="width: 15%">Nama</td>
                <td style="width: 1%">:</td>
                <td style="text-align: left">{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
            </tr>

            <tr>
                <td style="width: 15%">Alamat</td>
                <td style="width: 1%">:</td>
                <td style="text-align: left">{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}</td>
            </tr>

            <tr>
                <td style="width: 15%">Tgl. Lahir</td>
                <td style="width: 1%">:</td>
                <td style="text-align: left">
                    {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien) ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat('D MMMM Y') : '-' }}
                </td>
            </tr>
        </table>

        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="overflow-x:auto;">
            <tr>
                <td width="30%">
                </td>

                <td width="30%">
                </td>

                <td width="40%">
                    <table border="0" width="100%" class="border table result" style="overflow-x:auto;">

                        <tr>
                            <td>
                                <h2>PENTING UNTUK PASIEN</h2>
                                <p style="font-size: 20px">KARTU INI HARUS DIBAWA SAAT AKAN MELAKUKAN PEMERIKSAAN</p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>

    </div>
    @if (isset($signOption) and $signOption == 1)
    <div style="position: fixed; bottom: 0px; text-align: left;">
        <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara</i></p>
    </div>
    @endif
</body>

</html>
