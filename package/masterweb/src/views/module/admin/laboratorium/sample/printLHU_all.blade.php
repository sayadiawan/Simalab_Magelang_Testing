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

<body>
    <div class="row text-center " id="header">
        <img src="{{ asset('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px" class="img-fluid">
        <!--<div class="col-md-4"><img src="http://chart.apis.google.com/chart?cht=qr&chs=300x300&chl=" width="150px"></div>-->
    </div>

    <div class="container">

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
                            Magelang, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}

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
                            <u>BOYOLALI</u>
                        </td>
                    </tr>
                </table>
                <br>
                <br>
                Disampaikan dengan hormat hasil pemeriksaan laboratorium kami adalah sebagai berikut:<br>

                <table width="100%" class="table">
                    {{-- <tr>
            <td width="30%">
              No. Lab
            </td>
            <td width="1%">
              :
            </td>
            <td>
             {{ isset($lab_num->lab_number)?
                sprintf("%04d",(int)$lab_num->lab_number):""
                }}
            </td>
          </tr> --}}
                    <tr>
                        <td width="30%">
                            No. Sampel
                        </td>
                        <td width="1%">
                            :
                        </td>
                        <td>
                            {{ \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($sample->codesample_samples ?? '', (bool) data_get($sample, 'is_nomor_sampel_manual', false), 2) }}
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
                            Tanggal Diperiksa
                        </td>
                        <td width="1%">
                            :
                        </td>
                        <td>
                            {!! \Smt\Masterweb\Helpers\Smt::resolveTanggalDiperiksaKesmas($sample, true) !!}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%">
                            Parameter pemeriksaan
                        </td>
                        <td width="1%">
                            :
                        </td>
                        <td>
                            @if (count($laboratoriummethods) > 1)
                                @php
                                    $no = 1;
                                @endphp
                                @foreach ($laboratoriummethods as $laboratoriummethod)
                                    {{ $no }}. {{ $laboratoriummethod->params_method }}
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
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="text-align: left">Ket: Hasil pemeriksaan dengan tanda (*) tidak memenuhi syarat baku
                            mutu yang
                            ditetapkan</td>
                    </tr>
                </table>

                Rujukan baku mutu:Keputusan Menteri Kesehatan Republik Indonesia Nomor1204/MENKES/SK/X/2004.<br>
                Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.





                <p>&nbsp;</p>




                <div class="row batas">

                </div>
                <div class="row batas" style="text-align: right;">
                    <div class="col-md-8"></div>
                    <div class="col-md-4 text-center">
                        Kepala UPT Laboratorium Kesehatan<br>
                        Kabupaten Boyolali<br>
                        <br><br>
                        <br><br>

                        <strong>dr. Muharyati</strong><br>
                        Pembina<br>
                        NIP. 19721106 200212 2 001
                        {{-- <strong><u>{{$laboratorium->nama_ttd_kepala_laboratorium}}</u></strong><br>
            NIP.{{$laboratorium->nip_kepala_laboratorium}} --}}
                    </div>
                </div>

            </div>

        </div>




</body>

</html>
