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
    <td title="(static)"> - (Static) </td>
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
        - (static)
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
        - (static)
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
