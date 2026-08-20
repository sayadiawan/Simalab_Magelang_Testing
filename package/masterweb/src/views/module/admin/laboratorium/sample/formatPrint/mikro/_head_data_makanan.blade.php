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
        <td valign="top" style="vertical-align: top;">
            @php
                // Rapikan asal sampel agar tidak terlalu banyak line spacing
                $asalSampel = $sample->detailAlamatSamplingDisplay() ?? '-';
                $asalSampel = preg_replace('/<br\\s*\\/?>/i', "\n", $asalSampel);
                $asalSampel = strip_tags($asalSampel);
                $asalSampel = str_replace(["\r\n", "\r"], "\n", $asalSampel);
                $asalSampel = preg_replace("/\n{2,}/", "\n", $asalSampel);
                $asalSampel = trim($asalSampel);
                if ($asalSampel === '') {
                    $asalSampel = '-';
                }
            @endphp
            {!! nl2br(e($asalSampel)) !!}
        </td>
    </tr>
    <tr>
        <td>Pengirim Sampel</td>
        <td>:</td>
        <td>
            @php
                $pengirimSampel = optional($sample->permohonanuji)->pengirim_sample
                    ?? ($sample->pengirim_sample ?? '');
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
                $pengambilSampel = $sample->namaPengambilDisplay('');
                if ($pengambilSampel === '' || $pengambilSampel === '-') {
                    $pengambilSampel = '-';
                }
            @endphp
            {{ $pengambilSampel }}
        </td>
    </tr>
    <tr>
        <td>Tanggal Diterima</td>
        <td>:</td>
        <td>{!! \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($sample->datesampling_samples) !!}</td>
    </tr>
    <tr>
        <td>Tanggal Diperiksa</td>
        <td>:</td>
        <td>
            @php
                $extraSampleIdsTanggalDiperiksa = collect($all_samples ?? [])
                    ->pluck('id_samples')
                    ->filter()
                    ->values()
                    ->all();
            @endphp
            {!! \Smt\Masterweb\Helpers\Smt::resolveTanggalDiperiksaKesmas($sample, false, $extraSampleIdsTanggalDiperiksa) !!}
        </td>
    </tr>
    <tr>
        <td>Keterangan</td>
        <td>:</td>
        <td>
            @php
                $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? null);

                $isMakminMikro = isset($sample->name_sample_type) &&
                    $sample->name_sample_type === 'Makanan/Minuman/Lainnya';

                $acuanBakuMutu = collect($all_acuan_baku_mutu ?? [])
                    ->pluck('title_library')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                $jenisMakananIdsInPrint = collect($all_samples ?? [])
                    ->pluck('jenis_makanan_id')
                    ->filter(function ($id) {
                        return $id !== null && $id !== '';
                    })
                    ->unique()
                    ->values();

                $hasRequestJenisMakanan = request()->filled('jenis_makanan_id');
                $multiJenisTanpaFilter = !$hasRequestJenisMakanan && $jenisMakananIdsInPrint->count() > 1;

                $jenisMakananIdForKeterangan = null;
                if ($hasRequestJenisMakanan) {
                    $jenisMakananIdForKeterangan = request('jenis_makanan_id');
                } elseif (!$multiJenisTanpaFilter) {
                    if ($jenisMakananIdsInPrint->count() === 1) {
                        $jenisMakananIdForKeterangan = $jenisMakananIdsInPrint->first();
                    } elseif (!empty($sample->jenis_makanan_id)) {
                        $jenisMakananIdForKeterangan = $sample->jenis_makanan_id;
                    }
                }

                $namaJenisMakanan = '-';
                if (!empty($jenisMakananIdForKeterangan)) {
                    $jenisMakanan = \Smt\Masterweb\Models\JenisMakanan::query()
                        ->where('id_jenis_makanan', $jenisMakananIdForKeterangan)
                        ->first();
                    if ($jenisMakanan && !empty($jenisMakanan->name_jenis_makanan)) {
                        $namaJenisMakanan = $jenisMakanan->name_jenis_makanan;
                    }
                }

                $tanpaJenisMakananMikro = $multiJenisTanpaFilter ||
                    $namaJenisMakanan === '-' ||
                    trim((string) $namaJenisMakanan) === '' ||
                    empty($jenisMakananIdForKeterangan);
            @endphp

            @if ($keteranganMetodeCustom !== '')
                {{ $keteranganMetodeCustom }}
            @elseif ($isMakminMikro)
                @if ($tanpaJenisMakananMikro)
                    @if ($acuanBakuMutu !== '')
                        {{ $acuanBakuMutu }}
                    @else
                        Acuan Baku Mutu
                    @endif
                @else
                    {{ $namaJenisMakanan }} ({{ $acuanBakuMutu !== '' ? $acuanBakuMutu : '-' }})
                @endif
            @elseif (isset($all_acuan_baku_mutu) && count($all_acuan_baku_mutu) > 0)
                @foreach ($all_acuan_baku_mutu as $acuan)
                    {{ $acuan->title_library }}@if (!$loop->last), @endif
                @endforeach
            @else
                Acuan Baku Mutu
            @endif
        </td>
    </tr>

</table>
