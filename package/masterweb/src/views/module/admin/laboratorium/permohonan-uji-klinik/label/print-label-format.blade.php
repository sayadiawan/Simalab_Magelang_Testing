<td>
  <table border="0" cellpadding="0" cellspacing="0" width="100mm" height="80mm" class="border result2" style="overflow-x:auto; border: 1px solid;">
    <tr height="20%">
      <td class="border result" style="text-align: center; padding: 2px;">
        No. Rekam Medis :
        <br>
        {{ $get_data[$n]->permohonanujiklinik->pasien->no_rekammedis_pasien }}
      </td>
    </tr>
    <tr height="80%">
      <td class="border result" style="padding: 2px">
        <table width="100%">
          <tr>
            <td width="45%" style="text-align: start; border: none; vertical-align: top;">Nama</td>
            <td width="5%" style="text-align: center; border: none; vertical-align: top;">:</td>
            <td width="50%" style="text-align: start; border: none; vertical-align: top;">{{ $get_data[$n]->permohonanujiklinik->pasien->nama_pasien }}</td>
          </tr>
          <tr>
            <td width="45%" style="text-align: start; border: none; vertical-align: top;">Tgl. Lahir</td>
            <td width="5%" style="text-align: center; border: none; vertical-align: top;">:</td>
            <td width="50%" style="text-align: start; border: none; vertical-align: top;">{{ $get_data[$n]->permohonanujiklinik->pasien->tgllahir_pasien }}</td>
          </tr>
          <tr>
            <td width="45%" style="text-align: start; border: none; vertical-align: top;">Jenis Pemeriksaan</td>
            <td width="5%" style="text-align: center; border: none; vertical-align: top;">:</td>
            <td width="50%" style="text-align: start; border: none; vertical-align: top;">{{ $get_data[$n]->jenis_pemeriksaan }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</td>
