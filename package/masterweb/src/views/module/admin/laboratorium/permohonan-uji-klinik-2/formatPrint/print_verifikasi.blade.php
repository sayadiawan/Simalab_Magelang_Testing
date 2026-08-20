<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">


    <title>VERIFIKASI KLINIK - {{ $item->getDisplayNoregister() }} </title>
    <link rel="shortcut icon" href="">
    <link href="{{ asset('assets/admin/cdn-local/css/bootstrap5.min.css') }}" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- CSS only -->
    <!-- Bootstrap CSS -->

    <style>
        .starter-template {
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
            border: 1px solid;
        }

        .table2 {
            font-size: 5px;
            text-align: center;
        }

        .result {
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
        }

        .result th {
            border: 1px solid black;
            text-align: center;
        }

        @page {
            size: 794px 1248px;
            margin: 20px 20px 20px 20px;
        }

        body {
            font-size: {{ isset($fontsize) ? $fontsize : 12 }}px;
        }

        .page_break {
            page-break-before: always;
        }

        /* Border untuk tabel verifikasi */
        table.table {
            border-collapse: collapse;
            width: 100%;
        }

        table.table,
        table.table th,
        table.table td {
            border: 1px solid #000 !important;
        }

        table.table th,
        table.table td {
            padding: {{ isset($padding) ? $padding : 5 }}px;
            font-size: {{ isset($fontsize) ? $fontsize : 12 }}px;
        }

        .border-dark {
            border: 1px solid #000 !important;
        }
    </style>
</head>

<body style="margin: 10px; padding: 720px 0px 0px 0px;;">

    @php $showKopVal = isset($showKop) ? (int)$showKop : 1; @endphp
    @if ($showKopVal)
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="734px" height="140px">
            </td>
        </tr>
    </table>
    @else
        {{-- Space kosong pengganti kop: 5.5 cm --}}
        <div style="height: 5.5cm; width: 100%;"></div>
    @endif
    <table class="table table-bordered" style="border-collapse: collapse; width: 100%; border: 1px solid #000;">
        <tr>
            <td class="p-1" colspan="3" style="border: 1px solid #000;"><b>Hari/Tanggal :</b>
                {{ \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($datePrintVerification) }} </td>
            <td class="p-1" colspan="1" style="border: 1px solid #000;"><b>No.Reg :</b>
                {{ $item->noregister_permohonan_uji_klinik }}</td>
        </tr>
        <thead>
            <tr class="text-center">
                <th scope="col" class="p-1" style="font-size: 12px; border: 1px solid #000;">Jenis Kegiatan Lab
                    Klinik
                </th>
                <th scope="col" class="p-1" style="font-size: 12px; border: 1px solid #000;">Jam</th>
                <th scope="col" class="p-1" style="font-size: 12px; border: 1px solid #000;">Nama Petugas</th>
                <th scope="col" class="text-center p-1" style="font-size: 12px; border: 1px solid #000;">TTD</th>
            </tr>
        </thead>
        <tbody>
            {{-- 1. Pendaftaran / Registrasi --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Pendaftaran /
                    Registrasi</th>
                @if (isset($listVerifications[1]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            try {
                                $jam = \Carbon\Carbon::parse($listVerifications[1]->start_date)->format('H:i');
                            } catch (\Exception $e) {
                                $jam = '-';
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[1]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[1]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
            {{-- 2. Pengambil Sample --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Pengambil Sample
                </th>
                @if (isset($listVerifications[6]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            $jam = '-';
                            if ((int) ($listVerifications[6]->is_done ?? 0) === 1 && !empty($listVerifications[6]->start_date)) {
                                try {
                                    $jam = \Carbon\Carbon::parse($listVerifications[6]->start_date)->format('H:i');
                                } catch (\Exception $e) {
                                    $jam = '-';
                                }
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[6]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[6]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
            {{-- 3. Penerima Sampel --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Penerima Sampel</th>
                @if (isset($listVerifications[7]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            try {
                                $jam = \Carbon\Carbon::parse($listVerifications[7]->start_date)->format('H:i');
                            } catch (\Exception $e) {
                                $jam = '-';
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[7]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[7]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
            {{-- 4. Pengolah Sampel --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Pengolah Sampel</th>
                @if (isset($listVerifications[2]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            try {
                                $jam = \Carbon\Carbon::parse($listVerifications[2]->start_date)->format('H:i');
                            } catch (\Exception $e) {
                                $jam = '-';
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[2]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[2]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
            {{-- 5. Pemeriksa Sampel --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Pemeriksa Sampel
                </th>
                @if (isset($listVerifications[3]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            try {
                                $jam = \Carbon\Carbon::parse($listVerifications[3]->start_date)->format('H:i');
                            } catch (\Exception $e) {
                                $jam = '-';
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[3]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[3]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
            {{-- 6. Verifikasi --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Verifikasi</th>
                @if (isset($listVerifications[4]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            try {
                                $jam = \Carbon\Carbon::parse($listVerifications[4]->start_date)->format('H:i');
                            } catch (\Exception $e) {
                                $jam = '-';
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[4]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[4]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
            {{-- 7. Validasi --}}
            <tr>
                <th scope="row" class="p-1" style="text-align: left; border: 1px solid #000;">Validasi</th>
                @if (isset($listVerifications[5]))
                    <td class="p-1" style="border: 1px solid #000;">
                        @php
                            try {
                                $jam = \Carbon\Carbon::parse($listVerifications[5]->start_date)->format('H:i');
                            } catch (\Exception $e) {
                                $jam = '-';
                            }
                        @endphp
                        {{ $jam }}
                    </td>
                    <td class="p-1" style="border: 1px solid #000;">{{ $listVerifications[5]->nama_petugas }}</td>
                    @php
                        $petugas = $listVerifications[5]->nama_petugas;
                        $nip = '';
                    @endphp
                    @if (isset($signOption) and $signOption == 0)
                        <td style="border: 1px solid #000;"></td>
                    @else
                        <td class="p-1" style="border: 1px solid #000;">@include('masterweb::module.admin.laboratorium.template.TTD_BSRE_VERIFKLINIK')</td>
                    @endif
                @else
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                    <td style="border: 1px solid #000;"></td>
                @endif
            </tr>
        </tbody>
    </table>
    @if (isset($qrBase64))
        <img src="data:image/png;base64, {{ $qrBase64 }}" alt="QR Code" width="80" height="80">
    @endif
</body>

</html>
