<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Laporan Pendapatan Klinik</title>
  <link rel="shortcut icon" href="">
  <link rel="stylesheet" href="dist/css/bootstrap.min.css">
  <style>
    .starter-template {
      text-align: center;
    }


    table>tr>td {
      /* cell-padding: 5px !important; */
    }

    @media print {
      #cetak {
        display: none;
      }
    }

    .garis {
      border: 1px solid
    }

    .table2 {
      font-size: 5px;
      text-align: center
    }

    .result {
      border-collapse: collapse;
    }

    .result td {
      border: 1px solid black;
      text-align: center;
    }

    @page {
      size: 794px 1248px;
      margin: 20px 20px 20px 20px;
    }

    body {
      font-size: 12px;
    }

    .page_break {
      page-break-before: always;
    }
  </style>
</head>

<body>
  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td><img src="{{ asset('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px"></td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px">
    <tr>
      <td width="10%">
        Hal
      </td>
      <td width="1%">
        :
      </td>
      <td>
        Hasil Laporan Pendapatan Klinik
      </td>
      <td align="right">

      </td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td>
        @if ($start_date_format && $end_date_format)
          Disampaikan dengan hormat hasil Laporan Pendapatan Klinik mulai dari {{ $start_date_format }} sampai
          {{ $end_date_format }} adalah sebagai berikut:
        @else
          Disampaikan dengan hormat hasil Laporan Pendapatan Klinik adalah sebagai berikut:
        @endif
      </td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="5" border="1" style="margin-top: 5px">
    <thead>
      <tr>
        <th style="width: 5%">NO</th>
        <th style="width: 15%">NO. REGISTER</th>
        <th style="width: 15%">TANGGAL REGISTER</th>
        <th style="width: 25%">NAMA PASIEN</th>
        <th style="width: 15%">TANGGAL TRANSAKSI</th>
        <th style="width: 25%">TOTAL HARGA</th>
      </tr>
    </thead>

    <tbody>
      @php
        $no = 0;
        $total_harga = 0;
      @endphp

      @foreach ($permohonan_uji_klinik as $key => $item)
        <tr>
          <td style="text-align: center">{{ ++$no }}</td>
          <td>{{ $item->noregister_permohonan_uji_klinik }}</td>
          <td>
            {{ $item->tglregister_permohonan_uji_klinik != null
                ? \Carbon\Carbon::parse($item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y')
                : '-' }}
          </td>
          <td>{{ $item->nama_pasien }}</td>
          <td>
            {{ $item->created_at != null
                ? \Carbon\Carbon::parse($item->created_at)->isoFormat('D MMMM Y HH:mm')
                : '-' }}
          </td>
          <td>
            {{ $item->total_harga_permohonan_uji_klinik ? rupiah($item->total_harga_permohonan_uji_klinik) : 'Rp. 0' }}
          </td>
        </tr>

        @php
          $total_harga += $item->total_harga_permohonan_uji_klinik;
        @endphp
      @endforeach

      <tr>
        <td colspan="5" style="text-align: center">TOTAL</td>
        <td>{{ rupiah($total_harga) }}</td>
      </tr>
    </tbody>
  </table>
</body>

</html>
