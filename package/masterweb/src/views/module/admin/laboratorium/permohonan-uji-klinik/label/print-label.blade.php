<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Print-Label-Pasien.KRNGNY-2002006-AP </title>
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
      font-size: 6px;
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

  @include('masterweb::template.admin.metadata_print')
  <script src="{{asset('assets/admin/cdn-local/js/signature_pad-2.3.2.min.js')}}"></script>

</head>

<body>
  <div id="printable" class="container">

    {{-- @if (count($get_data) > 1)
      @for ($i = 0; $i < 3; $i++)
        @for ($i = 0; $i < count($get_data); $i++)
          <table border="0" cellpadding="0" cellspacing="0" width="250mm" class="border result2" style="overflow-x:auto; border: 1px solid;">
            <tr>
              <td width="100%">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="overflow-x:auto; ">
                  <tr>
                    <td class="border result" style="text-align: center; padding: 2px;">
                      No. Rekam Medis :
                      <br>
                      {{ $get_data[$i]->no_rekammedis_pasien }}
                    </td>
                  </tr>
                  <tr>
                    <td class="border result" style="padding: 2px">
                      <table width="100%">
                        <tr>
                          <td width="45%" style="text-align: start; border: none;">Nama</td>
                          <td width="5%" style="text-align: center; border: none;">:</td>
                          <td width="50%" style="text-align: start; border: none;">{{ $get_data[$i]->nama_pasien }}</td>
                        </tr>
                        <tr>
                          <td width="45%" style="text-align: start; border: none;">Tgl. Lahir</td>
                          <td width="5%" style="text-align: center; border: none;">:</td>
                          <td width="50%" style="text-align: start; border: none;">{{ $get_data[$i]->tgllahir_pasien }}</td>
                        </tr>
                        <tr>
                          <td width="45%" style="text-align: start; border: none;">Jenis Pemeriksaan</td>
                          <td width="5%" style="text-align: center; border: none;">:</td>
                          <td width="50%" style="text-align: start; border: none;">{{ 'Not Available!' }}</td>
                        </tr>
                      </table>
                </table>
              </td>
            </tr>
          </table>
        @endfor
      @endfor
    @else --}}

    {{-- max 27 per page --}}



      @php
        $label_count = count($get_data);
        $n = 0;
      @endphp

      @if (count($get_data) > 0)
        <table>
          <tr>
            @if (count($get_data) > 0)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 1)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 2)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 3)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 4)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
          </tr>
        </table>
      @endif

      @if (count($get_data) > 5)
        <table>
          <tr>
            @if (count($get_data) > 5)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 6)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 7)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 8)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 9)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
          </tr>
        </table>
      @endif

      @if (count($get_data) > 10)
        <table>
          <tr>
            @if (count($get_data) > 10)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 11)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 12)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 13)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
            @if (count($get_data) > 14)
              @include('masterweb::module.admin.laboratorium.permohonan-uji-klinik.label.print-label-format')
              @php
                $n++;
              @endphp
            @endif
          </tr>
        </table>
      @endif

  </div>

  <br>
  <br>

  @include('masterweb::template.admin.scripts')
  @yield('scripts')

  <script>
    $(function() {
      window.print();
    });
  </script>

</body>

</html>
