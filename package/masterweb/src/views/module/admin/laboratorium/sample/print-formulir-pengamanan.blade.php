<!DOCTYPE html>
<html lang="id">

@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 11.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.4;
    $padding = isset($padding) ? (float) $padding : 4.0;
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengamanan Sampel {{ $laboratorium->kode_laboratorium == 'KIM' ? 'Kimia' : 'Mikrobiologi' }}</title>
    <style>
        @page {
            size: 794px 1248px;
            margin: 0px 30px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
            padding: 40px 40px 0px 40px;
        }

        .header {
            margin-bottom: 10px;
        }

        .header img {
            width: 100%;
            display: block;
        }

        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 10px 0 10px 0;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: {{ $padding }}px 5px;
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
        }

        th {
            font-weight: bold;
        }

        .info-table td {
            vertical-align: top;
        }

        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid black;
            margin-right: 3px;
            vertical-align: middle;
            text-align: center;
            line-height: 8px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            position: relative;
            bottom: 1px;
        }

        .checkbox.checked {
            font-weight: bold;
        }

        .signature-box {
            min-height: 50px;
            text-align: center;
            padding: 4px;
        }

        .signature-img {
            max-height: 45px;
            max-width: 100%;
        }

        .notes-box {
            min-height: 30px;
            padding: 4px;
        }

        .text-small {
            font-size: 9px;
        }

        sub {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline;
            bottom: -0.25em;
        }
    </style>
</head>

