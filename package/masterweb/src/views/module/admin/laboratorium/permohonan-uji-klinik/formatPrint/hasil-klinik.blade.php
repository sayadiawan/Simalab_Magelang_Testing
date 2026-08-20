<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}</title>
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
            margin: 20px 20px 20px 20px;
        }

        @font-face {
            font-family: "source_sans_proregular";
            src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
            font-weight: normal;
            font-style: normal;
            font-size: 11px;
        }

        body {
            font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
            font-size: 11px;
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
    </style>
</head>

<body>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><img src="{{ public_path('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px"></td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="10%">
                Nomor
            </td>
            <td width="1%">
                :
            </td>
            <td>
                {!! $no_LHU !!}
            </td>
            <td align="right">
                Magelang,
                {{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)
                    ? \Carbon\Carbon::createFromFormat(
                        'Y-m-d',
                        $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                    )->isoFormat('D MMMM Y')
                    : '-' }}

            </td>
        </tr>
        <tr>
            <td width="10%">
                Hal
            </td>
            <td width="1%">
                :
            </td>
            <td>
                Hasil Klinik
            </td>
            <td align="right">

            </td>
        </tr>
    </table>

    <br>
    <table width="40%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2">
                Yang Terhormat :
            </td>

        </tr>
        <tr>
            <td colspan="2">
                {{ $item_permohonan_uji_klinik->pasien->nama_pasien }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
            </td>

        </tr>
    </table>
    <br>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Disampaikan dengan hormat hasil laboratorium klinik kami adalah sebagai berikut:</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="text-align: left; vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            No. Register
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Tanggal Register
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ isset($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)
                                ? \Carbon\Carbon::createFromFormat(
                                    'Y-m-d',
                                    $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik,
                                )->isoFormat('D MMMM Y')
                                : '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Nama Pasien
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            <strong>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</strong>
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Jenis Kelamin
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Tanggal Lahir / Umur
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
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
                </table>
            </td>

            <td style="text-align: right; vertical-align: top;">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            No. Pasien
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->format('dmY') . str_pad((int) $item_permohonan_uji_klinik->pasien->no_rekammedis_pasien, 4, '0', STR_PAD_LEFT) }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            No. Telepon
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ $item_permohonan_uji_klinik->pasien->phone_pasien ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Alamat Pasien
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            No. KTP
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Pengirim
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ $item_permohonan_uji_klinik->namapengirim_permohonan_uji_klinik ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="vertical-align: top;">
                            Dokter Perujuk
                        </td>
                        <td width="2%" style="vertical-align: top;">
                            :
                        </td>
                        <td>
                            {{ $item_permohonan_uji_klinik->dokter_permohonan_uji_klinik ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><strong>Hasil Pemeriksaan</strong></td>
        </tr>
        <tr>
            <td>
                <i>(<span style="size: 30pt">*</span>) Hasil diluar nilai rujukan</i>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="width: 30%;">Nama Test</th>
                <th style="width: 15%; text-align: center;">Hasil</th>
                <th style="width: 10%; text-align: center;">Flag</th>
                <th style="width: 10%; text-align: center;">Satuan</th>
                <th style="width: 15%; text-align: center;">Nilai Rujukan</th>
                <th style="width: 20%; text-align: center;">Keterangan</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                <tr>
                    <th colspan="6" style="text-align: left">
                        <strong>{{ $item_parameter_jenis_klinik['name_parameter_jenis_klinik'] }}</strong>
                    </th>
                </tr>
                @foreach ($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] as $key_satuan_klinik => $item_satuan_klinik)
                    @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                        <tr>
                            <td colspan="6" style="text-align: left">
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
                                <td style="text-align: center">
                                    {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                                        ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                                        : '' !!}

                                </td>

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

                                {{-- keterangan --}}
                                <td style="text-align: center">
                                    {{ $item_subsatuan_klinik['keterangan_permohonan_uji_sub_parameter_klinik'] ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            {{-- nama test --}}
                            <td style="text-align: left">
                                {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                            </td>

                            {{-- hasil --}}
                            <td style="text-align: center">
                                {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                            </td>

                            {{-- flag --}}
                            <td style="text-align: center">
                                {!! $item_satuan_klinik['flag_permohonan_uji_parameter_klinik']
                                    ? '<h4 style="margin-top: 0!important;margin-bottom: 0!important;">*</h4>'
                                    : '<h4 style="margin-top: 0!important;margin-bottom: 0!important;visibility: hidden;">*</h4>' !!}

                            </td>

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
                            <td style="text-align: center">
                                {{ $item_satuan_klinik['nilai_baku_mutu'] }}
                            </td>

                            {{-- keterangan --}}
                            <td style="text-align: center">
                                {{ $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'] ?? '' }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 5px; margin-top: 5px;">
        <tr>
            <td colspan="2">Waktu pengambilan spesimen:</td>
        </tr>

        <tr>
            <td style="width: 100px">Darah</td>
            <td style="width: 10px">:</td>
            <td>{{ $tgl_spesimen_darah }}</td>
        </tr>

        <tr>
            <td style="width: 100px">Urine</td>
            <td style="width: 10px">:</td>
            <td>{{ $tgl_spesimen_urine }}</td>
        </tr>
    </table>

    @if ($item_permohonan_uji_klinik->permohonanujianalisklinik)
        @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik))
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-size: 30px; text-decoration: underline" colspan="6">
                        <strong>KESIMPULAN</strong>
                    </td>
                </tr>

                <tr>
                    <td class="align-top" colspan="6">
                        {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->kesimpulan_permohonan_uji_analis_klinik !!}
                    </td>
                </tr>
            </table>
        @endif

        @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik))
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-size: 30px; text-decoration: underline">
                        <strong>KATEGORI</strong>
                    </td>
                </tr>

                <tr>
                    <td class="align-top">
                        {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->kategori_permohonan_uji_analis_klinik !!}
                    </td>
                </tr>
            </table>
        @endif

        @if (isset($item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik))
            <table width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-size: 30px; text-decoration: underline">
                        <strong>SARAN</strong>
                    </td>
                </tr>

                <tr>
                    <td class="align-top">
                        {!! $item_permohonan_uji_klinik->permohonanujianalisklinik->saran_permohonan_uji_analis_klinik !!}
                    </td>
                </tr>
            </table>
        @endif
    @endif

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px">
        <tr>
            <td style="width: 35%" style="text-align: left">Mengetahui:</td>
        </tr>
    </table>

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
    </table>
</body>

</html>
