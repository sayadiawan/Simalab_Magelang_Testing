<table class="result" width="98%" border="3" cellspacing="0" cellpadding="0">
    <tr>
        <td class="td-result" valign="middle"
            style="text-align: center; vertical-align: middle; width: 4%; border-bottom: 2px solid black; border-right: 2px solid black;"
            rowspan="3">No</td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 20%; border-bottom: 2px solid black;" rowspan="3">
            Kode Sampel</td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 20%; border-bottom: 2px solid black;" rowspan="3">
            Titik Sampel</td>
        <td class="td-result" valign="middle"
            style="text-align: center; vertical-align: middle; width: 20%; border-bottom: 2px solid black; padding-left: 3px !important; padding-right: 3px !important;"
            rowspan="3">Jenis Sampel</td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 66%; border-bottom: 3px solid black;"
            colspan="{{ count($method_all) * 2 }}"><b>Parameter pemeriksaan</b></td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 6%; border-bottom: 2px solid black;" rowspan="3">
            Ket.</td>
    </tr>

    <tr>
        @foreach ($method_all as $method)
            <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 0%" colspan="2">
                <b>{!! $method->name_report !!}</b>
            </td>
        @endforeach
    </tr>

    <tr align="center">
        @foreach (range(1, count($method_all)) as $num)
            <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; border-bottom: 2px solid black; font-size: 12pt;">Hasil</td>
            <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; border-bottom: 2px solid black; font-size: 12pt;">Maksimal</td>
        @endforeach
    </tr>

    <style>
        .table_tr {
            vertical-align: top !important;
            padding-top: 4px !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
            font-size: 12pt !important;
        }

        .table_tr-param {
            vertical-align: top !important;
            padding-top: 4px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            font-size: 12pt !important;
        }

        .td-result {
            font-size: 12pt !important;
            vertical-align: middle !important;
            text-align: center !important;
        }
    </style>


    @foreach ($table as $mytable)
        @php
            $loop_lab_num = data_get($lab_nums, data_get($mytable, 'sample_type.id_samples'));
        @endphp
        <tr style="border-bottom: 1px solid white" valign="top">
            <td class="table_tr" valign="top"
                style="vertical-align: top; text-align: center; border-right: 2px solid black;">
                {{ $loop->iteration + (isset($loop_lab_num->page_break) ? ($loop_lab_num->page_break - 1) * $lab_num_per_page : 0) }}
            </td>
            <td valign="top" style="vertical-align: top;">
                {!! $mytable['sample_type']->codesample_samples !!}
            </td>
            <td class="table_tr" valign="top" style="vertical-align: top;">
                @if (isset($mytable['sample_type']->titik_pengambilan) && $mytable['sample_type']->titik_pengambilan != '')
                    @php
                        $location = str_replace('"""', '', $mytable['sample_type']->titik_pengambilan);
                        $location = str_replace("\n", '<br>', $location);
                        $location = str_replace('<p>', '', $location);
                        $location = str_replace('</p>', '', $location);

                        if (str_contains($location, 'π')) {
                            $location = str_replace(
                                'π',
                                "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                $location,
                            );
                        }

                        if (str_contains($location, '&pi;')) {
                            $location = str_replace(
                                '&pi;',
                                "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                $location,
                            );
                        }
                    @endphp
                    {!! $location !!}
                @else
                    @if ($mytable['sample_type']->is_pudam == 1)
                        @php
                            $location = str_replace('"""', '', $mytable['sample_type']->address_location_pdam);
                            $location = str_replace("\n", '<br>', $location);
                            $location = str_replace('<p>', '', $location);
                            $location = str_replace('</p>', '', $location);

                            if ($location == '') {
                                $location = $mytable['sample_type']->name_pelanggan;
                            }

                            if (str_contains($location, 'π')) {
                                $location = str_replace(
                                    'π',
                                    "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                    $location,
                                );
                            }

                            if (str_contains($location, '&pi;')) {
                                $location = str_replace(
                                    '&pi;',
                                    "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                    $location,
                                );
                            }
                        @endphp
                        {!! $location !!}
                    @else
                        @php
                            $location = str_replace('"""', '', $mytable['sample_type']->titik_pengambilan);
                            $location = str_replace("\n", '<br>', $location);
                            $location = str_replace('<p>', '', $location);
                            $location = str_replace('</p>', '', $location);

                            if ($location == '') {
                                $location = $mytable['sample_type']->name_pelanggan;
                            }

                            if (str_contains($location, 'π')) {
                                $location = str_replace(
                                    'π',
                                    "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                    $location,
                                );
                            }

                            if (str_contains($location, '&pi;')) {
                                $location = str_replace(
                                    '&pi;',
                                    "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                    $location,
                                );
                            }
                        @endphp
                        {!! $location !!}
                    @endif
                @endif
            </td>
            <td class="table_tr" valign="top" style="vertical-align: top; text-align: center;" width="20px"
                title="$mytable['sample_type']->datesampling_samples">
                @php
                    $jenis_sample = [];
                    if (isset($mytable['sample_type']->jenis_sarana_names) && $mytable['sample_type']->jenis_sarana_names != null) {
                        $jenis_sample = $mytable['sample_type']->jenis_sarana_names;
                    } else {
                        $jenis_sample = 'Alat Masak';
                    }
                @endphp
                {{ $jenis_sample }}
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
                        <td class="table_tr-param" valign="top"
                            style="text-align: center; vertical-align: top;"
                            title="$result->hasil">
                            {!! rubahNilaikeFormForPrint($hasil) !!}
                        </td>
                        <td class="table_tr-param" valign="top" style="text-align: center; vertical-align: top;" title="$result->max">
                            {!! rubahNilaikeFormForPrint(data_get($result, 'nilai_baku_mutu')) !!}
                        </td>
                    @endif
                @endforeach
            @endforeach

            <td class="table_tr" valign="top"
                style="vertical-align: top; text-align: center; padding-left: 0 !important; padding-right: 0 !important;"
                width="75px" title="(static)">
                @foreach ($mytable['result'] as $result)
                    @if (!empty($result['keterangan']))
                        <span style="color: black; display:block;">{{ strip_tags($result['keterangan']) }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
    @endforeach

</table>
