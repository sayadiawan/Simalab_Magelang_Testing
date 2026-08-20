<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {{ $item_permohonan_uji_klinik->getDisplayNoregister() }}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <style>
        .starter-template {
            text-align: center;
        }


        table>tr>td {
            /* cell-padding: 5px !important; */
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
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
        }

        @page {
            size: 794px 1248px;
            margin: 20px 20px 20px 20px;
        }

        body {
            font-size: 12px;
        }

        .page_break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><img src="{{ asset('assets/admin/images/logo/kop_magelang.png') }}" width="730px"></td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="10%">
                Nomor
            </td>
            <td width="1%">
                :
            </td>
            <td>
                -
            </td>
            <td align="right">
                Kota Mungkid,
                @php
                    $tgl_register = null;
                    if (
                        isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik) &&
                        $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik
                    ) {
                        try {
                            // Coba parse dengan format datetime dulu
                            if (strlen($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik) > 10) {
                                $tgl_register = \Carbon\Carbon::parse(
                                    $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                                )->isoFormat('D MMMM Y');
                            } else {
                                $tgl_register = \Carbon\Carbon::createFromFormat(
                                    'Y-m-d',
                                    $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                                )->isoFormat('D MMMM Y');
                            }
                        } catch (\Exception $e) {
                            $tgl_register = '-';
                        }
                    } else {
                        $tgl_register = '-';
                    }
                @endphp
                {{ $tgl_register }}

            </td>
        </tr>
        <tr>
            <td width="10%">
                Hal
            </td>
            <td width="1%">
                :
            </td>
            <td>
                Hasil Klinik
            </td>
            <td align="right">

            </td>
        </tr>
    </table>

    <br>
    <table width="40%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2">
                Yang Terhormat :
            </td>

        </tr>
        <tr>
            <td colspan="2">
                {{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
            </td>
        </tr>
    </table>
    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Disampaikan dengan hormat hasil laboratorium klinik kami adalah sebagai berikut:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="30%">
                No. Register
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->getDisplayNoregister() }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Tanggal Register
            </td>
            <td width="1%">
                :
            </td>
            <td>
                @php
                    $tgl_register_2 = '-';
                    if (
                        isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik) &&
                        $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik
                    ) {
                        try {
                            $tgl_register_2 = \Carbon\Carbon::parse(
                                $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                            )->isoFormat('D MMMM Y');
                        } catch (\Exception $e) {
                            $tgl_register_2 = '-';
                        }
                    }
                @endphp
                {{ $tgl_register_2 }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Nama Pasien
            </td>
            <td width="1%">
                :
            </td>
            <td>
                <strong>{{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}</strong>
            </td>
        </tr>

        <tr>
            <td width="30%">
                Jenis Kelamin
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Tanggal Lahir
            </td>
            <td width="1%">
                :
            </td>
            <td>
                @php
                    $tgl_lahir = '';
                    if (
                        isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien) &&
                        $item_permohonan_uji_klinik->pasien->tgllahir_pasien
                    ) {
                        try {
                            $tgl_lahir = \Carbon\Carbon::parse(
                                $item_permohonan_uji_klinik->pasien->tgllahir_pasien,
                            )->isoFormat('D MMMM Y');
                        } catch (\Exception $e) {
                            $tgl_lahir = '';
                        }
                    }
                @endphp
                {{ $tgl_lahir }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Usia
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
                {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari
            </td>
        </tr>

        <tr>
            <td width="30%">
                No. Rekam Medis
            </td>
            <td width="1%">
                :
            </td>
            <td>
                @php
                    $no_rekam_medis = '';
                    if (
                        isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien) &&
                        $item_permohonan_uji_klinik->pasien->tgllahir_pasien
                    ) {
                        try {
                            $tgl_lahir_format = \Carbon\Carbon::parse(
                                $item_permohonan_uji_klinik->pasien->tgllahir_pasien,
                            )->format('dmY');
                            $no_rekam_medis =
                                $tgl_lahir_format .
                                str_pad(
                                    (int) ($item_permohonan_uji_klinik->pasien->no_rekammedis_pasien ?? 0),
                                    4,
                                    '0',
                                    STR_PAD_LEFT,
                                );
                        } catch (\Exception $e) {
                            $no_rekam_medis = str_pad(
                                (int) ($item_permohonan_uji_klinik->pasien->no_rekammedis_pasien ?? 0),
                                4,
                                '0',
                                STR_PAD_LEFT,
                            );
                        }
                    } else {
                        $no_rekam_medis = str_pad(
                            (int) ($item_permohonan_uji_klinik->pasien->no_rekammedis_pasien ?? 0),
                            4,
                            '0',
                            STR_PAD_LEFT,
                        );
                    }
                @endphp
                {{ $no_rekam_medis }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                No. Telepon
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->pasien->phone_pasien ?? '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Alamat Pasien
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                No. KTP
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Pengirim
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik ?? '-' }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Dokter Perujuk
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {{ $item_permohonan_uji_klinik->dokter_permohonan_uji_klinik ?? '-' }}
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <td style="text-align: center" align="center">
            Scan Disini<br>
            <img
                src="{{ route('qrcode-permohonan-uji-klinik', [$item_permohonan_uji_klinik->id_permohonan_uji_klinik, 250, 0]) }}" /><br>
            Tracking Hasil
        </td>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <tr>
            <td style="width: 35%" style="text-align: left">Mengetahui:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>

            <td style="text-align: left">PENANGGUNGJAWAB LAB. KLINIK <br> UPT Estu Lentera Indo Teknologi</td>
            <td style="text-align: right">PETUGAS PEMERIKSA</td>
        </tr>

        <tr>
            <td style="height: 100px; vertical-align: bottom; text-align: left">
                Estu Lentera Indo Teknologi <br> Pembina<br>
                NIP. 19721106 200212 2 001
            </td>
            <td style="height: 100px; vertical-align: bottom; text-align: right">
                {{ $item_permohonan_uji_klinik->name_analis_permohonan_uji_klinik ?? '' }} <br>
                {{ 'NIP.' . ($item_permohonan_uji_klinik->nip_analis_permohonan_uji_klinik ?? '') }}
            </td>
        </tr>
    </table>
    <!-- @if (isset($signOption) and $signOption == 1) -->
        <div style="position: fixed; bottom: 0px; text-align: left;">
            <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik
                    menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan
                    Siber dan Sandi Negara</i></p>
        </div>
    <!-- @endif -->
</body>

</html>
