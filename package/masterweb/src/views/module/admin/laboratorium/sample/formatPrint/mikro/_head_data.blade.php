<style>
    .head-data td {
        vertical-align: top !important;
    }
</style>
<table class="head-data">
    <tr>
        <td valign="top" style="vertical-align: top; white-space: nowrap;">Nomor Laboratorium</td>
        <td valign="top" style="vertical-align: top;">:</td>
        <td valign="top" style="vertical-align: top;">{!! $nomerLabDisplay ?? $sample->getNomorLab() !!}</td>
    </tr>
    <tr>
        <td valign="top" style="vertical-align: top;">Asal Sampel</td>
        <td valign="top" style="vertical-align: top;">:</td>
        <td valign="top" style="vertical-align: top;">{!! str_replace(['<p>', '</p>'], '', $sample->detailAlamatSamplingDisplay()) !!}</td>
    </tr>
    <tr>
        <td>Pengirim Sampel</td>
        <td>:</td>
        <td>{{ $sample->namaPengambilDisplay() }}</td>
    </tr>
    <tr>
        <td>Tanggal Diterima</td>
        <td>:</td>
        <td>{!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($sample->date_sending) !!}</td>
    </tr>
    <tr>
        <td>Tanggal Diperiksa</td>
        <td>:</td>
        <td>
            {!! \Smt\Masterweb\Helpers\Smt::resolveTanggalDiperiksaKesmas($sample) !!}
        </td>
    </tr>
    <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td>@php
            $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? null);

            // Cek jenis sampel dari sample pertama
            $jenisSampel = isset($sample->name_sample_type) ? strtolower($sample->name_sample_type) : '';
            $isAirSample = strpos($jenisSampel, 'air') !== false;
        @endphp

            @if ($keteranganMetodeCustom !== '')
                {{ $keteranganMetodeCustom }}
            @elseif ($isAirSample)


                @if ($jenisSampel == 'air minum' || $jenisSampel == 'air bersih' || $jenisSampel == 'air higiene')
                    Persyaratan maksimal yang diperbolehkan sesuai dengan Standar Baku Mutu : <br>
                    PERMENKES RI No 2 Tahun 2023
                @else
                    @foreach ($all_acuan_baku_mutu as $index => $acuan)
                        {{ $acuan->title_library ?? 'PERMENKES RI No 2 Tahun 2023' }}<br>
                    @endforeach
                @endif
            @else
                {{-- Untuk jenis sampel lain (makanan, dll): gunakan baku mutu dinamis --}}
                @if (isset($all_acuan_baku_mutu) && count($all_acuan_baku_mutu) > 0)
                    @foreach ($all_acuan_baku_mutu as $index => $acuan)
                        {{ $acuan->title_library ?? 'PERMENKES RI No 2 Tahun 2023' }}<br>
                    @endforeach
                @else
                    Persyaratan maksimal yang diperbolehkan sesuai dengan Standar Baku Mutu : <br>
                    PERMENKES RI No 2 Tahun 2023
                @endif
            @endif
        </td>
    </tr>
</table>
