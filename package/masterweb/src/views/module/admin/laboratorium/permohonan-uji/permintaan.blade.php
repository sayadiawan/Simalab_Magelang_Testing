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
        body {
            /* padding-top: 50px; */
        }

        .starter-template {
            padding: 40px 15px;
            text-align: center;
        }

        .batas {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        table>tr>td {
            /* cell-padding: 5px !important; */
        }

        @media print {}

        .garis {
            border: 1px solid
        }

        .table2 {
            font-size: 10px;
            text-align: center
        }

        table {
            max-width: 100%;
            max-height: 100%;
        }

        body {
            position: relative;
            width: 100%;
            height: 100%;
        }

        /* table th,
        table td {
            padding: .625em;
        text-align: center;
        } */
        /* table .kop:before {
            content: ': ';
        } */
        .left {
            text-align: left;
        }

        table #caption {
            font-size: 1.5em;
            /* margin: .5em 0 .75em; */
        }

        table.border {
            width: 100%;
            border-collapse: collapse
        }

        table.border tbody th,
        table.border tbody td {
            border: thin solid #000;
            /* padding: 2px */
        }

        .ttd td,
        .ttd th {
            padding-bottom: .5em;
        }

        .table tbody td {
            text-align: left;
            padding: .5em .5em;
        }

        /* p { page-break-after: always; } */
        p:last-child {
            page-break-after: never;
        }

        .page_break {
            page-break-before: always;
        }
    </style>
    <!--[if IE]>
        <script src="{{asset('assets/admin/cdn-local/js/html5shiv.min.js')}}"></script>
        <script src="{{asset('assets/admin/cdn-local/js/respond.min.js')}}"></script>
    <![endif]-->
</head>

