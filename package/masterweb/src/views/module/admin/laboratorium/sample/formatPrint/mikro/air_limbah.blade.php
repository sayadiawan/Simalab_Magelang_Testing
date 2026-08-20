<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MIKRO-{!! $no_LHU !!}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_style')
</head>

<body style="margin: 0 10px; padding: 0">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_kop')
    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_data')
    <br>

    <table class="result" width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; width: 0;" rowspan="4">No</td>
            <td style="text-align: center; width: 100px" rowspan="4">Kode Sampel</td>
            <td style="text-align: center; width: auto" rowspan="4">Titik Sampel</td>
            <td style="text-align: center; width: 100px" rowspan="4">Jenis Sampel</td>
            <td style="text-align: center; width: 0" colspan="{{ count($method_all) * 2 }}"> Parameter Pemeriksaan</td>
            <td style="text-align: center; width: 50px" rowspan="4">Ket</td>
        </tr>



        <tr>
            @foreach ($method_all as $method)
                <td style="text-align: center; width: 0%" colspan="2">{{ $method->name_report }}</td>
            @endforeach
        </tr>
        <tr>
            @foreach ($method_all as $method)
                @php
                    $unitLabel = $method->shortname_unit
                        ?? $method->unit_shortname_unit
                        ?? $method->name_unit
                        ?? $method->unit_name_unit
                        ?? '';
                @endphp
                <td style="text-align: center; width: 0%" colspan="2">{!! rubahNilaikeFormForPrint($unitLabel) !!}</td>
            @endforeach
        </tr>

        <tr align="center">
            @foreach (range(1, count($method_all)) as $num)
                <td style="font-size: 9pt"> Hasil</td>
                <td style="font-size: 9pt"> Maksimal</td>
            @endforeach
        </tr>


        @foreach ($table as $mytable)
            @php
                $loop_lab_num = data_get($lab_nums, data_get($mytable, 'sample_type.id_samples'));
                $titikSampel = $mytable['sample_type']->titik_pengambilan ?? '';
                $titikSampel = str_replace(['"""', '<p>', '</p>', '<P>', '</P>'], ['', '', '', '', ''], $titikSampel);
                $titikSampel = preg_replace('/^(<br\s*\/?>|\s)+/i', '', $titikSampel);
            @endphp
            <tr valign="top">
                <td style="text-align: center; vertical-align: top;">
                    {{ $loop->iteration + (isset($loop_lab_num->page_break) ? ($loop_lab_num->page_break - 1) * $lab_num_per_page : 0) }}
                </td>
                <td class="wysiwyg-data" style="text-align: start; vertical-align: top;">
                    {!! $mytable['sample_type']->codesample_samples !!}
                </td>
                <td valign="top" style="text-align: start; vertical-align: top !important; padding-top: 4px;">
                    {!! $titikSampel !!}
                </td>
                <td style="text-align: center; vertical-align: top;" width="20px" title="$mytable['sample_type']->datesampling_samples">
                    {{ $mytable['sample_type']->name_sample_type }}
                </td>


                @foreach ($method_all as $method)
                    @foreach ($mytable['result'] as $result)
                        @if ($method->id_method == $result['method_id'])
                            @php
                                $hasil = cek_hasil_color_mikro(
                                    isset($result['hasil'])
                                        ? $result['hasil']
                                        : (isset($result['equal'])
                                            ? $result['equal']
                                            : ''),
                                    $result['min'] ?? null,
                                    $result['max'] ?? null,
                                    $result['equal'] ?? null,
                                    'result_output_method_' . $result['method_id'] ?? null,
                                    $result['offset_baku_mutu'] ?? null,
                                    $result['number_format'] ?? 'en',
                                    $result['nilai_baku_mutu'] ?? null
                                );

                            @endphp

                            <td style="text-align: center; vertical-align: top;" title="$result->hasil">{!! rubahNilaikeFormForPrint($hasil) !!}</td>
                            <td style="text-align: center; vertical-align: top;" title="$result->max">{!! rubahNilaikeFormForPrint(data_get($result, 'nilai_baku_mutu')) !!}</td>
                        @endif
                    @endforeach
                @endforeach

                <td title="(static)" style="vertical-align: top;">
                    @foreach ($mytable['result'] as $result)
                        <span style="color: black;">{{ strip_tags($result['keterangan'] ?? '') }}</span>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </table>

    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_signature')
</body>

</html>
