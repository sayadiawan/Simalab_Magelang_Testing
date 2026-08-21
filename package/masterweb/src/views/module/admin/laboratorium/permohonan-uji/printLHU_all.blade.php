<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>LHU.ELIT-2002006-AP </title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
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
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
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
    </style>
    <!--[if IE]>
        <script src="{{ asset('assets/admin/cdn-local/js/html5shiv.min.js') }}"></script>
        <script src="{{ asset('assets/admin/cdn-local/js/respond.min.js') }}"></script>
    <![endif]-->
</head>

<body style="margin: 10px; padding:0">

    @php
        $jumlah_sample = 0;
    @endphp

    @foreach ($samples as $sample)
        @php

            $laboratorium = \Smt\Masterweb\Models\Laboratorium::findOrFail($sample->id_laboratorium);

            $laboratoriummethods = \Smt\Masterweb\Models\SampleMethod::where(
                'tb_sample_method.laboratorium_id',
                '=',
                $sample->id_laboratorium,
            )
                ->where('tb_sample_method.sample_id', '=', $sample->id_samples)
                ->orderBy('ms_method.created_at')
                ->join('ms_method', function ($join) {
                    $join
                        ->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at');
                })
                ->join('ms_unit', function ($join) {
                    $join
                        ->on('ms_unit.id_unit', '=', 'ms_method.unit_method')
                        ->whereNull('ms_unit.deleted_at')
                        ->whereNull('ms_method.deleted_at');
                })
                ->leftjoin('tb_sample_result', function ($join) {
                    $join
                        ->on('tb_sample_result.method_id', '=', 'tb_sample_method.method_id')
                        ->on('tb_sample_result.laboratorium_id', '=', 'tb_sample_method.laboratorium_id')
                        ->on('tb_sample_result.sample_id', '=', 'tb_sample_method.sample_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('tb_sample_result.deleted_at');
                })
                ->get();

            $verifikasi = \Smt\Masterweb\Models\VerifikasiHasil::where('sample_id', '=', $sample->id_samples)
                ->where('laboratorium_id', '=', $laboratorium->id_laboratorium)
                ->first();
        @endphp


        <div class="container">

            <div class="row text-center " id="header">
                <img src="{{ asset('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px" class="img-fluid">
                <!--<div class="col-md-4"><img src="http://chart.apis.google.com/chart?cht=qr&chs=300x300&chl=" width="150px"></div>-->
            </div>
            <div class="row batas">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <table width="100%" class="table">
                        <tr>
                            <td width="10%">
                                Nomor
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                                449.5/.5.22/II/2020
                            </td>
                            <td align="right">
                                —, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}

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
                                Hasil Pemeriksaan {{ $laboratorium->nama_laboratorium }}
                            </td>
                            <td align="right">

                            </td>
                        </tr>
                    </table>



                    <br>


                    <table width="40%" class="table">
                        <tr>
                            <td colspan="2">
                                Yang Terhormat :
                            </td>

                        </tr>
                        <tr>
                            <td colspan="2">
                                Direktur {{ $sample->name_customer }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                {{ $sample->address_customer }}
                            </td>

                        </tr>
                        <tr>
                            <td colspan="2">
                                di-
                            </td>

                        </tr>
                        <tr>
                            <td width="3%">
                            </td>
                            <td>
                                <u>KAB. MAGELANG</u>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <br>
                    Disampaikan dengan hormat hasil pemeriksaan laboratorium kami adalah sebagai berikut:<br>

                    <table width="100%" class="table">
                        <tr>
                            <td width="30%">
                                No. Sampel
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                                {!! \Smt\Masterweb\Models\Sample::codesampleTableCellHtmlFrom($sample) !!}
                            </td>
                        </tr>

                        <tr>
                            <td width="30%">
                                Jenis sampel
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                                {{ $sample->name_sample_type }}
                            </td>
                        </tr>
                        <tr>
                            <td width="30%">
                                Lokasi sampel
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                                {{ $sample->location_samples }}
                            </td>
                        </tr>
                        <tr>
                            <td width="30%">
                                Pengambil sampel
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                                Petugas UPT Lab.Kes Estu Lentera Indo Teknologi
                            </td>
                        </tr>
                        <tr>
                            <td width="30%">
                                Tanggal diambil/diterima
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>

                                {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td width="30%">
                                Tanggal pemeriksaan
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                                {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y') }}
                                s.d
                                {{ isset($sample->date_analitik_sample)
                                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_analitik_sample)->isoFormat('D MMMM Y')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="30%" style="vertical-align: top;">
                                Parameter pemeriksaan
                            </td>
                            <td width="1%" style="vertical-align: top;">
                                :
                            </td>
                            <td style="vertical-align: top;">
                                @if (count($laboratoriummethods) > 1)
                                    @php
                                        $no = 1;
                                    @endphp
                                    @foreach ($laboratoriummethods as $laboratoriummethod)
                                        {{ $no }}. {{ $laboratoriummethod->params_method }} <br>
                                        @php
                                            $no++;
                                        @endphp
                                    @endforeach
                                @else
                                    {{ $laboratoriummethods[0]->params_method }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td width="30%">
                                Hasil pemeriksaan
                            </td>
                            <td width="1%">
                                :
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%" colspan="3">
                                <table class="table table-bordered result" width="100%">
                                    <thead>
                                        <tr>
                                            <td width="5%"><br>No <br><br></td>

                                            <td width="30%"><br>Parameter Pemeriksaan<br><br></td>
                                            <td width="25%"><br>Hasil Pemeriksaan<br><br></td>
                                            <td width="25%"><br>Batas Syarat<br><br></td>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        @php
                                            $no = 1;
                                        @endphp
                                        @foreach ($laboratoriummethods as $laboratoriummethod)
                                            <tr>
                                                <td width="5%"><br>{{ $no }}<br><br></td>
                                                <td width="30%"><br>{{ $laboratoriummethod->params_method }}<br><br>
                                                </td>
                                                <td width="25%">
                                                    <br>{{ isset($laboratoriummethod->hasil) ? $laboratoriummethod->hasil : 'Masih Proses' }}<br><br>
                                                </td>
                                                <td width="25%"><br>
                                                    {{ $laboratoriummethod->kadar_diperbolehkan_method }}
                                                    {{ $laboratoriummethod->shortname_unit }}<br><br></td>
                                            </tr>
                                            . {{ $laboratoriummethod->params_method }}
                                            @php
                                                $no++;
                                            @endphp
                                        @endforeach


                                        </thead>
                                </table>

                            </td>

                        </tr>
                    </table>
                    <br>

                    Rujukan baku mutu:Keputusan Menteri Kesehatan Republik Indonesia Nomor1204/MENKES/SK/X/2004.<br>
                    Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.





                    <p>&nbsp;</p>




                    <div class="row batas">

                    </div>

                    <div class="row batas" style="float: right; justify-content: center;">
                        <div class="col-md-8"></div>
                        <div class="col-md-4" style="text-align: center;">
                            Kepala PT. Kabupaten Boyolali<br>
                            @if (isset($verifikasi))
                                {{-- <img src="{{ asset('assets/admin/images/ttd.png') }}" width="100px" class="img-fluid">
            <div style="clear: right">
            </div> --}}
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
                            <strong><u>dr. Muharyati</u></strong><br>
                            Pembina<br>
                            NIP. 19721106 200212 2 001
                        </div>
                    </div>

                </div>

            </div>

            @if ($jumlah_sample < count($samples) - 1)
                <div class="page_break">
                </div>
            @endif
            @php
                $jumlah_sample++;
            @endphp
    @endforeach








</body>

</html>
