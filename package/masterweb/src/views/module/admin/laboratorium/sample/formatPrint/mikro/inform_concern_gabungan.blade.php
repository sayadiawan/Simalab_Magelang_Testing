<html lang="">

@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 11.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.5;
    $padding = isset($padding) ? (float) $padding : 4.0;
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Inform Concern - Gabungan</title>
    <link rel="shortcut icon" href="">
    <style>
        .starter-template {
            text-align: center;
        }

        @media print {
            #cetak {
                display: none;
            }
        }

        @font-face {
            font-family: "Arial";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 11px;
        }

        @page {
            size: 794px 1248px;
            margin: 0px 30px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
            text-align: justify;
            text-justify: inter-word;
        }

        .checkbox {
            height: 10px;
            position: relative;
            bottom: 5px;
        }

        .table-syarat td,
        .table-syarat th {
            border: 1px solid black;
            border-collapse: collapse;
            padding: {{ $padding }}px 2px {{ $padding }}px 2px;
            font-size: {{ $fontsize }}px;
        }
    </style>
</head>

<body>

    @php
        // Hitung jumlah sample per tipe
        $countAirMinum = 0;
        $countAirBersih = 0;
        $countAirLimbah = 0;
        $countBakteri = 0;
        $countKuman = 0;
        $allNoRegistrasi = [];
        $allTanggalSampling = [];
        $allTanggalPenerimaan = [];
        $allJenisSample = [];
        $countLayakTempat = 0;
        $countLayakBerat = 0;

        foreach ($samplesData as $sampleData) {
            $sample = $sampleData['sample'];
            $allNoRegistrasi[] = $sample->codesample_samples;
            $allTanggalSampling[] = Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $sample->datesampling_samples,
            )->format('d/m/Y');
            $allTanggalPenerimaan[] = Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $sample->date_sending)->format(
                'd/m/Y',
            );
            $allJenisSample[] = $sample->name_sample_type;

            if (!empty($sampleData['airMinum'])) {
                $countAirMinum++;
            }
            if (!empty($sampleData['airBersih'])) {
                $countAirBersih++;
            }
            if (!empty($sampleData['airLimbah'])) {
                $countAirLimbah++;
            }
            if (!empty($sampleData['bakteri'])) {
                $countBakteri++;
            }
            if (!empty($sampleData['kuman'])) {
                $countKuman++;
            }

            // Hitung kelayakan
            if (
                isset($sampleData['penerimaan_sample']) &&
                $sampleData['penerimaan_sample']->tempat_kemasan_layak == 1
            ) {
                $countLayakTempat++;
            }
            if (isset($sampleData['penerimaan_sample']) && $sampleData['penerimaan_sample']->berat_volume_layak == 1) {
                $countLayakBerat++;
            }
        }

        // Get first and last dates
        $tanggalSamplingRange =
            count(array_unique($allTanggalSampling)) == 1
                ? $allTanggalSampling[0]
                : min($allTanggalSampling) . ' - ' . max($allTanggalSampling);

        $tanggalPenerimaanRange =
            count(array_unique($allTanggalPenerimaan)) == 1
                ? $allTanggalPenerimaan[0]
                : min($allTanggalPenerimaan) . ' - ' . max($allTanggalPenerimaan);

        // Tentukan jenis sample yang akan ditampilkan di pernyataan
        $mainSampleType = 'AIR LIMBAH';
        if ($countAirMinum > 0) {
            $mainSampleType = 'AIR MINUM';
        } elseif ($countAirBersih > 0) {
            $mainSampleType = 'AIR HIGIENE';
        } elseif ($countBakteri > 0) {
            $mainSampleType = 'MAKANAN/MINUMAN';
        } elseif ($countKuman > 0) {
            $mainSampleType = 'ANGKA KUMAN/ALT';
        }

        $countTidakLayakTempat = count($samplesData) - $countLayakTempat;
        $countTidakLayakBerat = count($samplesData) - $countLayakBerat;

        // Format nomor registrasi menjadi range yang compact
        function formatNoRegistrasiRange($allNoRegistrasi)
        {
            if (empty($allNoRegistrasi)) {
                return '';
            }

            // Kelompokkan berdasarkan prefix dan suffix
            $grouped = [];
            foreach ($allNoRegistrasi as $noReg) {
                // Pattern: PREFIX/NOMOR/SUFFIX (contoh: AL.02/0016/2025)
                if (preg_match('/^(.+?)\/(\d+)\/(.+)$/', $noReg, $matches)) {
                    $prefix = $matches[1];
                    $number = $matches[2];
                    $suffix = $matches[3];

                    $key = $prefix . '||' . $suffix;
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'prefix' => $prefix,
                            'suffix' => $suffix,
                            'numbers' => [],
                        ];
                    }
                    $grouped[$key]['numbers'][] = $number;
                } else {
                    // Jika format tidak sesuai, masukkan langsung
                    $grouped['other'][] = $noReg;
                }
            }

            $result = [];
            foreach ($grouped as $key => $group) {
                if ($key === 'other') {
                    $result = array_merge($result, $group);
                } else {
                    sort($group['numbers']);
                    if (count($group['numbers']) > 1) {
                        // Tampilkan range
                        $first = reset($group['numbers']);
                        $last = end($group['numbers']);
                        $result[] = $group['prefix'] . '/' . $first . '-' . $last . '/' . $group['suffix'];
                    } else {
                        // Hanya satu nomor
                        $result[] = $group['prefix'] . '/' . $group['numbers'][0] . '/' . $group['suffix'];
                    }
                }
            }

            return implode(', ', $result);
        }

        $noRegistrasiFormatted = formatNoRegistrasiRange($allNoRegistrasi);
    @endphp

            <div style="padding-top: 40px;">
                @if ($showKop)
                    <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%">
                @else
                    <div style="height: 120px;"></div>
                @endif

        <div style="padding: 0px 40px 0px 40px;">
            <table style="margin: 5px 0px 5px 0px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="font-size: 13px; font-weight: bold; text-align: center;">
                        FORMULIR PERMINTAAN PEMERIKSAAN
                    </td>
                </tr>
            </table>

            <table style="font-size: 11px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="33%">No. REGISTRASI</td>
                    <td width="2%">:</td>
                    <td>{{ $noRegistrasiFormatted }}</td>
                </tr>
                @if (isset($permohonan_uji))
                    <tr>
                        <td width="33%">NAMA</td>
                        <td width="2%">:</td>
                        <td>
                            @php
                                $namaPelanggan = $permohonan_uji->name_customer;
                                if (str_contains($namaPelanggan, 'π')) {
                                    $namaPelanggan = str_replace(
                                        'π',
                                        "<span style='font-family: \"DejaVu Sans\", sans-serif;'>π</span>",
                                        $namaPelanggan,
                                    );
                                }
                            @endphp
                            {!! $namaPelanggan !!}
                        </td>
                    </tr>
                    <tr>
                        <td width="33%" style="vertical-align: top;">ALAMAT</td>
                        <td width="2%" style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">{{ $permohonan_uji->address_customer }}</td>
                    </tr>
                @endif
                <tr>
                    <td width="33%">JENIS SAMPLE</td>
                    <td width="2%">:</td>
                    <td>{{ implode(', ', array_unique($allJenisSample)) }} Bakteriologi</td>
                </tr>
                <tr>
                    <td width="33%">TANGGAL SAMPLING</td>
                    <td width="2%">:</td>
                    <td>{{ $tanggalSamplingRange }}</td>
                </tr>
                <tr>
                    <td width="33%">TANGGAL PENERIMAAN</td>
                    <td width="2%">:</td>
                    <td>{{ $tanggalPenerimaanRange }}</td>
                </tr>
                @if (isset($permohonan_uji))
                    <tr>
                        <td width="33%">NO. TELP</td>
                        <td width="2%">:</td>
                        <td>{{ $permohonan_uji->cp_customer ?? '-' }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <hr style="height: 3px; background-color: black; margin: 10px 0;">

        <div style="padding: 0px 30px 0px 30px;">
            <table style="margin: 5px 0px 10px 0px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="font-size: 13px; font-weight: bold; text-align: center;">
                        <u>PARAMETER PEMERIKSAAN MIKROBIOLOGI</u>
                    </td>
                </tr>
            </table>

            <table style="font-size: 11px;" width="100%" cellspacing="5" cellpadding="0">
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        <b>I. BAKTERIOLOGI AIR</b>
                        <table width="100%" cellspacing="0" cellpadding="2" style="margin-top: 5px;">
                            <tr>
                                <td width="20px">
                                    <input type="checkbox" class="checkbox"
                                        @if ($countAirMinum > 0) checked @endif />
                                </td>
                                <td>Air Minum (Escherichia coli dan total Coliform)</td>
                                <td width="60px" style="text-align: right;">
                                    <b>{{ $countAirMinum > 0 ? $countAirMinum . ' sampel' : '' }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td width="20px">
                                    <input type="checkbox" class="checkbox"
                                        @if ($countAirBersih > 0) checked @endif />
                                </td>
                                <td>Air Higiene (Escherichia coli dan Total Coliform)</td>
                                <td width="60px" style="text-align: right;">
                                    <b>{{ $countAirBersih > 0 ? $countAirBersih . ' sampel' : '' }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td width="20px">
                                    <input type="checkbox" class="checkbox"
                                        @if ($countAirLimbah > 0) checked @endif />
                                </td>
                                <td>Air Limbah</td>
                                <td width="60px" style="text-align: right;">
                                    <b>{{ $countAirLimbah > 0 ? $countAirLimbah . ' sampel' : '' }}</b>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="50%" style="vertical-align: top;">
                        <b>II. MIKROBIOLOGI MAKANAN/MINUMAN</b>
                        <table width="100%" cellspacing="0" cellpadding="2" style="margin-top: 5px;">
                            <tr>
                                <td width="20px">
                                    <input type="checkbox" class="checkbox"
                                        @if ($countBakteri > 0) checked @endif />
                                </td>
                                <td>Bakteriologis Makanan</td>
                                <td width="60px" style="text-align: right;">
                                    <b>{{ $countBakteri > 0 ? $countBakteri . ' sampel' : '' }}</b>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top: 10px;">
                            <b>III. ANGKA KUMAN/ALT</b>
                            <table width="100%" cellspacing="0" cellpadding="2" style="margin-top: 5px;">
                                <tr>
                                    <td width="20px">
                                        <input type="checkbox" class="checkbox"
                                            @if ($countKuman > 0) checked @endif />
                                    </td>
                                    <td>ANGKA KUMAN/ALT</td>
                                    <td width="60px" style="text-align: right;">
                                        <b>{{ $countKuman > 0 ? $countKuman . ' sampel' : '' }}</b>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <table style="margin: 10px 0px 10px 0px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="font-size: 13px; font-weight: bold; text-align: center;">
                        <u>PERNYATAAN PERSETUJUAN</u>
                    </td>
                </tr>
            </table>

            <p style="font-size: 11px; margin-bottom: 5px; text-align: justify;">
                Dengan ini menyatakan bahwa <b>SETUJU</b> terhadap sampel yang telah diserahkan berupa
                <b>{{ $mainSampleType }}</b> kepada UPTD Laboratorium Kesehatan Kabupaten Magelang, dengan :
            </p>

            <p style="font-size: 11px; margin: 5px 0; text-align: justify;">
                Hasil pemeriksaan selama 10 hari kerja terhitung dari sampel diterima petugas
            </p>

            <table style="margin: 10px 0px 5px 0px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="font-size: 13px; font-weight: bold; text-align: center;">
                        <u>SYARAT KELAYAKAN SAMPEL</u>
                    </td>
                </tr>
            </table>

            <table style="font-size: 10px; margin-bottom: 10px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="5%" style="vertical-align: top;">1.</td>
                    <td width="95%">Sampel Pemeriksaan Mikrobiologi Air<br />
                        Wadah botol kaca coklat steril Volume minimal 500 ml</td>
                </tr>
                <tr>
                    <td width="5%" style="vertical-align: top;">2.</td>
                    <td width="95%">Sampel Makanan<br />
                        Kemasan dari plastik / Wadah yang bersih, Berat minimal 250 gr</td>
                </tr>
            </table>

            <table class="table-syarat" width="100%" cellspacing="0" cellpadding="3" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th width="30%">Spesifikasi</th>
                        <th width="35%">Layak</th>
                        <th width="35%">Tidak Layak</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tempat / Kemasan</td>
                        <td style="text-align: center;">
                            @if ($countLayakTempat > 0)
                                <input type="checkbox" class="checkbox" checked /> ({{ $countLayakTempat }} sampel)
                            @else
                                <input type="checkbox" class="checkbox" />
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($countTidakLayakTempat > 0)
                                <input type="checkbox" class="checkbox" checked /> ({{ $countTidakLayakTempat }}
                                sampel)
                            @else
                                <input type="checkbox" class="checkbox" />
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Berat / Vol</td>
                        <td style="text-align: center;">
                            @if ($countLayakBerat > 0)
                                <input type="checkbox" class="checkbox" checked /> ({{ $countLayakBerat }} sampel)
                            @else
                                <input type="checkbox" class="checkbox" />
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if ($countTidakLayakBerat > 0)
                                <input type="checkbox" class="checkbox" checked /> ({{ $countTidakLayakBerat }}
                                sampel)
                            @else
                                <input type="checkbox" class="checkbox" />
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 15px;">
                <table style="font-size: 10px;" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="50%" style="vertical-align: bottom; text-align: center;">
                            Penerima / Petugas
                            <br><br><br><br>
                            @if (isset($samplesData[0]['penerimaan_sample']) && $samplesData[0]['penerimaan_sample']->penerima_petugas)
                                {{ $samplesData[0]['penerimaan_sample']->penerima_petugas }}
                            @else
                                _____________________
                            @endif
                        </td>
                        <td width="50%" style="vertical-align: bottom; text-align: center;">
                            Magelang, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                            Pengirim
                            <br><br><br><br>
                            _____________________
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

</body>

</html>
