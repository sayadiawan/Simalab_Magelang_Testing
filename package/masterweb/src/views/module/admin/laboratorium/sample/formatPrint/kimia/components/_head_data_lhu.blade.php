<style>
    .head-data-lhu {
        border-collapse: collapse;
        width: 100%;
    }

    .head-data-lhu tr,
    .head-data-lhu td {
        vertical-align: top !important;
        padding: 3px 5px;
    }

    .head-data-lhu .col-label {
        width: 10%;
        white-space: nowrap;
    }

    .head-data-lhu .col-colon {
        width: 1%;
        white-space: nowrap;
    }
</style>

@php
    if (!isset($samples) && isset($sample)) {
        $samples = collect([$sample]);
    }

    $firstSample = $samples->first();

    $customerName = $firstSample->name_pelanggan ?? $firstSample->name_customer ?? '';

    $kecamatan = trim((string) ($firstSample->kecamatan_sampling_text ?? ''));
    $kabupaten = trim((string) ($firstSample->kabupaten_sampling_text ?? ''));

    if ($kecamatan === '' && $kabupaten === '') {
        $alamatSampel = trim((string) ($firstSample->address_customer ?? ''));
    } else {
        $alamatSampel = implode(', ', array_filter([$kecamatan, $kabupaten]));
    }

    $pengirim = $firstSample->name_sampling ?? $firstSample->pengirim_sample ?? '';
    if ($pengirim === '' || $pengirim === null) {
        $namaPengambil = $firstSample->namaPengambilDisplay('');
        if ($namaPengambil !== '' && $namaPengambil !== '-') {
            $pengirim = $namaPengambil;
        }
    }
    if ($pengirim === '' || $pengirim === null) {
        $pengirim = 'Petugas Dinas Kesehatan Kabupaten Magelang';
    }

    if (isset($firstSample->datesampling_samples)) {
        $tanggalDiterima = \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($firstSample->datesampling_samples);
    } elseif (isset($firstSample->date_sending)) {
        $tanggalDiterima = \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($firstSample->date_sending);
    } else {
        $tanggalDiterima = '-';
    }

    $extraSampleIdsTanggalDiperiksa = collect($samples ?? [])
        ->pluck('id_samples')
        ->filter()
        ->values()
        ->all();
    $tanggalDiperiksa = \Smt\Masterweb\Helpers\Smt::resolveTanggalDiperiksaKesmas(
        $firstSample,
        true,
        $extraSampleIdsTanggalDiperiksa
    );

    $nomorSampel = $firstSample->codesample_samples ?? '-';
    $jenisSampel = $firstSample->name_sample_type ?? '-';

    $titikSampel = preg_replace('/<\/?p[^>]*>/', '', $firstSample->titik_pengambilan ?? '');
    if (trim(strip_tags($titikSampel)) === '' && !empty($firstSample->location_samples)) {
        $titikSampel = preg_replace('/<\/?p[^>]*>/', '', $firstSample->location_samples);
    }
    $titikSampel = trim($titikSampel) !== '' ? $titikSampel : '-';

    $isMakminKimia = isset($firstSample->name_sample_type) &&
        $firstSample->name_sample_type === 'Makanan/Minuman/Lainnya';

    $namaJenisMakanan = '-';
    if (!empty($firstSample->jenis_makanan_id)) {
        $jenisMakanan = \Smt\Masterweb\Models\JenisMakanan::query()
            ->where('id_jenis_makanan', $firstSample->jenis_makanan_id)
            ->first();
        if ($jenisMakanan && !empty($jenisMakanan->name_jenis_makanan)) {
            $namaJenisMakanan = $jenisMakanan->name_jenis_makanan;
        }
    }

    $acuanBakuMutu = collect($all_acuan_baku_mutu ?? [])
        ->pluck('title_library')
        ->filter()
        ->unique()
        ->implode(', ');

    $tanpaJenisMakananKimia = $namaJenisMakanan === '-' ||
        trim((string) $namaJenisMakanan) === '' ||
        empty($firstSample->jenis_makanan_id);

    $sampleForNomorLab = $sample ?? $firstSample;
    if (!($sampleForNomorLab instanceof \Smt\Masterweb\Models\Sample) && !empty($sampleForNomorLab->id_samples)) {
        $sampleForNomorLab = \Smt\Masterweb\Models\Sample::find($sampleForNomorLab->id_samples);
    }
@endphp

<table class="head-data-lhu" cellspacing="0" cellpadding="0">
    <tr valign="top">
        <td valign="top" class="col-label">Nomor Laboratorium</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{!! $nomerLabDisplay ?? ($sampleForNomorLab ? $sampleForNomorLab->getNomorLab() : '-') !!}</td>
        <td valign="top" class="col-label">Diambil/dikirim oleh</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{{ $pengirim }}</td>
    </tr>
    <tr valign="top">
        <td valign="top" class="col-label">Nomor Sampel</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{{ $nomorSampel }}</td>
        <td valign="top" class="col-label">Diterima</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{!! $tanggalDiterima !!}</td>
    </tr>
    <tr valign="top">
        <td valign="top" class="col-label">Jenis Sampel</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{{ $jenisSampel }}</td>
        <td valign="top" class="col-label">Diperiksa</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{!! $tanggalDiperiksa !!}</td>
    </tr>
    <tr valign="top">
        <td valign="top" class="col-label">Asal Sampel</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">
            {!! $customerName !!}
            @if ($alamatSampel !== '')
                <br>{!! $alamatSampel !!}
            @endif
        </td>
        <td valign="top" class="col-label">Titik Sampel</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">{!! $titikSampel !!}</td>
    </tr>
    <tr valign="top">
        <td valign="top" class="col-label">Keterangan</td>
        <td valign="top" class="col-colon">:</td>
        <td valign="top">
            @php
                $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? $firstSample ?? null);
            @endphp
            @if ($keteranganMetodeCustom !== '')
                {{ $keteranganMetodeCustom }}
            @elseif ($isMakminKimia)
                @if ($tanpaJenisMakananKimia)
                    {{ $acuanBakuMutu !== '' ? $acuanBakuMutu : '-' }}
                @else
                    {{ $namaJenisMakanan }} ({{ $acuanBakuMutu !== '' ? $acuanBakuMutu : '-' }})
                @endif
            @elseif (isset($all_acuan_baku_mutu) && count($all_acuan_baku_mutu) > 0)
                @foreach ($all_acuan_baku_mutu as $acuan)
                    {{ $acuan->title_library }}@if (!$loop->last), @endif
                @endforeach
            @else
                -
            @endif
        </td>
        <td valign="top"></td>
        <td valign="top"></td>
        <td valign="top"></td>
    </tr>
</table>
