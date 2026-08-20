<style>
    table td {
        vertical-align: top;
    }
</style>
<table>
    <tr>
        <td style="white-space: nowrap;">Nomor Laboratorium</td>
        <td>:</td>
        <td>{!! $nomerLabDisplay ?? $sample->getNomorLab() !!}</td>
    </tr>
    <tr>
        <td>Asal Sampel</td>
        <td>:</td>
        <td>
            @php
                $firstSample = $samples->first();
                $customerName = $firstSample->name_pelanggan;

                $kecamatan = trim((string) ($firstSample->kecamatan_sampling_text ?? ''));
                $kabupaten = trim((string) ($firstSample->kabupaten_sampling_text ?? ''));

                if ($kecamatan === '' && $kabupaten === '') {
                    $alamatSampel = trim((string) ($firstSample->address_customer ?? ''));
                } else {
                    $alamatSampel = implode(', ', array_filter([$kecamatan, $kabupaten]));
                }
            @endphp
            {!! $customerName !!}
            <br>
            {!! $alamatSampel !!}
        </td>
    </tr>
    <tr>
        <td>Pengirim Sampel</td>
        <td>:</td>
        <td>
            @php
                $pengirimSampel = optional($firstSample->permohonanuji)->pengirim_sample
                    ?? ($firstSample->pengirim_sample ?? '');
                if ($pengirimSampel === '' || $pengirimSampel === null) {
                    $pengirimSampel = 'Petugas Dinas Kesehatan Kabupaten Magelang';
                }
            @endphp
            {{ $pengirimSampel }}
        </td>
    </tr>
    <tr>
        <td>Pengambil Sampel</td>
        <td>:</td>
        <td>
            @php
                $pengambilSampel = $firstSample->name_sampling ?? '';
                if ($pengambilSampel === '' || $pengambilSampel === null) {
                    $namaPengambil = $firstSample->namaPengambilDisplay('');
                    if ($namaPengambil !== '' && $namaPengambil !== '-') {
                        $pengambilSampel = $namaPengambil;
                    }
                }
                if ($pengambilSampel === '' || $pengambilSampel === null) {
                    $pengambilSampel = '-';
                }
            @endphp
            {{ $pengambilSampel }}
        </td>
    </tr>
    <tr>
        <td>Tanggal Diterima</td>
        <td>:</td>
        <td>
            @if (isset($firstSample->datesampling_samples))
                {!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($firstSample->datesampling_samples) !!}
            @else
                -
            @endif
        </td>
    </tr>
    <tr>
        <td>Tanggal Diperiksa</td>
        <td>:</td>
        <td>
            @php
                $sampleForTanggalDiperiksa = $firstSample ?? $sample ?? null;
                $extraSampleIdsTanggalDiperiksa = collect($samples ?? $all_samples ?? [])
                    ->pluck('id_samples')
                    ->filter()
                    ->values()
                    ->all();
            @endphp
            {!! \Smt\Masterweb\Helpers\Smt::resolveTanggalDiperiksaKesmas(
                $sampleForTanggalDiperiksa,
                true,
                $extraSampleIdsTanggalDiperiksa
            ) !!}
        </td>
    </tr>
    <tr>
        <td style="vertical-align:top">Keterangan</td>
        <td style="vertical-align:top">:</td>
        <td>
            @php
                $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? $firstSample ?? null);

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
    </tr>
    
</table>

