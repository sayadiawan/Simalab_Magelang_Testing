<html lang="">

@php
  $fontsize = isset($fontsize) ? (float) $fontsize : 12.0;
  $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.5;
  $padding = isset($padding) ? (float) $padding : 4.0;
  $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>AirHigiene-{!! $no_LHU !!}</title>
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
            margin: 5px 30px;
        }

        body {
          font-size: {{ $fontsize }}px;
          line-height: {{ $lineHeight }};
        }

        .page_break {
            page-break-before: always;
        }

        .table-container {
            flex: 2;
            margin-right: 10px;
        }

        .table-container table {
            width: 50%;
            border-collapse: collapse;
            font-size: 16px;
        }

        .information-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .information-table td {
            vertical-align: top;
        }

        .information-table td:nth-child(1) {
            width: 200px;
            font-weight: bold;
        }

        .information-table td:nth-child(2) {
            width: 10px;
            text-align: center;
        }

        .tembusan ol {
            padding-left: 18px;
        }
    </style>
</head>

<body style="margin:50px 10px 50px 10px; padding: 0; font-size: {{ $fontsize }}pt; line-height: {{ $lineHeight }};">
    @php
        $hasKimiaOrganik = $laboratoriummethods->contains(function ($laboratoriummethod) {
            return kesmas_lhu_is_jenis($laboratoriummethod, 'kimia organik') && $laboratoriummethod->hasil !== '-';
        });

        $hasKimiawi = $laboratoriummethods->contains(function ($laboratoriummethod) {
            return kesmas_lhu_is_jenis($laboratoriummethod, 'kimiawi') && $laboratoriummethod->hasil !== '-';
        });
    @endphp
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._kop')
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._head_data')
    <br>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 10px">
  <thead>
  <tr>
    <th width="5%" style="text-align: center">NO</th>
    <th width="10%" style="text-align: center">PARAMETER PEMERIKSAAN</th>
    <th width="5%" style="text-align: center">HASIL PEMERIKSAAN</th>
    <th width="35%" style="text-align: center">KADAR MAKSIMUM <br>YANG DIPERBOLEHKAN</th>
    <th width="25%" style="text-align: center">SATUAN</th>
    <th width="20%">METODE</th>
  </tr>
  </thead>

  @php
  // dd($fisikaCount);

  $fisikaCount = 0;
  $kimiaOrganikCount = 0;
  $kimiaCount = 0;
  foreach ($laboratoriummethods as $laboratoriummethod) {
      if (
          kesmas_lhu_is_jenis($laboratoriummethod, 'fisika') &&
          kesmas_lhu_include_parameter($laboratoriummethod)
      ) {
          $fisikaCount++;
      } elseif (
          kesmas_lhu_is_jenis($laboratoriummethod, 'kimiawi') &&
          kesmas_lhu_include_parameter($laboratoriummethod)
      ) {
          $kimiaCount++;
      } elseif (
          kesmas_lhu_is_jenis($laboratoriummethod, 'kimia organik') &&
          kesmas_lhu_include_parameter($laboratoriummethod)
      ) {
          $kimiaOrganikCount++;
      }
  }
@endphp
  <tbody>
  @if($fisikaCount == 0 && $kimiaOrganikCount == 0 && $kimiaCount == 0)
    <tr>
      <td colspan="6" style="text-align: center; padding: 4px"><b>Belum melakukan input hasil</b></td>
    </tr>
  @endif
  @if (count($laboratoriummethods) > 0)
@php $kesmasUrutFallback = 0; @endphp

    @if($sample->only_fisika)
      @if($fisikaCount > 0)
        <tr>
          <th style="text-align: center"></th>
          <th style="text-align: left; padding-left: 2px;" colspan="5">A. FISIKA</th>
        </tr>
      @endif

      {{-- foreach data --}}
