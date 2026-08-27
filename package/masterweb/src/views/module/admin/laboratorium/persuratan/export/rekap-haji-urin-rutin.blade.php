<table border="1" cellspacing="0" cellpadding="4"
    style="border-collapse:collapse;width:100%;font-size:12px;font-family:'Times New Roman',Times,serif">

    <thead>
        {{-- Baris 1: placeholder kop (gambar disisipkan via Drawing di Export) --}}
        <tr>
            <td colspan="26" style="height: 80px;"></td>
        </tr>
        <!-- JUDUL -->
        <tr>
            <th colspan="26" style="text-align:center;font-weight:bold;">
                HASIL PEMERIKSAAN URIN RUTIN CALON JAMAAH HAJI {{ $tahun_haji }}
            </th>
        </tr>
        <tr>
            <th colspan="26" style="text-align:center;">
                PUSKESMAS : {{ $nama_pusklesmas }}
            </th>
        </tr>

        <tr>
            <td colspan="26"></td>
        </tr>

        <!-- INFO LAB -->
        <tr>
            <th colspan="3" style="text-align:left;">No Lab</th>
            <th colspan="5" style="text-align:left;">: {{ $no_lab ?? '445.03/160/05.31/2025' }}</th>
            <th colspan="18"></th>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left;">Diambil</th>
            <th colspan="5" style="text-align:left;">: {{ $tgl_diambil ?? '16 Januari 2025' }}</th>
            <th colspan="18"></th>
        </tr>
        <tr>
            <th colspan="3" style="text-align:left;">Diperiksa</th>
            <th colspan="5" style="text-align:left;">: {{ $tgl_diperiksa ?? '16 Januari 2025' }}</th>
            <th colspan="18"></th>
        </tr>

        <tr>
            <td colspan="26"></td>
        </tr>

        <!-- HEADER KOLOM -->
        <tr>
            <th rowspan="4">NO</th>
            <th rowspan="4">NO SPESIMEN</th>
            <th rowspan="4">NAMA</th>
            <th rowspan="4"
                style="writing-mode:vertical-rl;transform:rotate(180deg);">
                UMUR
            </th>
            <th rowspan="4"
                style="writing-mode:vertical-rl;transform:rotate(180deg);">
                SEX
            </th>
            <th rowspan="4">ALAMAT</th>
        
            <th colspan="19">URINE RUTIN</th>
            <th rowspan="4">PP Test</th>
        </tr>
        
        <tr>
            <th rowspan="2">WARNA</th>
            <th rowspan="2">BAU</th>
            <th rowspan="2">KEJERNIHAN</th>
            <th rowspan="2">ERITROSIT</th>
            <th rowspan="2">UROBILINOGEN</th>
            <th rowspan="2">BILIRUBIN</th>
            <th rowspan="2">PROTEIN</th>
            <th rowspan="2">NITRAT</th>
            <th rowspan="2">KETON</th>
            <th rowspan="2">GLUKOSA</th>
            <th rowspan="2">pH</th>
            <th rowspan="2">BERAT JENIS</th>
            <th rowspan="2">LEU</th>
        
            <th colspan="6">SEDIMEN URINE</th>
        </tr>
        
        <tr>
            <th>EPITEL</th>
            <th>LEU</th>
            <th>ERY</th>
            <th>CYLI</th>
            <th>KRISTAL</th>
            <th>Lain2</th>
        </tr>
        
        <!-- NILAI RUJUKAN -->
        <tr>
            <th>Kuning muda–tua</th>
            <th>Tidak menyengat</th>
            <th>Jernih</th>
            <th>Negatif</th>
            <th>Norm (0.2–1)</th>
            <th>Negatif</th>
            <th>Negatif</th>
            <th>Negatif</th>
            <th>Negatif</th>
            <th>Negatif</th>
            <th>5–7</th>
            <th>1.005–1.030</th>
            <th>Negatif</th>
        
            <th>&lt;10 /LPK</th>
            <th>0–5 /LPB</th>
            <th>0–3 /LPB</th>
            <th>Negatif</th>
            <th>&lt; pos 1(+)</th>
            <th>(bakteri)</th>
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
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['warna'] ?? '-', 'warna', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['bau'] ?? '-', 'bau', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['kejernihan'] ?? '-', 'kejernihan', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['eritrosit'] ?? '-', 'eritrosit', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['urobilinogen'] ?? '-', 'urobilinogen', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['bilirubin'] ?? '-', 'bilirubin', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['protein'] ?? '-', 'protein', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['nitrat'] ?? '-', 'nitrat', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['keton'] ?? '-', 'keton', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['glukosa'] ?? '-', 'glukosa', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['ph'] ?? '-', 'ph', $jk) }}</td>
            <td data-type="s" style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['berat_jenis'] ?? '-', 'berat_jenis', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['leu'] ?? '-', 'leu', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['epitel'] ?? '-', 'epitel', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['leu_sedimen'] ?? '-', 'leu_sedimen', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['ery'] ?? '-', 'ery', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['cyli'] ?? '-', 'cyli', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['kristal'] ?? '-', 'kristal', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $fmt::format($row['lain2'] ?? '-', 'lain2', $jk) }}</td>
            <td style="border: 1px solid #000; text-align:center;">{{ $row['pp_test'] ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tr>
        <td colspan="26"></td>
    </tr>

    <tr>
        <th style="text-align:left;">Ket</th>
        <th colspan="25" style="text-align:left;">:  -  Tanda (*) = hasil di luar nilai rujukan / baku mutu</th>
        <th></th>
    </tr>
    <tr>
        <th style="text-align:left;"></th>
        <th colspan="25" style="text-align:left;"> -  Apabila ada keragu-raguan dengan hasil pemeriksaan mohon menghubungi Laboratorium SIMLAB (0293) 3301587</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;">—, {{ \Carbon\Carbon::now()->format('d F Y') }}</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;">Dokter Penanggung Jawab Klinis</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;">Laboratorium SIMLAB</th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;"></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;"></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;"></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;"><u>dr. Dummy Pengirim</u></th>
        <th></th>
    </tr>
    <tr>
        <th colspan="25"></th>
        <th style="text-align:center;">NIP.197001282000031001</th>
        <th></th>
    </tr>

</table>