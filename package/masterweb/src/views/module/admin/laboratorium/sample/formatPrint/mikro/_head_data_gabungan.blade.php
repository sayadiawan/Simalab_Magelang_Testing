<style>
    .head-data td {
        vertical-align: top !important;
    }
</style>
<table class="head-data">
    <tr>
        <td valign="top" style="vertical-align: top;">Kode Hasil</td>
        <td valign="top" style="vertical-align: top;">:</td>
        <td valign="top" style="vertical-align: top;">
            {!! $data['no_agenda'] !!}
        </td>
    </tr>
    <tr>
        <td valign="top" style="vertical-align: top;">Asal Sampel</td>
        <td valign="top" style="vertical-align: top;">:</td>
        <td valign="top" style="vertical-align: top;">
            @php
                $asalSampel = $data['permohonanuji']->detail_alamat_sampling ?? '';
                $asalSampel = str_replace(['<p>', '</p>'], '', $asalSampel);
                $asalSampel = trim($asalSampel);
            @endphp
            {!! $asalSampel !!}
        </td>
    </tr>
    <tr>
        <td>Diambil/dikirim oleh</td>
        <td>:</td>
        <td>{{ $data['permohonanuji']->name_sampling }}</td>
    </tr>
    <tr>
        <td>Diambil tanggal</td>
        <td>:</td>
        <td>{!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($data['sample']->datesampling_samples) !!}</td>
    </tr>
    <tr>
        <td>Diterima tanggal</td>
        <td>:</td>
        <td>{!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($data['sample']->date_sending) !!}</td>
    </tr>
    <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td>
            @php
                $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($data['sample'] ?? null);
            @endphp
            @if ($keteranganMetodeCustom !== '')
                {{ $keteranganMetodeCustom }}
            @else
                Persyaratan maksimal yang diperbolehkan sesuai dengan Standar Baku Mutu : <br> 1. PERMENKES RI No 2 Tahun
                2023
            @endif
        </td>
    </tr>
</table>
