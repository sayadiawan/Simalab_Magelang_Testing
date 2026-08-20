<table border="1" cellspacing="0" cellpadding="4"
    style="border-collapse:collapse;width:100%;font-size:12px">

    <thead>
        {{-- Baris 1: placeholder kop (gambar disisipkan via Drawing di Export) --}}
        <tr>
            <td colspan="28" style="height: 80px;"></td>
        </tr>
        <!-- JUDUL -->
        <tr>
            <th colspan="28" style="text-align:center;font-weight:bold;">
                HASIL PEMERIKSAAN DARAH CALON JAMAAH HAJI {{ $tahun_haji }}
            </th>
        </tr>
        <tr>
            <th colspan="28" style="text-align:center;">
                PUSKESMAS : {{ $nama_pusklesmas }}
            </th>
        </tr>

        <tr>
            <td colspan="28"></td>
        </tr>

        <!-- INFO LAB -->
        <tr>
            <th colspan="3" style="text-align:left;">No Lab</th>
            <th colspan="5" style="text-align:left;">: {{ $no_lab ?? '445.03/160/05.31/2025' }}</th>
            <th colspan="20"></th>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left;">Diambil</th>
            <th colspan="5" style="text-align:left;">: {{ $tgl_diambil ?? '16 Januari 2025' }}</th>
            <th colspan="20"></th>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left;">Diperiksa</th>
            <th colspan="5" style="text-align:left;">: {{ $tgl_diperiksa ?? '16 Januari 2025' }}</th>
            <th colspan="20"></th>
        </tr>

        <tr>
            <td colspan="28"></td>
        </tr>

        <!-- HEADER KOLOM -->
        <tr>
            <th rowspan="4" style="border: 1px solid #000;">NO</th>
            <th rowspan="4" style="border: 1px solid #000;">NO SPESIMEN</th>
            <th rowspan="4" style="border: 1px solid #000;">NAMA</th>
            <th rowspan="4" style="border: 1px solid #000; writing-mode: vertical-rl; text-orientation: mixed;">UMUR</th>
            <th rowspan="4" style="border: 1px solid #000; writing-mode: vertical-rl; text-orientation: mixed;">JENIS<br>KELAMIN</th>
            <th rowspan="4" style="border: 1px solid #000;">ALAMAT</th>
            <th rowspan="3" style="border: 1px solid #000;">HbA1c</th>

            <th colspan="10" style="border: 1px solid #000;">KIMIA DARAH</th>
            <th colspan="9" style="border: 1px solid #000;">DARAH RUTIN</th>

            <th rowspan="3" style="border: 1px solid #000;">LED</th>
            <th rowspan="4" style="border: 1px solid #000;">Gol. Darah</th>
        </tr>

        <tr>
            <!-- KIMIA DARAH -->
            <th rowspan="2" style="border: 1px solid #000;">GDP</th>
            <th rowspan="2" style="border: 1px solid #000;">GPP</th>
            <th rowspan="2" style="border: 1px solid #000;">CHOL</th>
            <th rowspan="2" style="border: 1px solid #000;">TG</th>
            <th rowspan="2" style="border: 1px solid #000;">Kreatinin</th>
            <th rowspan="2" style="border: 1px solid #000;">eGFR</th>
            <th rowspan="2" style="border: 1px solid #000;">Ureum</th>
            <th rowspan="2" style="border: 1px solid #000;">SGOT</th>
            <th rowspan="2" style="border: 1px solid #000;">SGPT</th>
            <th rowspan="2" style="border: 1px solid #000;">Δ Leu</th>

            <!-- DARAH RUTIN -->
            <th rowspan="2" style="border: 1px solid #000;">Δ Eri</th>
            <th rowspan="2" style="border: 1px solid #000;">Hb</th>
            <th rowspan="2" style="border: 1px solid #000;">Hematokrit</th>
            <th rowspan="2" style="border: 1px solid #000;">Trombosit</th>
            <th colspan="5" style="border: 1px solid #000;">HITUNG JENIS LEUKOSIT</th>
        </tr>

        <tr>
            <th style="border: 1px solid #000;">NEU</th>
            <th style="border: 1px solid #000;">LYM</th>
            <th style="border: 1px solid #000;">MONO</th>
            <th style="border: 1px solid #000;">EOS</th>
            <th style="border: 1px solid #000;">BASO</th>
        </tr>

        <!-- NILAI RUJUKAN -->
        <tr>
            <th style="border: 1px solid #000;">&lt;5.7%</th>

            <th style="border: 1px solid #000;">70-110</th>
            <th style="border: 1px solid #000;">70-140</th>
            <th style="border: 1px solid #000;">150-200</th>
            <th style="border: 1px solid #000;">120-150</th>
            <th style="border: 1px solid #000;">0.5-1.2</th>
            <th style="border: 1px solid #000;">&ge;90</th>
            <th style="border: 1px solid #000;">20-50</th>
            <th style="border: 1px solid #000;">L&lt;33 P&lt;27</th>
            <th style="border: 1px solid #000;">L&lt;46 P&lt;36</th>
            <th style="border: 1px solid #000;">5-10</th>

            <th style="border: 1px solid #000;">L 4.5-5.5<br>P 4.0-5.0</th>
            <th style="border: 1px solid #000;">L 13-16<br>P 12-14</th>
            <th style="border: 1px solid #000;">L 40-48<br>P 37-43</th>
            <th style="border: 1px solid #000;">150-400</th>

            <th style="border: 1px solid #000;">50-75%</th>
            <th style="border: 1px solid #000;">20-40%</th>
            <th style="border: 1px solid #000;">2-8%</th>
            <th style="border: 1px solid #000;">1-3%</th>
            <th style="border: 1px solid #000;">0-1%</th>

            <th style="border: 1px solid #000;">L&lt;15 P&lt;20</th>
        </tr>
    </thead>

    <tbody>
        @php
            $fmt = \Smt\Masterweb\Helpers\RekapHajiHasilFormatter::class;
        @endphp
        @foreach($data as $index => $row)
        @php $jk = $row['jk'] ?? null; @endphp
        <tr>
            <td style="border: 1px solid #000; text-align:center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $row['no_specimen'] ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $row['nama'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $row['umur'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $row['jk'] ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $row['alamat'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['hba1c'] ?? '-', 'hba1c', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['gdp'] ?? '-', 'gdp', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['gpp'] ?? '-', 'gpp', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['chol'] ?? '-', 'chol', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['tg'] ?? '-', 'tg', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['kreatinin'] ?? '-', 'kreatinin', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['egfr'] ?? '-', 'egfr', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['ureum'] ?? '-', 'ureum', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['sgot'] ?? '-', 'sgot', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['sgpt'] ?? '-', 'sgpt', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['delta_leu'] ?? '-', 'delta_leu', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['delta_eri'] ?? '-', 'delta_eri', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['hb'] ?? '-', 'hb', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['hematokrit'] ?? '-', 'hematokrit', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['trombosit'] ?? '-', 'trombosit', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['neu'] ?? '-', 'neu', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['lym'] ?? '-', 'lym', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['mono'] ?? '-', 'mono', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['eos'] ?? '-', 'eos', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['baso'] ?? '-', 'baso', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['led'] ?? '-', 'led', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $row['gol_darah'] ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tr>
        <td colspan="28"></td>
    </tr>

    <tr>
        <th style="text-align:left;">Ket</th>
        <th colspan="27" style="text-align:left;">:  -  Tanda (*) = hasil di luar nilai rujukan / baku mutu</th>
        <th></th>
    </tr>
    <tr>
        <th style="text-align:left;"></th>
        <th colspan="27" style="text-align:left;"> -  Untuk hasil pemeriksaan gula 2 jam PP (GPP) yang lebih rendah dari gula darah puasa (GDP) sudah dilakukan pengulangan pemeriksaan dengan hasil yang sama</th>
        <th></th>
    </tr>
    <tr>
        <th style="text-align:left;"></th>
        <th colspan="27" style="text-align:left;"> - Apabila ada keragu-raguan dengan hasil pemeriksaan mohon menghubungi Laboratorium Kesehatan Kab. Magelang (0293) 3301587</th>
        <th ></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;">Kota Mungkid, {{ \Carbon\Carbon::now()->format('d F Y') }}</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;">Dokter Penanggung Jawab Klinis</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;">Laboratorium Kesehatan Kabupaten Magelang</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;"></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;"></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;"></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;"><u>dr. Sunantyo, M.P.H</u></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="27"></th>
        <th style="text-align:center;">NIP.197001282000031001</th>
        <th></th>
    </tr>

</table>