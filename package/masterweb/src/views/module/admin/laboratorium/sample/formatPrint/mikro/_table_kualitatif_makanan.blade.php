@php
    $jenisMakananNameMap = \Smt\Masterweb\Models\JenisMakanan::query()
        ->whereIn(
            'id_jenis_makanan',
            collect($table ?? [])
                ->map(function ($row) {
                    return data_get($row, 'sample_type.jenis_makanan_id');
                })
                ->filter(function ($id) {
                    return $id !== null && $id !== '';
                })
                ->unique()
                ->values()
                ->all()
        )
        ->pluck('name_jenis_makanan', 'id_jenis_makanan')
        ->all();
@endphp
<table class="result" width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: center; width: 4%">No</td>
            <td style="text-align: center; width: 12%">Kode Sampel</td>
            <td style="text-align: center; width: 16%">Titik Sampel</td>
            <td style="text-align: center; width: 12%">Jenis Sampel</td>
            <td style="text-align: center; width: 22%">Parameter Pemeriksaan</td>
            <td style="text-align: center; width: 18%">Hasil Pemeriksaan</td>
            <td style="text-align: center; width: 16%">Keterangan</td>
        </tr>

        @php $no = 1; @endphp
        @foreach ($table as $mytable)
            @php
                // Tampilkan semua parameter; hasil kosong tetap muncul (kolom hasil kosong)
                $filteredResults = collect($mytable['result'])->values();
                $resultCount = count($filteredResults);
                $sampleRow = $mytable['sample_type'] ?? null;
            @endphp

            @if ($resultCount > 0 && $sampleRow)
                @foreach ($filteredResults as $index => $result)
                    <tr>
                        @if ($index == 0)
                            <td style="text-align: center" rowspan="{{ $resultCount }}">{{ $no++ }}</td>
                            <td style="text-align: center" rowspan="{{ $resultCount }}">
                                {!! $sampleRow->codesample_samples !!}
                            </td>
                            <td style="text-align: center" rowspan="{{ $resultCount }}">
                                {{ $sampleRow->titikSampelDisplay('') }}
                            </td>
                            <td style="text-align: center" rowspan="{{ $resultCount }}">
                                {{ $sampleRow->jenisSampelMakananDisplay('', $jenisMakananNameMap) }}
                            </td>
                        @endif
                        <td style="text-align: center">
                            {!! data_get($result, 'name_report', '-') !!}
                        </td>

                        <td style="text-align: center; color: {{ data_get($result, 'hasil') == 'Positif' ? 'red' : 'black' }}">
                            @php
                                $hasilVal = data_get($result, 'hasil', '');
                                $hasilVal = ($hasilVal === '-' || $hasilVal === null) ? '' : $hasilVal;
                            @endphp
                            {!! $hasilVal !!}
                        </td>
                        <td style="text-align: center">
                            -
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    </table>
