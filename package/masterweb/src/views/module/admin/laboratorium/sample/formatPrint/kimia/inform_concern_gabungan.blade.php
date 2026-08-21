<html lang="">

@php
    $fontsize = isset($fontsize) ? (float) $fontsize : 11.0;
    $lineHeight = isset($lineHeight) ? (float) $lineHeight : 1.5;
    $padding = isset($padding) ? (float) $padding : 4.0;
    $showKop = isset($showKop) ? (int) $showKop : 1;
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Inform Concern - Gabungan</title>
    <link rel="shortcut icon" href="">
    <style>
        .starter-template {
            text-align: center;
        }

        table>tr>td {
            /* cell-padding: 5px !important; */
        }

        @media print {
            #cetak {
                display: none;
            }
        }

        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 11px;
        }


        .garis {
            border: 1px solid
        }

        .table2 {
            font-size: 5px;
            text-align: center
        }

        .result {
            border-collapse: collapse;
        }

        .result td {
            border: 1px solid black;
            text-align: center;
        }

        @page {
            size: 794px 1248px;
            margin: 0px 30px;
        }



        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: {{ $fontsize }}px;
            line-height: {{ $lineHeight }};
            text-align: justify;
            text-justify: inter-word;
        }

        .page_break {
            page-break-before: always;
        }

        .flex-container {
            display: flex !important;
            flex-wrap: nowrap !important;
        }

        .flex-container>div {
            width: 100px !important;
            margin: 10px !important;
        }

        .border {
            border: 1.5px solid black;
        }

        .v-align-top {
            vertical-align: top;
        }

        .checkbox {
            height: 10px;
            position: relative;
            bottom: 5px;
        }

        @font-face {
            font-family: 'DejaVu Sans', sans-serif !important;
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 11px;
        }


        .text-center {
            text-align: center;
        }

        .td-header {
            font-family: "Times New Roman", Times, serif !important;
            font-weight: bold;
            text-align: center;
        }

        .table-syarat td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 4px 2px 4px 2px;
            padding: {{ $padding }}px 2px {{ $padding }}px 2px;
            font-size: {{ $fontsize }}px;

        .table-clear td {
            border: 0px;
            padding: 0px;
        }

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

        .sample-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px dashed #999;
        }

        .sample-section:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>

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
                        FORMULIR PERMINTAAN PEMERIKSAAN - LABORATORIUM
                        {{ strtoupper($laboratorium->nama_laboratorium) }}
                    </td>
                </tr>
            </table>

            @if (isset($permohonan_uji))
                <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="33%">NAMA PELANGGAN</td>
                        <td width="2%">:</td>
                        <td>{{ $permohonan_uji->name_customer }}</td>
                    </tr>
                    <tr>
                        <td width="33%" style="vertical-align: top;">ALAMAT</td>
                        <td width="2%" style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">{{ $permohonan_uji->address_customer }}</td>
                    </tr>
                    <tr>
                        <td width="33%">NO. TELP</td>
                        <td width="2%">:</td>
                        <td>{{ $permohonan_uji->cp_customer }}</td>
                    </tr>
                </table>
            @endif
        </div>

        <hr style="height: 3px; background-color: black; margin: 10px 0;">

        @foreach ($samplesData as $index => $sampleData)
            @php
                $sample = $sampleData['sample'];
                $kimia = $sampleData['kimia'];
                $fisika = $sampleData['fisika'];
                $kimiaMakanan = $sampleData['kimiaMakanan'];
                $penerimaan_sample = $sampleData['penerimaan_sample'];
                $not_in_list_kimia = $sampleData['not_in_list_kimia'];
            @endphp

            <div class="sample-section" @if ($index > 0) style="page-break-before: always;" @endif>
                <div style="padding: 0px 40px 0px 40px;">
                    <table style="font-size: 13px; margin-bottom: 10px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="33%"><strong>No. REGISTRASI</strong></td>
                            <td width="2%">:</td>
                            <td><strong>{{ $sample->codesample_samples }}</strong></td>
                        </tr>
                        <tr>
                            <td width="33%">JENIS SAMPLE</td>
                            <td width="2%">:</td>
                            @php
                                if (str_contains($sample->codesample_samples, '- F')) {
                                    $is_fisika = true;
                                } else {
                                    $is_fisika = false;
                                }

                                $foodType = '';
                                if ($sample->namaJenisMakananPlain('') !== '') {
                                    $foodType = '(' . $sample->namaJenisMakananPlain() . ')';
                                }
                            @endphp

                            @if ($is_fisika)
                                <td>{{ $sample->name_sample_type }} Fisika {{ $foodType }}</td>
                            @else
                                <td>{{ $sample->name_sample_type }} Kimia {{ $foodType }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td width="33%">TANGGAL SAMPLING</td>
                            <td width="2%">:</td>
                            <td>{{ $sample->datesampling_samples }}</td>
                        </tr>
                        <tr>
                            <td width="33%">TANGGAL PENERIMAAN</td>
                            <td width="2%">:</td>
                            <td>{{ $sample->date_sending }}</td>
                        </tr>
                    </table>
                </div>

                <div style="padding: 0px 30px 0px 30px;">
                    <table style="margin: 5px 0px 10px 0px;" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="font-size: 13px; font-weight: bold; text-align: center;">
                                <u>PARAMETER PEMERIKSAAN KIMIA / FISIKA</u>
                            </td>
                        </tr>
                    </table>

                    @php
                        // Helper: return attribute string if parameter exists in provided map(s)
                        $paramAttr = function ($labels) use ($fisika, $kimia) {
                            $labels = (array) $labels;
                            foreach ($labels as $label) {
                                if (isset($fisika[$label])) {
                                    return $fisika[$label];
                                }
                                if (isset($kimia[$label])) {
                                    return $kimia[$label];
                                }
                            }
                            return '';
                        };

                        $extraKimiaCol1 = [];
                        $extraKimiaCol2 = [];
                        $extraIdx = 0;
                        foreach ($not_in_list_kimia ?? [] as $param => $checked) {
                            if ($extraIdx % 2 === 0) {
                                $extraKimiaCol1[$param] = $checked;
                            } else {
                                $extraKimiaCol2[$param] = $checked;
                            }
                            $extraIdx++;
                        }
                    @endphp

                    <table class="compact-table" width="100%" cellspacing="4" cellpadding="0">
                        <tr>
                            <td width="28%"><b>Jenis Sampel</b></td>
                            <td width="36%"><b>Parameter</b></td>
                            <td width="36%"><b>Parameter</b></td>
                        </tr>

                        <tr>
                            <!-- Jenis Sampel -->
                            <td class="compact-cell">
                                <table width="100%" cellspacing="0" cellpadding="1">
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains($sample->name_sample_type, 'Air Minum')) checked @endif />
                                        </td>
                                        <td>Air Minum</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains(strtolower($sample->name_sample_type), 'air higiene') || str_contains(strtolower($sample->name_sample_type), 'air bersih')) checked @endif />
                                        </td>
                                        <td>Air Higiene</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains($sample->name_sample_type, 'Kolam')) checked @endif />
                                        </td>
                                        <td>Air Kolam Renang</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains($sample->name_sample_type, 'Limbah')) checked @endif />
                                        </td>
                                        <td>Air Limbah</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains($sample->name_sample_type, 'Makanan/Minuman')) checked @endif />
                                        </td>
                                        <td>Makanan/Minuman</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains($sample->name_sample_type, 'Kosmetik')) checked @endif />
                                        </td>
                                        <td>Kosmetik</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox"
                                                @if (str_contains($sample->name_sample_type, 'Perbekalan')) checked @endif />
                                        </td>
                                        <td>Perbekalan Kesehatan Rumah Tangga</td>
                                    </tr>
                                    <tr>
                                        <td width="18px">
                                            <input type="checkbox" class="checkbox" />
                                        </td>
                                        <td>Lainnya......</td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Parameter Column 1 -->
                            <td class="compact-cell">
                                <table class="compact-param" width="100%" cellspacing="0" cellpadding="1">
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Besi (Fe)') }} /></td>
                                        <td>Besi (Fe)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Fluorida') }} /></td>
                                        <td>Fluorida</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Kesadahan (CaCO3)', 'Kesadahan']) }} /></td>
                                        <td>Kesadahan (CaCO3)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Klorida (Cl)', 'Chlorida', 'Klorida', 'Chlorida (Cl)']) }} />
                                        </td>
                                        <td>Klorida (Cl)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Mangan (Mn)', 'Mangan']) }} /></td>
                                        <td>Mangan (Mn)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Nitrat') }} /></td>
                                        <td>Nitrat (NO3)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Nitrit') }} /></td>
                                        <td>Nitrit (NO2)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('pH') }} /></td>
                                        <td>pH</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Sianida', 'Sianida (CN)']) }} /></td>
                                        <td>Sianida (CN)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Sulfat') }} /></td>
                                        <td>Sulfat</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Zat Organik', 'Zat Organik (KMnO4)']) }} /></td>
                                        <td>Zat Organik (KMnO4)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Amonia') }} /></td>
                                        <td>Amonia</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Arsen') }} /></td>
                                        <td>Arsen</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Kadmium', 'Kadmium (Cd)']) }} /></td>
                                        <td>Kadmium (Cd)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Kromium Valensi 6', 'Kromium (VI)', 'Kromium Heksavalensi']) }} />
                                        </td>
                                        <td>Kromium (VI)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Selenium') }} /></td>
                                        <td>Selenium</td>
                                    </tr>
                                    @foreach ($extraKimiaCol1 as $param => $checked)
                                        <tr>
                                            <td width="18px"><input type="checkbox" class="checkbox"
                                                    {{ $checked }} /></td>
                                            <td>{{ $param }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>

                            <!-- Parameter Column 2 -->
                            <td class="compact-cell">
                                <table class="compact-param" width="100%" cellspacing="0" cellpadding="1">
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Seng') }} /></td>
                                        <td>Seng (Zn)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Sisa Khlor') }} /></td>
                                        <td>Sisa Khlor</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Alumunium', 'Aluminium', 'Alumunium (Al)']) }} /></td>
                                        <td>Alumunium (Al)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Tembaga', 'Tembaga (Cu)']) }} /></td>
                                        <td>Tembaga (Cu)</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Warna') }} /></td>
                                        <td>Warna</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Bau') }} /></td>
                                        <td>Bau</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['TDS', 'Zat Padat Terlarut', 'Zat Padat Terlarut (TDS)']) }} />
                                        </td>
                                        <td>TDS</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Kekeruhan') }} /></td>
                                        <td>Kekeruhan</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Suhu') }} /></td>
                                        <td>Suhu</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Rasa', 'Organoleptik Rasa']) }} /></td>
                                        <td>Rasa</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Borax', 'Boraks']) }} /></td>
                                        <td>Boraks</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Formalin') }} /></td>
                                        <td>Formalin</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr(['Methanyl Yellow', 'Methanil Yellow', 'Pewarna']) }} />
                                        </td>
                                        <td>Pewarna</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Rhodamin B') }} /></td>
                                        <td>Rhodamin B</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Siklamat') }} /></td>
                                        <td>Siklamat</td>
                                    </tr>
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $paramAttr('Sakarin') }} /></td>
                                        <td>Sakarin</td>
                                    </tr>
                                    @foreach ($extraKimiaCol2 as $param => $checked)
                                        <tr>
                                            <td width="18px"><input type="checkbox" class="checkbox"
                                                    {{ $checked }} /></td>
                                            <td>{{ $param }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>

                    @if (!empty($kimiaMakanan))
                        <div style="margin-top: 10px;">
                            <strong>Parameter Makanan/Minuman:</strong>
                            <table class="compact-param" width="100%" cellspacing="0" cellpadding="1">
                                @foreach ($kimiaMakanan as $param => $checked)
                                    <tr>
                                        <td width="18px"><input type="checkbox" class="checkbox"
                                                {{ $checked }} /></td>
                                        <td>{{ $param }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        <div style="padding: 0px 30px; margin-top: 20px;">
            <table style="font-size: 11px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="50%"></td>
                    <td width="50%" style="text-align: center;">
                        —, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                        Yang Menyerahkan Sampel,<br><br><br><br>
                        ( ________________________ )
                    </td>
                </tr>
            </table>
        </div>

    </div>

    @if (isset($signOption) and $signOption == 1)
        <div style="position: fixed; bottom: 0px; text-align: left;">
            <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara</i></p>
        </div>
    @endif

</body>

</html>
