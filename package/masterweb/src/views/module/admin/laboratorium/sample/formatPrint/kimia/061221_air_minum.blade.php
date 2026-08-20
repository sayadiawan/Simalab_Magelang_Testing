<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>AirMinum-{!! $no_LHU !!}</title>
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

    /* @page {
      size: 794px 1248px;
      margin: 20px 20px 20px 20px;
    } */

    body {
      font-size: 12px;
    }

    .page_break {
      page-break-before: always;
    }
  </style>
</head>

<body>
  <div class="row text-center" id="header">
    <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="730px" class="img-fluid">
  </div>

  <div class="container">

    <div class="row batas">
      <div class="col-md-2"></div>
      <div class="col-md-8">
        <table width="100%" cellspacing="0" cellpadding="5">
          <tr>
            <td width="10%">
              Nomor
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {!! $no_LHU !!}
            </td>
            <td align="right">
              Magelang,
              {{ isset($pengesahan_hasil->pengesahan_hasil_date) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',
              $pengesahan_hasil->pengesahan_hasil_date)->isoFormat('D MMMM Y') : '' }}

            </td>
          </tr>
          <tr>
            <td width="10%">
              Hal
            </td>
            <td width="1%">
              :
            </td>
            <td>
              Hasil Pemeriksaan {{ $laboratorium->nama_laboratorium }}
            </td>
            <td align="right">

            </td>
          </tr>
        </table>

        <br>
        <table width="40%" cellspacing="0" cellpadding="5">
          <tr>
            <td colspan="2">
              Yang Terhormat :
            </td>

          </tr>
          <tr>
            <td colspan="2">
              {{ $sample->name_customer }}
            </td>
          </tr>
          <tr>
            <td colspan="2">
              {{ $sample->address_customer }}
            </td>

          </tr>
          <tr>
            <td colspan="2">
              di-
            </td>

          </tr>
          <tr>
            <td width="3%">
            </td>
            <td>
              <u>{{ $sample->kecamatan_customer }}</u>
            </td>
          </tr>
        </table>
        <br>
        <br>

        <table width="100%" cellspacing="0" cellpadding="5">
          <tr>
            <td>Disampaikan dengan hormat hasil pemeriksaan laboratorium kami adalah sebagai berikut:</td>
          </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="5">
          <tr>
            <td width="30%">
              No. Sampel
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $sample->codesample_samples }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Jenis sampel
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $sample->name_sample_type }}
            </td>
          </tr>
          <tr>
            <td width="30%">
              Lokasi sampel
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ $sample->location_samples }}
            </td>
          </tr>
          <tr>
            <td width="30%">
              Pengambil sampel
            </td>
            <td width="1%">
              :
            </td>
            <td>
              Petugas UPT Lab.Kes Estu Lentera Indo Teknologi
            </td>
          </tr>
          <tr>
            <td width="30%">
              Tanggal diambil/diterima
            </td>
            <td width="1%">
              :
            </td>
            <td>

              {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y') }}
            </td>
          </tr>
          <tr>
            <td width="30%">
              Tanggal pemeriksaan
            </td>
            <td width="1%">
              :
            </td>
            <td>
              {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->isoFormat('D MMMM Y') }} s.d
              {{ isset($sample->date_analitik_sample) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',
              $sample->date_analitik_sample)->isoFormat('D MMMM Y') : '-' }}
            </td>
          </tr>

          <tr>
            <td width="30%">
              Hasil pemeriksaan
            </td>
            <td width="1%">
              :
            </td>
            <td>
            </td>
          </tr>
        </table>

        <table width="100%" cellspacing="0" cellpadding="5" border="1" style="margin-top: 20px">
          <thead>
            <tr>
              <th width="5%" style="text-align: center">No</th>
              <th width="30%" style="text-align: center">Parameter Pemeriksaan</th>
              <th width="25%" style="text-align: center">Hasil Pemeriksaan</th>
              <th width="25%" style="text-align: center">Batas Syarat</th>
            </tr>
          </thead>

          <tbody>
            @if (count($laboratoriummethods) > 0)
            @php $kesmasUrutFallback = 0; @endphp
            @foreach ($laboratoriummethods as $laboratoriummethod)
            <tr>
              <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
              <td width="30%" style="text-align: center">{!! $laboratoriummethod->name_report !!}</td>
              @php
              $hasilRaw = $laboratoriummethod->hasil;
              if (isset($hasilRaw) && $hasilRaw !== '' && $hasilRaw !== '-') {
                $isOver = false;
                $minVal   = $laboratoriummethod->min ?? null;
                $maxVal   = $laboratoriummethod->max ?? null;
                $equalVal = $laboratoriummethod->equal ?? null;
                if (!is_null($minVal) && $minVal !== '' && is_numeric($hasilRaw) && is_numeric($minVal) && (float)$hasilRaw < (float)$minVal) $isOver = true;
                if (!is_null($maxVal) && $maxVal !== '' && is_numeric($hasilRaw) && is_numeric($maxVal) && (float)$hasilRaw > (float)$maxVal) $isOver = true;
                if (!is_null($equalVal) && $equalVal !== '' && $hasilRaw != $equalVal) $isOver = true;
                $hasil = $isOver ? "<span style='font-weight:bold'>" . $hasilRaw . '*</span>' : $hasilRaw;
              } else {
                $hasil = isset($hasilRaw) ? $hasilRaw : 'Masih Proses';
              }
                $unit = $laboratoriummethod->shortname_unit;
                if (isset($unit)) {
                $unit = '';
                if (trim($laboratoriummethod->shortname_unit) != '-' && trim($hasil) != '-') {
                $unit = $laboratoriummethod->shortname_unit;
                }
                } else {
                $unit = '';
                }
                @endphp
                <td width="25%" style="text-align: center">{!! $hasil . $unit !!}</td>
                <td width="25%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!} {!! $unit !!}
                </td>
            </tr>
            . {{ $laboratoriummethod->params_method }}

            @endforeach

            @endif

            </thead>
        </table>
        <table width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td style="text-align: left">Ket: Hasil pemeriksaan dengan tanda (*) tidak memenuhi syarat baku mutu yang ditetapkan</td>
          </tr>
        </table>
        <table width="100%" cellspacing="0" cellpadding="5">
          <tr>
            <td width="30%" style="vertical-align:top;">Rujukan baku mutu</td>
            <td width="1%" style="vertical-align:top;">:</td>
            <td width="69%" style="vertical-align:top;">
              <table width="100%" cellspacing="0">
                @if (count($all_acuan_baku_mutu) > 0)

                @if (count($all_acuan_baku_mutu) > 1)
                @php
                $no = 1;
                @endphp
                @foreach ($all_acuan_baku_mutu as $acuan_baku_mutu)
                <tr>
                  <td width="3%" style="vertical-align:top;">{{ $no }}.</td>
                  <td width="93%" style="vertical-align:top;">{{ $acuan_baku_mutu->title_library }}</td>
                </tr>

                @endforeach
                @else
                <tr>
                  <td width="100%" style="vertical-align:top;">{{ $all_acuan_baku_mutu[0]->title_library }}</td>
                </tr>

                @endif
                @endif
              </table>
            </td>
          </tr>
        </table>


        <table width="100%" cellspacing="0" cellpadding="5">
          <tr>
            <td>Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.</td>
          </tr>
        </table>


        <p>&nbsp;</p>




        <div class="row batas">

        </div>
        <div class="row batas" style="text-align: right;">
          <div class="col-md-8"></div>
          <div class="col-md-4 text-center">
            Kepala UPT Laboratorium Kesehatan<br>
            Kabupaten Boyolali<br>

            @if (isset($verifikasi))
            <img src="{{ asset('assets/admin/images/ttd.png') }}" width="100px" class="img-fluid" style="float: right;">
            <div style="clear: right">
            </div>
            @else
            <br>
            <br>
            <br>
            <br>
            @endif
            <strong><u>dr. Muharyati</u></strong><br>
            Pembina<br>
NIP. 19721106 200212 2 001
            {{-- <strong><u>{{$laboratorium->nama_ttd_kepala_laboratorium}}</u></strong><br>
            NIP.{{$laboratorium->nip_kepala_laboratorium}} --}}
          </div>
        </div>

      </div>

    </div>
</body>

</html>

