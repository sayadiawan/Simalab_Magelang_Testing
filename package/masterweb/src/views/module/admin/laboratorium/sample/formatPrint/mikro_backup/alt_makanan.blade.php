<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MIKRO-{{ $no_LHU }}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_style')
</head>

<body style="margin: 10px; padding: 0">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_kop')

    <br>



    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="10%">
                Nomor
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {!! $no_LHU !!}
            </td>
            <td align="right">
                —, {{ isset($pengesahan_hasil->pengesahan_hasil_date)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $pengesahan_hasil->pengesahan_hasil_date)->isoFormat('D MMMM
                                                                                                                                                                Y')
                    : '' }}

            </td>
            <td></td>
        </tr>

        <tr>
            <td width="0%">
                Perihal
            </td>
            <td width="0%">
                :
            </td>
            <td title="$laboratorium->nama_laboratorium">
                Hasil Pemeriksaan {{ $laboratorium->nama_laboratorium }}
            </td>
            <td width="150px" colspan="3">
                <table cellspacing="0" cellpadding="0" hidden>
                    <tr>
                        <td colspan="2">
                            Yth.
                        </td>

                    </tr>
                    <tr>
                        <td colspan="2">
                            {{ $sample->name_customer }}
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
                            <u>{{ $sample->kecamatan_customer }}</u>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Jenis Sampel --}}
        <tr>
            <td width="0%">
                Jenis Sampel
            </td>
            <td width="0%">
                :
            </td>
            <td title="$sample->name_sample_type">
                <b>
                    {{ $sample->name_sample_type }}
                </b>
            </td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        {{-- Lokasi --}}
        <tr>
            <td width="0%">
                Lokasi / Asal Sampel
            </td>
            <td width="0%">
                :
            </td>
            <td title="(static)"> - </td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        {{-- Nomor Register --}}
        <tr>
            <td width="0%">
                Nomor Register
            </td>
            <td width="0%">
                :
            </td>
            <td title="(static)"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        {{-- Nama Pelanggan --}}
        {{-- <tr>
      <td width="0%">
        Nama Pelanggan
      </td>
      <td width="0%">
        :
      </td>
      <td>

      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr> --}}

        {{-- Alamat Register --}}
        {{-- <tr>
      <td width="0%">
        Alamat Register
      </td>
      <td width="0%">
        :
      </td>
      <td>
      </td>
      <td width="150px">Petugas Sampling</td>
      <td width="0%">
        :
      </td>
      <td></td>
    </tr> --}}

        {{-- Jenis Sarana --}}
        {{-- <tr style="vertical-align: top">
      <td width="0%">
        Jenis Sarana
      </td>
      <td width="0%">
        :
      </td>
      <td><strong> IPAL </strong></td>
      <td width="150px">Parameter</td>
      <td width="0%">
        :
      </td>
      <td>
        <ul style="list-style: none; margin: 0; padding: 0; table">
          @foreach ($method_all as $method)
          <li> {{ $method->name_report }} </li>
          @endforeach
        </ul>
      </td>
    </tr> --}}

        {{-- Metode Pemeriksaan --}}
        {{-- <tr>
      <td width="25%">
        Metode Pemeriksaan
      </td>
      <td width="0%">
        :
      </td>
      <td><strong> MPN Tabung Ganda </strong></td>
      <td></td>
      <td></td>
      <td></td>
    </tr> --}}

        {{-- Hasil Pemeriksaan --}}
        {{-- <tr>
      <td width="0%">
        Hasil Pemeriksaan
      </td>
      <td width="0%">
        :
      </td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr> --}}
    </table>


    <br>

    <table class="result" width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; width: 0%" rowspan="2">No. <br> Lab.</td>
            <td style="text-align: center; width: 12em" rowspan="2">
                <u>Diterima tgl</u>
                <br>
                Diperiksa tgl
            </td>
            <td style="text-align: center; width: 25em" rowspan="2">
                Nama Sampel
            </td>

            <td style="text-align: center; width: 0" colspan="{{ count($method_all) }}"> Hasil Pemeriksaan </td>

            <td style="text-align: center; width: 0" rowspan="2">Satuan</td>
            <td style="text-align: center; width: 0" rowspan="2">Ket</td>
        </tr>

        <tr align="center">
            @foreach ($method_all as $method)
                <td style="text-align: center;">{{ $method->name_report }}</td>
            @endforeach
        </tr>

        @foreach ($table as $mytable)
            {{-- @dd($mytable) --}}
            <tr>
                <td style="text-align: center">{{ $loop->iteration }}</td>
                <td style="text-align: left">
                    <u><span title="$mytable['sample_type']->date_sending">
                            {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', data_get($mytable, 'sample_type.date_sending'))->isoFormat('D MMMM Y, HH:mm') }}
                        </span></u> <br>
                    <span title="$mytable['sample_type']->date_analitik_sample">
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', data_get($mytable, 'sample_type.date_analitik_sample'))->isoFormat('D MMMM Y, HH:mm') }}
                    </span>
                </td>
                <td style="text-align: center">
                    {!! $mytable['sample_type']->location_samples !!}
                </td>

                @foreach ($mytable['result'] as $result)
                    <td style="text-align: center">{{ data_get($result, 'hasil') }}</td>
                @endforeach

                <td style="text-align: center" title="$result->satuan_bakumutu">{!! $result['satuan_bakumutu'] !!}</td>
                <td title="(static)">-</td>
            </tr>
        @endforeach
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left">
                Keterangan :
            </td>
        </tr>
    </table>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_baku_mutu')

    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left">Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.</td>
        </tr>
    </table>

    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_signature')
</body>

</html>
