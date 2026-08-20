<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Nota.KRNGNY-KLINIK </title>
  <link rel="shortcut icon" href="">
  <style>
    .starter-template {
      padding: 0px 0px;
      text-align: center;
    }


    table>tr>td {
      cell-padding: 5px !important;
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
      border: 1px solid black;
    }

    .result td {
      border: 1px solid black;
      text-align: center;
      font-size: 12px;

    }

    .result2 td {

      font-style: bold;
      font-size: 13px;

    }

    .result3 td {

      font-style: bold;
      font-size: 8px;

    }

    @page {
      margin: 20px 50px 20px 50px;
    }

    body {
      font-size: 12px;
    }

    .page_break {
      page-break-before: always;
    }

    .parallelogram {
      width: 100%;
      height: 25px;
      border: 1px solid black;
      transform: skew(-20deg);
    }

    span.text {
      font-size: 15px;
      display: inline-block;
      -webkit-transform: skew(20deg);
      -moz-transform: skew(20deg);
      -o-transform: skew(20deg);
    }

    table.fixed {
      table-layout: fixed;
    }
  </style>

  <script src="{{asset('assets/admin/cdn-local/js/signature_pad-2.3.2.min.js')}}"></script>

</head>

<body>
  <div id="printable" class="container">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="border result2"
      style="overflow-x:auto;">
      <thead>
        <tr>
          <td width="40%">
            <h4 style="margin-bottom: 0; margin-top: 0;">
              UPT ESTU LENTERA INDO TEKNOLOGI
            </h4>

            <h5 style="margin-bottom: 0; margin-top: 0;">
              Berdasarkan ESTU LENTERA INDO TEKNOLOGI <br>
              Tahun 2024
            </h5>
          </td>

          <td width="30%">
          </td>
          <td width="30%">
            <table border="0" width="100%" class="border table result" style="overflow-x:auto;">
              <tr>
                <td>
                  <h2>KUITANSI</h2>

                  {{-- KONDISI SUDAH TERBAYAR ATAU BELUM --}}
                  @if ($item_permohonan_uji_klinik->is_paid_permohonan_uji_klinik == '1')
                    @if ($detail_payment->no_nota_permohonan_uji_payment_klinik)
                      <h3>No.
                        {{ 'KW-' . str_pad((int) $detail_payment->no_nota_permohonan_uji_payment_klinik, 4, '0', STR_PAD_LEFT) }}
                      </h3>
                    @else
                      <h3>No. ..................</h3>
                    @endif
                  @else
                    <h3>No. ..................</h3>
                  @endif

                </td>
              </tr>

            </table>
          </td>
        </tr>

      </thead>
    </table>

    <br>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="border result2 fixed"
      style="overflow-x:auto; ">
      <thead>
        <tr>
          <td width="30%">
            TELAH DITERIMA DARI
          </td>
          <td width="2%">
            :
          </td>
          <td width="68%">
            {{ $item_permohonan_uji_klinik->pasien->nama_pasien }}
          </td>
        </tr>
        <tr>
          <td width="30%"></td>
          <td width="1%"></td>
          <td width="69%"></td>
        </tr>
        <tr>
          <td width="30%">
            ALAMAT
          </td>
          <td width="1%">
            :
          </td>
          <td width="69%">
            {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
          </td>
        </tr>
        <tr>
          <td width="30%"></td>
          <td width="1%"></td>
          <td width="69%"></td>
        </tr>
        <tr>
          <td width="30%">
            UANG SEJUMLAH
          </td>
          <td width="1%">
            :
          </td>
          <td width="69%">
            <div class="parallelogram">
              &nbsp; &nbsp;&nbsp;
              {{ terbilang($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ? (int) $item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik : (int) '0') }}

            </div>

          </td>
        </tr>
        <tr>
          <td width="30%"></td>
          <td width="1%"></td>
          <td width="69%"></td>
        </tr>
        <tr>
          <td width="30%">
            GUNA MEMBAYAR
          </td>
          <td width="1%">
            :
          </td>
          <td width="69%">
            JENIS PEMERIKSAAN LABORATORIUM KLINIK, terdiri dari:
          </td>
        </tr>

      </thead>
    </table>

    <br>
    <br>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="border result2"
      style="overflow-x:auto; ">

      <tr>
        <td width="100%">
          <table border="0" cellpadding="0" cellspacing="0" width="100%" style="overflow-x:auto; ">

            <tr>
              <td width="5%" class="border result" align="center">
                NO.
              </td>

              <td width="35%" class="border result" align="center">
                CUSTOM/PAKET
              </td>

              <td width="15%" class="border result" align="center">
                JUMLAH PARAMETER
              </td>

              <td width="15%" class="border result" align="center">
                HARGA SATUAN (Rp)
              </td>

              <td width="25%" class="border result" align="center">
                JUMLAH<br>
                (Rp)
              </td>
            </tr>

            @php
              $no = 1;
            @endphp

            {{-- FOR LOOP SEMUA PARAMETER YANG PAKET DAN TIDAK --}}
            @foreach ($value_items as $key => $value)
              {{-- KONDISI MENCARI PAKET --}}
              <tr>
                <td width="5%" class="border result" align="center">
                  {{ $no }}.
                </td>

                <td width="35%" class="border result">

                  {{ $value['name_item'] }}
                </td>
                <td width="15%" class="border result" align="center">

                  {{ $value['count_item'] }}
                </td>

                <td width="15%" class="border result" align="center">
                  {{ rupiah($value['price_item']) }}
                </td>

                <td width="25%" class="border result" align="center">
                  {{ rupiah($value['total']) }}
                </td>
              </tr>



              @php
                $no++;
              @endphp
            @endforeach
          </table>
        </td>
      </tr>


    </table>

    <br>
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="border result2"
      style="overflow-x:auto; ">

      <tr>
        <td width="70%">

        </td>
        <td width="15%">
          JUMLAH
        </td>
        <td width="15%">
          {{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik != null ? $item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik : '0') }}
        </td>
      </tr>
      <tr>
        <td width="70%">

        </td>
        <td width="15%">
          <br>
        </td>
        <td width="15%">

        </td>
      </tr>
      <tr>
        <td width="70%">
          <table border="0" cellpadding="0" cellspacing="0" width="100%" class="border result2"
            style="overflow-x:auto; ">
            <tr>
              <td width="15%">
                TERBILANG:
              </td>
              <td width="5%">

              </td>
              <td width="40%">
                <div class="parallelogram">
                  <span class="text">
                    &nbsp; &nbsp;&nbsp;
                    {{ rupiah($item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik ? (int) $item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik : (int) '0') }}
                  </span>
                </div>
              </td>
              <td width="35%">
              </td>
            </tr>
          </table>


        </td>
        <td width="15%">

        </td>
        <td width="15%">
          Yang menerima<br>
          Petugas,<br><br><br>

          {{-- KONDISI SUDAH TERBAYAR ATAU BELUM --}}
          @if ($item_permohonan_uji_klinik->is_paid_permohonan_uji_klinik == '1')
            @if ($detail_payment->nota_petugas_permohonan_uji_payment_klinik)
              {{ $detail_payment->petugas->name }}
            @else
              ..................
            @endif
          @else
            ..................
          @endif
        </td>
      </tr>



    </table>
  </div>
</body>

</html>
