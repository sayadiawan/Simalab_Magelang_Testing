@php
    $hasKimiaOrganik = $laboratoriummethods->contains(function ($laboratoriummethod) {
        return kesmas_lhu_is_jenis($laboratoriummethod, 'kimia organik') && $laboratoriummethod->hasil !== '-';
    });

    $hasKimiawi = $laboratoriummethods->contains(function ($laboratoriummethod) {
        return kesmas_lhu_is_jenis($laboratoriummethod, 'kimiawi') && $laboratoriummethod->hasil !== '-';
    });

    $fisikaCount = 0;
    $kimiaOrganikCount = 0;
    $kimiaCount = 0;
    foreach ($laboratoriummethods as $laboratoriummethod) {
        if (
            kesmas_lhu_is_jenis($laboratoriummethod, 'fisika') &&
            kesmas_lhu_include_parameter($laboratoriummethod)
        ) {
            $fisikaCount++;
        } elseif (
            kesmas_lhu_is_jenis($laboratoriummethod, 'kimiawi') &&
            kesmas_lhu_include_parameter($laboratoriummethod)
        ) {
            $kimiaCount++;
        } elseif (
            kesmas_lhu_is_jenis($laboratoriummethod, 'kimia organik') &&
            kesmas_lhu_include_parameter($laboratoriummethod)
        ) {
            $kimiaOrganikCount++;
        }
    }

@endphp

