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
    <title>Inform Concern</title>
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
            padding: {{ $padding }}px 2px {{ $padding }}px 2px;
            font-size: {{ $fontsize + 2 }}px;
        }

        .table-clear td {
            border: 0px;
            padding: 0px;
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
                    <td
                        style="
            font-size: 13px;
            font-weight: bold;
            text-align: center;
          ">
                        FOLMULIR PERMINTAAN PEMERIKSAAN
                    </td>
                </tr>
            </table>

            <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="33%">No. REGISTRASI</td>
                    <td width="2%">:</td>
                    <td>{{ $sample->codesample_samples }}</td>
                </tr>
                <tr>
                    <td width="33%" style="vertical-align: top;">NAMA</td>
                    <td width="2%" style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">
                        {{ $sample->name_pelanggan ?? $sample->namaPelangganDisplay() }}
                    </td>
                </tr>
                <tr>
                    <td width="33%" style="vertical-align: top;">ALAMAT</td>
                    <td width="2%" style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">{{ $sample->address_customer }}</td>
                </tr>
                <tr>
                    <td width="33%">JENIS SAMPLE</td>
                    <td width="2%">:</td>
                    @php

                        if (str_contains($sample->codesample_samples, '- F')) {
                            # code...
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
                <tr>
                    <td width="33%">NO. TELP</td>
                    <td width="2%">:</td>
                    <td>{{ $sample->cp_customer }}</td>
                </tr>
            </table>
        </div>

        <hr style="height: 3px; background-color: black;">

        <div style="padding: 0px 30px 0px 30px;">
            <table style="margin: 5px 0px 10px 0px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td
                        style="
            font-size: 13px;
            font-weight: bold;
            text-align: center;
          ">
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

                $rightParams = [
                    ['Borax', ['Borax']],
                    ['Formalin', ['Formalin']],
                    ['Enzim Diastase', ['Enzim Diastase']],
                    ['Hidroksimetil Furfural', ['Hidroksimetil Furfural', 'Hidroximetil Furfural']],
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

                $knownAliases = [];
                foreach (array_merge($leftParams, $rightParams) as [, $aliases]) {
                    foreach ($aliases as $alias) {
                        $knownAliases[$alias] = true;
                    }
                }

                $extraParams = $not_in_list_kimia ?? [];
                foreach ($fisika ?? [] as $param => $checked) {
                    if (!isset($knownAliases[$param])) {
                        $extraParams[$param] = $checked;
                    }
                }

                $extraCol1 = [];
                $extraCol2 = [];
                $extraIdx = 0;
                foreach ($extraParams as $param => $checked) {
                    if ($extraIdx % 2 === 0) {
                        $extraCol1[$param] = $checked;
                    } else {
                        $extraCol2[$param] = $checked;
                    }
                    $extraIdx++;
                }
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
                                        @if (str_contains($sample->name_sample_type, 'Makanan')) checked @endif />
                                </td>
                                <td>Makanan/ minuman</td>
                            </tr>
                        </table>
                    </td>

                    <!-- Parameter kolom kiri -->
                    <td class="compact-cell">
                        <table width="100%" cellspacing="0" cellpadding="1" class="compact-param">
                            @foreach ($leftParams as [$label, $aliases])
                                @php $attr = $paramAttr($aliases); @endphp
                                <tr>
                                    <td width="18px"><input type="checkbox" class="checkbox"
                                            @if ($attr) {!! $attr !!} @endif /></td>
                                    <td>{{ $label }}</td>
                                </tr>
                            @endforeach
                            @foreach ($extraCol1 as $param => $checked)
                                <tr>
                                    <td width="18px"><input type="checkbox" class="checkbox"
                                            {{ $checked }} /></td>
                                    <td>{{ $param }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>

                    <!-- Parameter kolom kanan -->
                    <td class="compact-cell">
                        <table width="100%" cellspacing="0" cellpadding="1" class="compact-param">
                            @foreach ($rightParams as [$label, $aliases])
                                @php $attr = $paramAttr($aliases); @endphp
                                <tr>
                                    <td width="18px"><input type="checkbox" class="checkbox"
                                            @if ($attr) {!! $attr !!} @endif /></td>
                                    <td>{{ $label }}</td>
                                </tr>
                            @endforeach
                            @foreach ($extraCol2 as $param => $checked)
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

            <table style="margin: 10px 0px 15px 0px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td
                        style="
            font-size: 15px;
            font-weight: bold;
            text-align: center;
          ">
                        <u>PERNYATAAN PERSETUJUAN</u>
                    </td>
                </tr>
            </table>

            <table style="font-size: 13px;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: justify;">
                        Dengan ini menyatakan bahwa <b>SETUJU</b> terhadap sampel yang telah diserahkan
                        berupa <b>{{ $sample->name_sample_type }}</b> kepada
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
                    <td
                        style="
            font-size: 15px;
            font-weight: bold;
            text-align: center;
          ">
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
                                <td> <input type="checkbox" class="checkbox"
                                        @if ($penerimaan_sample['kelayakan_tempat_kemasan'] == 'layak') {{ 'checked' }} @endif /></td>
                                <td> <input type="checkbox" class="checkbox"
                                        @if ($penerimaan_sample['kelayakan_tempat_kemasan'] == 'tidak layak') {{ 'checked' }} @endif /></td>
                            </tr>
                            <tr>
                                <td>Berat / Vol</td>
                                <td> <input type="checkbox" class="checkbox"
                                        @if ($penerimaan_sample['kelayakan_berat_vol'] == 'layak') {{ 'checked' }} @endif /></td>
                                <td> <input type="checkbox" class="checkbox"
                                        @if ($penerimaan_sample['kelayakan_berat_vol'] == 'tidak layak') {{ 'checked' }} @endif /></td>
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
                                    {{ $sample->petugas_penerima ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{--    <table style="margin: 0px 0px 9px 0px;" width="100%" cellspacing="0" cellpadding="0"> --}}
            {{--      <tr> --}}
            {{--        <td style="font-size: 13px;"> --}}
            {{--          Hasil pengujian hanya berlaku contoh sampel yang diuji --}}
            {{--        </td> --}}
            {{--      </tr> --}}
            {{--    </table> --}}

            {{--    <table class="table-syarat" width="100%" cellspacing="0" cellpadding="0"> --}}
            {{--      <tr> --}}
            {{--        <td width="20%">Nomor Register</td> --}}
            {{--        <td></td> --}}
            {{--      </tr> --}}
            {{--      <tr> --}}
            {{--        <td>Pengambilan Hasil</td> --}}
            {{--        <td></td> --}}
            {{--      </tr> --}}
            {{--    </table> --}}
            {{--  </div> --}}
            {{-- </div> --}}

            {{-- <div style="padding: 80px 40px 0px 40px;"> --}}
            {{--  <table class="table-syarat" width="100%" cellspacing="0" cellpadding="0"> --}}
            {{--    <tr> --}}
            {{--      <td colspan="5"> --}}
            {{--        <table class="table-clear" width="100%" cellspacing="0" cellpadding="0"> --}}
            {{--          <tr> --}}
            {{--            <td width="5%"></td> --}}
            {{--            <td width="25%" style="border: 1px solid black;" class="text-center">LOGO</td> --}}
            {{--            <td class="text-center"> --}}
            {{--              <table width="100%" cellspacing="0" cellpadding="0"> --}}
            {{--                <tr> --}}
            {{--                  <td style=" --}}
            {{--                      text-align: center; --}}
            {{--                      font-weight: bold; --}}
            {{--                      font-size: 13px; --}}
            {{--                    "> --}}
            {{--                    PEMERINTAH KABUPATEN BOYOLALI --}}
            {{--                  </td> --}}
            {{--                </tr> --}}
            {{--                <tr> --}}
            {{--                  <td style=" --}}
            {{--                      text-align: center; --}}
            {{--                      font-weight: bold; --}}
            {{--                      font-size: 13px; --}}
            {{--                    "> --}}
            {{--                    DINAS KESEHATAN --}}
            {{--                  </td> --}}
            {{--                </tr> --}}
            {{--                <tr> --}}
            {{--                  <td style=" --}}
            {{--                      text-align: center; --}}
            {{--                      font-weight: bold; --}}
            {{--                      font-size: 13px; --}}
            {{--                    "> --}}
            {{--                    LABORATORIUM KESEHATAN --}}
            {{--                  </td> --}}
            {{--                </tr> --}}
            {{--                <tr> --}}
            {{--                  <td style=" --}}
            {{--                      text-align: center; --}}
            {{--                      font-size: 12px; --}}
            {{--                    "> --}}
            {{--                    Komplek Perkantoran Terpadu Kabupaten Boyolali --}}
            {{--                  </td> --}}
            {{--                </tr> --}}
            {{--                <tr> --}}
            {{--                  <td style=" --}}
            {{--                      text-align: center; --}}
            {{--                      font-size: 12px; --}}
            {{--                    "> --}}
            {{--                    Jalan Ahmad Yani No. 1, Siswodipuran, Boyolali 57311 --}}
            {{--                  </td> --}}
            {{--                </tr> --}}
            {{--              </table> --}}
            {{--            </td> --}}
            {{--            <td width="5%"></td> --}}
            {{--          </tr> --}}
            {{--        </table> --}}
            {{--      </td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td colspan="2"> --}}
            {{--        Hari/ Tanggal : --}}
            {{--      </td> --}}
            {{--      <td colspan="3"> --}}
            {{--        No. Reg : --}}
            {{--      </td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td class="text-center"> --}}
            {{--        Jenis Kegiatan --}}
            {{--        <br>Lab Kesmas --}}
            {{--      </td> --}}
            {{--      <td width="18%" class="text-center"> --}}
            {{--        Tgl Mulai<br>/Jam --}}
            {{--      </td> --}}
            {{--      <td width="18%" class="text-center"> --}}
            {{--        Tgl Selesai<br>/Jam --}}
            {{--      </td> --}}
            {{--      <td width="18%" class="text-center"> --}}
            {{--        Nama<br>Petugas --}}
            {{--      </td> --}}
            {{--      <td width="17%" class="text-center"> --}}
            {{--        TTD --}}
            {{--      </td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td> --}}
            {{--        Pengambilan sampel --}}
            {{--      </td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td> --}}
            {{--        Pendaftaran/Registrasi --}}
            {{--      </td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td> --}}
            {{--        Pemeriksaan/Analitik --}}
            {{--      </td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td> --}}
            {{--        Input/Output Hasil Px --}}
            {{--      </td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td> --}}
            {{--        Verifikasi --}}
            {{--      </td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--    </tr> --}}
            {{--    <tr> --}}
            {{--      <td> --}}
            {{--        Validasi --}}
            {{--      </td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--      <td></td> --}}
            {{--    </tr> --}}
            {{--  </table> --}}
        </div>
    @if (isset($signOption) and $signOption == 1)
        <div style="position: fixed; bottom: 0px; text-align: left;">
            <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara</i></p>
        </div>
    @endif
</body>

</html>
