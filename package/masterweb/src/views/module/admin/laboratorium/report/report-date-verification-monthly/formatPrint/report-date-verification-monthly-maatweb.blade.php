<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
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

        @media print {
            @page {
                size: landscape
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
            size: 1920px 1080px;
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
        @if (isset($item_sampletype))
            <tr>
                <td style="text-align: center; font-size: 20px; padding-top: 20px" colspan="{{ 7 + $size }}">
                    <strong>PEMERIKSAAN {{ $item_sampletype->name_sample_type }}</strong>
                </td>
            </tr>

            <tr>
                <td style="text-align: center; font-size: 20px; padding-bottom: 20px" colspan="{{ 7 + $size }}">
                    <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                </td>
            </tr>
        @else
            @if (isset($item_program))
                <tr>
                    <td style="text-align: center; font-size: 20px; padding-top: 20px" colspan="{{ 7 + $size }}">
                        <strong>PEMERIKSAAN {{ $item_program->name_program }}</strong>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center; font-size: 20px; padding-bottom: 20px" colspan="{{ 7 + $size }}">
                        <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                    </td>
                </tr>
            @else
                <tr>
                    <td style="text-align: center; font-size: 20px; padding-top: 20px" colspan="{{ 7 + $size }}">
                        <strong>REKAPITULASI PEMERIKSAAN UPT. LABORATORIUM KESEHATAN</strong>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center; font-size: 20px; padding-bottom: 20px"
                        colspan="{{ 7 + $size }}">
                        <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                    </td>
                </tr>
            @endif


        @endif

    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="text-align: center;" colspan="1" rowspan="2">NO</th>
                <th style="text-align: center;" colspan="1" rowspan="2">Kode Sampel</th>
                <th style="text-align: center;" colspan="1" rowspan="2">Nama Sarana</th>
                <th style="text-align: center;" colspan="1" rowspan="2">TITIK/LOKASI</th>
                <th style="text-align: center;" colspan="1" rowspan="2">JENIS SAMPEL</th>
                <th style="text-align: center;" colspan="1" rowspan="2">Nama Pengirim</th>
                <th style="text-align: center;" colspan="1" rowspan="2">Laboratorium</th>
                <th style="text-align: center;" colspan="{{ $size }}">Tanggal</th>
            </tr>
            <tr>
                @if (in_array('Pendaftaran / Registrasi', $date_verification))
                    <th style="text-align: center">Pendaftaran / Registrasi</th>
                @endif
                @if (in_array('Pengambilan Sample', $date_verification))
                    <th style="text-align: center">Pengambilan Sample</th>
                @endif
                @if (in_array('Pemeriksaan / Analitik', $date_verification))
                    <th style="text-align: center">Pemeriksaan / Analitik</th>
                @endif
                @if (in_array('Input / Output Hasil Px', $date_verification))
                    <th style="text-align: center">Input / Output Hasil Px</th>
                @endif
                @if (in_array('Verifikasi', $date_verification))
                    <th style="text-align: center">Verifikasi</th>
                @endif
                @if (in_array('Validasi', $date_verification))
                    <th style="text-align: center">Validasi</th>
                @endif
            </tr>
        </thead>
        <tbody>

            {{-- Panggil data yang bukan sedimen --}}
            @if (count($samples) > 0)
                @foreach ($samples as $key_dsm => $sample)
                    <tr>
                        <td style="text-align: center" colspan="1" rowspan="{{ count($sample['lab_id']) }}">
                            {{ $key_dsm + 1 }}
                        </td>
                        <td style="text-align: center" colspan="1" rowspan="{{ count($sample['lab_id']) }}">
                            {{ $sample['sample']->codesample_samples ?? '-' }}</td>
                        <td style="text-align: center" colspan="1" rowspan="{{ count($sample['lab_id']) }}">
                            {{ $sample['sample']->namaPelangganDisplay() }}
                        </td>
                        <td style="text-align: center" colspan="1" rowspan="{{ count($sample['lab_id']) }}">
                            {{ $sample['sample']->location_samples ?? '-' }}</td>
                        <td style="text-align: center" colspan="1" rowspan="{{ count($sample['lab_id']) }}">
                            {{ $sample['sample']->sampletype->name_sample_type }}</td>
                        <td style="text-align: center" colspan="1" rowspan="{{ count($sample['lab_id']) }}">
                            {{ optional($sample['sample']->permohonanuji)->pengirim_sample ?? '-' }}</td>
                        @foreach ($sample['lab_id'] as $key_lab => $lab)
                            @if ($key_lab == 'KIM')
                                <td style="text-align: center" colspan="1" rowspan="1">Kimia</td>
                            @endif
                            @if ($key_lab == 'MBI')
                                <td style="text-align: center" colspan="1" rowspan="1">Mikrobiologi</td>
                            @endif

                            @if (in_array('Pendaftaran / Registrasi', $date_verification))
                                <td style="text-align: center" colspan="1" rowspan="1">
                                    {{ isset($lab['pendaftaran_registrasi']->stop_date) ? $lab['pendaftaran_registrasi']->stop_date : '-' }}
                                </td>
                            @endif
                            @if (in_array('Pengambilan Sample', $date_verification))
                                <td style="text-align: center" colspan="1" rowspan="1">
                                    {{ isset($lab['pengambilan_sample']->stop_date) ? $lab['pengambilan_sample']->stop_date : '-' }}
                                </td>
                            @endif
                            @if (in_array('Pemeriksaan / Analitik', $date_verification))
                                <td style="text-align: center" colspan="1" rowspan="1">
                                    {{ isset($lab['pemeriksaan_analitik']->stop_date) ? $lab['pemeriksaan_analitik']->stop_date : '-' }}
                                </td>
                            @endif
                            @if (in_array('Input / Output Hasil Px', $date_verification))
                                <td style="text-align: center" colspan="1" rowspan="1">
                                    {{ isset($lab['input_output_hasil_px']->stop_date) ? $lab['input_output_hasil_px']->stop_date : '-' }}
                                </td>
                            @endif
                            @if (in_array('Verifikasi', $date_verification))
                                <td style="text-align: center" colspan="1" rowspan="1">
                                    {{ isset($lab['verifikasi']->stop_date) ? $lab['verifikasi']->stop_date : '-' }}
                                </td>
                            @endif
                            @if (in_array('Validasi', $date_verification))
                                <td style="text-align: center" colspan="1" rowspan="1">
                                    {{ isset($lab['validasi']->stop_date) ? $lab['validasi']->stop_date : '-' }}
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>

        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">

                <br>
                <br>

            </td>
        </tr>


        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">Kepala UPT Laboratorium Kesehatan</td>
        </tr>

        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">Estu Lentera Indo Teknologi</td>
        </tr>

        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">
                @if (isset($verifikasi))
                    <br>
                    <br>
                    <br>
                    <br>
                @else
                    <br>
                    <br>
                    <br>
                    <br>
                @endif
            </td>
        </tr>
        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">
                @if (isset($verifikasi))
                    <br>
                    <br>
                    <br>
                    <br>
                @else
                    <br>
                    <br>
                    <br>
                    <br>
                @endif
            </td>
        </tr>
        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">
                @if (isset($verifikasi))
                    <br>
                    <br>
                    <br>
                    <br>
                @else
                    <br>
                    <br>
                    <br>
                    <br>
                @endif
            </td>
        </tr>

        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%"><strong><u>dr. Siti Mahfudah</u></strong></td>
        </tr>

        <tr>
            <td style="width: 35%"></td>
            <td style="width: 35%"></td>
            <td style="text-align: right; width: 30%">Pembina<br>
                NIP. 19721106 200212 2 001</td>
        </tr>
    </table>
</body>

</html>
