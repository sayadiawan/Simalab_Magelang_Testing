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

    if (!isset($colWidths) || !is_array($colWidths) || empty($colWidths)) {
        $sampleForWidth = $sample
            ?? (isset($table[0]['sample_type']) ? $table[0]['sample_type'] : null);
        $labForWidth = $lab ?? ($laboratorium ?? null);
        $colWidths = \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::resolve(
            $sampleForWidth,
            request(),
            \Smt\Masterweb\Helpers\KesmasHasilColumnWidth::PROFILE_MIKRO_MAKANAN_8COL,
            $labForWidth
        );
    }
    $wNo = ($colWidths['no'] ?? 4) . '%';
    $wKode = ($colWidths['kode_sampel'] ?? 11) . '%';
    $wTitik = ($colWidths['titik_sampel'] ?? 14) . '%';
    $wJenis = ($colWidths['jenis_sampel'] ?? 11) . '%';
    $wParam = ($colWidths['parameter'] ?? 20) . '%';
    $wSatuan = ($colWidths['satuan'] ?? 9) . '%';
    $wBatas = ($colWidths['batas_maksimal'] ?? 13) . '%';
    $wHasil = ($colWidths['hasil'] ?? 18) . '%';
@endphp
<table class="result" width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
    <tr style="font-size: 7.5pt">
        <td style="text-align: center; width: {{ $wNo }}">No</td>
        <td style="text-align: center; width: {{ $wKode }}">Kode Sampel</td>
        <td style="text-align: center; width: {{ $wTitik }}">Titik Sampel</td>
        <td style="text-align: center; width: {{ $wJenis }}">Jenis Sampel</td>
        <td style="text-align: center; width: {{ $wParam }}">Parameter Pemeriksaan</td>
        <td style="text-align: center; width: {{ $wSatuan }}">Satuan</td>
        <td style="text-align: center; width: {{ $wBatas }}">Batas Maksimal</td>
        <td style="text-align: center; width: {{ $wHasil }}">Hasil Pemeriksaan</td>
    </tr>
@foreach ($table as $mytable)
    @php
        // Tampilkan semua parameter; hasil kosong tetap muncul (kolom hasil kosong)
        $filteredResults = collect($mytable['result'])->values();
        $resultCount = count($filteredResults);
        $sampleRow = $mytable['sample_type'] ?? null;
        // Titik Sampel LHU makanan = field Jenis Sampel (nama_jenis_makanan)
        $titikDariJenisSampel = $sampleRow
            ? $sampleRow->namaJenisMakananPlain('')
            : '';
        if ($titikDariJenisSampel === '' && $sampleRow) {
            $titikDariJenisSampel = $sampleRow->titikSampelDisplay('');
        }
        if ($titikDariJenisSampel === '') {
            $titikDariJenisSampel = '-';
        }
    @endphp

    @if ($resultCount > 0 && $sampleRow)
        @foreach ($filteredResults as $index => $result)
            <tr style="page-break-inside: avoid">
                @if ($index == 0)
                    <td style="text-align: center; vertical-align: middle" rowspan="{{ $resultCount }}">{{ $no++ }}</td>
                    <td style="text-align: center; vertical-align: middle; word-wrap: break-word" rowspan="{{ $resultCount }}">
                        {!! $sampleRow->codesample_samples !!}
                    </td>
                    <td style="text-align: center; vertical-align: middle; word-wrap: break-word" rowspan="{{ $resultCount }}">
                        {{ $titikDariJenisSampel }}
                    </td>
                    <td style="text-align: center; vertical-align: middle; word-wrap: break-word" rowspan="{{ $resultCount }}">
                        {{ $sampleRow->jenisSampelMakananDisplay('-', $jenisMakananNameMap) }}
                    </td>
                @endif
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
                        // Jangan pakai ?: — nilai "0" dianggap falsy di PHP dan hilang di laporan
                        $displayBakuMutu = null;
                        foreach ([$nilaiBakuMutu, $equalBakuMutu, $maxBakuMutu] as $candidate) {
                            if ($candidate !== null && $candidate !== '') {
                                $displayBakuMutu = $candidate;
                                break;
                            }
                        }
                        if ($displayBakuMutu === null) {
                            $displayBakuMutu = '-';
                        }
                    @endphp
                    {!! function_exists('rubahNilaikeFormForPrint') ? rubahNilaikeFormForPrint($displayBakuMutu) : $displayBakuMutu !!}
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
    @endif
@endforeach
</table>
