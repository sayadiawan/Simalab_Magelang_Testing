<html lang="">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>MIKRO-{{ $no_LHU }}</title>
  <link rel="shortcut icon" href="">
  <link rel="stylesheet" href="dist/css/bootstrap.min.css">
  @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_style')
</head>

<body style="margin: 10px; padding: 0">
  @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_kop')

  <br>

  <table width="100%" cellspacing="0" cellpadding="0">
    {{-- Nomor Agenda --}}
    <tr>
      <td width="0%">
        Nomor Agenda
      </td>
      <td width="0%">
        :
      </td>
      <td title="$no_LHU">
        {!! $no_LHU !!}
      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>

    {{-- Nomor Register --}}
    <tr>
      <td width="0%">
        Nomor Register
      </td>
      <td width="0%">
        :
      </td>
      <td title="(static)"> - </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>

    {{-- Nama Pelanggan --}}
    <tr>
      <td width="0%">
        Nama Pelanggan
      </td>
      <td width="0%">
        :
      </td>
      <td title="$sample->namaPelangganDisplay()">
        {{ $sample->namaPelangganDisplay() }}
      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>

    {{-- Alamat Register --}}
    <tr>
      <td width="0%">
        Alamat Register
      </td>
      <td width="0%">
        :
      </td>
      <td title="$sample->permohonanuji->customer->address_customer">
        {{ $sample->permohonanuji->customer->address_customer }}
      </td>
      <td width="150px">Petugas Sampling</td>
      <td width="0%">
        :
      </td>
      <td title="$sample->permohonanuji->petugas_penerima">
        {{ $sample->permohonanuji->petugas_penerima }}
      </td>
    </tr>

    {{-- Jenis Sampel --}}
    <tr>
      <td width="0%">
        Jenis Sampel
      </td>
      <td width="0%">
        :
      </td>
      <td title="$sample->name_sample_type">
        <b>
          {{ $sample->name_sample_type }}
        </b>
      </td>
      <td width="150px">Tanggal Sampling</td>
      <td width="0%">
        :
      </td>
      <td title="$checking_min and $done_max">
        {{ isset($checking_min)
          ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $checking_min)->isoFormat('D MMMM Y')
          : '-' }}
      s.d
      {{ isset($done_max) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $done_max)->isoFormat('D MMMM Y') : '-' }}
      </td>
    </tr>

    {{-- Jenis Sarana --}}
    <tr style="vertical-align: top">
      <td width="0%">
        Jenis Sarana
      </td>
      <td width="0%">
        :
      </td>
      <td title="(static)">
        <strong>
          -
        </strong>
      </td>
      <td width="150px">Parameter</td>
      <td width="0%">
        :
      </td>
      <td>
        <ul style="list-style: none; margin: 0; padding: 0; table">
          @foreach ($method_all as $method)
          <li> {{ $method->name_report }} </li>
          @endforeach
        </ul>
      </td>
    </tr>

    {{-- Metode Pemeriksaan --}}
    <tr>
      <td width="25%">
        Metode Pemeriksaan
      </td>
      <td width="0%">
        :
      </td>
      <td>
        <strong>
          -
        </strong>
      </td>
      <td></td>
      <td></td>
      <td></td>
    </tr>

    {{-- Hasil Pemeriksaan --}}
    <tr>
      <td width="0%">
        Hasil Pemeriksaan
      </td>
      <td width="0%">
        :
      </td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>

  <br>

  <table class="result" width="100%" border="1" cellspacing="0" cellpadding="0">
    <tr>
      <td style="text-align: center; width: 0%" rowspan="3">Nomor <br> Lab.</td>
      <td style="text-align: center;" rowspan="3">Lokasi</td>
      <td style="text-align: center; width: 0" rowspan="3">Jam Sampling</td>
      <td style="text-align: center; width: 0" colspan="{{ count($method_all) * 2 }}"> Parameter </td>
      <td style="text-align: center; width: 0" rowspan="3">Satuan</td>
      <td style="text-align: center; width: 0" rowspan="3">Ket</td>
    </tr>

    <tr>
      @foreach ($method_all as $method)
      <td style="text-align: center; width: 0%" colspan="2">{{ $method->name_report }}</td>
      @endforeach
    </tr>

    <tr align="center">
      @foreach (range(1, count($method_all)) as $num)
        <td> Hasil <br> Pemeriksaan </td>
        <td> Kadar <br> Maksimum yg <br> diperbolehkan </td>
      @endforeach
    </tr>

    @foreach ($table as $mytable)
      <tr>
        <td style="text-align: center">{{ $loop->iteration }}</td>
        <td style="text-align: center" title="$mytable['sample_type']->name_sample_type and $mytable['sample_type']->location_samples">
          {!! $mytable['sample_type']->name_sample_type !!} <br> {!! $mytable['sample_type']->location_samples !!}
        </td>
        <td style="text-align: center" width="100px" title="$mytable['sample_type']->datesampling_samples">
          {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', data_get($mytable, 'sample_type.datesampling_samples'))->isoFormat('D MMMM Y, HH:mm') }}
        </td>

        @foreach ($mytable['result'] as $result)
          <td style="text-align: center" title="$result->hasil">{!! data_get($result, 'hasil') !!}</td>
          <td style="text-align: center" title="$result->max">{!! data_get($result, 'nilai_baku_mutu') !!}</td>
        @endforeach

        <td style="text-align: center" title="$result->satuan_bakumutu">{!! $result['satuan_bakumutu'] !!}</td>
        <td title="(static)"> - </td>
      </tr>
    @endforeach
  </table>

  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td style="text-align: left">
        Keterangan :
      </td>
    </tr>
  </table>

  @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_baku_mutu')

  <br>

  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td style="text-align: left">Demikian hasil pemeriksaan ini untuk dapat digunakan seperlunya.</td>
    </tr>
  </table>

  <br>

  @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_signature')
</body>

</html>
