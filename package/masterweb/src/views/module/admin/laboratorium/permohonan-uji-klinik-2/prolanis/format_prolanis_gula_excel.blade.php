<table style="width: 100%">
    <thead>
        <tr>
            <th align="center" colspan="15"><b> HASIL PROLANIS GULA {{ strtoupper($nama_prolanis) }},
                    {{ strtoupper(\Carbon\Carbon::parse($tgl_prolanis)->translatedFormat('d F Y')) }}
                </b></th>
        </tr>
    </thead>
</table>


<table style="width: 100%">
    <thead>
        <tr style="background-color: green; border-collapse: collapse;">
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 1cm; border: 1px solid black;">
                No.</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 7cm; border: 1px solid black;">
                Nama</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tgl Lahir</th>

            <th scope="col"
                style="background-color: red; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tgl Lahir (String)</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 5cm; border: 1px solid black;">
                Alamat</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                Kelamin</th>

            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 5cm; border: 1px solid black;">
                No. Lab</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                HbA1C</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Gula Darah Puasa</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Gula Darah 2 Jam PP</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Rerata Gula Darah</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                CHOL</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                LDL</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                HDL</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                TG</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                UREUM</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                CREAT</th>
            <th scope="col"
                style="background-color: green; text-align: center; vertical-align: middle; width: 3cm; border: 1px solid black;">
                MIKROALB.</th>
            <th scope="col"
                style="background-color: yellow; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tanggal Registrasi</th>
            <th scope="col"
                style="background-color: yellow; text-align: center; vertical-align: middle; width: 4cm; border: 1px solid black;">
                Tanggal Validasi</th>
            <th scope="col" colspan="5"
                style="background-color: red; text-align: center; width: 2cm; border: 1px solid black;">Nomor Amplop
            </th>
            {{-- <th scope="col" colspan="3" style="background-color: yellow; text-align: center; width: 10cm; border: 1px solid black;">Pendaftaran / Registrasi</th>
          <th scope="col" colspan="3" style="background-color: yellow; text-align: center; width: 10cm; border: 1px solid black;">Pengambilan Sample</th>
          <th scope="col" colspan="3" style="background-color: yellow; text-align: center; width: 10cm; border: 1px solid black;">Pemeriksaan / Analitik</th>
          <th scope="col" colspan="3" style="background-color: yellow; text-align: center; width: 10cm; border: 1px solid black;">Input / Output Hasil Px</th>
          <th scope="col" colspan="3" style="background-color: yellow; text-align: center; width: 10cm; border: 1px solid black;">Verifikasi</th>
          <th scope="col" colspan="3" style="background-color: yellow; text-align: center; width: 10cm; border: 1px solid black;">Validasi</th> --}}
        </tr>
        {{-- <tr style="background-color: yellow; border-collapse: collapse;">
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Mulai</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Berakhir</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 4cm; border: 1px solid black;">Nama Petugas</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Mulai</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Berakhir</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 4cm; border: 1px solid black;">Nama Petugas</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Mulai</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Berakhir</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 4cm; border: 1px solid black;">Nama Petugas</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Mulai</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Berakhir</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 4cm; border: 1px solid black;">Nama Petugas</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Mulai</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Berakhir</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 4cm; border: 1px solid black;">Nama Petugas</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Mulai</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 3cm; border: 1px solid black;">Tanggal Berakhir</th>
          <th scope="col" style="background-color: yellow; text-align: center; width: 4cm; border: 1px solid black;">Nama Petugas</th>
      </tr> --}}
    </thead>
    <tbody>
        {{-- contoh input  --}}
        <tr>
            <th style="text-align: center; border: 1px solid black; background-color: #00dbf8;">1</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">Pasien A</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">02 Januari 1995</th>
            <th style="text-align: left; border: 1px solid black; background-color: #red;">= TEXT(C4,"DD MMMM YYYY")
            </th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">Pengging RT 06/ RW 04</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">L</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">0001/LK/VIII/2024</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">1</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">2</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">3</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">4</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">5</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">6</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">7</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">8</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">9</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">10</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">11</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">29 Agustus 2024</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">30 Agustus 2024</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">=TEXT(499.5; "0.0")</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">842</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">4.2.26</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">XII</th>
            <th style="text-align: left; border: 1px solid black; background-color: #00dbf8;">2023</th>
        </tr>
        @foreach ($rows as $key => $val)
            <tr>
                <td style="text-align: center; border: 1px solid black;">{{ $key + 1 }}.</td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;background-color: #red;">=
                    TEXT(C{{ $key + 5 }},"DD MMMM YYYY")</td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;">
                    =IF(LEFT(B{{ $key + 5 }}, 3) = "Ny.", "P", IF(LEFT(B{{ $key + 5 }}, 3) = "Tn.",
                    "L", ""))
                </td>
                <td>
                    {{ str_pad((int) $count + $key + 1, 4, '0', STR_PAD_LEFT) . '/LK/' . $romanMonth . '/' . date('Y') }}
                </td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;">{{ (int) $count + $key + 1 }}</td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
                <td style="text-align: left; border: 1px solid black;"></td>
            </tr>
        @endforeach
    </tbody>
</table>