<body>
    <div class="header">
        @if ($showKop)
            <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%">
        @else
            <div style="height: 120px;"></div>
        @endif
    </div>

    <div class="title">
        FORMULIR RANGKAIAN PENGAMANAN SAMPEL {{ $laboratorium->kode_laboratorium == 'KIM' ? 'KIMIA' : 'MIKROBIOLOGI' }}
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 25%;"><strong>Tanggal diterima sampel:</strong></td>
            <td style="width: 40%;">
                {{ $penerimaan_sample && $penerimaan_sample->penerima_tanggal ? \Carbon\Carbon::parse($penerimaan_sample->penerima_tanggal)->format('d/m/Y H:i') : '' }}
            </td>
            <td rowspan="2" style="width: 35%; text-align: left; vertical-align: top;">
                <strong>Jumlah sampel: <span
                        style="font-size: 16px; font-weight: bold;">{{ $samples->count() }}</span></strong>
            </td>
        </tr>
        <tr>
            <td style="width: 25%;"><strong>Tanggal selesai pengujian:</strong></td>
            <td style="width: 40%;">
                @if (isset($pengetikan_laporan->is_done))

                    @if ($pengetikan_laporan && $pengetikan_laporan->is_done == 1)
                        {{ \Carbon\Carbon::parse($pengetikan_laporan->stop_date)->format('d/m/Y H:i') }}
                    @endif
                @endif
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td colspan="2" style="width: 50%;"><strong>Disposisi penerima sampel ke Koordinator Kesmas:</strong>
            </td>
            <td colspan="2" style="width: 50%;"><strong>Disposisi Koordinator Kesmas ke Analis:</strong></td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Tanggal:</strong>
                {{ $penerimaan_sample && $penerimaan_sample->disposisi_tanggal ? \Carbon\Carbon::parse($penerimaan_sample->disposisi_tanggal)->format('d/m/Y H:i') : '' }}
            </td>
            <td colspan="2">
                <strong>Tanggal:</strong>
                {{ $penerimaan_sample && $penerimaan_sample->disposisi_analis_tanggal ? \Carbon\Carbon::parse($penerimaan_sample->disposisi_analis_tanggal)->format('d/m/Y H:i') : '' }}
            </td>
        </tr>
        <tr>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Penerima sampel:</strong></td>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Koordinator Kesmas:</strong></td>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Koordinator Kesmas:</strong></td>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Analis:</strong></td>
        </tr>
        <tr>
            <td style="width: 25%;" class="signature-box">
                @if ($penerimaan_sample && $penerimaan_sample->penerima_signature)
                    <img src="{{ $penerimaan_sample->penerima_signature }}" class="signature-img"
                        alt="Tanda Tangan Penerima">
                    <div class="text-small">{{ $penerimaan_sample->penerima_sampel }}</div>
                @endif
            </td>
            <td style="width: 25%;" class="signature-box">

                @if ($penerimaan_sample && $penerimaan_sample->disposisi_signature)
                    <img src="{{ $penerimaan_sample->disposisi_signature }}" class="signature-img"
                        alt="Tanda Tangan Koordinator">
                    <div class="text-small">{{ $penerimaan_sample->disposisi_koordinator_kesmas }}</div>
                @endif
            </td>
            <td style="width: 25%;" class="signature-box">
                @if ($penerimaan_sample && $penerimaan_sample->disposisi_signature)
                    <img src="{{ $penerimaan_sample->disposisi_signature }}" class="signature-img"
                        alt="Tanda Tangan Koordinator">
                    <div class="text-small">{{ $penerimaan_sample->disposisi_koordinator_kesmas }}</div>
                @endif
            </td>
            <td style="width: 25%;" class="signature-box">
                @if ($penerimaan_sample && $penerimaan_sample->disposisi_analis_signature)
                    <img src="{{ $penerimaan_sample->disposisi_analis_signature }}" class="signature-img"
                        alt="Tanda Tangan Analis">
                    <div class="text-small">{{ $penerimaan_sample->disposisi_analis }}</div>
                @endif
            </td>
        </tr>
    </table>


    <table class="info-table">
        <tr>
            <td colspan="2" style="width: 50%;"><strong>Verifikasi Koordinator Teknik Laboratorium:</strong></td>
            <td colspan="2" style="width: 50%;"><strong>Pengetikan Laporan Hasil Uji:</strong></td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Tanggal:</strong>
                @if (isset($verifikasi_koordinator))

                    @if ($verifikasi_koordinator && $verifikasi_koordinator->is_done == 1)
                        {{ \Carbon\Carbon::parse($verifikasi_koordinator->start_date)->format('d/m/Y H:i') }}
                    @endif
                @endif
            </td>
            <td colspan="2">
                <strong>Tanggal:</strong>
                @if (isset($pengetikan_laporan))

                    @if ($pengetikan_laporan && isset($pengetikan_laporan->start_date))
                        {{ \Carbon\Carbon::parse($pengetikan_laporan->start_date)->format('d/m/Y H:i') }}
                    @endif
                @endif
            </td>
        </tr>
        <tr>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Analis:</strong></td>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Koordinator Kesmas:</strong></td>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Koordinator Kesmas:</strong></td>
            <td style="width: 25%; vertical-align: top; padding: 3px;"><strong>Paraf Petugas Adm. Lab.:</strong></td>
        </tr>
        <tr>
            <td style="width: 25%; min-height: 40px;" class="signature-box">
                @if (
                    $verifikasi_koordinator &&
                        $verifikasi_koordinator->is_done == 1 &&
                        $penerimaan_sample &&
                        $penerimaan_sample->disposisi_analis_signature)
                    <img src="{{ $penerimaan_sample->disposisi_analis_signature }}" class="signature-img"
                        alt="Tanda Tangan Analis">
                    @if ($penerimaan_sample && $penerimaan_sample->disposisi_analis)
                        <div class="text-small">{{ $penerimaan_sample->disposisi_analis }}</div>
                    @endif
                @else
                    <div style="min-height: 40px;">&nbsp;</div>
                @endif
            </td>
            <td style="width: 25%; min-height: 40px;" class="signature-box">
                @if (
                    $verifikasi_koordinator &&
                        $verifikasi_koordinator->is_done == 1 &&
                        $penerimaan_sample &&
                        $penerimaan_sample->disposisi_signature)
                    <img src="{{ $penerimaan_sample->disposisi_signature }}" class="signature-img"
                        alt="Tanda Tangan Koordinator">
                    @if ($penerimaan_sample && $penerimaan_sample->disposisi_koordinator_kesmas)
                        <div class="text-small">{{ $penerimaan_sample->disposisi_koordinator_kesmas }}</div>
                    @endif
                @else
                    <div style="min-height: 40px;">&nbsp;</div>
                @endif
            </td>
            <td style="width: 25%; min-height: 40px;" class="signature-box">
                @if (isset($pengetikan_laporan->is_done))
                    @if ($penerimaan_sample && $penerimaan_sample->disposisi_signature)
                        <img src="{{ $penerimaan_sample->disposisi_signature }}" class="signature-img"
                            alt="Tanda Tangan Koordinator">
                        @if ($penerimaan_sample && $penerimaan_sample->disposisi_koordinator_kesmas)
                            <div class="text-small">{{ $penerimaan_sample->disposisi_koordinator_kesmas }}</div>
                        @endif
                    @else
                        <div style="min-height: 40px;">&nbsp;</div>
                    @endif
                @endif
            </td>
            <td style="width: 25%; min-height: 40px;" class="signature-box">
                @if (isset($pengetikan_laporan->is_done))

                    @if ($penerimaan_sample && $penerimaan_sample->disposisi_signature)
                        <img src="{{ $penerimaan_sample->disposisi_signature }}" class="signature-img"
                            alt="Tanda Tangan Petugas Adm Lab">
                        @if ($penerimaan_sample && $penerimaan_sample->disposisi_koordinator_kesmas)
                            <div class="text-small">{{ $penerimaan_sample->disposisi_koordinator_kesmas }}</div>
                        @endif
                    @else
                        <div style="min-height: 40px;">&nbsp;</div>
                    @endif
                @endif
            </td>
        </tr>
    </table>

    @php
        // Hitung mayoritas pengawetan dilakukan oleh siapa
        $count_pelanggan = 0;
        $count_laboratorium = 0;
        foreach ($samples as $s) {
            $p = isset($penerimaan_per_sample[$s->id_samples]) ? $penerimaan_per_sample[$s->id_samples] : null;
            if ($p) {
                if ($p->pengawetan_oleh == 'Pelanggan') {
                    $count_pelanggan++;
                } elseif ($p->pengawetan_oleh == 'Laboratorium') {
                    $count_laboratorium++;
                }
            }
        }
        $mayoritas_pengawetan = $count_pelanggan >= $count_laboratorium ? 'Pelanggan' : 'Laboratorium';
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 13%;">No. Sampel</th>
                <th style="width: 18%;">Jenis Sampel</th>
                <th style="width: 30%;">Parameter</th>
                <th style="width: 35%;">
                    <div style="line-height: 1.4; font-size: 10px;">
                        <strong>Pengawetan dilakukan oleh:</strong><br><br>
                        <span
                            class="checkbox {{ $mayoritas_pengawetan == 'Pelanggan' ? 'checked' : '' }}">{{ $mayoritas_pengawetan == 'Pelanggan' ? '✔' : '' }}</span>
                        Pelanggan &nbsp;
                        <span
                            class="checkbox {{ $mayoritas_pengawetan == 'Laboratorium' ? 'checked' : '' }}">{{ $mayoritas_pengawetan == 'Laboratorium' ? '✔' : '' }}</span>
                        Laboratorium<br>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($samples as $index => $sample)
                @php
                    $penerimaan = isset($penerimaan_per_sample[$sample->id_samples])
                        ? $penerimaan_per_sample[$sample->id_samples]
                        : null;

                    // Parse pengawetan
                    $pengawetan_dengan = [];
                    if ($penerimaan && $penerimaan->pengawetan_dengan) {
                        $pengawetan_dengan = array_map('trim', explode('; ', $penerimaan->pengawetan_dengan));
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $sample->codesample_samples }}</td>
                    <td>{{ $sample->name_sample_type }}</td>
                    <td style="font-size: 10px; line-height: 1.3;">
                        @if ($sample->parameters)
                            @foreach ($sample->parameters as $method)
                                {{ $method->params_method }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        @endif
                    </td>
                    <td style="font-size: 10px; line-height: 1.4;">
                        @if ($penerimaan)
                            <span
                                class="checkbox {{ in_array('Pendinginan', $pengawetan_dengan) ? 'checked' : '' }}">{{ in_array('Pendinginan', $pengawetan_dengan) ? '✔' : '' }}</span>
                            Pendinginan<br>
                            <span
                                class="checkbox {{ in_array('HNO3', $pengawetan_dengan) ? 'checked' : '' }}">{{ in_array('HNO3', $pengawetan_dengan) ? '✔' : '' }}</span>
                            HNO<sub>3</sub><br>
                            <span
                                class="checkbox {{ in_array('H2SO4', $pengawetan_dengan) ? 'checked' : '' }}">{{ in_array('H2SO4', $pengawetan_dengan) ? '✔' : '' }}</span>
                            H<sub>2</sub>SO<sub>4</sub><br>
                            <span
                                class="checkbox {{ in_array('NaOH', $pengawetan_dengan) ? 'checked' : '' }}">{{ in_array('NaOH', $pengawetan_dengan) ? '✔' : '' }}</span>
                            NaOH<br>
                            @php
                                $lainnya = '';
                                foreach ($pengawetan_dengan as $item) {
                                    if (stripos($item, 'lainnya:') === 0) {
                                        $lainnya = trim(substr($item, 8));
                                        break;
                                    }
                                }
                            @endphp
                            <span
                                class="checkbox {{ !empty($lainnya) ? 'checked' : '' }}">{{ !empty($lainnya) ? '✔' : '' }}</span>
                            {{ $lainnya ? $lainnya : '...................' }}
                        @else
                            <span class="checkbox"></span> Pelanggan &nbsp;
                            <span class="checkbox"></span> Laboratorium
                            <br><br>
                            <span class="checkbox"></span> Pendinginan<br>
                            <span class="checkbox"></span> HNO₃<br>
                            <span class="checkbox"></span> H₂SO₄<br>
                            <span class="checkbox"></span> NaOH<br>
                            <span class="checkbox"></span> ...................
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tr>
            <td colspan="2" style="padding: 3px;"><strong>Kondisi sampel pada saat diterima termasuk abnormalitas
                    atau penyimpangan dari kondisi normal</strong></td>
        </tr>
        @php
            // Kumpulkan kondisi per jenis untuk semua sample
            $kondisi_per_jenis = [
                'tidak diawetkan di lapangan' => [],
                'wadah sampel tidak sesuai' => [],
                'sampel kadaluarsa' => [],
                'lainnya' => [],
            ];
            $catatan_lainnya = '';

            // Loop semua sample untuk cek kelayakan dan kondisi
            foreach ($samples as $s) {
                $p = isset($penerimaan_per_sample[$s->id_samples]) ? $penerimaan_per_sample[$s->id_samples] : null;
                if ($p) {
                    // Cek kelayakan
                    $tidak_layak =
                        $p->kelayakan_tempat_kemasan == 'tidak layak' || $p->kelayakan_berat_vol == 'tidak layak';

                    if ($tidak_layak && $p->kondisi_sample) {
                        $kondisi_items = array_filter(array_map('trim', explode('; ', $p->kondisi_sample)));
                        foreach ($kondisi_items as $item) {
                            $item_lower = strtolower($item);
                            if (stripos($item, 'lainnya:') === 0) {
                                $catatan_lainnya = trim(substr($item, 8));
                                $kondisi_per_jenis['lainnya'][] = $s->codesample_samples;
                            } elseif ($item_lower == 'tidak diawetkan di lapangan') {
                                $kondisi_per_jenis['tidak diawetkan di lapangan'][] = $s->codesample_samples;
                            } elseif ($item_lower == 'wadah sampel tidak sesuai') {
                                $kondisi_per_jenis['wadah sampel tidak sesuai'][] = $s->codesample_samples;
                            } elseif ($item_lower == 'sampel kadaluarsa') {
                                $kondisi_per_jenis['sampel kadaluarsa'][] = $s->codesample_samples;
                            }
                        }
                    }
                }
            }
        @endphp
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 4px; font-size: 10px;">
                <span
                    class="checkbox {{ count($kondisi_per_jenis['tidak diawetkan di lapangan']) > 0 ? 'checked' : '' }}">{{ count($kondisi_per_jenis['tidak diawetkan di lapangan']) > 0 ? '✔' : '' }}</span>
                tidak diawetkan di lapangan
                @if (count($kondisi_per_jenis['tidak diawetkan di lapangan']) > 0)
                    <br><strong style="font-size: 9px;">No. Sampel:
                        {{ implode(', ', $kondisi_per_jenis['tidak diawetkan di lapangan']) }}</strong>
                @endif
                <br>
                <span
                    class="checkbox {{ count($kondisi_per_jenis['wadah sampel tidak sesuai']) > 0 ? 'checked' : '' }}">{{ count($kondisi_per_jenis['wadah sampel tidak sesuai']) > 0 ? '✔' : '' }}</span>
                wadah sampel tidak sesuai
                @if (count($kondisi_per_jenis['wadah sampel tidak sesuai']) > 0)
                    <br><strong style="font-size: 9px;">No. Sampel:
                        {{ implode(', ', $kondisi_per_jenis['wadah sampel tidak sesuai']) }}</strong>
                @endif
                <br>
            </td>
            <td style="width: 50%; vertical-align: top; padding: 4px; font-size: 10px;">
                <span
                    class="checkbox {{ count($kondisi_per_jenis['sampel kadaluarsa']) > 0 ? 'checked' : '' }}">{{ count($kondisi_per_jenis['sampel kadaluarsa']) > 0 ? '✔' : '' }}</span>
                sampel kadaluarsa
                @if (count($kondisi_per_jenis['sampel kadaluarsa']) > 0)
                    <br><strong style="font-size: 9px;">No. Sampel:
                        {{ implode(', ', $kondisi_per_jenis['sampel kadaluarsa']) }}</strong>
                @endif
                <br>
                <span
                    class="checkbox {{ count($kondisi_per_jenis['lainnya']) > 0 ? 'checked' : '' }}">{{ count($kondisi_per_jenis['lainnya']) > 0 ? '✔' : '' }}</span>
                lainnya, sebutkan
                @if (count($kondisi_per_jenis['lainnya']) > 0)
                    <br><strong style="font-size: 9px;">No. Sampel:
                        {{ implode(', ', $kondisi_per_jenis['lainnya']) }}</strong>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 4px;">
                <strong style="font-size: 10px;">Catatan abnormalitas lainnya:</strong><br>
                <div class="notes-box" style="font-size: 10px;">
                    {{ $catatan_lainnya }}
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
