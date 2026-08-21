<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Laporan Bulanan {{ $item_program->name_program }}</title>
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
        <tr>
            <td><img src="{{ asset('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px"></td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; font-size: 20px; padding-top: 20px">
                <strong>PEMERIKSAAN {{ $item_program->name_program }}</strong>
            </td>
        </tr>

        <tr>
            <td style="text-align: center; font-size: 20px; padding-bottom: 20px">
                <strong>Bulan {{ fbulan($bulan) }} {{ $tahun }} </strong>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="width: 10%" rowspan="2">NO</th>
                <th style="width: 15%" rowspan="2">TGL. PEMERIKSAAN</th>
                <th style="width: 15%" rowspan="2">NAMA SARANA</th>
                <th style="width: 15%" rowspan="2">TITIK/LOKASI</th>
                <th style="width: 15%" rowspan="2">JENIS SAMPEL</th>
                <th style="width: 30%" colspan="{{ count($method_all) }}">HASIL PEMERIKSAAN
                    {{ strtoupper($item_program->name_program) }}</th>
            </tr>

            <tr>
                @foreach ($method_all as $key_ma => $item_ma)
                    <th style="text-align: center">{{ $item_ma->params_method }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            {{-- Panggil data yang bukan sedimen --}}
            @if (count($data_sample) > 0)
                @foreach ($data_sample as $key_ds => $item_ds)
                    <tr>
                        <td style="text-align: center">{{ $key_ds + 1 }}</td>
                        <td style="text-align: center">{!! \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item_ds->date_penanganan_sample)->isoFormat('D MMMM Y') !!}</td>
                        <td style="text-align: center">{{ $item_ds->codesample_samples ?? '-' }}</td>
                        <td style="text-align: center">{{ $item_ds->location_samples ?? '-' }}</td>
                        <td style="text-align: center">{{ $item_ds->sampletype->name_sample_type }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="100%" style="text-align: center">
                        Tidak ada pemeriksaan terkait data pasien.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
        <tr>
            <td style="text-align: right">—, {{ fdate_sas(date('Y-m-d'), 'DDMMYYYY') }}</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: right">
                Kepala UPT Laboratorium Kesehatan <br>
                Kabupaten BOYOLALI
            </td>
        </tr>

        <tr>
            <td style="height: 100px; vertical-align: bottom; text-align: right">
                Untari Tri Wardani,SKM M.Kes <br> NIP.197402132000032001
            </td>
        </tr>
    </table>
</body>

</html>
