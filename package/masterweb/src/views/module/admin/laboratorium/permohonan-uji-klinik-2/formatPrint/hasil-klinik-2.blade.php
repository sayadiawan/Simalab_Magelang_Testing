<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {{ $item_permohonan_uji_klinik->getDisplayNoregister() }}</title>
    <link rel="shortcut icon" href="">
    @php

        // dd(count($value_items));
        // dd($arr_permohonan_parameter);

        $value_items = 0;
        foreach ($arr_permohonan_parameter as $item) {
            # code...

            foreach ($item['item_permohonan_parameter_satuan'] as $key => $parameter) {
                # code...
                $value_items++;
            }
            // dd($item);
        }

        if ($value_items < 3) {
            # code...
            $size_table = 10;
        } elseif ($value_items >= 4 && $value_items <= 16) {
            # code...
            $size_table = 9;
        } else {
            $size_table = 8;
        }

    @endphp
    <style>
        .starter-template {
            text-align: center;
        }

        table {
            table-layout: fixed;
            width: 100%;
        }


        td,
        th {
            /* cell-padding: 5px !important; */
            word-wrap: break-word;
            /* This ensures text breaks into the next line */
            white-space: normal;
            /* Allows wrapping */
        }

        @media print {
            #cetak {
                display: none;
            }
        }

        .garis {
            border: 1px solid
        }

        .table2 {
            /* font-size: 5px; */
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
            margin: 20px 20px 20px 20px;
        }

        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            /* font-size: 11px; */
        }

        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: 12px;
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

        .signature {
            width: 100px;
            cursor: grab;
            position: absolute;
            z-index: 1000;
        }

        th {
            margin-top: 2px;
            font-size: {{ $size_table }}pt !important;
        }

        td {
            margin-top: 2px;
            font-size: {{ $size_table }}pt !important;
        }

        .signature {
            width: 100px;
            cursor: grab;
            position: absolute;
            z-index: 1000;
        }
    </style>
</head>


