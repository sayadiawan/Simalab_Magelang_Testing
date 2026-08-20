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

    $no = 1;
@endphp
@foreach ($table as $mytable)
    @php
        // Tampilkan semua parameter; hasil kosong tetap muncul (kolom hasil kosong)
        $filteredResults = collect($mytable['result'])->values();
        $resultCount = count($filteredResults);
        $sampleRow = $mytable['sample_type'] ?? null;
    @endphp

    @if ($resultCount > 0 && $sampleRow)
        @php $sampleNo = $no++; @endphp
        <table class="result" width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed; margin-bottom: 12px">
            <tr style="font-size: 7.5pt">
                <td style="text-align: center; width: 4%">No</td>
                <td style="text-align: center; width: 11%">Kode Sampel</td>
                <td style="text-align: center; width: 14%">Titik Sampel</td>
                <td style="text-align: center; width: 11%">Jenis Sampel</td>
                <td style="text-align: center; width: 20%">Parameter Pemeriksaan</td>
                <td style="text-align: center; width: 9%">Satuan</td>
                <td style="text-align: center; width: 13%">Batas Maksimal</td>
                <td style="text-align: center; width: 18%">Hasil Pemeriksaan</td>
            </tr>
        @foreach ($filteredResults as $index => $result)
            <tr style="page-break-inside: avoid">
                <td style="text-align: center; vertical-align: middle">{{ $sampleNo }}</td>
                <td style="text-align: center; vertical-align: middle; word-wrap: break-word">
                    {!! $sampleRow->codesample_samples !!}
                </td>
                <td style="text-align: center; vertical-align: middle; word-wrap: break-word">
                    {{ $sampleRow->titikSampelDisplay('') }}
                </td>
                <td style="text-align: center; vertical-align: middle; word-wrap: break-word">
                    {{ $sampleRow->jenisSampelMakananDisplay('', $jenisMakananNameMap) }}
                </td>
                <td style="text-align: center; vertical-align: middle; word-wrap: break-word">
                    {!! data_get($result, 'name_report', '-') !!}
                </td>
                <td style="text-align: center; vertical-align: middle">
                    {!! data_get($result, 'satuan_bakumutu', '-') ?: '-' !!}
                </td>
                <td style="text-align: center; vertical-align: middle">
                    @php
                        $nilaiBakuMutu = data_get($result, 'nilai_baku_mutu');
                        $equalBakuMutu = data_get($result, 'equal');
                        $maxBakuMutu = data_get($result, 'max');
                        $displayBakuMutu = $nilaiBakuMutu ?: ($equalBakuMutu ?: ($maxBakuMutu ?: '-'));
                    @endphp
                    {!! $displayBakuMutu !!}
                </td>
                <td style="text-align: center; vertical-align: middle">
                    @php
                        $hasilVal  = data_get($result, 'hasil', '');
                        $hasilVal  = ($hasilVal === '-' || $hasilVal === null) ? '' : $hasilVal;
                        if ($hasilVal === '') {
                            $hasilDisplay = '';
                        } else {
                            $minVal    = data_get($result, 'min');
                            $maxVal    = data_get($result, 'max');
                            $equalVal  = data_get($result, 'equal');
                            $nilaiBm   = data_get($result, 'nilai_baku_mutu');
                            $isOver = kesmas_hasil_melewati_baku_mutu_print($hasilVal, $minVal, $maxVal, $equalVal, $nilaiBm);
                            $hasilDisplay = kesmas_format_hasil_print_with_baku_mutu_marker($hasilVal, $isOver);
                        }
                    @endphp
                    {!! $hasilDisplay !!}
                </td>
            </tr>
        @endforeach
        </table>
    @endif
@endforeach
