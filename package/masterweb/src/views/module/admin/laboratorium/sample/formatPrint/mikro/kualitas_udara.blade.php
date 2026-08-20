@php
    $x_baku_mutu = [
        'Peraturan Menteri Kesehatan No. 7 Tahun 2019 tentang Kesehatan Lingkungan Rumah Sakit',
        'Permenkes No. 2 Tahun 2023 tentang Peraturan Pelaksanaan Peraturan Pemerintah Nomor 66 tentang Kesehatan
Lingkungan',
    ];
@endphp

<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>MIKRO-{!! $no_LHU !!}</title>
    <link rel="shortcut icon" href="">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_style')
</head>

<body style="margin: 10px; padding: 0">
    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_kop')
    <br>

    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._head_data_kualitas_udara')
    <br>


    {{-- @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._table_result') --}}
    <table class="result" width="100%" border="1" style="width: 100%">
        <tr>
            <td style="text-align: center;" rowspan="2"> Nomor Sampel </td>
            <td style="text-align: center;" rowspan="2"> Jenis Sampel / Lokasi </td>
            <td style="text-align: center;" colspan="{{ count($method_all) }}"> Hasil Pemeriksaan </td>
            <td style="text-align: center;" rowspan="2"> Batas Syarat </td>
            <td style="text-align: center;" rowspan="2"> Satuan </td>
        </tr>

        <tr>
            @foreach ($method_all as $method)
                <td style="text-align: center; width: 0%">{!! $method->name_report !!}</td>
            @endforeach
        </tr>

        @foreach ($table as $mytable)
            @php
                $loop_lab_num = data_get($lab_nums, data_get($mytable, 'sample_type.id_samples'));
                $selectedRuangan = $mytable['sample_type']->selected_ruangan ?? null;
                $bakuMutuDisplay = '';
                
                // Jika ada ruangan yang dipilih, ambil baku mutu dari lokasi_data
                if ($selectedRuangan) {
                    $allLokasiData = [];
                    foreach ($method_all as $method) {
                        if (isset($method->lokasi_data) && !empty($method->lokasi_data)) {
                            $lokasiData = json_decode($method->lokasi_data, true);
                            if (is_array($lokasiData)) {
                                foreach ($lokasiData as $lokasi) {
                                    if (!empty($lokasi['nama']) && $lokasi['nama'] === $selectedRuangan) {
                                        if (!empty($lokasi['nilai_baku_mutu'])) {
                                            $allLokasiData[] = $lokasi['nama'] . ' : ' . $lokasi['nilai_baku_mutu'];
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($allLokasiData)) {
                        $bakuMutuDisplay = implode('<br>', array_unique($allLokasiData));
                    }
                }
                
                // Jika tidak ada ruangan atau tidak ada lokasi_data, gunakan nilai_baku_mutu default
                if (empty($bakuMutuDisplay)) {
                    $defaultBakuMutu = [];
                    foreach ($method_all as $method) {
                        if (isset($method->nilai_baku_mutu) && !empty($method->nilai_baku_mutu)) {
                            $defaultBakuMutu[] = $method->nilai_baku_mutu;
                        }
                    }
                    if (!empty($defaultBakuMutu)) {
                        $bakuMutuDisplay = implode('<br>', array_unique($defaultBakuMutu));
                    } else {
                        // Fallback ke hardcoded jika tidak ada data
                        $bakuMutuDisplay = 'OK Kosong : 0-35 <br>
OK dengan aktivitas : 0-180 <br>
OK Ultraclean : 0-10 <br>
Perinatal/Perawatan : 200 - 500 <br>
R. Bersalin : 200 <br>
R.Pemulihan/perawatan : 200-500 <br>
R.Observasi/perawatan bayi : 200 <br>
R.Perawatan premature : 200 <br>
ICU : 200';
                    }
                }
            @endphp
            <tr>
                <td style="text-align: center">
                    {{ !empty($loop_lab_num) ? sprintf('%04d', (int) $loop_lab_num) : '' }}
                </td>

                <td class="wysiwyg-data" style="text-align: center">
                    {!! $mytable['sample_type']->titik_pengambilan !!}
                 
                </td>

                @foreach ($mytable['result'] as $result)
                    @php
                        // Ambil min, max, equal dari lokasi_data jika ada ruangan yang dipilih
                        // Gunakan lokasi_selected dari result jika ada, jika tidak gunakan dari sample_type
                        $ruanganUntukBakuMutu = $result['lokasi_selected'] ?? $selectedRuangan;
                        
                        $min = $result['min'] ?? null;
                        $max = $result['max'] ?? null;
                        $equal = $result['equal'] ?? null;
                        
                        if ($ruanganUntukBakuMutu) {
                            foreach ($method_all as $method) {
                                if ($method->id_method == $result['method_id'] && isset($method->lokasi_data) && !empty($method->lokasi_data)) {
                                    $lokasiData = json_decode($method->lokasi_data, true);
                                    if (is_array($lokasiData)) {
                                        foreach ($lokasiData as $lokasi) {
                                            if (!empty($lokasi['nama']) && $lokasi['nama'] === $ruanganUntukBakuMutu) {
                                                $min = $lokasi['min'] ?? $min;
                                                $max = $lokasi['max'] ?? $max;
                                                $equal = $lokasi['equal'] ?? $equal;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        $hasil = cek_hasil_color(
                            isset($result['hasil'])
                                ? $result['hasil']
                                : (isset($result['equal'])
                                    ? $result['equal']
                                    : ''),
                            $min,
                            $max,
                            isset($equal) ? $equal : '',
                            'result_output_method_' . $result['method_id'] ?? null,
                            $result['offset_baku_mutu'] ?? null,
                        );
                    @endphp
                    <td style="text-align: center">{!! $hasil !!}</td>
                @endforeach

                <td style="text-align: center">
                    {!! $bakuMutuDisplay !!}
                </td>

                <td style="text-align: center" title="$result->satuan_bakumutu">{!! isset($mytable['result'][0]['satuan_bakumutu']) ? $mytable['result'][0]['satuan_bakumutu'] : '-' !!}</td>
            </tr>
        @endforeach
    </table>
    <br>


    @include('masterweb::module.admin.laboratorium.sample.formatPrint.mikro._foot_signature')
</body>

</html>
