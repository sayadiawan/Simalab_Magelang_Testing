<table>
  <tr>
    <td colspan="19" style="text-align: center; font-size: 16px; font-weight: bold; padding: 10px;">
      MONITORING SAMPLING DAN PENERIMA
    </td>
  </tr>
  <tr>
    <td colspan="19" style="text-align: center; font-size: 14px; padding: 5px;">
      Bulan {{ \Smt\Masterweb\Helpers\Smt::fbulan(sprintf('%02d', $month)) }} Tahun {{ $year }}
    </td>
  </tr>
  <tr>
    <td colspan="19" style="height: 10px;"></td>
  </tr>
  
  {{-- Table Header --}}
  <tr>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Tanggal</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No. RM</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">No. Spesimen</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Nama Pasien</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jenis Pemeriksaan</th>
    <th rowspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jenis Sampel</th>
    <th rowspan="2" colspan="3" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Sampling</th>
    <th colspan="11" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Penerimaan Sampel</th>
  </tr>
  <tr>
    <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Darah</th>
    <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Serum</th>
    <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Urine</th>
    <th colspan="2" style="text-align: center; border: 1px solid #000; background-color: #e9ecef; font-weight: bold;">Feses</th>
    <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Petugas</th>
    <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jam</th>
    <th rowspan="2" style="text-align: center; vertical-align: middle; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Keterangan</th>
  </tr>
  <tr>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Berhasil/Gagal</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Paraf Petugas</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Jam</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Volume</th>
    <th style="text-align: center; border: 1px solid #000; background-color: #f0f0f0; font-weight: bold;">Kualitas Sampel</th>
  </tr>
  
  {{-- Table Body --}}
  @if(isset($data) && count($data) > 0)
    @foreach ($data as $row)
      <tr>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['no'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['tanggal'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['no_rm'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['no_spesimen'] }}</td>
        <td style="border: 1px solid #000;">{{ $row['nama_pasien'] }}</td>
        <td style="border: 1px solid #000;">{{ $row['jenis_pemeriksaan'] }}</td>
        <td style="border: 1px solid #000;">{{ $row['jenis_sampel'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['status_sampling'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['petugas_sampling'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['jam_sampling'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['darah_volume'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['darah_kualitas'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['serum_volume'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['serum_kualitas'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['urine_volume'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['urine_kualitas'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['feses_volume'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['feses_kualitas'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['petugas_penerimaan'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['jam_penerimaan'] }}</td>
        <td style="text-align: center; border: 1px solid #000;">{{ $row['keterangan'] }}</td>
      </tr>
    @endforeach
  @else
    <tr>
      <td colspan="19" style="text-align: center; border: 1px solid #000;">Tidak ada data untuk bulan dan tahun yang dipilih</td>
    </tr>
  @endif
</table>

