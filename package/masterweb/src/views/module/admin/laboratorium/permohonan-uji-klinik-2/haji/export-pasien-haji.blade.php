<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:100%;font-size:12px">
    <tr>
        <th colspan="8" style="text-align:center;font-weight:bold;font-size:14px;">
            DAFTAR HADIR PEMERIKSAAN HAJI
        </th>
    </tr>
    <tr>
        <th colspan="8" style="text-align:center;font-weight:bold;">
            PUSKESMAS: {{ mb_strtoupper($haji->nama_haji ?? '-', 'UTF-8') }}
        </th>
    </tr>
    <tr>
        <th colspan="8" style="text-align:center;">
            Tanggal Pemeriksaan :
            @if (!empty($filterTanggal))
                {{ \Carbon\Carbon::parse($filterTanggal)->isoFormat('DD MMMM YYYY') }}
            @elseif (!empty($haji->tgl_haji))
                {{ \Carbon\Carbon::parse($haji->tgl_haji)->isoFormat('DD MMMM YYYY') }}
            @else
                -
            @endif
        </th>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <th style="text-align:center;border:1px solid #000;width:90px;">NO SPESIMEN</th>
        <th style="text-align:center;border:1px solid #000;width:40px;">NO</th>
        <th style="text-align:center;border:1px solid #000;">NAMA</th>
        <th style="text-align:center;border:1px solid #000;width:40px;">JK</th>
        <th style="text-align:center;border:1px solid #000;width:100px;">TANGGAL LAHIR</th>
        <th style="text-align:center;border:1px solid #000;width:50px;">UMUR</th>
        <th style="text-align:center;border:1px solid #000;">ALAMAT</th>
        <th style="text-align:center;border:1px solid #000;width:120px;">KETERANGAN</th>
    </tr>
    @forelse ($rows as $row)
        <tr>
            <td style="text-align:center;border:1px solid #000;">{{ $row['no_spesimen'] }}</td>
            <td style="text-align:center;border:1px solid #000;">{{ $row['no'] }}</td>
            <td style="border:1px solid #000;">{{ $row['nama'] }}</td>
            <td style="text-align:center;border:1px solid #000;">{{ $row['jk'] }}</td>
            <td style="text-align:center;border:1px solid #000;">{{ $row['tanggal_lahir'] }}</td>
            <td style="text-align:center;border:1px solid #000;">{{ $row['usia'] }}</td>
            <td style="border:1px solid #000;">{{ $row['alamat'] }}</td>
            <td style="border:1px solid #000;">{{ $row['keterangan'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="8" style="text-align:center;border:1px solid #000;">Tidak ada data pasien</td>
        </tr>
    @endforelse
    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td colspan="8" style="font-weight:bold;">
            Jumlah Jamaah : {{ count($rows) }} orang
        </td>
    </tr>
</table>
