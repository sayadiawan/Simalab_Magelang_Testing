<table>
  <thead>
    <tr>
      <th colspan="20" style="text-align:center;font-weight:bold;">REKAPAN BULAN {{ strtoupper($bulanNama) }}</th>
    </tr>
    <tr>
      <th rowspan="2">No</th>
      <th rowspan="2">Tanggal</th>
      <th colspan="3">Klinis</th>
      <th colspan="3">Haji</th>
      <th colspan="7">Mikrobiologi</th>
      <th colspan="5">Kimia</th>
    </tr>
    <tr>
      <th>Darah</th>
      <th>Urine</th>
      <th>Feses</th>
      <th>Darah</th>
      <th>Urine</th>
      <th>Feses</th>
      <th>Air Bersih</th>
      <th>Air Minum</th>
      <th>Air Limbah</th>
      <th>Kolam</th>
      <th>MM</th>
      <th>Usap</th>
      <th>Udara</th>
      <th>Air Bersih</th>
      <th>Air Minum</th>
      <th>Air Limbah</th>
      <th>Kolam</th>
      <th>MM</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($rows as $row)
      <tr>
        <td>{{ $row['no'] }}</td>
        <td>{{ $row['tanggal'] }}</td>
        <td>{{ $row['counts']['klinis_darah'] }}</td>
        <td>{{ $row['counts']['klinis_urine'] }}</td>
        <td>{{ $row['counts']['klinis_feses'] }}</td>
        <td>{{ $row['counts']['haji_darah'] }}</td>
        <td>{{ $row['counts']['haji_urine'] }}</td>
        <td>{{ $row['counts']['haji_feses'] }}</td>
        <td>{{ $row['counts']['mikro_air_bersih'] }}</td>
        <td>{{ $row['counts']['mikro_air_minum'] }}</td>
        <td>{{ $row['counts']['mikro_air_limbah'] }}</td>
        <td>{{ $row['counts']['mikro_kolam'] }}</td>
        <td>{{ $row['counts']['mikro_mm'] }}</td>
        <td>{{ $row['counts']['mikro_usap'] }}</td>
        <td>{{ $row['counts']['mikro_udara'] }}</td>
        <td>{{ $row['counts']['kimia_air_bersih'] }}</td>
        <td>{{ $row['counts']['kimia_air_minum'] }}</td>
        <td>{{ $row['counts']['kimia_air_limbah'] }}</td>
        <td>{{ $row['counts']['kimia_kolam'] }}</td>
        <td>{{ $row['counts']['kimia_mm'] }}</td>
      </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <td colspan="2">Total</td>
      <td>{{ $totals['klinis_darah'] }}</td>
      <td>{{ $totals['klinis_urine'] }}</td>
      <td>{{ $totals['klinis_feses'] }}</td>
      <td>{{ $totals['haji_darah'] }}</td>
      <td>{{ $totals['haji_urine'] }}</td>
      <td>{{ $totals['haji_feses'] }}</td>
      <td>{{ $totals['mikro_air_bersih'] }}</td>
      <td>{{ $totals['mikro_air_minum'] }}</td>
      <td>{{ $totals['mikro_air_limbah'] }}</td>
      <td>{{ $totals['mikro_kolam'] }}</td>
      <td>{{ $totals['mikro_mm'] }}</td>
      <td>{{ $totals['mikro_usap'] }}</td>
      <td>{{ $totals['mikro_udara'] }}</td>
      <td>{{ $totals['kimia_air_bersih'] }}</td>
      <td>{{ $totals['kimia_air_minum'] }}</td>
      <td>{{ $totals['kimia_air_limbah'] }}</td>
      <td>{{ $totals['kimia_kolam'] }}</td>
      <td>{{ $totals['kimia_mm'] }}</td>
    </tr>
    <tr>
      <td colspan="2">Komulatif</td>
      <td colspan="18">{{ $komulatif }}</td>
    </tr>
  </tfoot>
</table>
