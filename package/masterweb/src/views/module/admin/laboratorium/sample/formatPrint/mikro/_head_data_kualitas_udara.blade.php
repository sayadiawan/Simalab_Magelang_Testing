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
            {!! str_replace(['<p>', '</p>'], '', $sample->location_samples ?? $sample->detailAlamatSamplingDisplay() ?? '') !!}
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
                    // Untuk kualitas udara, gunakan baku mutu dinamis
                    foreach ($all_acuan_baku_mutu as $index => $acuan) {
                        echo ($acuan->title_library ?? 'PERMENKES RI No 2 Tahun 2023') . '<br>';
                    }
                } else {
                    // Default baku mutu untuk kualitas udara
                    if (isset($x_baku_mutu) && is_array($x_baku_mutu) && count($x_baku_mutu) > 0) {
                        foreach ($x_baku_mutu as $baku_mutu) {
                            echo $baku_mutu . '<br>';
                        }
                    } else {
                        echo 'Peraturan Menteri Kesehatan No. 7 Tahun 2019 tentang Kesehatan Lingkungan Rumah Sakit<br>';
                        echo 'Permenkes No. 2 Tahun 2023 tentang Peraturan Pelaksanaan Peraturan Pemerintah Nomor 66 tentang Kesehatan Lingkungan';
                    }
                }
            @endphp
        </td>
    </tr>
</table>

