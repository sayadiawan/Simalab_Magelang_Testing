<td>
  <table style="width: 100%; height: 100%; border-collapse: collapse;">
      <tr>
          <td id="no_rekamedis" style="text-align: center;">
              <b style="font-size: 12px;">{{ $get_data[$n]->noregister_permohonan_uji_klinik }}</b>
              <hr style="border: 1px solid black; margin: 2px 0;"> <!-- Garis horizontal -->
          </td>
      </tr>
      <tr>
          <td>
              <table style="width: 100%; border-collapse: collapse;">
                  <tr>
                      <td id="nama_pasien" style="text-align: center;">
                          <b style="font-size: 10px;">{{ $get_data[$n]->pasien->nama_pasien }}</b>
                      </td>
                  </tr>
                  <tr>
                      <td id="tgllahir_pasien" style="text-align: center;">
                          <b style="font-size: 10px;">{{ date('d/m/Y', strtotime($get_data[$n]->pasien->tgllahir_pasien)) }}</b>
                      </td>
                  </tr>
                  <tr>
                      <td id="jenis_pemeriksaan" style="text-align: center;">
                          <b style="font-size: 10px;">{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($get_data[$n]->pasien) }}</b>
                      </td>
                  </tr>
              </table>
          </td>
      </tr>
  </table>
</td>
