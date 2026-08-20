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
                <td style="text-align: center; font-size: 20px; padding-top: 20px"
                    colspan="{{ 6 + count($method_all) }}">
                    <strong>PEMERIKSAAN {{ $item_sampletype->name_sample_type }}</strong>
                </td>
            </tr>

            <tr>
                <td style="text-align: center; font-size: 20px; padding-bottom: 20px"
                    colspan="{{ 6 + count($method_all) }}">
                    <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                </td>
            </tr>
        @elseif (isset($item_laboratorium))
            <tr>
                <td style="text-align: center; font-size: 20px; padding-top: 20px"
                    colspan="{{ 6 + count($method_all) }}">
                    <strong>PEMERIKSAAN {{ strtoupper($item_laboratorium->nama_laboratorium) }}</strong>
                </td>
            </tr>

            <tr>
                <td style="text-align: center; font-size: 20px; padding-bottom: 20px"
                    colspan="{{ 6 + count($method_all) }}">
                    <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                </td>
            </tr>
        @else
            @if (isset($item_program))
                <tr>
                    <td style="text-align: center; font-size: 20px; padding-top: 20px"
                        colspan="{{ 6 + count($method_all) }}">
                        <strong>PEMERIKSAAN {{ $item_program->name_program }}</strong>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center; font-size: 20px; padding-bottom: 20px"
                        colspan="{{ 6 + count($method_all) }}">
                        <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                    </td>
                </tr>
            @else
                <tr>
                    <td style="text-align: center; font-size: 20px; padding-top: 20px"
                        colspan="{{ 6 + count($method_all) }}">
                        <strong>REKAPITULASI PEMERIKSAAN UPT. LABORATORIUM KESEHATAN</strong>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center; font-size: 20px; padding-bottom: 20px"
                        colspan="{{ 6 + count($method_all) }}">
                        <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
                    </td>
                </tr>
            @endif


        @endif

    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="text-align: center; width: 10%" colspan="1" rowspan="2">NO</th>
                <th style="text-align: center; width: 15%" colspan="1" rowspan="2">TGL. PEMERIKSAAN</th>
                <th style="text-align: center; width: 15%" colspan="1" rowspan="2">Kode Sampel</th>
                <th style="text-align: center; width: 15%" colspan="1" rowspan="2">Nama Sarana</th>
                <th style="text-align: center; width: 15%" colspan="1" rowspan="2">TITIK/LOKASI</th>
                <th style="text-align: center; width: 15%" colspan="1" rowspan="2">JENIS SAMPEL</th>
                <th style="text-align: center; width: 15%" colspan="1" rowspan="2">Nama Pengirim</th>
                <th style="text-align: center; width: 30%" colspan="{{ count($method_all) }}">HASIL PEMERIKSAAN
                    {{ isset($item_sampletype)
                        ? strtoupper($item_sampletype->name_sample_type)
                        : (isset($item_program)
                            ? strtoupper($item_program->name_program)
                            : '') }}
                </th>
            </tr>

            <tr>
                @foreach ($method_all as $key_ma => $item_ma)
                    <th style="text-align: center">{{ $item_ma->params_method }}</th>
                @endforeach
            </tr>
            <tr>
                <th style="text-align: center; width: 15%" colspan="7">Satuan</th>
                @foreach ($method_all as $key_ma => $item_ma)
                    <th style="text-align: center">{{ $item_ma->shortname_unit }}</th>
                @endforeach

            </tr>
            <tr>
                <th style="text-align: center; width: 15%" colspan="7">Baku Mutu</th>
                @foreach ($method_all as $key_ma => $item_ma)
                    <th style="text-align: center">{{ $item_ma->nilai_baku_mutu }}</th>
                @endforeach

            </tr>
        </thead>

        <tbody>
            {{-- Panggil data yang bukan sedimen --}}
            @if (count($data_sample_method) > 0)
                @foreach ($data_sample_method as $key_dsm => $item_dsm)
                    <tr>
                        <td style="text-align: center">{{ $key_dsm + 1 }}</td>
                        <td style="text-align: center">
                            {{ $item_dsm['sample']->penanganan_sample_date
                                ? fdate_carbon_sas($item_dsm['sample']->penanganan_sample_date, 'DDMMYYYY-HHMM')
                                : '-' }}
                        </td>
                        <td style="text-align: center">{{ $item_dsm['sample']->codesample_samples ?? '-' }}</td>
                        <td style="text-align: center">
                            {{ $item_dsm['sample']->namaPelangganDisplay() }}
                        </td>
                        <td style="text-align: center">{{ $item_dsm['sample']->location_samples ?? '-' }}</td>
                        <td style="text-align: center">{{ $item_dsm['sample']->sampletype->name_sample_type }}</td>
                        <td style="text-align: center">{{ optional($item_dsm['sample']->permohonanuji)->pengirim_sample ?? '-' }}</td>
                        @foreach ($item_dsm['method'] as $key_method => $item_dsm)
                            <th style="text-align: center">{{ $item_dsm }}</th>
                        @endforeach
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($method_all) }}" style="text-align: center">
                        Tidak ada pemeriksaan terkait data pasien.
                    </td>
                </tr>
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