<table class="result" width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 10px">
    <thead>
        <tr>
            <th width="5%" style="text-align: center">NO</th>
            <th width="10%" style="text-align: center">PARAMETER <br>PEMERIKSAAN</th>
            <th width="5%" style="text-align: center">HASIL <br>PEMERIKSAAN</th>
            <th width="35%" style="text-align: center">KADAR MAKSIMUM <br>YANG DIPERBOLEHKAN</th>
            <th width="25%" style="text-align: center">SATUAN</th>
            <th width="20%" style="text-align: center">METODE</th>
        </tr>
    </thead>
    <tbody>
        @if ($fisikaCount == 0 && $kimiaOrganikCount == 0 && $kimiaCount == 0)
            <tr>
                <td colspan="6" style="text-align: center; padding: 4px"><b>Belum melakukan input hasil</b></td>
            </tr>
        @endif
        @if (count($laboratoriummethods) > 0)
            @php $kesmasUrutFallback = 0; @endphp
            @if ($sample->only_fisika ?? false)
                @if ($fisikaCount > 0)
                    <tr>
                        <th style="text-align: center"></th>
                        <th style="text-align: left; padding-left: 2px;" colspan="5">A. FISIKA</th>
                    </tr>
                @endif
                @foreach ($laboratoriummethods as $laboratoriummethod)
                    @if (kesmas_lhu_is_jenis($laboratoriummethod, 'fisika') && kesmas_lhu_include_parameter($laboratoriummethod))
                        @php
                            $hasil = cek_hasil_color(
                                isset($laboratoriummethod->hasil)
                                    ? $laboratoriummethod->hasil
                                    : (isset($laboratoriummethod->equal) ? $laboratoriummethod->equal : ''),
                                $laboratoriummethod->min,
                                $laboratoriummethod->max,
                                $laboratoriummethod->equal,
                                'result_output_method_' . $laboratoriummethod->method_id,
                                $laboratoriummethod->offset_baku_mutu,
                            );
                            $unitAll = $laboratoriummethod->shortname_unit;
                        @endphp
                        @if ($hasil != '-')
                            <tr>
                                <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
                                <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
                                <td width="20%" style="text-align: center">{!! $hasil !!}</td>
                                <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
                                <td width="15%" style="text-align: center">{!! $unitAll !!}</td>
                                @if ($laboratoriummethod->is_ready == 1)
                                    <td width="20%" style="text-align: left; padding-left: 2px;">{!! !empty($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
                                @else
                                    <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
                                @endif
                            </tr>
                        @endif
                    @endif
                @endforeach
            @else
                @if ($fisikaCount > 0)
                    <tr>
                        <th style="text-align: center"></th>
                        <th style="text-align: left; padding-left: 2px;" colspan="5">A. FISIKA</th>
                    </tr>
                @endif
                @foreach ($laboratoriummethods as $laboratoriummethod)
                    @if (kesmas_lhu_is_jenis($laboratoriummethod, 'fisika') && kesmas_lhu_include_parameter($laboratoriummethod))
                        @php
                            $hasil = cek_hasil_color(
                                isset($laboratoriummethod->hasil)
                                    ? $laboratoriummethod->hasil
                                    : (isset($laboratoriummethod->equal) ? $laboratoriummethod->equal : ''),
                                $laboratoriummethod->min,
                                $laboratoriummethod->max,
                                $laboratoriummethod->equal,
                                'result_output_method_' . $laboratoriummethod->method_id,
                                $laboratoriummethod->offset_baku_mutu,
                            );
                            $unitAll = $laboratoriummethod->shortname_unit;
                        @endphp
                        @if ($hasil != '-')
                            <tr>
                                <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
                                <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
                                <td width="20%" style="text-align: center">{!! $hasil !!}</td>
                                <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
                                <td width="15%" style="text-align: center">{!! $unitAll !!}</td>
                                @if ($laboratoriummethod->is_ready == 1)
                                    <td width="20%" style="text-align: left; padding-left: 2px;">{!! !empty($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
                                @else
                                    <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
                                @endif
                            </tr>
                        @endif
                    @endif
                @endforeach

                @if ($hasKimiawi || $hasKimiaOrganik)
                    <tr>
                        <th style="text-align: center"></th>
                        <th style="text-align: left; padding-left: 2px;" colspan="5">
                            @if ($fisikaCount > 0)
                                B.
                            @else
                                A.
                            @endif
                            KIMIA
                        </th>
                    </tr>
                @endif

                @foreach ($laboratoriummethods as $laboratoriummethod)
                    @if (kesmas_lhu_is_jenis($laboratoriummethod, 'kimiawi') && kesmas_lhu_include_parameter($laboratoriummethod))
                        @php
                            $hasil = cek_hasil_color(
                                isset($laboratoriummethod->hasil)
                                    ? $laboratoriummethod->hasil
                                    : (isset($laboratoriummethod->equal) ? $laboratoriummethod->equal : ''),
                                $laboratoriummethod->min,
                                $laboratoriummethod->max,
                                $laboratoriummethod->equal,
                                'result_output_method_' . $laboratoriummethod->method_id,
                                $laboratoriummethod->offset_baku_mutu,
                            );
                            $unitAll = $laboratoriummethod->shortname_unit;
                        @endphp
                        @if ($hasil != '-')
                            <tr>
                                <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
                                <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
                                <td width="20%" style="text-align: center">{!! $hasil !!}</td>
                                <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
                                <td width="15%" style="text-align: center">{!! $unitAll !!}</td>
                                @if ($laboratoriummethod->is_ready == 1)
                                    <td width="20%" style="text-align: left; padding-left: 2px;">{!! !empty($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
                                @else
                                    <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
                                @endif
                            </tr>
                        @endif
                    @endif
                @endforeach

                @foreach ($laboratoriummethods as $laboratoriummethod)
                    @if (kesmas_lhu_is_jenis($laboratoriummethod, 'kimia organik') && kesmas_lhu_include_parameter($laboratoriummethod))
                        @php
                            $hasil = cek_hasil_color(
                                isset($laboratoriummethod->hasil)
                                    ? $laboratoriummethod->hasil
                                    : (isset($laboratoriummethod->equal) ? $laboratoriummethod->equal : ''),
                                $laboratoriummethod->min,
                                $laboratoriummethod->max,
                                $laboratoriummethod->equal,
                                'result_output_method_' . $laboratoriummethod->method_id,
                                $laboratoriummethod->offset_baku_mutu,
                            );
                            $unitAll = $laboratoriummethod->shortname_unit;
                        @endphp
                        @if ($hasil != '-')
                            <tr>
                                <td width="5%" style="text-align: center">{{ kesmas_parameter_urut_number($laboratoriummethod, $kesmasUrutFallback) }}</td>
                                <td width="20%" style="text-align: left; padding-left: 2px;">{!! $laboratoriummethod->name_report !!}</td>
                                <td width="20%" style="text-align: center">{!! $hasil !!}</td>
                                <td width="20%" style="text-align: center">{!! $laboratoriummethod->nilai_baku_mutu !!}</td>
                                <td width="15%" style="text-align: center">{!! $unitAll !!}</td>
                                @if ($laboratoriummethod->is_ready == 1)
                                    <td width="20%" style="text-align: left; padding-left: 2px;">{!! !empty($laboratoriummethod->metode) ? $laboratoriummethod->metode : $laboratoriummethod->name_method !!}</td>
                                @else
                                    <td style="text-align: center">Alat Dan Reagen tidak tersedia</td>
                                @endif
                            </tr>
                        @endif
                    @endif
                @endforeach

            @endif
        @endif
    </tbody>
</table>
