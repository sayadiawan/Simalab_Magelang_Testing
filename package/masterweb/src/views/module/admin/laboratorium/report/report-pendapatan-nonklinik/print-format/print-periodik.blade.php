<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Laporan Pendapatan Non-Klinik</title>
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

  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td width="10%">
        Hal
      </td>
      <td width="1%">
        :
      </td>
      <td>
        Hasil Laporan Pendapatan Non-Klinik
      </td>
      <td align="right">

      </td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td>
        @if ($start_date_format && $end_date_format)
          Disampaikan dengan hormat hasil Laporan Pendapatan Non-Klinik mulai dari {{ $start_date_format }} sampai
          {{ $end_date_format }} untuk {{ $nama_laboratorium }} adalah sebagai berikut:
        @else
          Disampaikan dengan hormat hasil Laporan Pendapatan Non-Klinik untuk {{ $nama_laboratorium }} adalah sebagai
          berikut:
        @endif

      </td>
    </tr>
  </table>

  <table width="100%" cellspacing="0" cellpadding="5" border="1" style="margin-top: 5px">
    <thead>
      <tr>
        <th style="width: 5%">NO</th>
        <th style="width: 15%">NO. REGISTER</th>
        <th style="width: 15%">JENIS SAMPEL</th>
        <th style="width: 20%">NAMA PELANGGAN</th>
        <th style="width: 15%">LABORATORIUM</th>
        <th style="width: 15%">TANGGAL TRANSAKSI</th>
        <th style="width: 15%">TOTAL HARGA</th>
      </tr>
    </thead>

    <tbody>
      @php
        $no = 0;
        $cost_samples = 0;
      @endphp

      @foreach ($permohonan_uji_nonklinik as $key => $item)
        <tr>
          <td style="text-align: center">{{ ++$no }}</td>
          <td>{{ $item->codesample_samples }}</td>
          <td>
            @php
              $hasRectal = !empty($item->has_rectal_swab) || (float) ($item->rectal_swab_price ?? 0) > 0;
              $name_sample_type = $item->name_sample_type ?? '-';
              if ($hasRectal) {
                  $name_sample_type .= ' + Biaya Rectal Swab';
              }
              $rowAmount = isset($item->resolved_cost)
                  ? (float) $item->resolved_cost
                  : ((float) ($item->cost_samples ?? 0) + (float) ($item->rectal_swab_price ?? 0));
            @endphp
            {{ $name_sample_type }}
          </td>
          <td>{{ $item->name_customer }}</td>
          <td>{{ $item->nama_laboratorium }}</td>
          <td>
            {{ $item->date_sending != null
                ? \Carbon\Carbon::parse($item->date_sending)->isoFormat('D MMMM Y HH:mm')
                : '-' }}
          </td>
          <td>{{ rupiah($rowAmount) }}</td>
        </tr>

        @php
          $cost_samples += $rowAmount;
        @endphp
      @endforeach

      <tr>
        <td colspan="6" style="text-align: center">TOTAL</td>
        <td>{{ rupiah($cost_samples) }}</td>
      </tr>
    </tbody>
  </table>
</body>

</html>
