<div style="float: left; max-width: 50%">
  <table>
    {{-- Nomor Agenda --}}
    <tr>
      <td style="width: 170px">
        Nomor Agenda
      </td>
      <td>
        :
      </td>
      <td>
        {!! $no_LHU !!}
      </td>
    </tr>

    {{-- Nomor Register --}}
    <tr>
      <td>
        Nomor Register
      </td>
      <td>
        :
      </td>
      <td>
        <ol style="list-style: none; margin: 0; padding: 0; white-space:nowrap">
          {{-- @foreach ($table as $mytable)
<li> {{ data_get($mytable, 'sample_type.codesample_samples') }} </li>
@endforeach --}}
          {{ $lab_string }}
        </ol>
      </td>
    </tr>

    {{-- Nama Pelanggan --}}
    <tr>
      <td>
        Nama Pelanggan
      </td>
      <td>
        :
      </td>
      <td>
        <b>

                    <span>
                      @php
                        $namaPelanggan = $sample->namaPelangganDisplay();
                        if (str_contains($namaPelanggan, 'π')){
                          $namaPelanggan = str_replace('π', "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>", $namaPelanggan);
                        }

                        if (str_contains($namaPelanggan, '&pi;')){
                          $namaPelanggan = str_replace('&pi;', "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>", $namaPelanggan);
                        }
                      @endphp
                      {!! $namaPelanggan !!}
                    </span>

        </b>
      </td>
    </tr>

    {{-- Alamat Register --}}
    <tr>
      <td>
        Alamat Register
      </td>
      <td>
        :
      </td>
      <td style="width: 380px;">
        @php
            $address = $sample->customerAddressDisplay('');
        @endphp
        {{ $address }}
      </td>
    </tr>

    {{-- Jenis Sampel --}}
    <tr>
      <td>
        Jenis Sampel
      </td>
      <td>
        :
      </td>
      <td>
        <b>


          @php
            $foodType = '';
            if ($sample->namaJenisMakananPlain('') !== '') {
                $foodType = '(' . $sample->namaJenisMakananPlain() . ')';
            }
          @endphp
          {{ $sample->name_sample_type }} {{ $foodType }}
        </b>
      </td>
    </tr>

    {{-- Jenis Sarana --}}
    <tr style="vertical-align: top">
      <td>
        Jenis Sarana
      </td>
      <td>
        :
      </td>
      <td>
        <strong>
          @php
            $saranas = [];
            $arraySarana = [];

            foreach ($all_samples as $sarana){
              $arraySarana[] = $sarana->jenis_sarana_names;
            }

            $arraySarana = array_unique(array_filter(array_map('trim', $arraySarana)));
            if (count($arraySarana) > 1) {
                $last = array_pop($arraySarana);
                $jenisSarana = implode(', ', $arraySarana) . ' dan ' . $last;
            } else {
                $jenisSarana = $arraySarana[0] ?? '';
            }
          @endphp
          {{ $jenisSarana }}
        </strong>
      </td>
    </tr>

    <tr>
      <td colspan="3">
        <br>
      </td>
    </tr>

    {{-- Metode Pemeriksaan --}}
    <tr>
      <td>
        Metode Pemeriksaan
      </td>
      <td>
        :
      </td>
      <td>
        <ol style="list-style: none; margin: 0; padding: 0; white-space:nowrap; font-weight: bold">
          @if ($method_all[0]->sampletype_id == '939ea9c5-f562-43d3-a7c2-75203c990a53')
            <li>ALT</li>
          @else
            @foreach ($metode_pemeriksaan as $metode)
              <li>{{ $metode }}</li>
            @endforeach
          @endif
        </ol>
      </td>
    </tr>

    {{-- Hasil Pemeriksaan --}}
    <tr>
      <td>
        Hasil Pemeriksaan
      </td>
      <td>
        :
      </td>
      <td></td>
    </tr>
  </table>
</div>

<div style="float: right; max-width: 44%">
  <table style="margin-left: auto">
    {{-- Nomor Agenda --}}
    <tr>
      <td colspan="3">
        <br>
      </td>
    </tr>

    {{-- Alamat Register --}}
    <tr>
      <td style="width: 150px">Petugas Sampling</td>
      <td>
        :
      </td>
      <td>
        {{ $sample->namaPengambilDisplay() }}
      </td>
    </tr>

    {{-- Jenis Sampel --}}
    <tr>
      <td>Tanggal Sampling</td>
      <td>
        :
      </td>
      <td>
        @if (count($tanggal_pengambilan) > 2)
          <ul style="list-style: none; margin: 0; padding: 0;">
            @foreach ($tanggal_pengambilan as $tanggal)
              <li>
                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $tanggal)->isoFormat('D MMMM Y') }}
                @if (!$loop->last)
                  <span>,</span>
                @endif
              </li>
            @endforeach
          </ul>
        @elseif(count($tanggal_pengambilan) == 2)
          {{ \Carbon\Carbon::createFromFormat('Y-m-d', head($tanggal_pengambilan ?? []))->isoFormat('D MMMM Y') }}
          <span> & </span>
          {{ \Carbon\Carbon::createFromFormat('Y-m-d', last($tanggal_pengambilan ?? []))->isoFormat('D MMMM Y') }}
        @else
          {{ \Carbon\Carbon::createFromFormat('Y-m-d', head($tanggal_pengambilan ?? []))->isoFormat('D MMMM Y') }}
        @endif
      </td>
    </tr>

    {{-- Jenis Sarana --}}
    <tr style="vertical-align: top">
      <td>Parameter</td>
      <td>
        :
      </td>
      <td>
        <ul style="list-style: none; margin: 0; padding: 0;">
          @foreach ($method_all as $method)
            @if ($method->sampletype_id != 'ba2aa1af-02c7-4656-ad00-6e19d22f5eb2')
              <li> {{ $method->name_report }} </li>
            @endif
          @endforeach



          @if ($method->sampletype_id == 'ba2aa1af-02c7-4656-ad00-6e19d22f5eb2')
            <li> Total Coliform</li>
          @endif
        </ul>
      </td>
    </tr>

    {{-- Metode Pemeriksaan --}}
    <tr>
      <td></td>
      <td></td>
      <td></td>
    </tr>

    {{-- Hasil Pemeriksaan --}}
    <tr>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </table>
</div>

<div class="clearfix" style="clear: both"></div>
