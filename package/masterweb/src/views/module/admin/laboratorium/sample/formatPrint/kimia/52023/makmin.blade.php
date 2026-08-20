<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Makmin-{!! $no_LHU !!}</title>
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
            font-size: 12px;
        }

        .page_break {
            page-break-before: always;
        }
    </style>
</head>

<body style="margin: 10px; padding: 0;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                {{-- <img src="/mnt/sda1/laravel/BOYOLALI-prod/public/storage/admin/images/logo/kop_BOYOLALI.png"
          width="730px"> --}}
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%">

            </td>
        </tr>
    </table>

    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center"><strong><u> PEMERIKSAAN BAHAN TAMBAHAN MAKANAN</u></strong></td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="30%">
                No. Lab
            </td>
            <td width="1%">
                :
            </td>
            <td>
                @if (isset($lab_string))
                    {{ $lab_string }}
                @else
                    - /MAK-KIM/{{ getRomawi(date('m')) }}/{{ date('Y') }}
                @endif
            </td>
        </tr>

        {{-- <tr>
      <td width="30%">
        No. Lab
      </td>
      <td width="1%">
        :
      </td>
      <td>
        @if (isset($lab_string))
        {{ $lab_string }}
        @else
        - /Mak-KIM/{{ getRomawi(date('m')) }}/{{ date('Y') }}
        @endif
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
                {{-- $sampleList .= $prefix . explode('/', $mytable['sample_type']->codesample_samples)[2]; --}}
                @php
                    $min = \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint((string) ($all_samples_min ?? ''), (bool) ($all_samples_min_is_manual ?? false), 2);
                    $max = \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint((string) ($all_samples_max ?? ''), (bool) ($all_samples_max_is_manual ?? false), 2);

                @endphp
                {{ $min . ' - ' . $max }}
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
                {!! arrayToKomma($table, 'jenis_makanan', 'name_jenis_makanan') !!}

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
                {{ $permohonan_uji->location_permohonan_uji }}
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
                {{ $permohonan_uji->pdam_pengirim_sample }}
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

                {{ isset($diambil_min)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $diambil_min)->isoFormat('D MMMM Y')
                    : '-' }}
                /
                {{ isset($diambil_max)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $diambil_max)->isoFormat('D MMMM Y')
                    : '-' }}
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
                {{ isset($checking_min)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $checking_min)->isoFormat('D MMMM Y')
                    : '-' }}
                s.d
                {{ isset($done_max) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $done_max)->isoFormat('D MMMM Y') : '-' }}
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
                {{ arrayToKomma($method_all, 'name_report') }}
            </td>
        </tr>

        <tr>
            <td width="30%">
                Hasil Pemeriksaan
            </td>
            <td width="1%">
                :
            </td>
            <td>
                Metode Tes Kit
            </td>
        </tr>



    </table>

    <table width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; width: 6%" rowspan="2">No Sampel</td>

            <td style="text-align: center; width: 12%" rowspan="2">Jenis Sampel</td>
            <td style="text-align: center; width: 70%" colspan="{{ count($method_all) }}">Hasil Pemeriksaan</td>
            <td style="text-align: center; width: 70%" colspan="{{ count($method_all) }}">Batas Syarat</td>
        </tr>
        <tr>
            @foreach ($method_all as $method)
                <td style="text-align: center; width: 5%">{!! $method->name_report !!}</td>
            @endforeach
            @foreach ($method_all as $method)
                <td style="text-align: center; width: 5%">{!! $method->name_report !!}</td>
            @endforeach
        </tr>
        @php
            $no = 1;
        @endphp
        @foreach ($table as $mytable)
            <tr>

                <td style="text-align: center">{{ \Smt\Masterweb\Models\Sample::codesampleSegmentForPrint($mytable['sample_type']->codesample_samples ?? '', (bool) data_get($mytable['sample_type'], 'is_nomor_sampel_manual', false), 2) }}</td>
                <td style="text-align: left">{!! $mytable['sample_type']->location_samples !!}</td>

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
                    <td style="text-align: center">{!! $hasil !!}</td>
                    <td style="text-align: center">{!! $method->nilai_baku_mutu !!}</td>
                @endforeach

            </tr>

            @php
                $no++;
            @endphp
        @endforeach

    </table>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left">Ket: Hasil pemeriksaan dengan tanda (*) tidak memenuhi syarat baku mutu yang
                ditetapkan</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">

        {{-- <tr>
      <td style="text-align: left;vertical-align: top; width: 30%">Rujukan Baku Mutu</td>
      <td style="text-align: left;vertical-align: top; width: 3%">:</td>
      <td style="text-align: left;vertical-align: top; width: 67%">
        <table width="100%" cellspacing="0" cellpadding="0">
          @foreach ($all_acuan_baku_mutu as $all_acuan_baku_mutu)
          <tr>
            <td style="text-align: left;vertical-align: top;">-</td>
            <td style="text-align: left;vertical-align: top;">{{ $all_acuan_baku_mutu->title_library }}</td>
          </tr>
          @endforeach

        </table>

      </td>
    </tr> --}}
        <tr>
            <td style="text-align: left" colspan="3">Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.
            </td>
        </tr>
    </table>

    <br>
    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr style="padding-bottom: 1em">
            <td style="width: 70%;"></td>
            <td style="text-align: left;  padding-bottom: 1em">Semarang,
                {{ isset($date_verif_max)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date_verif_max)->isoFormat('D MMMM Y')
                    : '-' }}
            </td>
        </tr>

        <tr>
            <td style="width: 70%;"></td>
            <td style="text-align: left; ">Kepala UPT Laboratorium Kesehatan</td>
        </tr>

        <tr>
            <td style="width: 70%;"></td>
            <td style="text-align: left;">DINKES Kabupaten BOYOLALI</td>
        </tr>

        <tr>
            <td style="width: 70%;"></td>
            <td style="text-align: left;">
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
            <td style="width: 70%;"></td>
            <td style="text-align: left"><strong><u>dr. Siti Mahfudah</u></strong></td>
        </tr>

        <tr>
            <td style="width: 70%;"></td>
            <td style="text-align: left">Pembina<br>
                NIP. 19721106 200212 2 001</td>
        </tr>
    </table>

</body>

</html>
