<table class="result" width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; width: 5%">No</td>
            <td style="text-align: center; width: 14%">Nomer Sampel</td>
            <td style="text-align: center; width: 16%">Jenis Sampel</td>
            <td style="text-align: center; width: 20%">Parameter Pemeriksaan</td>
            <td style="text-align: center; width: 16%">Kadar maks. yang Diperbolehkan</td>
            <td style="text-align: center; width: 14%">Hasil Pemeriksaan</td>
            <td style="text-align: center; width: 15%">Keterangan</td>
        </tr>

        @php $sampleRowNo = 1; @endphp
        @foreach ($laboratoriummethodsArray as $laboratoriummethods)
            @php
                // Tampilkan semua parameter; hasil kosong tetap muncul (kolom hasil kosong)
                // Tabel kualitatif: tanpa kolom Satuan
                $filteredMethods = collect($laboratoriummethods['methods'])
                    ->sort(function ($a, $b) {
                        return kesmas_lhu_sort_key($a) <=> kesmas_lhu_sort_key($b);
                    })
                    ->values();
                $methodCount = count($filteredMethods);
            @endphp

            @if ($methodCount > 0)
                @foreach ($filteredMethods as $index => $method)
                    <tr>
                        @if ($index == 0)
                            <td style="text-align: center" rowspan="{{ $methodCount }}">{{ $sampleRowNo++ }}</td>
                            <td style="text-align: center" rowspan="{{ $methodCount }}">
                                {!! $laboratoriummethods['sample_info']['codesample_samples'] !!}
                            </td>
                            <td style="text-align: center" rowspan="{{ $methodCount }}">
                                @php
                                    $titik = preg_replace('/<\/?p[^>]*>/', '', $laboratoriummethods['sample_info']['titik_pengambilan'] ?? '');
                                    $makanan = preg_replace('/<\/?p[^>]*>/', '', $laboratoriummethods['sample_info']['nama_jenis_makanan'] ?? '');
                                @endphp
                                {!! $titik !!}
                            </td>
                        @endif
                        <td style="text-align: center">
                            {!! data_get($method, 'params_method', '-') !!}
                        </td>
                        <td style="text-align: center">
                            {!! (($bm = data_get($method, 'nilai_baku_mutu')) !== null && $bm !== '') ? $bm : '-' !!}
                        </td>
                        <td style="text-align: center">
                            @php
                                $hasilVal  = data_get($method, 'hasil', '');
                                $hasilVal  = ($hasilVal === '-' || $hasilVal === null) ? '' : $hasilVal;
                                if ($hasilVal === '') {
                                    $hasilDisplay = '';
                                } else {
                                    $minVal    = data_get($method, 'min');
                                    $maxVal    = data_get($method, 'max');
                                    $equalVal  = data_get($method, 'equal');
                                    $nilaiBm   = data_get($method, 'nilai_baku_mutu');
                                    $isOver = kesmas_hasil_melewati_baku_mutu_print($hasilVal, $minVal, $maxVal, $equalVal, $nilaiBm);
                                    $hasilDisplay = kesmas_format_hasil_print_with_baku_mutu_marker($hasilVal, $isOver);
                                }
                            @endphp
                            {!! $hasilDisplay !!}
                        </td>
                        <td style="text-align: center">
                            @php
                                $ket = trim(strip_tags((string) data_get($method, 'keterangan', '')));
                            @endphp
                            {{ $ket !== '' ? $ket : '-' }}
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    </table>
