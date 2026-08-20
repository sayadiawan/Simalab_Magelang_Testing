<!DOCTYPE html>
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
    <title>Informed Consent - Gabungan</title>
    <style>
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

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    @foreach ($allLabsData as $index => $labData)
        @php
            $samplesData = $labData['samplesData'];
            $laboratorium = $labData['laboratorium'];
        @endphp

        @if ($index > 0)
            <div class="page-break"></div>
        @endif

        @if ($labData['type'] == 'kimia')
            {{-- Konten Kimia --}}
            @php
                // Hitung data kimia
                $allNoRegistrasi = [];
                $allTanggalSampling = [];
                $allTanggalPenerimaan = [];
                $allJenisSample = [];

                foreach ($samplesData as $sampleData) {
                    $sample = $sampleData['sample'];
                    $allNoRegistrasi[] = $sample->codesample_samples;
                    $allTanggalSampling[] = Carbon\Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $sample->datesampling_samples,
                    )->format('d/m/Y');
                    $allTanggalPenerimaan[] = Carbon\Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $sample->date_sending,
                    )->format('d/m/Y');
                    $allJenisSample[] = $sample->name_sample_type;
                }

                $tanggalSamplingRange =
                    count(array_unique($allTanggalSampling)) == 1
                        ? $allTanggalSampling[0]
                        : min($allTanggalSampling) . ' - ' . max($allTanggalSampling);
                $tanggalPenerimaanRange =
                    count(array_unique($allTanggalPenerimaan)) == 1
                        ? $allTanggalPenerimaan[0]
                        : min($allTanggalPenerimaan) . ' - ' . max($allTanggalPenerimaan);
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
                                FOLMULIR PERMINTAAN PEMERIKSAAN
                            </td>
                        </tr>
                    </table>

                    @php
                        // Format nomor registrasi untuk Kimia
                        function formatNoRegistrasiRangeKimia($allNoRegistrasi)
                        {
                            if (empty($allNoRegistrasi)) {
                                return '';
                            }

                            $grouped = [];
                            foreach ($allNoRegistrasi as $noReg) {
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
                                        $first = reset($group['numbers']);
                                        $last = end($group['numbers']);
                                        $result[] =
                                            $group['prefix'] . '/' . $first . '-' . $last . '/' . $group['suffix'];
                                    } else {
                                        $result[] =
                                            $group['prefix'] . '/' . $group['numbers'][0] . '/' . $group['suffix'];
                                    }
                                }
                            }
                            return implode(', ', $result);
                        }

                        $noRegistrasiFormattedKimia = formatNoRegistrasiRangeKimia($allNoRegistrasi);
                    @endphp

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="33%">No. REGISTRASI</td>
                            <td width="2%">:</td>
                            <td>{{ $noRegistrasiFormattedKimia }}</td>
                        </tr>
                        @if (isset($permohonan_uji))
                            <tr>
                                <td width="33%" style="vertical-align: top;">NAMA</td>
                                <td width="2%" style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">{{ $permohonan_uji->name_customer }}</td>
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
                            <td>{{ implode(', ', array_unique($allJenisSample)) }} Kimia/Fisika</td>
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
                                <u>PARAMETER PEMERIKSAAN KIMIA / FISIKA</u>
                            </td>
                        </tr>
                    </table>

                    @php
                        // Kumpulkan semua parameter dengan nomor sample-nya
                        $parameterList = [];
                        foreach ($samplesData as $sampleData) {
                            $sample = $sampleData['sample'];
                            $noReg = $sample->codesample_samples;

                            // Gabungkan semua parameter
                            $allParams = array_merge(
                                array_keys($sampleData['kimia']),
                                array_keys($sampleData['fisika']),
                                array_keys($sampleData['kimiaMakanan']),
                            );

                            foreach ($allParams as $param) {
                                if (!isset($parameterList[$param])) {
                                    $parameterList[$param] = [];
                                }
                                $parameterList[$param][] = $noReg;
                            }
                        }

                        // Helper function untuk format range nomor - hanya angka tengah
                        function formatSampleNumbers($samples)
                        {
                            $samples = array_unique($samples);
                            $numbers = [];

                            foreach ($samples as $sample) {
                                if (preg_match('/\/(\d+)\//', $sample, $matches)) {
                                    $numbers[] = $matches[1];
                                }
                            }

                            if (empty($numbers)) {
                                return '';
                            }

                            sort($numbers);

                            // Jika hanya 1 atau 2 nomor, tampilkan semua
                            if (count($numbers) <= 2) {
                                return implode(', ', $numbers);
                            }

                            // Jika lebih dari 2, cek apakah berurutan
                            $isSequential = true;
                            for ($i = 1; $i < count($numbers); $i++) {
                                if ((int) $numbers[$i] - (int) $numbers[$i - 1] != 1) {
                                    $isSequential = false;
                                    break;
                                }
                            }

                            if ($isSequential) {
                                return reset($numbers) . ' - ' . end($numbers);
                            } else {
                                return implode(', ', $numbers);
                            }
                        }

                        // Helper: return attribute string if parameter exists in provided map(s)
                        $paramAttr = function ($labels) use ($parameterList) {
                            $labels = (array) $labels;
                            foreach ($labels as $label) {
                                if (isset($parameterList[$label])) {
                                    return [
                                        'checked' => 'checked',
                                        'samples' => formatSampleNumbers($parameterList[$label]),
                                    ];
                                }
                            }
                            return ['checked' => '', 'samples' => ''];
                        };
                    @endphp

                    <style>
                        .compact-table {
                            font-size: 11px;
                            line-height: 1.15;
                        }

                        .compact-table td {
                            padding: 0 0 2px 0;
                        }

                        .compact-cell {
                            padding-left: 6px !important;
                            vertical-align: top;
                        }

                        .compact-param td {
                            padding: 0 0 2px 0;
                        }
                    </style>

                    <table class="compact-table" width="100%" cellspacing="4" cellpadding="0">
                        <tr>
                            <td width="28%"><b>Jenis Sampel</b></td>
                            <td width="36%"><b>Parameter</b></td>
                            <td width="36%"><b>Parameter</b></td>
                        </tr>

                        <tr>
                            <!-- Jenis Sampel -->
                            <td class="compact-cell">
                                @php
                                    // Cek jenis sample yang ada
                                    $hasAirMinum = false;
                                    $hasAirBersih = false;
                                    $hasKolam = false;
                                    $hasLimbah = false;
                                    $hasMakanan = false;
                                    $hasKosmetik = false;
                                    $hasPerbekalan = false;

                                    foreach ($allJenisSample as $jenis) {
                                        if (str_contains(strtolower($jenis), 'air minum')) {
                                            $hasAirMinum = true;
                                        }
                                        if (str_contains(strtolower($jenis), 'air higiene') || str_contains(strtolower($jenis), 'air bersih')) {
                                            $hasAirBersih = true;
                                        }
                                        if (str_contains(strtolower($jenis), 'kolam')) {
                                            $hasKolam = true;
                                        }
                                        if (str_contains(strtolower($jenis), 'limbah')) {
                                            $hasLimbah = true;
                                        }
                                        if (
                                            str_contains(strtolower($jenis), 'makanan') ||
                                            str_contains(strtolower($jenis), 'minuman')
                                        ) {
                                            $hasMakanan = true;
                                        }
                                        if (str_contains(strtolower($jenis), 'kosmetik')) {
                                            $hasKosmetik = true;
                                        }
                                        if (str_contains(strtolower($jenis), 'perbekalan')) {
                                            $hasPerbekalan = true;
                                        }
                                    }
                                @endphp
                                <table width="100%" cellspacing="0" cellpadding="1">
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasAirMinum) checked @endif />
                                        </td>
                                        <td>Air Minum</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasAirBersih) checked @endif />
                                        </td>
                                        <td>Air Higiene</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasKolam) checked @endif />
                                        </td>
                                        <td>Air Kolam Renang</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasLimbah) checked @endif />
                                        </td>
                                        <td>Air Limbah</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasMakanan) checked @endif />
                                        </td>
                                        <td>Makanan/ minuman</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasKosmetik) checked @endif />
                                        </td>
                                        <td>Kosmetik</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if ($hasPerbekalan) checked @endif />
                                        </td>
                                        <td>Perbekalan Kesehatan</td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Parameter kolom kiri -->
                            <td class="compact-cell">
                                <table width="100%" cellspacing="0" cellpadding="1" class="compact-param">
                                    @php
                                        $leftParams = [
                                            ['Bau', ['Bau']],
                                            ['Rasa', ['Rasa']],
                                            ['Warna', ['Warna']],
                                            ['Suhu', ['Suhu']],
                                            ['Kekeruhan', ['Kekeruhan']],
                                            ['pH', ['pH']],
                                            [
                                                'TDS',
                                                [
                                                    'Zat padat terlarut<br>(Total Dissolved Solid)',
                                                    'Total Zat Padat Terlarut (TDS)',
                                                    'TDS',
                                                ],
                                            ],
                                            ['Sisa Klor', ['Sisa Klor']],
                                            ['Klorida', ['Chlorida', 'Klorida']],
                                            ['Kesadahan', ['Kesadahan', 'Kesadahan (CaCO3)']],
                                            ['Zat Organik', ['Zat Organik']],
                                            ['TSS', ['TSS']],
                                            ['Besi', ['Besi (Fe)', 'Besi']],
                                            ['Mangan', ['Mangan (Mn)', 'Mangan', 'Mangaan']],
                                            ['Nitrat', ['Nitrat']],
                                            ['Nitrit', ['Nitrit']],
                                            ['Sulfat', ['Sulfat']],
                                            ['Arsen', ['Arsen']],
                                            ['Kromium Valensi 6', ['Kromium Valensi 6']],
                                            ['Sianida', ['Sianida']],
                                            ['Timbal', ['Timbal']],
                                            ['Seng', ['Seng']],
                                            ['Deterjen', ['Deterjen']],
                                        ];
                                    @endphp
                                    @foreach ($leftParams as [$label, $aliases])
                                        @php $result = $paramAttr($aliases); @endphp
                                        <tr>
                                            <td width="18px"><input type="checkbox" class="checkbox"
                                                    @if ($result['checked']) {!! $result['checked'] !!} @endif />
                                            </td>
                                            <td>{{ $label }}
                                                @if (!empty($result['samples']))
                                                    <span style="color: #0066cc;">({{ $result['samples'] }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>

                            <!-- Parameter kolom kanan -->
                            <td class="compact-cell">
                                <table width="100%" cellspacing="0" cellpadding="1" class="compact-param">
                                    @php
                                        $rightParams = [
                                            ['Borax', ['Borax']],
                                            ['Formalin', ['Formalin']],
                                            ['Enzim Diastase', ['Enzim Diastase']],
                                            [
                                                'Hidroksimetil Furfural',
                                                ['Hidroksimetil Furfural', 'Hidroximetil Furfural'],
                                            ],
                                            ['Sakarin', ['Sakarin']],
                                            ['Rhodamin B', ['Rhodamin B']],
                                            ['FFA', ['FFA']],
                                            ['Ketengikan', ['Ketengikan']],
                                            ['Angka Peroksida', ['Angka Peroksida']],
                                            ['Garam Beryodium', ['Garam Beryodium']],
                                            ['Kadar Abu', ['Kadar Abu']],
                                            ['Angka Penyabunan', ['Angka Penyabunan']],
                                            ['Iodium', ['Iodium']],
                                            ['Kadar Air', ['Kadar Air']],
                                            ['NaCl', ['NaCl']],
                                            ['Minuman Beralkohol', ['Minuman Beralkohol']],
                                            ['Kadmium', ['Kadmium']],
                                            ['Fluorida', ['Fluorida']],
                                            ['DHL', ['DHL']],
                                            ['Aluminium', ['Aluminium']],
                                            ['Tembaga', ['Tembaga']],
                                            ['DO', ['DO']],
                                            ['Amoniak', ['Amoniak']],
                                            ['BOD', ['BOD']],
                                        ];
                                    @endphp
                                    @foreach ($rightParams as [$label, $aliases])
                                        @php $result = $paramAttr($aliases); @endphp
                                        <tr>
                                            <td width="18px"><input type="checkbox" class="checkbox"
                                                    @if ($result['checked']) {!! $result['checked'] !!} @endif />
                                            </td>
                                            <td>{{ $label }}
                                                @if (!empty($result['samples']))
                                                    <span style="color: #0066cc;">({{ $result['samples'] }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table style="margin: 10px 0px 15px 0px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 15px; font-weight: bold; text-align: center;">
                                <u>PERNYATAAN PERSETUJUAN</u>
                            </td>
                        </tr>
                    </table>

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: justify;">
                                Dengan ini menyatakan bahwa <b>SETUJU</b> terhadap sampel yang telah diserahkan
                                berupa <b>{{ implode(', ', array_unique($allJenisSample)) }}</b> kepada
                                UPTD Laboratorium Kesehatan Kabupaten Magelang, dengan :
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td></td>
                                        <td width="20%" style="text-align: center;">
                                            Pelanggan
                                            <br>
                                            <br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Hasil pemeriksaan selama 10 hari kerja terhitung dari sampel diterima petugas
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td></td>
                                        <td width="20%" style="text-align: center;">
                                            <hr style="border-bottom: 0.5px solid;">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table style="margin: 10px 0px 15px 0px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 15px; font-weight: bold; text-align: center;">
                                <u>SYARAT KELAYAKAN SAMPEL</u>
                            </td>
                        </tr>
                    </table>

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="1">
                        <tr>
                            <td>1.</td>
                            <td>Sampel Pemeriksaan Kimia Air</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>dengan Wadah botol plastik / botol kaca bening bersih volume minimal 500 ml</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>Sampel Makanan</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Kemasan dari plastik / Wadah yang bersih, Berat minimal 250 gr</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Sampel tersendiri untuk pemeriksaan secara kimia</td>
                        </tr>
                    </table>

                    @php
                        // Hitung kelayakan dari semua samples
                        $countLayak = 0;
                        $countTidakLayak = 0;
                        $countBeratLayak = 0;
                        $countBeratTidakLayak = 0;
                        $petugasCount = [];

                        foreach ($samplesData as $sampleData) {
                            if (isset($sampleData['penerimaan_sample'])) {
                                if ($sampleData['penerimaan_sample']->kelayakan_tempat_kemasan == 'layak') {
                                    $countLayak++;
                                }
                                if ($sampleData['penerimaan_sample']->kelayakan_berat_vol == 'layak') {
                                    $countBeratLayak++;
                                }
                            }

                            // Ambil petugas dari sample object
                            if (isset($sampleData['sample']) && !empty($sampleData['sample']->petugas_penerima)) {
                                $petugas = $sampleData['sample']->petugas_penerima;
                                if (!isset($petugasCount[$petugas])) {
                                    $petugasCount[$petugas] = 0;
                                }
                                $petugasCount[$petugas]++;
                            }
                        }
                        $countTidakLayak = count($samplesData) - $countLayak;
                        $countBeratTidakLayak = count($samplesData) - $countBeratLayak;

                        // Ambil petugas dengan jumlah terbanyak
                        $petugasPenerima = '';
                        if (!empty($petugasCount)) {
                            arsort($petugasCount);
                            $petugasPenerima = array_key_first($petugasCount);
                        }
                    @endphp

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="65%">
                                <table class="table-syarat" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="text-align: center;">Spefikasi</td>
                                        <td width="30%" style="text-align: center;">Layak</td>
                                        <td width="30%" style="text-align: center;">Tidak Layak</td>
                                    </tr>
                                    <tr>
                                        <td>Tempat / Kemasan</td>
                                        <td>
                                            @if ($countLayak > 0)
                                                <input type="checkbox" class="checkbox" checked />
                                            @else
                                                <input type="checkbox" class="checkbox" />
                                            @endif
                                        </td>
                                        <td>
                                            @if ($countTidakLayak > 0)
                                                <input type="checkbox" class="checkbox" checked />
                                            @else
                                                <input type="checkbox" class="checkbox" />
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Berat / Vol</td>
                                        <td>
                                            @if ($countBeratLayak > 0)
                                                <input type="checkbox" class="checkbox" checked />
                                            @else
                                                <input type="checkbox" class="checkbox" />
                                            @endif
                                        </td>
                                        <td>
                                            @if ($countBeratTidakLayak > 0)
                                                <input type="checkbox" class="checkbox" checked />
                                            @else
                                                <input type="checkbox" class="checkbox" />
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                            <td width="20%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center;">
                                            Penerima / Petugas
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center;">
                                            {{ $petugasPenerima }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        @elseif ($labData['type'] == 'mikro')
            {{-- Konten Mikro --}}
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
                $petugasCount = [];

                foreach ($samplesData as $sampleData) {
                    $sample = $sampleData['sample'];
                    $allNoRegistrasi[] = $sample->codesample_samples;
                    $allTanggalSampling[] = Carbon\Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $sample->datesampling_samples,
                    )->format('d/m/Y');
                    $allTanggalPenerimaan[] = Carbon\Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $sample->date_sending,
                    )->format('d/m/Y');
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

                    if (
                        isset($sampleData['penerimaan_sample']) &&
                        $sampleData['penerimaan_sample']->kelayakan_tempat_kemasan == 'layak'
                    ) {
                        $countLayakTempat++;
                    }
                    if (
                        isset($sampleData['penerimaan_sample']) &&
                        $sampleData['penerimaan_sample']->kelayakan_berat_vol == 'layak'
                    ) {
                        $countLayakBerat++;
                    }

                    // Hitung petugas penerima dari sample object
                    if (isset($sampleData['sample']) && !empty($sampleData['sample']->petugas_penerima)) {
                        $petugas = $sampleData['sample']->petugas_penerima;
                        if (!isset($petugasCount[$petugas])) {
                            $petugasCount[$petugas] = 0;
                        }
                        $petugasCount[$petugas]++;
                    }
                }

                $tanggalSamplingRange =
                    count(array_unique($allTanggalSampling)) == 1
                        ? $allTanggalSampling[0]
                        : min($allTanggalSampling) . ' - ' . max($allTanggalSampling);
                $tanggalPenerimaanRange =
                    count(array_unique($allTanggalPenerimaan)) == 1
                        ? $allTanggalPenerimaan[0]
                        : min($allTanggalPenerimaan) . ' - ' . max($allTanggalPenerimaan);

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

                // Ambil petugas dengan jumlah terbanyak
                $petugasPenerima = '';
                if (!empty($petugasCount)) {
                    arsort($petugasCount);
                    $petugasPenerima = array_key_first($petugasCount);
                }

                // Format nomor registrasi
                function formatNoRegistrasiRange($allNoRegistrasi)
                {
                    if (empty($allNoRegistrasi)) {
                        return '';
                    }

                    $grouped = [];
                    foreach ($allNoRegistrasi as $noReg) {
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
                                $first = reset($group['numbers']);
                                $last = end($group['numbers']);
                                $result[] = $group['prefix'] . '/' . $first . '-' . $last . '/' . $group['suffix'];
                            } else {
                                $result[] = $group['prefix'] . '/' . $group['numbers'][0] . '/' . $group['suffix'];
                            }
                        }
                    }
                    return implode(', ', $result);
                }

                $noRegistrasiFormatted = formatNoRegistrasiRange($allNoRegistrasi);
            @endphp

            <div style="padding-top: 40px;">
                <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%">

                <div style="padding: 0px 40px 0px 40px;">
                    <table style="margin: 5px 0px 5px 0px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 13px; font-weight: bold; text-align: center;">
                                FORMULIR PERMINTAAN PEMERIKSAAN
                            </td>
                        </tr>
                    </table>

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="33%">No. REGISTRASI</td>
                            <td width="2%">:</td>
                            <td>{{ $noRegistrasiFormatted }}</td>
                        </tr>
                        @if (isset($permohonan_uji))
                            <tr>
                                <td width="33%">NAMA</td>
                                <td width="2%">:</td>
                                <td>{{ $permohonan_uji->name_customer }}</td>
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

                    <table style="font-size: 13px;" width="100%" cellspacing="5" cellpadding="0">
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

                    @php
                        // Buat daftar detail sample per kategori
                        $detailSamples = [
                            'Air Minum' => [],
                            'Air Higiene' => [],
                            'Air Limbah' => [],
                            'Bakteriologis Makanan' => [],
                            'Angka Kuman/ALT' => [],
                        ];

                        foreach ($samplesData as $sampleData) {
                            $sample = $sampleData['sample'];
                            $noReg = $sample->codesample_samples;

                            if (!empty($sampleData['airMinum'])) {
                                $detailSamples['Air Minum'][] = $noReg;
                            }
                            if (!empty($sampleData['airBersih'])) {
                                $detailSamples['Air Higiene'][] = $noReg;
                            }
                            if (!empty($sampleData['airLimbah'])) {
                                $detailSamples['Air Limbah'][] = $noReg;
                            }
                            if (!empty($sampleData['bakteri'])) {
                                $detailSamples['Bakteriologis Makanan'][] = $noReg;
                            }
                            if (!empty($sampleData['kuman'])) {
                                $detailSamples['Angka Kuman/ALT'][] = $noReg;
                            }
                        }
                    @endphp

                    <table style="font-size: 11px; width: 100%; border-collapse: collapse; margin-top: 10px;"
                        cellspacing="0" cellpadding="3">
                        <thead>
                            <tr style="background-color: #f0f0f0;">
                                <th style="border: 1px solid #000; padding: 5px; text-align: left;" width="5%">No
                                </th>
                                <th style="border: 1px solid #000; padding: 5px; text-align: left;" width="35%">
                                    Jenis Pemeriksaan</th>
                                <th style="border: 1px solid #000; padding: 5px; text-align: center;" width="15%">
                                    Jumlah</th>
                                <th style="border: 1px solid #000; padding: 5px; text-align: left;" width="45%">
                                    Nomor Registrasi Sample</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($detailSamples as $jenis => $samples)
                                @if (!empty($samples))
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                            {{ $no++ }}</td>
                                        <td style="border: 1px solid #000; padding: 3px;">{{ $jenis }}</td>
                                        <td style="border: 1px solid #000; padding: 3px; text-align: center;">
                                            {{ count($samples) }} sampel</td>
                                        <td style="border: 1px solid #000; padding: 3px;">
                                            {{ implode(', ', $samples) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    <table style="margin: 10px 0px 15px 0px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 15px; font-weight: bold; text-align: center;">
                                <u>PERNYATAAN PERSETUJUAN</u>
                            </td>
                        </tr>
                    </table>

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="text-align: justify;">
                                Dengan ini menyatakan bahwa <b>SETUJU</b> terhadap sampel yang telah diserahkan berupa
                                <b>{{ $mainSampleType }}</b> kepada UPTD Laboratorium Kesehatan Kabupaten Magelang,
                                dengan :
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td></td>
                                        <td width="20%" style="text-align: center;">
                                            Pelanggan
                                            <br>
                                            <br>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Hasil pemeriksaan selama 10 hari kerja terhitung dari sampel diterima petugas
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td></td>
                                        <td width="20%" style="text-align: center;">
                                            <hr style="border-bottom: 0.5px solid;">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table style="margin: 10px 0px 15px 0px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 15px; font-weight: bold; text-align: center;">
                                <u>SYARAT KELAYAKAN SAMPEL</u>
                            </td>
                        </tr>
                    </table>

                    <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="1">
                        <tr>
                            <td>1.</td>
                            <td>Sampel Pemeriksaan Mikrobiologi Air</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Wadah botol kaca coklat steril Volume minimal 500 ml</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>Sampel Makanan</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Kemasan dari plastik / Wadah yang bersih, Berat minimal 250 gr</td>
                        </tr>
                    </table>

                    <table class="table-syarat" width="100%" cellspacing="0" cellpadding="3"
                        style="margin-top: 10px;">
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
                                        <input type="checkbox" class="checkbox" checked /> ({{ $countLayakTempat }}
                                        sampel)
                                    @else
                                        <input type="checkbox" class="checkbox" />
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if ($countTidakLayakTempat > 0)
                                        <input type="checkbox" class="checkbox" checked />
                                        ({{ $countTidakLayakTempat }}
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
                                        <input type="checkbox" class="checkbox" checked /> ({{ $countLayakBerat }}
                                        sampel)
                                    @else
                                        <input type="checkbox" class="checkbox" />
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if ($countTidakLayakBerat > 0)
                                        <input type="checkbox" class="checkbox" checked />
                                        ({{ $countTidakLayakBerat }}
                                        sampel)
                                    @else
                                        <input type="checkbox" class="checkbox" />
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table style="font-size: 13px; margin-top: 10px;" width="100%" cellspacing="0"
                        cellpadding="0">
                        <tr>
                            <td width="65%">
                                <!-- Kosong untuk spacing -->
                            </td>
                            <td></td>
                            <td width="20%">
                                <table width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center;">
                                            Penerima / Petugas
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center;">
                                            @if (!empty($petugasPenerima))
                                                {{ $petugasPenerima }}
                                            @else
                                                &nbsp;
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif
    @endforeach
</body>

</html>