<body style="margin:10px; padding:0">
    {{-- <img src="/home/dannu/Laravel/BOYOLALI-prod/public/assets/admin/images/logo/kop_BOYOLALI.png"
    width="730px"> --}}

    <img src="{{ public_path('assets/admin/images/logo/kop_magelang_rev_3.png') }}" width="100%" class="img-fluid">




    @php
        $i = 0;
        $len = count($samples);
    @endphp



    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="overflow-x:auto;">


        <tr>
            <td width="25%"></td>
            <td width="25%"></td>
            <td width="25%" style="text-align: right; margin-right: 20px; font-size:12px">KRA/ADM/FORM/001</td>
        </tr>
        <tr>
            <td width="100%" colspan="3" style="text-align: center"><br><b>BLANGKO PERMINTAAN PEMERIKSAAN
                    LABORATORIUM</b>
                <br><br>
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table" style="overflow-x:auto; ">
        <thead>

            <tr>
                <td width="32%" style="text-align: left"></td>
                <td width="1%"></td>
                <td width="25%"></td>
                <td width="25%" style="border: 1px solid #080808; text-align: center">Kode Sampel</td>
            </tr>
            <tr>
                <td width="32%" style="text-align: left"></td>
                <td width="1%"></td>
                <td width="25%"></td>
                <td width="25%" style="border: 1px solid #080808; text-align: center">
                    {{ $samples_first->codesample_samples != $samples_last->codesample_samples
                        ? \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($samples_first->codesample_samples ?? '', (bool) data_get($samples_first, 'is_nomor_sampel_manual', false), 2) . ' - ' . \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($samples_last->codesample_samples ?? '', (bool) data_get($samples_last, 'is_nomor_sampel_manual', false), 2)
                        : \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($samples_last->codesample_samples ?? '', (bool) data_get($samples_last, 'is_nomor_sampel_manual', false), 2) }}
                </td>
            </tr>
        </thead>
        <tbody width="100%" style="border: 1px solid #080808;">
            <tr>
                <td width="32%">Nama</td>
                <td width="1%">:</td>
                <td width="25%" colspan="2">{{ $permohonan_uji->name_customer }}</td>
                {{-- <td width="5%"></td>
        <td width="10%">Tanggal</td>
        <td width="0%">:</td>
        <td width="35%" class="left kop">Wonosobo, 22 Maret 2019</td> --}}
            </tr>
            <tr>
                <td width="32%">Alamat</td>
                <td width="1%">:</td>
                <td width="25%" colspan="2">{{ $permohonan_uji->address_customer }}</td>
                {{-- <td width="5%"></td>
        <td width="10%">Tanggal</td>
        <td width="0%">:</td>
        <td width="35%" class="left kop">Wonosobo, 22 Maret 2019</td> --}}
            </tr>
            <tr>
                <td width="32%">Tanggal Pendaftaran</td>
                <td width="1%">:</td>
                <td width="25%" colspan="2">
                    @php
                        $jamPendaftaran = '-';
                        if ($samples_first) {
                            $vasReg = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $samples_first->id_samples)
                                ->where('id_verification_activity', 1)
                                ->first();
                            if ($vasReg && $vasReg->stop_date) {
                                $tanggalPendaftaran = \Carbon\Carbon::parse($vasReg->stop_date)->translatedFormat('d F Y');
                            }
                        }
                    @endphp
                    {{ $tanggalPendaftaran }}
                </td>
            </tr>
            <tr>
                <td width="32%">Jenis Sample</td>
                <td width="1%">:</td>
                <td width="25%" colspan="2">
                    @if (count($sample_types) > 1)
                        @php
                            $no = 1;
                        @endphp
                        @foreach ($sample_types as $sample_type)
                            {{ $no . '. ' . $sample_type->name_sample_type }}<br>
                            @php
                                $no++;
                            @endphp
                        @endforeach
                    @elseif (count($sample_types) == 1)
                        {{ $sample_types[0]->name_sample_type }}
                    @endif
                </td>
                {{-- <td width="5%"></td>
        <td width="10%">Tanggal</td>
        <td width="0%">:</td>
        <td width="35%" class="left kop">Wonosobo, 22 Maret 2019</td> --}}
            </tr>

            <tr>
                <td style="text-align: left">Tanggal/Jam Pengambilan Sampel</td>
                <td>:</td>
                <td colspan="2">
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sampling_first->datesampling_samples)->isoFormat(
                        'D MMMM Y HH:mm',
                    ) !=
                    \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sampling_last->datesampling_samples)->isoFormat(
                        'D MMMM Y HH:mm',
                    )
                        ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sampling_first->datesampling_samples)->isoFormat(
                                'D MMMM Y HH:mm',
                            ) .
                            " -
                                                                                          " .
                            \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sampling_last->datesampling_samples)->isoFormat(
                                'D MMMM Y HH:mm',
                            )
                        : \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sampling_first->datesampling_samples)->isoFormat(
                            'D MMMM Y HH:mm',
                        ) }}

                </td>
                {{-- <td></td> --}}
                {{-- <td style="text-align: left">Perihal</td>
        <td>:</td>
        <td class="left kop">Sewa Harian</td> --}}
            </tr>
            <tr>
                <td style="text-align: left">Tanggal/Jam Pengiriman Sampel</td>
                <td>:</td>

                <td colspan="2">

                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sending_first->date_sending)->isoFormat(
                        'D MMMM Y HH:mm',
                    ) != \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sending_last->date_sending)->isoFormat('D MMMM Y HH:mm')
                        ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sending_first->date_sending)->isoFormat(
                                'D MMMM Y HH:mm',
                            ) .
                            " -
                                                                                          " .
                            \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sending_last->date_sending)->isoFormat('D MMMM Y HH:mm')
                        : \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_sending_first->date_sending)->isoFormat('D MMMM Y HH:mm') }}

                </td>
                {{-- <td></td> --}}
                {{-- <td style="text-align: left">Perihal</td>
        <td>:</td>
        <td class="left kop">Sewa Harian</td> --}}
            </tr>

            <tr>
                <td style="text-align: left">Tanggal/Jam Diterima Sampel</td>
                <td>:</td>

                <td colspan="2">

                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_penerimaan_first->penerimaan_sample_date)->isoFormat(
                        'D MMMM Y HH:mm',
                    ) !=
                    \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_penerimaan_last->penerimaan_sample_date)->isoFormat(
                        'D MMMM Y HH:mm',
                    )
                        ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_penerimaan_first->penerimaan_sample_date)->isoFormat(
                                'D MMMM Y HH:mm',
                            ) .
                            " -
                                                                                          " .
                            \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_penerimaan_last->penerimaan_sample_date)->isoFormat(
                                'D MMMM Y HH:mm',
                            )
                        : \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample_penerimaan_first->penerimaan_sample_date)->isoFormat(
                            'D MMMM Y HH:mm',
                        ) }}

                </td>
                {{-- <td></td> --}}
                {{-- <td style="text-align: left">Perihal</td>
        <td>:</td>
        <td class="left kop">Sewa Harian</td> --}}
            </tr>
            <tr>
                <td style="text-align: left">Ket. Kondisi Sampel</td>
                <td>:</td>

                <td colspan="2">
                    {{ $permohonan_uji->catatan }}
                </td>
                {{-- <td></td> --}}
                {{-- <td style="text-align: left">Perihal</td>
        <td>:</td>
        <td class="left kop">Sewa Harian</td> --}}
            </tr>


        </tbody>
    </table>

    <br>

    <center>Permintaan Pemeriksaan Laboratorium</center>
    <br>
    @php

        $char = 'A';

    @endphp

    <table border="1" cellpadding="0" cellspacing="0" width="100%" class="table" style="overflow-x:auto; ">

        @foreach ($laboratoriums as $laboratorium)
            <tr>
                <td width="1%" style="text-align: left">{{ $char }}.</td>
                <td width="45%">LABORATORIUM {{ strtoupper($laboratorium->nama_laboratorium) }}</td>
                @php
                    $laboratoriummethods = \Smt\Masterweb\Models\SampleMethod::where(
                        'tb_sample_method.laboratorium_id',
                        '=',
                        $laboratorium->id_laboratorium,
                    )
                        // ->where('tb_sample_method.sample_id','=',$sample->id_samples)
                        ->where('tb_samples.permohonan_uji_id', '=', $id)
                        ->orderBy('ms_method.created_at')
                        ->join('tb_samples', function ($join) {
                            $join
                                ->on('tb_samples.id_samples', '=', 'tb_sample_method.sample_id')
                                ->whereNull('tb_sample_method.deleted_at')
                                ->whereNull('tb_samples.deleted_at');
                        })
                        ->join('ms_method', function ($join) {
                            $join
                                ->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                                ->whereNull('tb_sample_method.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('ms_unit', function ($join) {
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
                        ->select('ms_method.params_method', 'ms_method.created_at')
                        ->distinct('ms_method.id_method')
                        ->get();
                    $char++;
                @endphp
                <td width="54%">
                    @php
                        $j = 0;
                    @endphp
                    @foreach ($laboratoriummethods as $method)
                        {{ $method->params_method }}
                        @if ($j < count($laboratoriummethods) - 1)
                            ,
                        @endif @php $j++ @endphp
                    @endforeach
                </td>
            </tr>

            {{-- <p>{{$char}}. {{$laboratorium->nama_laboratorium}}</p> --}}
        @endforeach
    </table>

    <br>

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="border">
        <tfoot>
            <tr>
                <td colspan="3">Hasil diambil tanggal : - </td>
            </tr>

            <tr class="ttd">
                <td rowspan="2" width="34%" align="center">Tanda Tangan<br>Pengirim Sampel</td>
                <td width="33%"></td>
                <td rowspan="2" width="33%" align="center">Tanda Tangan<br>Petugas</td>
            </tr>

            <tr>
                <td style="text-align: center" align="center">
                    Scan Disini<br>
                    <img src="{{ route('qrcode', [$permohonan_uji->id_permohonan_uji]) }}" /><br>
                    Tracking Hasil
                </td>
            </tr>

            <tr>
                <td rowspan="2" width="34%" style="text-align: center">{{ $permohonan_uji->pengirim_sample }}
                </td>
                <td width="33%"></td>
                <td width="33%" rowspan="2" style="text-align: center">{{ $permohonan_uji->petugas_penerima }}
                </td>
            </tr>
        </tfoot>
    </table>
    @include('masterweb::template.admin.scripts')
    @yield('scripts')

</body>

</html>
