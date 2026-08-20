<table class="result" width="98%" border="3" cellspacing="0" cellpadding="0">
    <tr>
        <td class="td-result" valign="middle"
            style="text-align: center; vertical-align: middle; width: 4%; border-bottom: 2px solid black; border-right: 2px solid black;"
            rowspan="3">No</td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 15%; border-bottom: 2px solid black;" rowspan="3">
            Kode Sampel</td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 35%; border-bottom: 2px solid black;" rowspan="3">
            Titik Sampel</td>
        <td class="td-result" valign="middle"
            style="text-align: center; vertical-align: middle; width: 12%; border-bottom: 2px solid black; padding-left: 3px !important; padding-right: 3px !important;"
            rowspan="3">Jenis Sampel</td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 35%; border-bottom: 3px solid black;"
            colspan="{{ count($data['parameter']) * 2 }}"><b>Parameter pemeriksaan</b></td>
        <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 4%; border-bottom: 2px solid black;" rowspan="3">
            Ket.</td>
    </tr>



    <tr>
        @foreach ($data['parameter'] as $param)
            <td class="td-result" valign="middle" style="text-align: center; vertical-align: middle; width: 0%" colspan="2">
                <b>{!! $param !!}</b><br>
                <span style="font-size: 10pt;">CFU/100 ml</span>
            </td>
        @endforeach
    </tr>

    <tr align="center">
        @foreach (range(1, count($data['parameter'])) as $num)
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
    @foreach ($data['results'] as $result)
        <tr style="border-bottom: 1px solid white" valign="top">
            <td class="table_tr" valign="top"
                style="vertical-align: top; text-align: center; border-right: 2px solid black;">
                {{ $loop->iteration }}
            </td>
            <td valign="top" style="vertical-align: top;">
                {!! $result->codesample_samples !!}
            </td>


            <td class="table_tr" valign="top" style="vertical-align: top;">

                @if (isset($result->titik_pengambilan) && $result->titik_pengambilan != '')
                    @php
                        // Konversi konten TinyMCE ke teks pendek yang rapi untuk PDF
                        $location = $result->titik_pengambilan;
                        $location = html_entity_decode($location, ENT_QUOTES, 'UTF-8');
                        // Buang seluruh tag HTML dan extra whitespace
                        $location = strip_tags($location);
                        $location = preg_replace('/\s+/', ' ', $location);
                        $location = trim($location);
                        // Potong agar tidak terlalu panjang di tabel
                        $maxLen = 120;
                        if (mb_strlen($location) > $maxLen) {
                            $location = mb_substr($location, 0, $maxLen) . '...';
                        }
                    @endphp
                    {!! $location !!}
                @else
                    @if ($result->is_pudam == 1)
                        @php

                            $location = str_replace('"""', '', $result->address_location_pdam);
                            $location = str_replace("\n", '<br>', $location);
                            $location = str_replace('<p>', '', $location);
                            $location = str_replace('</p>', '', $location);

                            if ($location == '') {
                                $location = $result->name_pelanggan;
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
                            // Fallback jika tidak ada location_samples: gunakan name_pelanggan
                            $location = $result->location_samples ?: $result->name_pelanggan ?? '';
                            $location = html_entity_decode($location, ENT_QUOTES, 'UTF-8');
                            $location = strip_tags($location);
                            $location = preg_replace('/\s+/', ' ', $location);
                            $location = trim($location);
                            $maxLen = 120;
                            if (mb_strlen($location) > $maxLen) {
                                $location = mb_substr($location, 0, $maxLen) . '...';
                            }
                        @endphp

                        {!! $location !!}
                    @endif
                @endif
            </td>


            <td valign="top" style="vertical-align: top;">
                {{ $result->sampletype->name_sample_type ?? '-' }}
            </td>

            @foreach ($data['parameter'] as $key => $param)
                @php
                    $foundResult = false;
                    $hasil = '';
                    $nilaiBakuMutu = '';

                    foreach ($result->sampleresult as $sampleresult) {
                        if ($key == $sampleresult->method_id) {
                            $foundResult = true;
                            $hasil = cek_hasil_color_mikro(
                                isset($sampleresult->hasil) ? $sampleresult->hasil : '',
                                $sampleresult->method[0]->bakumutu->min ?? null,
                                $sampleresult->method[0]->bakumutu->max ?? null,
                                $sampleresult->method[0]->bakumutu->equal ?? null,
                                'result_output_method_' . $sampleresult->method_id ?? null,
                                $sampleresult->offset_baku_mutu ?? null,
                                $sampleresult->number_format ?? 'en',
                                $sampleresult->method[0]->bakumutu->nilai_baku_mutu ?? null
                            );
                            $nilaiBakuMutu = $sampleresult->method[0]->bakumutu->nilai_baku_mutu ?? '';
                            break;
                        }
                    }

                    // Jika tidak ditemukan hasil untuk parameter ini pada sampel,
                    // isi default: Hasil = 0, Maksimal = baku mutu dari data gabungan (jika ada)
                    if (!$foundResult && isset($data['bakumutu'][$key])) {
                        $bm = $data['bakumutu'][$key];
                        $nilaiBakuMutu = $bm->nilai_baku_mutu ?? '';
                        $hasil = cek_hasil_color_mikro(
                            0,
                            $bm->min ?? null,
                            $bm->max ?? null,
                            $bm->equal ?? null,
                            'result_output_method_' . $key,
                            'default',
                            $bm->number_format ?? 'en',
                            $bm->nilai_baku_mutu ?? null
                        );
                    }
                @endphp
                <td class="table_tr-param" valign="top" style="text-align: center; vertical-align: top;" title="$result->hasil">
                    {!! rubahNilaikeFormForPrint($hasil) !!}
                </td>
                <td class="table_tr-param" valign="top" style="text-align: center; vertical-align: top;" title="$result->max">
                    {!! rubahNilaikeFormForPrint($nilaiBakuMutu) !!}
                </td>
            @endforeach
            <td class="table_tr" valign="top"
                style="vertical-align: top; text-align: center; padding-left: 0 !important; padding-right: 0 !important;"
                width="75px" title="(static)">
                @foreach ($result->sampleresult as $sampleresult)
                    @if (!empty($sampleresult->keterangan))
                        <span style="color: black; display:block;">{{ strip_tags($sampleresult->keterangan) }}</span>
                    @endif
                @endforeach
            </td>
        </tr>
    @endforeach
</table>
