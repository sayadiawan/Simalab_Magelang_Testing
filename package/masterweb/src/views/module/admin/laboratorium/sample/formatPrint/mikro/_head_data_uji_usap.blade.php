<style>
    .head-data td {
        vertical-align: top !important;
    }
</style>
<table class="head-data">
    <tr>
        <td valign="top" style="vertical-align: top;">Kode Hasil</td>
        <td valign="top" style="vertical-align: top;">:</td>
        <td valign="top" style="vertical-align: top;">{!! $no_LHU !!}</td>
    </tr>
    <tr>
        <td valign="top" style="vertical-align: top;">Asal Sampel</td>
        <td valign="top" style="vertical-align: top;">:</td>
        <td valign="top" style="vertical-align: top;">
            {!! str_replace(['<p>', '</p>'], '', $sample->detailAlamatSamplingDisplay() ?? '') !!}
        </td>
    </tr>
    <tr>
        <td>Diambil/dikirim oleh</td>
        <td>:</td>
        <td>{{ $sample->namaPengambilDisplay() }}</td>
    </tr>
    <tr>
        <td>Diambil tanggal</td>
        <td>:</td>
        <td>{!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($sample->datesampling_samples) !!}</td>
    </tr>
    <tr>
        <td>Diterima tanggal</td>
        <td>:</td>
        <td>{!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($sample->date_sending) !!}</td>
    </tr>
    <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td>
            @php
                $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? null);

                if ($keteranganMetodeCustom !== '') {
                    echo e($keteranganMetodeCustom);
                } elseif (isset($all_acuan_baku_mutu) && count($all_acuan_baku_mutu) > 0) {
                    // Untuk uji usap, gunakan baku mutu dinamis
                    foreach ($all_acuan_baku_mutu as $index => $acuan) {
                        echo ($acuan->title_library ?? 'PERMENKES RI No 2 Tahun 2023') . '<br>';
                    }
                } else {
                    echo 'Persyaratan maksimal yang diperbolehkan sesuai dengan Standar Baku Mutu : <br>PERMENKES RI No 2 Tahun 2023';
                }
            @endphp
        </td>
    </tr>
</table>
