<html lang="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hasil Klinik - {!! $no_LHU !!}</title>
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
    </style>
</head>

<body>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td><img src="{{ asset('assets/admin/images/logo/kop_perusahaan.png') }}" width="730px"></td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px">
        <tr>
            <td style="text-align: center">
                <h2>SURAT KETERANGAN</h2>
                Nomor: {!! $no_LHU !!}
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="4">Yang bertanda tangan di bawah ini:</td>
        </tr>

        <tr>
            <td width="5%">1.</td>
            <td style="width: 20%">Nama</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->dokter->name }}</td>
        </tr>

        <tr>
            <td width="5%">2.</td>
            <td style="width: 20%">NIP</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->dokter->nip_users }}</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="4">Dengan ini memberi keterangan bahwa:</td>
        </tr>

        <tr>
            <td width="5%">1.</td>
            <td style="width: 20%">Nama</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->nama_pasien }}</td>
        </tr>

        <tr>
            <td width="5%">2.</td>
            <td style="width: 20%">Jenis Kelamin</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->gender_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>

        <tr>
            <td width="5%">3.</td>
            <td style="width: 20%">Umur</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                {{ $item_permohonan_uji_klinik->umurbulan_pasien_permohonan_uji_klinik ?? '-' }} Bulan
                {{ $item_permohonan_uji_klinik->umurhari_pasien_permohonan_uji_klinik ?? '-' }} Hari</td>
        </tr>

        <tr>
            <td width="5%">4.</td>
            <td style="width: 20%">Alamat</td>
            <td width="1%">:</td>
            <td>{{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}</td>
        </tr>

        <tr>
            <td width="5%">5.</td>
            <td style="width: 20%">NIK</td>
            <td width="1%">:</td>
            <td>{{ $item_permohonan_uji_klinik->pasien->nik_pasien ?? '-' }}</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="padding-right: 20px">
                Telah dilakukan pemeriksaan laboratorium dengan metode Pemeriksaan RT-PCR di Laboratorium rujukan dengan
                hasil sebagai berikut :
            </td>
        </tr>
        <tr>
            <td>
                <i>(<span style="color: red">*</span>) Hasil diluar nilai rujukan</i>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="width: 20%">JENIS PEMERIKSAAN</th>
                <th style="width: 20%; text-align: center;">NILAI RUJUKAN</th>
                <th style="width: 20%; text-align: center;">HASIL</th>
                <th style="width: 20%; text-align: center;">TANGGAL DIPERIKSA</th>
                <th style="width: 20%; text-align: center;">LABORATORIUM PEMERIKSAAN</th>
            </tr>
        </thead>

        <tbody>
            {{-- panggil semua paket yang ada untuk mengeluarkan parameter satuan dan parameter sub satuan --}}
            @if (count($arr_permohonan_parameter) > 0)
                {{-- mengeluarkan data semua permhonanuji yang ada di tb_permohonan_uji_paket_klinik --}}
                @foreach ($arr_permohonan_parameter as $key_paket => $item_paket)
                    {{-- pada analis tidak memerlukan kondisi untuk mengeluarkan tipe paket atau custom --}}
                    {{-- kondisi apkah ada data paket yang memiliki parameter satuan --}}
                    @if (count($item_paket['data_permohonan_uji_satuan_klinik']) > 0)
                        {{-- mapping data parameter satuan dari paket yang dipilih --}}
                        @foreach ($item_paket['data_permohonan_uji_satuan_klinik'] as $key_satuan_klinik => $item_satuan_klinik)
                            {{-- pada analis hanya mengeluarkan kondisi apakah parameter satuan memiliki data subparameter atau hanya
      parameter satuan saja --}}
                            @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik']) > 0)
                                <tr>
                                    <th colspan="6" style="text-align: left">
                                        @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                            <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                            {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                        @else
                                            {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}: ~
                                        @endif
                                    </th>
                                </tr>

                                {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                                @foreach ($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                    <tr>
                                        <td style="width: 20%; text-align: center">
                                            <p style="padding-left: 30px">
                                                {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }} ~
                                            </p>
                                        </td>

                                        <td style="width: 20%; text-align: center">
                                            {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                        </td>

                                        <td style="width: 20%; text-align: center">
                                            {{ $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? '-' }}
                                        </td>

                                        <td style="width: 20%; text-align: center">
                                            {{ $tgl_pengujian }}
                                        </td>

                                        <td style="width: 20%; text-align: center">
                                            -
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <th style="width: 20%; text-align: left">
                                        @if ($item_paket['nama_parameter_paket_klinik'] != null)
                                            <strong>{{ $item_paket['nama_parameter_paket_klinik'] }}</strong> -
                                            {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                        @else
                                            {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                        @endif
                                    </th>

                                    <td style="width: 20%; text-align: center">
                                        {{ $item_satuan_klinik['nilai_baku_mutu'] }}
                                    </td>

                                    <td style="width: 20%; text-align: center">
                                        {{ $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? '-' }}
                                    </td>

                                    <td style="width: 20%; text-align: center">
                                        {{ $tgl_pengujian }}
                                    </td>

                                    <td style="width: 20%; text-align: center">
                                        -
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" border="1" style="margin-top: 5px">
        <thead>
            <tr>
                <th style="width: 20%">CT</th>
                <th style="width: 20%">HASIL</th>
                <th style="width: 20%">RUJUKAN</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Gen 1 : ORF1ab</td>
                <td></td>
                <td rowspan="2" style="text-align: center">>36</td>
            </tr>
            <tr>
                <td>Gen 2 : E</td>
                <td></td>
            </tr>
        </tbody>
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

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>Yang bersangkutan dianjurkan untuk tetap menerapkan protokol kesehatan.</td>
        </tr>

        <tr>
            <td>Demikian Surat Keterangan ini dibuat untuk dapat digunakan sebagaimana mestinya</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 5px">
        <tr>
            <td style="text-align: right">Magelang, {{ fdate_sas(date('Y-m-d'), 'DDMMYYYY') }}</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 5px">
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