<body>


    <img id="signature" class="signature" src="{{ public_path('assets/admin/images/logo/logo-bsre.png') }}"
        style="left: {{ $positionX }}px; top: {{ $positionY }}px;">

    <br>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td> <img src="{{ public_path('assets/admin/images/logo/kop_magelang.png') }}" width="100%"></td>
        </tr>
    </table>




    <br>
    {{--
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Disampaikan dengan hormat hasil laboratorium klinik kami adalah sebagai berikut:</td>
        </tr>
    </table> --}}

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left; vertical-align: top;">
                <table width="90%" cellspacing="0" cellpadding="0" border="1">

                    <tr>
                        <td width="30%" style="vertical-align: top; padding: 3px; ">
                            Nama
                        </td>
                        <td style="padding-left: 3px;">
                            <strong>{{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien, 'UTF-8') }}</strong>
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top; padding: 3px;">
                            Tanggal Lahir / Umur
                        </td>
                        <td style="padding: 3px;">
                            {{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                                ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                                    'D MMMM Y',
                                )
                                : '' }}
                            / {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                            {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
                            {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari
                        </td>
                    </tr>


                    <tr>
                        <td width="30%" style="vertical-align: top; padding: 3px;">
                            Alamat
                        </td>
                        <td style="padding-left: 3px;">
                            {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
                        </td>
                    </tr>


                </table>
            </td>


            <td style="text-align: right; vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0" border="1">
                    <tr>
                        <td width="30%" style="vertical-align: top; padding-left: 3px;">
                            No Lab
                        </td>
                        <td style="padding-left: 3px;">
                            {!! $no_LHU !!}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top; padding-left: 3px;">
                            Tanggal Register
                        </td>
                        <td style="padding-left: 3px;">
                            {{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)
                                ? \Carbon\Carbon::createFromFormat(
                                    'Y-m-d H:i:s',
                                    $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                                )->isoFormat('D MMMM Y')
                                : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" style="vertical-align: top; padding-left: 3px;">
                            Dokter
                        </td>
                        <td style="padding-left: 3px;">
                            dr. Dini Nurani Kusumastuti, Sp. PK
                        </td>
                    </tr>



                </table>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td></td>
        </tr>
        <tr>
            <td>

            </td>
        </tr>
    </table>

    <br>

    <br>



    <table class="font_dinamic_table" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead style="background-color: rgb(184, 246, 246)">
            <tr>
                <th style="width: 40%;">PEMERIKSAAN</th>
                <th style="width: 15%; text-align: center;">HASIL</th>
                <th style="width: 15%; text-align: center;">SATUAN</th>
                <th style="width: 15%; text-align: center;">NILAI RUJUKAN</th>
                <th style="width: 20%; text-align: center;">METODE</th>
                <th style="width: 40%; text-align: center;">KETERANGAN</th>
            </tr>
        </thead>


        <tbody>
            @php
                $item_permohonan_parameter_satuan = 0; // Variabel untuk menyimpan total jumlah elemen

                // Misalkan $data adalah array yang diberikan
                foreach ($arr_permohonan_parameter as $item) {
                    // Pastikan item_permohonan_parameter_satuan ada dan adalah array
                    if (
                        isset($item['item_permohonan_parameter_satuan']) &&
                        is_array($item['item_permohonan_parameter_satuan'])
                    ) {
                        // Hitung elemen di dalam setiap array item_permohonan_parameter_satuan
                        $item_permohonan_parameter_satuan += count($item['item_permohonan_parameter_satuan']);
                    }
                }

                // Menampilkan total jumlah elemen
                // dd ($arr_permohonan_parameter);

            @endphp
            @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                <tr>
                    <th colspan="6" style="text-align: left; padding-left: 3px;">
                        <strong>{{ $item_parameter_jenis_klinik['name_parameter_jenis_klinik'] }}</strong>
                    </th>
                </tr>
                @foreach ($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] as $key_satuan_klinik => $item_satuan_klinik)
                    @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                        <tr>
                            <td colspan="6" style="text-align: left; padding-left: 3px;">
                                {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}:
                            </td>
                        </tr>

                        {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                        @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                            <tr>
                                {{-- nama test --}}
                                <td style="text-align: center">
                                    <p style="padding-left: 30px">
                                        {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~</p>
                                </td>

                                {{-- hasil --}}
                                <td style="text-align: center">
                                    {{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                </td>

                                {{-- flag --}}


                                {{-- satuan --}}
                                <td style="text-align: center">
                                    @if ($item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] != null)
                                        {{ $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'] }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- nilai rujukan --}}
                                <td style="text-align: center">
                                    {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                </td>

                                {{-- metode --}}
                                <td style="text-align: center">
                                    {{ $item_subsatuan_klinik['method_permohonan_uji_parameter_klinik'] }}
                                </td>

                                {{-- keterangan --}}
                                <td style="text-align: center">
                                    {!! $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '' !!}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr style="">
                            {{-- nama test --}}
                            <td style="text-align: left; padding-left: 3px;">
                                {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                            </td>



                            {{-- hasil --}}
                            <td style="text-align: center">
                                {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                            </td>

                            {{-- flag --}}

                            {{-- satuan --}}
                            {{-- kondisi jika data satuan pada permohonan uji parameter klinik belum terpilih dan suda terpilih --}}
                            <td style="text-align: center">

                                @if ($item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] != null)
                                    {{ $item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik'] }}
                                @else
                                    -
                                @endif

                            </td>

                            {{-- nilai rujukan --}}
                            <td style="text-align: center; font-family: DejaVu Sans !important;">
                                {!! rubahNilaikeHtml($item_satuan_klinik['nilai_baku_mutu']) !!}
                            </td>

                            {{-- metode --}}
                            <td style="text-align: center">
                                {{ $item_satuan_klinik['method_permohonan_uji_parameter_klinik'] }}
                            </td>


                            {{-- keterangan --}}
                            {{-- @if ($item_satuan_klinik['nama_parameter_satuan_klinik'] == 'Cholesterol HDL')

                            @endif --}}
                            <td style="text-align: left; font-family: DejaVu Sans !important; padding-left: 3px;">
                                @php
                                    // Ambil keterangan
                                    $keterangan =
                                        $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? '';

                                    // Hapus tag <br> dan konversi entitas HTML
                                    $keterangan = str_replace('<br>', '', $keterangan);
                                    $keterangan = html_entity_decode($keterangan); // Mengonversi entitas HTML
                                    $keterangan = str_replace('&nbsp;', ' ', $keterangan); // Mengganti &nbsp; dengan spasi

                                    // Trim untuk menghapus spasi di awal dan akhir
                                    $keterangan = trim($keterangan);
                                @endphp

                                @if ($keterangan !== strip_tags($keterangan) && !empty($keterangan))
                                    {{-- Jika string mengandung HTML --}}
                                    {!! rubahNilaikeHtml($keterangan) !!}
                                @else
                                    {{-- Jika string tidak mengandung HTML, tampilkan dengan nl2br --}}
                                    {!! rubahNilaikeHtml(e($keterangan)) !!}
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>

    <br>

    <br>

    <table width="100%" cellspacing="0" cellpadding="0">

        <tr>
            <td width="30%" style="text-align: center">
            </td>
            <td width="30%">

            </td>
            @php
                $validasi = Smt\Masterweb\Models\VerificationActivitySample::where(
                    'is_klinik',
                    $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                )
                    ->where('id_verification_activity', 5)
                    ->first();

                if (isset($validasi)) {
                    # code...
                    $tanggal_validasi = $validasi->stop_date;
                    $nama_petugas_validasi = $validasi->nama_petugas;
                } else {
                    $tanggal_validasi = null;
                    $nama_petugas_validasi = null;
                }
                // dd($tanggal_validasi);
            @endphp
            <td width="30%" style="text-align: center">Kota Mungkid,
                {{ isset($tanggal_validasi)
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $tanggal_validasi)->isoFormat('D MMMM Y')
                    : '-' }}
            </td>
        </tr>
        @php
            $day = Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
            )->dayName;

        @endphp
        @if (isset($validasi) && isset($nama_petugas_validasi))
            @if ($nama_petugas_validasi == 'dr. Muharyati' && ($day != 'Selasa' && $day != "Jum'at"))
                <tr>
                    <td width="30%" style="text-align: center"></td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center">di Otorisasi oleh</td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center">
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center"></td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center"></td>
                    <td width="30%"></td>
                    <td width="30%" style="text-align: center"><u>dr. Muharyati</u> <br> NIP. 19721106 2002 12 2
                        001</td>
                </tr>
            @else
                <tr>
                    <td width="30%" style="text-align: center">Penanggungjawab Lab. Klinik
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center">Petugas</td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center">
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center"></td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center">

                        <span style="text-decoration: underline;">dr. DINI NURANI KUSUMASTUTI. Sp.PK</span> <br> SIP.
                        503.5/0034/SIPD/4.14/I/2024
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center"><u>{{ $nama_petugas_pemeriksa }}</td>
                </tr>
            @endif
        @else
            @if ($day != 'Selasa' && $day != "Jum'at")
                <tr>
                    <td width="30%" style="text-align: center"></td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center">di Otorisasi oleh</td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center">
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center"></td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center"></td>
                    <td width="30%"></td>
                    <td width="30%" style="text-align: center"><u>dr. Muharyati</u> <br> NIP. 19721106 2002 12 2
                        001</td>
                </tr>
            @else
                <tr>
                    <td width="30%" style="text-align: center">Penanggungjawab Lab. Klinik
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center">Petugas</td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center">
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                    </td>
                    <td width="30%">

                    </td>
                    <td width="30%" style="text-align: center"></td>
                </tr>
                <tr>
                    <td width="30%" style="text-align: center">

                        <span style="text-decoration: underline;">dr. DINI NURANI KUSUMASTUTI. Sp.PK</span> <br> SIP.
                        503.5/0034/SIPD/4.14/I/2024
                    </td>
                    <td width="30%">

                    </td>
                    @php
                        $nama_petugas_pemeriksa = '...................';
                    @endphp
                    <td width="30%" style="text-align: center">{{ $nama_petugas_pemeriksa }}</td>
                </tr>
            @endif

        @endif
    </table>
    {{--
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left">PENANGGUNGJAWAB LAB. KLINIK <br> UPT Estu Lentera Indo Teknologi</td>
            <td style="text-align: right">PETUGAS PEMERIKSA</td>
        </tr>

        <tr>
            <td style="height: 80px; vertical-align: bottom; text-align: left">
                Estu Lentera Indo Teknologi <br> Pembina<br>
                NIP. 19721106 200212 2 001
            </td>
            <td style="height: 80px; vertical-align: bottom; text-align: right">
                {{ $item_permohonan_uji_klinik->name_analis_permohonan_uji_klinik ?? '' }} <br>
                {{ 'NIP.' . ($item_permohonan_uji_klinik->nip_analis_permohonan_uji_klinik ?? '') }}
            </td>
        </tr>
    </table> --}}
        <div style="position: fixed; bottom: 0px; text-align: left;">
            <p style="font-size: 12px; margin: 0; padding: 0;"><i>Dokumen ini ditandatangani secara elektronik
                    menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan
                    Siber dan Sandi Negara</i></p>
        </div>
</body>


</html>
