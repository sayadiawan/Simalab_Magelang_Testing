@php
  $columnGroups = $columnGroups ?? [];
  $columnTotal = $columnTotal ?? 0;
  $colspanTitle = 7 + max($columnTotal, 1);
@endphp
<table>
  <tr>
    <td colspan="{{ $colspanTitle }}" style="text-align: center; font-size: 16px; font-weight: bold; padding: 10px;">
      REGISTER HASIL KLINIS
    </td>
  </tr>
  <tr>
    <td colspan="{{ $colspanTitle }}" style="text-align: center; font-size: 14px; padding: 5px;">
      Bulan {{ \Smt\Masterweb\Helpers\Smt::fbulan(sprintf('%02d', $month)) }} Tahun {{ $year }}
    </td>
  </tr>
  <tr>
    <td colspan="{{ $colspanTitle }}" style="height: 10px;"></td>
  </tr>

  {{-- Table Header --}}
  <tr>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Tanggal</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No Spesimen</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No RM</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Nama Pasien</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Umur</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Alamat</th>
    <th colspan="{{ max($columnTotal, 1) }}" style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Hasil Pemeriksaan</th>
  </tr>
  <tr>
    @forelse ($columnGroups as $group)
      <th colspan="{{ count($group['columns']) }}" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">{{ $group['label'] }}</th>
    @empty
      <th style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">-</th>
    @endforelse
  </tr>
  <tr>
    @forelse ($columnGroups as $group)
      @foreach ($group['columns'] as $col)
        <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">{{ $col['label'] }}</th>
      @endforeach
    @empty
      <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">-</th>
    @endforelse
  </tr>

  {{-- Data Rows --}}
  @if(isset($data) && count($data) > 0)
    @foreach ($data as $row)
      <tr>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['no'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['tanggal'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['no_spesimen'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['no_rm'] }}</td>
        <td style="border: 1px solid #000;">{{ $row['nama_pasien'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['umur'] }}</td>
        <td style="border: 1px solid #000;">{{ $row['alamat'] }}</td>
        @foreach ($columnGroups as $group)
          @foreach ($group['columns'] as $col)
            <td style="text-align: center; border: 1px solid #000;">{{ $row['results'][$group['key']][$col['kode']] ?? '' }}</td>
          @endforeach
        @endforeach
      </tr>
    @endforeach
  @else
    <tr>
      <td colspan="{{ $colspanTitle }}" style="text-align: center; border: 1px solid #000;">Tidak ada data untuk bulan dan tahun yang dipilih</td>
    </tr>
  @endif
</table>
