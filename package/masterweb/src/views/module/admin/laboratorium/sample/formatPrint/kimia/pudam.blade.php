<html lang="">

@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 12.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.5;
    $padding = isset($padding) ? (float) $padding : 4.0;
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>PUDAM-{!! $no_LHU !!}</title>
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
            margin: 20px 20px 20px 50px;
        }

        body {
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
        }

        .page_break {
            page-break-before: always;
        }

        .table-container {
            flex: 2;
            margin-right: 10px;
        }

        .table-container table {
            width: 60%;
            border-collapse: collapse;
            font-size: 16px;
        }
    </style>
</head>

<body style="margin:50px 10px 50px 10px; padding: 0; font-size: {{ $fontsize }}pt; line-height: {{ $lineHeight }};">
    @if ($showKop)
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%">
                </td>
            </tr>
        </table>
    @else
        <div style="height: 120px;"></div>
    @endif

    <div class="row batas" style="float: right; justify-content: center; width: 250px;">
        <div class="">
            <div class="justify-content-end" style="text-align: center;">
                KEPADA<br>
                Yth. <span style="font-family: 'DejaVu Sans', sans-serif !important;">
                    {{ $sample->name_customer }}
                </span>
                <br>
                @if (isset($sample->address_customer) && $sample->address_customer !== '')
                    @php
                        $addresArray = explode(',', $sample->address_customer);
                        $kabupaten = $addresArray[count($addresArray) - 1];

                        unset($addresArray[count($addresArray) - 1]);

                        $address = implode(',', $addresArray);

                    @endphp
                    d.a {{ $address }}<br>
                    <div style="display: inline-block; vertical-align: top;">
                        Di_ <span
                            style="display: inline-block; padding-top: 24px;"><strong><u>{{ $kabupaten }}</u></strong></span>
                    </div>
                @else
                    Di <strong>{{ $sample->kecamatan_customer }}</strong>
                @endif
            </div>
        </div>
    </div>
    <div class="table-container">
        <table>
            <tr>
                <td>No. Agenda</td>
                <td>:</td>
                <td>{!! $no_LHU !!}</td>
            </tr>
            <tr>
                <td>No. Code Reg</td>
                <td>:</td>
                <td></td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td>Hasil Pemeriksaan {{ $laboratorium->nama_laboratorium }}</td>
            </tr>
            <tr>
                <td>Asal Contoh Air</td>
                <td>:</td>
                <td class="wrap-text">
                    @if ($sample->is_pudam == 1)
                        @if (isset($sample->location_samples))
                            @php
                                $location = str_replace("\n", '<br>', $sample->location_samples);
                                $location = str_replace(
                                    '<div id="simple-translate" class="simple-translate-system-theme">&nbsp;</div>',
                                    '',
                                    $location,
                                );
                                $location = str_replace('<p>', '', $location);
                                $location = str_replace('</p>', '', $location);

                            @endphp


                            {!! $location !!}
                        @else
                            {{ $sample->name_customer_pdam }}
                        @endif
                    @else
                        @php
                            $location = str_replace("\n", '<br>', $sample->location_samples);

                            $location = str_replace('<p>', '', $location);
                            $location = str_replace('</p>', '', $location);

                        @endphp


                        {!! $location !!}
                    @endif
                </td>
            </tr>
            <tr>
                <td>Tanggal diambil</td>
                <td>:</td>
                <td>{{ isset($sample->datesampling_samples) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->datesampling_samples)->isoFormat('D MMMM Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td>Tanggal diterima</td>
                <td>:</td>
                <td>{{ isset($sample->date_sending) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y') : '-' }}
                </td>
            </tr>
            <tr>
                <td>No. Lab</td>
                <td>:</td>
                <td>
                    {{ isset($lab_num->lab_number) ? sprintf('%04d', (int) $lab_num->lab_number) : '' }}
                </td>
            </tr>
            <tr>
                <td>Bahan</td>
                <td>:</td>
                <td>{{ $sample->name_sample_type }}</td>
            </tr>
        </table>
    </div>


    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left"><strong>Hasil pemeriksaan</strong></td>
        </tr>
    </table>

    <table width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; width: 6%" rowspan="2">No<br>Sampel</td>

            <td style="text-align: center; width: 12%" rowspan="2">Lokasi</td>
            <td style="text-align: center; width: 12%">Diambil tgl</td>
            <td style="text-align: center; width: 70%" colspan="{{ count($method_all) }}">Hasil</td>
        </tr>
        <tr>
            <td style="text-align: center; width: 12%">Diperiksa tgl</td>
            @foreach ($method_all as $method)
                <td style="text-align: center; width: 5%">{!! $method->name_report !!}</td>
            @endforeach
        </tr>
        @php
            $no = 1;
        @endphp
        @foreach ($table as $mytable)
            <tr>

                <td style="text-align: center" rowspan="2">
                    {{ \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($mytable['sample_type']->codesample_samples ?? '', (bool) data_get($mytable['sample_type'], 'is_nomor_sampel_manual', false), 2) }}
                </td>
                <td style="text-align: left" rowspan="2">


                    {{ $mytable['sample_type']->name_customer_pdam }}

                    @php
                        $location = str_replace("\n", '<br>', $mytable['sample_type']->address_location_pdam);

                        $location = str_replace('<p>', '<br>', $location);
                        $location = str_replace('</p>', '', $location);

                    @endphp
                    {!! $location !!}
                </td>
                <td style="text-align: center;">
                    {{ isset($mytable['sample_type']->datesampling_samples)
                        ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $mytable['sample_type']->datesampling_samples)->isoFormat('D
                                                                                                                                                                                                                                                                                      MMMM Y')
                        : '-' }}
                </td>
                @foreach ($mytable['result'] as $result)
                    @php
                        $hasil = $result['hasil'];
                        if (isset($hasil)) {
                            if ($hasil != '-') {
                                $hasil = $result['hasil'];
                                if (isset($result['min'])) {
                                    if ((float) $hasil < (float) $result['min']) {
                                        $hasil =
                                            "<span style='font-weight:bold'>" .
                                            $result['hasil'] .
                                            '*</span>
        ';
                                    }
                                }
                                if (isset($result['max'])) {
                                    if ((float) $hasil > (float) $result['max']) {
                                        $hasil = "<span style='font-weight:bold'>" . $result['hasil'] . '*</span> ';
                                    }
                                }

                                if (isset($result['equal'])) {
                                    if ($result['hasil'] != $result['equal']) {
                                        $hasil = "<span style='font-weight:bold'>" . $result['hasil'] . '*</span> ';
                                    }
                                }
                            } else {
                                $hasil = $result['hasil'];
                            }
                        } else {
                            $hasil = '-';
                        }
                    @endphp
                    <td style="text-align: center" rowspan="2">{!! $hasil !!}</td>
                @endforeach
            </tr>
            <tr>
                <td style="text-align: center;">
                    {{ isset($mytable['sample_type']->date_checking)
                        ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $mytable['sample_type']->date_checking)->isoFormat('D MMMM Y')
                        : '-' }}
                </td>
            </tr>
            @php
                $no++;
            @endphp
        @endforeach
        <tr>

            <td style="text-align: center" colspan="3">Satuan</td>
            @foreach ($method_all as $method)
                <td style="text-align: center">{!! $method->shortname_unit !!}</td>
            @endforeach
        </tr>
        <tr>

            <td style="text-align: center" colspan="3">Kadar Maksimum Yang Diperbolehkan</td>
            @foreach ($method_all as $method)
                <td style="text-align: center">{!! $method->nilai_baku_mutu !!}</td>
            @endforeach
        </tr>

    </table>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left">Ket: Hasil pemeriksaan dengan tanda (*) tidak memenuhi syarat baku mutu yang
                ditetapkan</td>
        </tr>
    </table>

    <strong class="text-keterangan">KETERANGAN</strong> <br>
    @php
        $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? null);
    @endphp
    @if ($keteranganMetodeCustom !== '')
        <span>{{ $keteranganMetodeCustom }}</span>
    @else
        <span>{{ $sample->name_sample_type }} mengacu </span>
        @if (count($all_acuan_baku_mutu) > 0)

            @if (count($all_acuan_baku_mutu) > 1)
                @php
                    $no = 1;
                @endphp
                @foreach ($all_acuan_baku_mutu as $acuan_baku_mutu)
                    {{ $acuan_baku_mutu->title_library }}

                    @php
                        $no++;
                    @endphp
                @endforeach
            @else
                {{ $all_acuan_baku_mutu[0]->title_library }}

            @endif
        @endif
    @endif

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._signature')

    {{-- <div class="row batas" style="float: right; justify-content: center; margin-top: 20px;">
        <div class="col-md-12">
            <div class="justify-content-end" style="text-align: center;">
                Magelang,...............2024 <br>
                Kepala Laboratorium Kesehatan<br>
                Kabupaten Boyolali<br>
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
                <strong><u>dr. Muharyati</u></strong><br>
                Pembina<br>
                NIP. 19721106 200212 2 001
            </div>
        </div>
    </div>
    <div>
        <p><u>Tembusan dikirim Kepada Yth:</u></p>
        <ol>
            <li>Pertanggal</li>
        </ol>
        <p>Catatan :</p>
    </div> --}}

    {{--    <table width="100%" cellspacing="0" cellpadding="0"> --}}

    {{--        <tr> --}}
    {{--            <td style="text-align: left;vertical-align: top; width: 30%">Rujukan Baku Mutu</td> --}}
    {{--            <td style="text-align: left;vertical-align: top; width: 3%">:</td> --}}
    {{--            <td style="text-align: left;vertical-align: top; width: 67%"> --}}
    {{--                <table width="100%" cellspacing="0" cellpadding="0"> --}}
    {{--                    @foreach ($all_acuan_baku_mutu as $all_acuan_baku_mutu) --}}
    {{--                        <tr> --}}
    {{--                            <td style="text-align: left;vertical-align: top;">-</td> --}}
    {{--                            <td style="text-align: left;vertical-align: top;">{{ $all_acuan_baku_mutu->title_library }} --}}
    {{--                            </td> --}}
    {{--                        </tr> --}}
    {{--                    @endforeach --}}

    {{--                </table> --}}

    {{--            </td> --}}
    {{--        </tr> --}}
    {{--        <tr> --}}
    {{--            <td style="text-align: left" colspan="3">Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya. --}}
    {{--            </td> --}}
    {{--        </tr> --}}
    {{--    </table> --}}

    {{--    <br> --}}
    {{--    <br> --}}


    {{--    <table width="100%" cellspacing="0" cellpadding="0"> --}}
    {{--        --}}{{-- <tr style="padding-bottom: 1em"> --}}
    {{--      <td style="width: 70%;"></td> --}}
    {{--      <td style="text-align: left;  padding-bottom: 1em">Magelang, --}}
    {{--        {{ isset($date_verif_max) --}}
    {{--        ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date_verif_max)->isoFormat('D MMMM Y') --}}
    {{--        : '-' }} --}}
    {{--      </td> --}}
    {{--    </tr> --}}

    {{--        <tr> --}}
    {{--            <td style="width: 70%;"></td> --}}
    {{--            <td style="text-align: left; ">Kepala UPT Laboratorium Kesehatan</td> --}}
    {{--        </tr> --}}

    {{--        <tr> --}}
    {{--            <td style="width: 70%;"></td> --}}
    {{--            <td style="text-align: left;">DINKES Kabupaten BOYOLALI</td> --}}
    {{--        </tr> --}}

    {{--        <tr> --}}
    {{--            <td style="width: 70%;"></td> --}}
    {{--            <td style="text-align: left;"> --}}
    {{--                @if (isset($verifikasi)) --}}
    {{--                    <br> --}}
    {{--                    <br> --}}
    {{--                    <br> --}}
    {{--                    <br> --}}
    {{--                @else --}}
    {{--                    <br> --}}
    {{--                    <br> --}}
    {{--                    <br> --}}
    {{--                    <br> --}}
    {{--                @endif --}}
    {{--            </td> --}}
    {{--        </tr> --}}

    {{--        <tr> --}}
    {{--            <td style="width: 70%;"></td> --}}
    {{--            <td style="text-align: left"><strong><u>dr. Siti Mahfudah</u></strong></td> --}}
    {{--        </tr> --}}

    {{--        <tr> --}}
    {{--            <td style="width: 70%;"></td> --}}
    {{--            <td style="text-align: left">Pembina<br> --}}
    {{--                NIP. 19721106 200212 2 001</td> --}}
    {{--        </tr> --}}
    {{--    </table> --}}

</body>

</html>