@foreach ($laboratoriummethods as $laboratoriummethod)
        @if (kesmas_lhu_is_jenis($laboratoriummethod, 'fisika') && kesmas_lhu_include_parameter($laboratoriummethod))
            @php
              $hasil = cek_hasil_color(
                  isset($laboratoriummethod->hasil)
                      ? $laboratoriummethod->hasil
                      : (isset($laboratoriummethod->equal)
                          ? $laboratoriummethod->equal
                          : ''),
                  $laboratoriummethod->min,
                  $laboratoriummethod->max,
                  $laboratoriummethod->equal,
                  'result_output_method_' . $laboratoriummethod->method_id,
                  $laboratoriummethod->offset_baku_mutu,
              );

              $unit = $laboratoriummethod->shortname_unit;

              $unitAll = $laboratoriummethod->shortname_unit;

              if (isset($unit)) {
                  $unit = '';
                  if (trim($laboratoriummethod->shortname_unit) != '-' && trim($hasil) != '-') {
                      $unit = $laboratoriummethod->shortname_unit;
                  }
              } else {
                  $unit = '';
              }
            @endphp

            @if ($hasil != '-')

            <tr>
              <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
              <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
              <td width="20%" style="text-align: center">{!! $hasil !!}</td>
              <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
              <td width='15%' style="text-align: center">{!! $unitAll !!}</td>
              @if($laboratoriummethod->is_ready == 1)
                <td width="20%" style="text-align: left; padding-left: 2px;">{!! isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
              @else
                <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
              @endif
            </tr>
            @endif
        @endif
      @endforeach
    @else
      @if($fisikaCount > 0)
        <tr>
          <th style="text-align: center"></th>
          <th style="text-align: left; padding-left: 2px;" colspan="5">A. FISIKA</th>
        </tr>
      @endif

      {{-- foreach data --}}
@foreach ($laboratoriummethods as $laboratoriummethod)
        @if (kesmas_lhu_is_jenis($laboratoriummethod, 'fisika') && kesmas_lhu_include_parameter($laboratoriummethod))
            @php
              $hasil = cek_hasil_color(
                  isset($laboratoriummethod->hasil)
                      ? $laboratoriummethod->hasil
                      : (isset($laboratoriummethod->equal)
                          ? $laboratoriummethod->equal
                          : ''),
                  $laboratoriummethod->min,
                  $laboratoriummethod->max,
                  $laboratoriummethod->equal,
                  'result_output_method_' . $laboratoriummethod->method_id,
                  $laboratoriummethod->offset_baku_mutu,
              );

              $unit = $laboratoriummethod->shortname_unit;

              $unitAll = $laboratoriummethod->shortname_unit;

              if (isset($unit)) {
                  $unit = '';
                  if (trim($laboratoriummethod->shortname_unit) != '-' && trim($hasil) != '-') {
                      $unit = $laboratoriummethod->shortname_unit;
                  }
              } else {
                  $unit = '';
              }
            @endphp
            @if ($hasil != '-')

            <tr>
              <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
              <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
              <td width="20%" style="text-align: center">{!! $hasil !!}</td>
              <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
              <td width='15%' style="text-align: center">{!! $unitAll !!}</td>
              @if($laboratoriummethod->is_ready == 1)
                <td width="20%" style="text-align: left; padding-left: 2px;">{!! isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
              @else
                <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
              @endif
            </tr>
            @endif
        @endif
      @endforeach

      @if ($hasKimiawi || $hasKimiaOrganik)
          <tr>
              @if ($fisikaCount > 0)
                  <th style="text-align: center"></th>
              @else
                  <th style="text-align: center"></th>
              @endif
              <th style="text-align: left; padding-left: 2px;" colspan="5">
                @if ($fisikaCount > 0)
                    B.
                @else
                    A.
                @endif
                KIMIA
              </th>
          </tr>
      @endif
      {{-- @if ($kimiaCount > 0 and $kimiaOrganikCount > 0)
          <tr>
              <th></th>
              <th style="text-align: left; padding-left: 2px;" colspan="5">a. KIMIA AN - ORGANIK
              </th>
          </tr>
      @endif --}}

      {{-- foreach B --}}
@foreach ($laboratoriummethods as $laboratoriummethod)
        @if (
            kesmas_lhu_is_jenis($laboratoriummethod, 'kimiawi') && kesmas_lhu_include_parameter($laboratoriummethod))

            @php
              $hasil = cek_hasil_color(
                  isset($laboratoriummethod->hasil)
                      ? $laboratoriummethod->hasil
                      : (isset($laboratoriummethod->equal)
                          ? $laboratoriummethod->equal
                          : ''),
                  $laboratoriummethod->min,
                  $laboratoriummethod->max,
                  $laboratoriummethod->equal,
                  'result_output_method_' . $laboratoriummethod->method_id,
                  $laboratoriummethod->offset_baku_mutu,
              );

              $unit = $laboratoriummethod->shortname_unit;

              $unitAll = $laboratoriummethod->shortname_unit;

              if (isset($unit)) {
                  $unit = '';
                  if (trim($laboratoriummethod->shortname_unit) != '-' && trim($hasil) != '-') {
                      $unit = $laboratoriummethod->shortname_unit;
                  }
              } else {
                  $unit = '';
              }
            @endphp

            @if ($hasil != '-')

            <tr>
              <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
              <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
              <td width="20%" style="text-align: center">{!! $hasil !!}</td>
              <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
              <td width='15%' style="text-align: center">{!! $unitAll !!}</td>
              @if($laboratoriummethod->is_ready == 1)
                <td width="20%" style="text-align: left; padding-left: 2px;">{!! isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
              @else
                <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
              @endif
            </tr>
            @endif
        @endif
      @endforeach

      {{-- @if ($hasKimiaOrganik)
          @if ($kimiaOrganikCount > 0 and $kimiaCount > 0)
              <tr>
                  <th></th>
                  @if ($kimiaCount > 0)
                      <th style="text-align: left; padding-left: 2px;" colspan="5">b. KIMIA ORGANIK
                      </th>
                  @else
                      <th style="text-align: left; padding-left: 2px;" colspan="5">a. KIMIA ORGANIK
                      </th>
                  @endif
              </tr>
          @endif
      @endif --}}


      {{-- foreach B --}}
      @foreach ($laboratoriummethods as $laboratoriummethod)

        @if (
            kesmas_lhu_is_jenis($laboratoriummethod, 'kimia organik') && kesmas_lhu_include_parameter($laboratoriummethod))
            @php
              $hasil = cek_hasil_color(
                  isset($laboratoriummethod->hasil)
                      ? $laboratoriummethod->hasil
                      : (isset($laboratoriummethod->equal)
                          ? $laboratoriummethod->equal
                          : ''),
                  $laboratoriummethod->min,
                  $laboratoriummethod->max,
                  $laboratoriummethod->equal,
                  'result_output_method_' . $laboratoriummethod->method_id,
                  $laboratoriummethod->offset_baku_mutu,
              );

              $unit = $laboratoriummethod->shortname_unit;

              $unitAll = $laboratoriummethod->shortname_unit;

              if (isset($unit)) {
                  $unit = '';
                  if (trim($laboratoriummethod->shortname_unit) != '-' && trim($hasil) != '-') {
                      $unit = $laboratoriummethod->shortname_unit;
                  }
              } else {
                  $unit = '';
              }
            @endphp
          @if ($hasil != '-')

          <tr>
            <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
            <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
            <td width="20%" style="text-align: center">{!! $hasil !!}</td>
            <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
            <td width='15%' style="text-align: center">{!! $unitAll !!}</td>
            @if($laboratoriummethod->is_ready == 1)
              <td width="20%" style="text-align: left; padding-left: 2px;">{!! isset($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
            @else
              <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
            @endif
          </tr>
          @endif
        @endif
      @endforeach



    @endif
  @endif
  </tbody>
</table>
    <br>
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.kimia.components._signature')
</body>

</html>
