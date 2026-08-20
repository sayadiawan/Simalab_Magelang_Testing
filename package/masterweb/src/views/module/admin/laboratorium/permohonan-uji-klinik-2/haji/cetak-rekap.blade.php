<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Haji - {{ $haji->nama_haji }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            border: 2px solid #000;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .summary-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP DATA HAJI</h2>
        <h3>{{ $haji->nama_haji }}</h3>
        <p>Tanggal: {{ \Carbon\Carbon::parse($haji->tgl_haji)->isoFormat('DD MMMM YYYY') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>No. Lab</th>
                <th>Nama Pasien</th>
                <th>Tanggal Lahir</th>
                <th>Jenis Kelamin</th>
                <th>Alamat</th>
                <th class="text-right">Total Harga</th>
                <th class="text-right">Terbayar</th>
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($data as $item)
                @php
                    $total_harga_item = $item->total_harga_permohonan_uji_klinik ?? 0;
                    $terbayar_item = $item->payment ? $item->payment->terbayar_permohonan_uji_payment_klinik : 0;
                    $status_pembayaran = ($terbayar_item >= $total_harga_item && $total_harga_item > 0) ? 'Lunas' : 'Belum Lunas';
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
                    <td>{{ $item->pasien->nama_pasien ?? '-' }}</td>
                    <td>{{ $item->pasien->tgllahir_pasien ? \Carbon\Carbon::parse($item->pasien->tgllahir_pasien)->isoFormat('DD MMMM YYYY') : '-' }}</td>
                    <td>{{ $item->pasien->gender_pasien == 'L' ? 'Laki-laki' : ($item->pasien->gender_pasien == 'P' ? 'Perempuan' : '-') }}</td>
                    <td>{{ \Smt\Masterweb\Helpers\Smt::alamatLengkapPasien($item->pasien) }}</td>
                    <td class="text-right">{{ number_format($total_harga_item, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($terbayar_item, 0, ',', '.') }}</td>
                    <td>{{ $status_pembayaran }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Jumlah Pasien:</span>
            <span>{{ $jumlah_pasien }} orang</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Harga:</span>
            <span>Rp. {{ number_format($total_harga, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Terbayar:</span>
            <span>Rp. {{ number_format($total_terbayar, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Sisa:</span>
            <span>Rp. {{ number_format($total_harga - $total_terbayar, 0, ',', '.') }}</span>
        </div>
    </div>
</body>
</html>

