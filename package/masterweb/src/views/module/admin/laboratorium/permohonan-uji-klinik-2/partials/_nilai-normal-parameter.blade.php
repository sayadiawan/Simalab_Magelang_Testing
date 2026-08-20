@php
    $nilaiNormal = $item_satuan_klinik['keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik'] ?? null;
    if ($nilaiNormal === null || $nilaiNormal === '' || $nilaiNormal === '-') {
        $nilaiNormal = $item_satuan_klinik['nilai_baku_mutu'] ?? null;
    }
    $hasNilai = !empty($nilaiNormal) && $nilaiNormal !== '-';
    $hasMultiple = isset($item_satuan_klinik['has_multiple_baku_mutu']) && $item_satuan_klinik['has_multiple_baku_mutu'];
@endphp

@if ($hasNilai)
    {{-- Snapshot keterangan_dilaporan per permohonan (prioritas tertinggi) --}}
    {!! rubahNilaikeForm($nilaiNormal) !!}
@elseif ($hasMultiple)
    {{-- Fallback: generate tier list dari multiple_baku_mutu jika snapshot kosong --}}
    @php
        $nilaiRujukanLines = getNilaiRujukanLinesForParameter($item_satuan_klinik);
    @endphp
    @if (count($nilaiRujukanLines) > 0)
        <div class="nilai-normal-content">
            @foreach ($nilaiRujukanLines as $tierLine)
                {{ $tierLine }}@if (!$loop->last)<br>@endif
            @endforeach
        </div>
    @else
        <span class="badge badge-info mt-1"
            title="Parameter ini memiliki multiple baku mutu">
            <i class="fa fa-info-circle"></i> Multiple Baku Mutu
        </span>
    @endif
@else
    -
@endif
