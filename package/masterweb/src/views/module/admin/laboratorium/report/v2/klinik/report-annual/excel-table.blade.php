{{-- Title --}}
<tr>
  <td colspan="{{ $daysInMonth + 4 }}" style="text-align: center; font-weight: bold; font-size: 14px; padding: 10px;">
    {{ $reportTitle ?? 'Catatan Harian Pemeriksaan Unit Klinik' }}
  </td>
</tr>
<tr>
  <td colspan="{{ $daysInMonth + 4 }}" style="text-align: center; font-weight: bold; padding: 5px;">
    Bulan : {{ fbulan(sprintf('%02d', $month)) }} {{ $year }}
  </td>
</tr>
<tr><td colspan="{{ $daysInMonth + 4 }}"></td></tr>

{{-- Table Header --}}
<tr>
  <td rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">No</td>
  <td rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Uraian</td>
  <td colspan="{{ $daysInMonth }}" style="text-align: center; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Tanggal</td>
  <td rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Jumlah</td>
  <td rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Ket</td>
</tr>
<tr>
  @for ($i = 1; $i <= $daysInMonth; $i++)
    <td style="text-align: center; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">{{ $i }}</td>
  @endfor
</tr>

@php
  $rowNum = 1;
@endphp

{{-- Row 1: Jumlah pasien --}}
@php
  $pasienTotal = array_sum($monthData['jumlah_pasien'] ?? []);
@endphp
<tr>
  <td style="text-align: center; border: 1px solid #000;">{{ $rowNum }}</td>
  <td style="border: 1px solid #000; font-weight: bold;">Jumlah pasien</td>
  @for ($day = 1; $day <= $daysInMonth; $day++)
    <td style="text-align: center; border: 1px solid #000;">{{ ($monthData['jumlah_pasien'][$day] ?? 0) > 0 ? $monthData['jumlah_pasien'][$day] : '' }}</td>
  @endfor
  <td style="text-align: center; border: 1px solid #000; font-weight: bold;">{{ $pasienTotal }}</td>
  <td style="border: 1px solid #000;"></td>
</tr>
@php $rowNum++; @endphp

{{-- Kimia klinik section: header = pasien unik per hari --}}
@php
  $kimiaDaily = $monthData['kimia_klinik'] ?? array_fill(1, $daysInMonth, 0);
  $kimiaTotal = array_sum($kimiaDaily);
@endphp
<tr>
  <td style="text-align: center; vertical-align: top; border: 1px solid #000;" rowspan="{{ count($kimiaParams) + 1 }}">{{ $rowNum }}</td>
  <td style="border: 1px solid #000; font-weight: bold;">Kimia klinik</td>
  @for ($day = 1; $day <= $daysInMonth; $day++)
    <td style="text-align: center; border: 1px solid #000;">{{ ($kimiaDaily[$day] ?? 0) > 0 ? $kimiaDaily[$day] : '' }}</td>
  @endfor
  <td style="text-align: center; border: 1px solid #000; font-weight: bold;">{{ $kimiaTotal > 0 ? $kimiaTotal : '' }}</td>
  <td style="border: 1px solid #000;"></td>
</tr>
@foreach ($kimiaParams as $param)
  @php
    $paramData = $monthData['parameters'][$param] ?? array_fill(1, $daysInMonth, 0);
    $paramTotal = array_sum($paramData);
  @endphp
  <tr>
    <td style="border: 1px solid #000;">{{ $param }}</td>
    @for ($day = 1; $day <= $daysInMonth; $day++)
      <td style="text-align: center; border: 1px solid #000;">{{ ($paramData[$day] ?? 0) > 0 ? $paramData[$day] : '' }}</td>
    @endfor
    <td style="text-align: center; border: 1px solid #000;">{{ $paramTotal > 0 ? $paramTotal : '' }}</td>
    <td style="border: 1px solid #000;"></td>
  </tr>
@endforeach
@php $rowNum++; @endphp

{{-- Darah rutin & Hemoglobin dipisah (ada di otherParams) --}}
@foreach ($otherParams as $param)
  @php
    $paramData = $monthData['parameters'][$param] ?? array_fill(1, $daysInMonth, 0);
    $paramTotal = array_sum($paramData);
  @endphp
  <tr>
    <td style="text-align: center; border: 1px solid #000;">{{ $rowNum }}</td>
    <td style="border: 1px solid #000;">{{ $param }}</td>
    @for ($day = 1; $day <= $daysInMonth; $day++)
      <td style="text-align: center; border: 1px solid #000;">{{ ($paramData[$day] ?? 0) > 0 ? $paramData[$day] : '' }}</td>
    @endfor
    <td style="text-align: center; border: 1px solid #000;">{{ $paramTotal > 0 ? $paramTotal : '' }}</td>
    <td style="border: 1px solid #000;"></td>
  </tr>
  @php $rowNum++; @endphp
@endforeach

{{-- Empty row before signature --}}
<tr><td colspan="{{ $daysInMonth + 4 }}" style="height: 20px;"></td></tr>

{{-- Signature Section - Left Side --}}
<tr>
  <td colspan="{{ floor(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top; padding-top: 10px;">
    Mengetahui
  </td>
  <td colspan="{{ ceil(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top; padding-top: 10px;">
    —, {{ \Carbon\Carbon::create($year, $month, \Carbon\Carbon::create($year, $month, 1)->daysInMonth)->isoFormat('D MMMM Y') }}
  </td>
</tr>
<tr>
  <td colspan="{{ floor(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top;">
    Kepala Laboratorium Kesehatan
  </td>
  <td colspan="{{ ceil(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top;">
    Petugas
  </td>
</tr>
<tr>
  <td colspan="{{ floor(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top;">
    SIMLAB Testing
  </td>
  <td colspan="{{ ceil(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top;">
    &nbsp;
  </td>
</tr>
{{-- Empty rows for signature space --}}
@for($i = 1; $i <= 5; $i++)
<tr>
  <td colspan="{{ floor(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top; height: 20px;">
    &nbsp;
  </td>
  <td colspan="{{ ceil(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top; height: 20px;">
    &nbsp;
  </td>
</tr>
@endfor
{{-- Signature names --}}
<tr>
  <td colspan="{{ floor(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top; padding-top: 10px;">
    ENDANG SUKAWATI, SKM, M.Kes
  </td>
  <td colspan="{{ ceil(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top; padding-top: 10px;">
    PRITA WIDYA PRATIWI, S.Tr.Kes
  </td>
</tr>
<tr>
  <td colspan="{{ floor(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top;">
    NIP. 196804111992032007
  </td>
  <td colspan="{{ ceil(($daysInMonth + 4) / 2) }}" style="text-align: center; vertical-align: top;">
    NIP. 198804012010012020
  </td>
</tr>

{{-- Empty row between tables (if not last) --}}
@if(!$isLast)
  <tr><td colspan="{{ $daysInMonth + 4 }}" style="height: 30px;"></td></tr>
@endif

