<style>
    table td {
        vertical-align: top;
    }

    .judul-surat {
        text-align: center;
        margin-bottom: 10px;
        font-size: 14px;
    }
</style>
<div class="judul-surat">
    <h3><u>LAPORAN HASIL UJI</u></h3>
</div>

<table width="100%" cellspacing="0" cellpadding="0">
    @php
        $extraSampleIdsTanggalDiperiksa = collect(isset($samples) ? $samples : (isset($all_samples) ? $all_samples : []))
            ->pluck('id_samples')
            ->filter()
            ->values()
            ->all();
        $tanggalPemeriksaan = \Smt\Masterweb\Helpers\Smt::resolveTanggalDiperiksaKesmas(
            $sample,
            true,
            $extraSampleIdsTanggalDiperiksa
        );
        $tanggalDiterima = isset($sample->date_sending)
            ? \Smt\Masterweb\Helpers\Smt::safeFormatDateIndo($sample->date_sending)
            : '-';
    @endphp
    <tr>
        <td width="15%" style="white-space: nowrap;">Nomor Laboratorium</td>
        <td width="1%">:</td>
        <td>{!! $nomerLabDisplay ?? $sample->getNomorLab() !!}</td>
        <td width="18%">Pengirim Sampel</td>
        <td width="1%">:</td>

        <td>{{ $sample->namaPengambilDisplay() }}</td>
    </tr>
    <tr>
        <td>Kode Sampel</td>
        <td>:</td>
        <td>{{ $sample->codesample_samples }}</td>
        <td>Diterima tanggal</td>
        <td>:</td>
        <td>{{ $tanggalDiterima }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>Tanggal Diperiksa</td>
        <td>:</td>
        <td>{{ $tanggalPemeriksaan }}</td>
    </tr>
    <tr>
        <td>Jenis Sampel</td>
        <td>:</td>
        <td>{{ $sample->name_sample_type }}</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Asal Sampel</td>
        <td>:</td>
        <td>
            @if ($sample->is_pudam == 1)
                @if (isset($sample->location_samples))
                    @php
                        $location = str_replace("\n", '<br>', $sample->location_samples);
                        $location = str_replace(
                            '<div id="simple-translate" class="simple-translate-system-theme">&nbsp;</div>',
                            '',
                            $location,
                        );
                        $location = str_replace('<p>', '', $location);
                        $location = str_replace('</p>', '', $location);

                    @endphp


                    {!! $location !!}
                @else
                    {{ $sample->name_customer_pdam }}
                @endif
            @else
                @php
                    $location = str_replace("\n", '<br>', $sample->location_samples);

                    $location = str_replace('<p>', '', $location);
                    $location = str_replace('</p>', '', $location);

                @endphp


                {!! $location !!}
            @endif
        </td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Titik Sampel</td>
        <td>:</td>
        <td>
            @php
                $titikPengambilan = str_replace('<p>', '', $sample->titik_pengambilan);
                $titikPengambilan = str_replace('</p>', '', $titikPengambilan);
            @endphp
            {!! $titikPengambilan !!}
        </td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td style="vertical-align:top">Keterangan</td>
        <td style="vertical-align:top">:</td>
        <td colspan="4">
            @php
                $keteranganMetodeCustom = \Smt\Masterweb\Helpers\Smt::getSavedKeteranganMetodeKesmas($sample ?? null);
            @endphp
            @if ($keteranganMetodeCustom !== '')
                {{ $keteranganMetodeCustom }}
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
